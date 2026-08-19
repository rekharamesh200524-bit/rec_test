<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AiInterviewQuestionGenerator Library v3
 *
 * Real AI-Powered (Google Gemini) Candidate-Personalized Interview Question Engine.
 * Features Real Resume Project Extraction, Must-Have Skill Priority, and Multi-Level Deduplication.
 */
class AiInterviewQuestionGenerator {

    protected $ci;
    protected $aiEnabled  = false;
    protected $apiKey     = '';
    protected $model      = 'gemini-3.6-flash';
    protected $timeout    = 45;
    protected $maxRetries = 2;

    public function __construct() {
        $this->ci = get_instance();
        if (isset($this->ci->load)) {
            $this->ci->load->database();
        }
        $this->_loadAiConfig();
    }

    // =========================================================================
    // CONFIG
    // =========================================================================

    private function _loadAiConfig() {
        if (!file_exists(APPPATH . 'config/ai_config.php')) return;
        $config = [];
        include APPPATH . 'config/ai_config.php';

        $provider   = $config['ai_provider']    ?? 'gemini';
        $enabled    = $config['ai_enabled']    ?? false;
        $key        = $config['ai_api_key']    ?? '';
        $model      = $config['ai_model']      ?? 'gemini-3.6-flash';
        $timeout    = $config['ai_timeout']    ?? 45;
        $maxRetries = $config['ai_max_retries'] ?? 2;

        if (empty($key)) { $key = getenv('GEMINI_API_KEY') ?: ''; }

        $this->aiEnabled  = ($enabled && $provider === 'gemini' && !empty($key));
        $this->apiKey     = $key;
        $this->model      = $model;
        $this->timeout    = (int)$timeout;
        $this->maxRetries = (int)$maxRetries;
    }

    // =========================================================================
    // PUBLIC: GENERATE
    // =========================================================================

    public function generateForInterview($interviewId, $createdBy = null, $isRegeneration = false) {
        $interviewId = (int)$interviewId;
        if (empty($interviewId)) {
            return ['status' => 'error', 'message' => 'Invalid Interview ID specified.'];
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(60);
        }

        try {
            // 1. Interview
            $interview = $this->ci->db
                ->select('ci.*, ja.CandidateId, ja.Jid, ja.CurrentStage, ja.CurrentStatus')
                ->from('CandidateInterviews ci')
                ->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId', 'inner')
                ->where('ci.InterviewId', $interviewId)
                ->get()->row_array();
            if (empty($interview)) {
                return ['status' => 'error', 'message' => 'Interview record not found.'];
            }

            $candidateId = (int)$interview['CandidateId'];
            $vacancyId   = (int)$interview['Jid'];

            // 2. Candidate
            $candidate = $this->ci->db->where('CandidateId', $candidateId)->get('IHrCandidates')->row_array();
            if (empty($candidate)) {
                return ['status' => 'error', 'message' => 'Candidate record not found.'];
            }

            // 3. Vacancy
            $vacancy = $this->ci->db->where('Jid', $vacancyId)->get('IHRJobsList')->row_array();
            if (empty($vacancy)) {
                return ['status' => 'error', 'message' => 'Job vacancy record not found.'];
            }

            // 4. Skill groups
            $skillGroups  = $this->extractSkillGroups($candidate, $vacancy);

            // 5. Candidate context (with Real Resume Project Extraction)
            $candidateCtx = $this->extractCandidateContext($candidate, $vacancy, $skillGroups);

            // 6. Difficulty
            $difficulty   = $this->determineDifficulty($candidate, $vacancy);

            // 7. Previous questions
            $prevCand    = $this->fetchPreviousQuestions($candidateId, $vacancyId, null);
            $prevVacancy = $this->fetchPreviousQuestions(null,        $vacancyId, $candidateId);
            $allPrev     = array_merge($prevCand, $prevVacancy);

            // 8. Generation version
            $maxVerRow   = $this->ci->db->select('MAX(generation_version) as max_ver')
                ->where('interview_id', $interviewId)->get('ai_interview_questions')->row_array();
            $nextVersion = !empty($maxVerRow['max_ver']) ? ((int)$maxVerRow['max_ver'] + 1) : 1;

            // 9. Budget
            $budget = $this->allocateQuestionBudget($skillGroups);

            // 10. Generate
            $source    = 'ai';
            $questions = [];
            $uncovered = [];
            $aiError   = null;

            if ($this->aiEnabled) {
                for ($attempt = 1; $attempt <= ($this->maxRetries + 1); $attempt++) {
                    $aiResult = $this->callGemini($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $prevCand, $prevVacancy, $nextVersion);
                    if ($aiResult['status'] === 'success') {
                        $validated = $this->validateAndFillGaps($aiResult['questions'], $skillGroups, $candidateCtx, $difficulty, $allPrev, $budget);
                        $questions = $this->deduplicateQuestions($validated, $allPrev);
                        $uncovered = $this->findUncoveredMustHaveSkills($questions, $skillGroups['must_have']);
                        if (!empty($questions)) break;
                    } else {
                        $aiError = $aiResult['message'] ?? 'AI generation failed.';
                        if (!empty($aiResult['skip_retry'])) {
                            break; // Immediately switch to candidate-personalized fallback engine on rate-limit/auth error
                        }
                    }
                }
            }


            if (empty($questions)) {
                $source   = 'fallback';
                $fbResult = $this->fallbackGenerate($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $allPrev, $nextVersion);
                $questions = $fbResult['questions'];
                $uncovered = $this->findUncoveredMustHaveSkills($questions, $skillGroups['must_have']);
                if (!empty($aiError)) {
                    log_message('error', '[AiInterviewQuestionGenerator] AI failed: ' . $aiError . ' — using fallback.');
                }
            }

            if (empty($questions)) {
                return ['status' => 'error', 'message' => 'Failed to generate interview questions. Please try again.'];
            }

            // 11. Persist
            if ($isRegeneration || $nextVersion > 1) {
                $this->ci->db->where('interview_id', $interviewId)->update('ai_interview_questions', ['is_active' => 0]);
            }

            $now = date('Y-m-d H:i:s');
            $insertedQuestions = [];

            foreach ($questions as $q) {
                $qText = isset($q['question']) ? trim($q['question']) : '';
                if (empty($qText)) continue;

                $qHash   = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', $qText)));
                $qType   = !empty($q['type'])       ? strtolower(trim($q['type']))       : 'technical';
                $qDiff   = !empty($q['difficulty']) ? strtolower(trim($q['difficulty'])) : strtolower($difficulty);
                $skills  = !empty($q['skills'])     ? (is_array($q['skills']) ? implode(', ', $q['skills']) : $q['skills']) : ($q['skill'] ?? '');
                $qReason = !empty($q['reason'])     ? trim($q['reason']) : '';

                $validTypes = ['must_have_skill','technical','candidate_specific','scenario','behavioral','role_specific'];
                if (!in_array($qType, $validTypes)) $qType = 'technical';

                $insertData = [
                    'candidate_id'       => $candidateId,
                    'vacancy_id'         => $vacancyId,
                    'interview_id'       => $interviewId,
                    'question'           => $qText,
                    'question_type'      => $qType,
                    'difficulty'         => $qDiff,
                    'skill'              => $skills,
                    'reason'             => $qReason,
                    'personalized'       => 1,
                    'generation_version' => $nextVersion,
                    'is_active'          => 1,
                    'question_hash'      => $qHash,
                    'status_notes'       => 'unasked',
                    'created_by'         => $createdBy,
                    'created_at'         => $now,
                ];

                $this->ci->db->insert('ai_interview_questions', $insertData);
                $insertData['id'] = $this->ci->db->insert_id();
                $insertedQuestions[] = $insertData;
            }

            return [
                'status'                     => 'success',
                'interview_id'               => $interviewId,
                'candidate_name'             => $candidate['Fullname'] ?? 'Candidate',
                'job_title'                  => $vacancy['JobTitle'] ?? '',
                'ats_score'                  => $candidate['ProfileMatchPer'] ?? 'N/A',
                'generation_version'         => $nextVersion,
                'difficulty'                 => $difficulty,
                'source'                     => $source,
                'total_questions'            => count($insertedQuestions),
                'covered_must_have_skills'   => array_values(array_diff($skillGroups['must_have'], $uncovered)),
                'uncovered_must_have_skills' => $uncovered,
                'covered_projects'           => array_column($candidateCtx['projects'], 'name'),
                'questions'                  => $insertedQuestions,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[AiInterviewQuestionGenerator Throwable] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return ['status' => 'error', 'message' => 'AI question generation encountered an error: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // PUBLIC: GET QUESTIONS
    // =========================================================================

    public function getQuestionsForInterview($interviewId, $version = null) {
        $interviewId = (int)$interviewId;
        $this->ci->db->where('interview_id', $interviewId);
        if ($version !== null) {
            $this->ci->db->where('generation_version', (int)$version);
        } else {
            $this->ci->db->where('is_active', 1);
        }
        $this->ci->db->order_by('id', 'ASC');
        $rows = $this->ci->db->get('ai_interview_questions')->result_array();

        if (empty($rows) && $version === null) {
            $rows = $this->ci->db
                ->where('interview_id', $interviewId)
                ->order_by('generation_version', 'DESC')
                ->order_by('id', 'ASC')
                ->get('ai_interview_questions')->result_array();
        }
        return $rows;
    }

    // =========================================================================
    // STEP: EXTRACT SKILL GROUPS
    // =========================================================================

    private function extractSkillGroups($candidate, $vacancy) {
        $mustHave = $this->parseSkillList($vacancy['MustHaveSkills']  ?? '');
        $niceHave = $this->parseSkillList($vacancy['NiceToHaveSkills'] ?? '');

        $scoreData     = [];
        $matchedSkills = [];
        $missingSkills = [];

        if (!empty($candidate['ScoreBreakdown'])) {
            $scoreData = json_decode($candidate['ScoreBreakdown'], true) ?: [];
        }

        if (!empty($scoreData['relevant_evidence'])) {
            foreach ($scoreData['relevant_evidence'] as $ev) {
                if (preg_match('/Matched Core Skills:\s*(.+)/i', $ev, $m)) {
                    $matchedSkills = array_merge($matchedSkills, $this->parseSkillList($m[1]));
                }
                if (preg_match('/Extracted Resume Skills:\s*(.+)/i', $ev, $m)) {
                    $matchedSkills = array_merge($matchedSkills, $this->parseSkillList($m[1]));
                }
            }
        }
        if (empty($matchedSkills) && !empty($candidate['MatchedSkills'])) {
            $matchedSkills = $this->parseSkillList($candidate['MatchedSkills']);
        }
        $matchedSkills = array_unique($matchedSkills);

        if (!empty($scoreData['missing_requirements'])) {
            foreach ($scoreData['missing_requirements'] as $mr) {
                if (preg_match('/Required Skills Not Found:\s*(.+)/i', $mr, $m)) {
                    $missingSkills = array_merge($missingSkills, $this->parseSkillList($m[1]));
                }
            }
        }
        $missingSkills = array_unique($missingSkills);

        $matchedMustHave = [];
        $missingMustHave = [];
        $evidenceMap     = [];
        $matchedLower    = array_map('strtolower', $matchedSkills);
        $missingLower    = array_map('strtolower', $missingSkills);

        foreach ($mustHave as $skill) {
            $lSkill = strtolower($skill);
            if (in_array($lSkill, $matchedLower)) {
                $matchedMustHave[] = $skill;
                $evidenceMap[$skill] = 'strong';
            } elseif (in_array($lSkill, $missingLower)) {
                $missingMustHave[] = $skill;
                $evidenceMap[$skill] = 'none';
            } else {
                $evidenceMap[$skill] = 'weak';
            }
        }

        return [
            'must_have'         => $mustHave,
            'nice_to_have'      => $niceHave,
            'candidate_skills'  => $matchedSkills,
            'matched_must_have' => $matchedMustHave,
            'missing_must_have' => $missingMustHave,
            'evidence_map'      => $evidenceMap,
            'score_breakdown'   => $scoreData,
        ];
    }

    // =========================================================================
    // STEP: EXTRACT CANDIDATE CONTEXT & REAL RESUME PROJECTS
    // =========================================================================

    private function extractCandidateContext($candidate, $vacancy, $skillGroups) {
        $expDetails = [];
        $jobs       = [];
        $totalExp   = (float)($candidate['ExpYrs'] ?? 0);

        if (!empty($candidate['ExperienceDetails'])) {
            $decoded = json_decode($candidate['ExperienceDetails'], true);
            if (is_array($decoded)) {
                $expDetails = $decoded;
                $jobs       = $decoded['jobs'] ?? [];
            }
        }

        // --- Extract Projects from Resume PDF and Experience Details ---
        $extractedProj = $this->extractProjectsFromResume($candidate, $jobs);
        $projects      = $extractedProj['projects'];
        $rawResumeText = $extractedProj['raw_text'];

        $scoreData            = $skillGroups['score_breakdown'];
        $relevantEvidence     = $scoreData['relevant_evidence']    ?? [];
        $recommendationReason = $scoreData['recommendation_reason'] ?? '';
        $recommendation       = $scoreData['recommendation']       ?? '';

        $resumeCtxParts = [];
        if ($totalExp > 0) $resumeCtxParts[] = "Total Experience: {$totalExp} years";

        foreach ($jobs as $job) {
            $from = $job['from'] ?? '';
            $to   = $job['to']   ?? 'present';
            $yrs  = $job['years'] ?? 0;
            $mon  = $job['months'] ?? 0;
            if (!empty($from)) {
                $resumeCtxParts[] = "Work Experience: {$from} to {$to} ({$yrs}y {$mon}m)";
            }
        }

        if (!empty($projects)) {
            $resumeCtxParts[] = "\n--- CANDIDATE FEATURED PROJECTS ---";
            foreach ($projects as $p) {
                $pStr = "Project Name: {$p['name']}";
                if (!empty($p['description'])) $pStr .= " | Description: {$p['description']}";
                if (!empty($p['details'])) $pStr .= "\n  Details: " . implode('; ', $p['details']);
                $resumeCtxParts[] = $pStr;
            }
        }

        foreach ($relevantEvidence as $ev) { $resumeCtxParts[] = $ev; }
        if (!empty($recommendationReason)) $resumeCtxParts[] = "ATS Assessment: {$recommendationReason}";

        return [
            'name'                 => $candidate['Fullname'] ?? 'Candidate',
            'experience_years'     => $totalExp,
            'jobs'                 => $jobs,
            'projects'             => $projects,
            'raw_resume_text'      => substr($rawResumeText, 0, 3500),
            'candidate_skills'     => $skillGroups['candidate_skills'],
            'relevant_evidence'    => $relevantEvidence,
            'recommendation'       => $recommendation,
            'recommendation_reason'=> $recommendationReason,
            'resume_context'       => implode("\n", $resumeCtxParts),
            'education_match'      => $candidate['EducationMatch']  ?? '',
            'experience_match'     => $candidate['ExperienceMatch'] ?? '',
        ];
    }

    /**
     * Safely parse projects from candidate PDF resume file using vendor PDF parser,
     * with fallback to ExperienceDetails if PDF is scanned or missing.
     */
    private function extractProjectsFromResume($candidate, $jobs = []) {
        $projects = [];
        $rawText  = '';

        // 1. Check ResumePath PDF
        if (!empty($candidate['ResumePath'])) {
            $pdfPath = FCPATH . $candidate['ResumePath'];
            if (!file_exists($pdfPath)) {
                $pdfPath = 'd:/xampp/htdocs/REC/' . ltrim($candidate['ResumePath'], '/');
            }
            if (file_exists($pdfPath)) {
                try {
                    if (class_exists('\Smalot\PdfParser\Parser')) {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf    = $parser->parseFile($pdfPath);
                        $rawText= $pdf->getText();
                    }
                } catch (\Throwable $e) {
                    log_message('error', '[AiInterviewQuestionGenerator] PDF Parse Exception: ' . $e->getMessage());
                }
            }
        }

        // 2. Extract projects from parsed resume text
        if (!empty($rawText)) {
            if (preg_match('/(?:Featured\s+Projects|Projects|Key\s+Projects|Academic\s+Projects|Project\s+Details)\s*[:\-\n]([\s\S]+?)(?:\n\s*\n[A-Z][a-z]+|\z)/i', $rawText, $pm)) {
                $projBlock = $pm[1];
                $lines     = explode("\n", $projBlock);
                $currProj  = null;

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Match project title line e.g., "LangMaster – Full Stack E-Learning Platform"
                    if (preg_match('/^([A-Z0-9][A-Za-z0-9\s\-_]{2,35})\s*[–\-\:]\s*(.+)/u', $line, $tm)) {
                        if ($currProj) $projects[] = $currProj;
                        $currProj = [
                            'name'        => trim($tm[1]),
                            'description' => trim($tm[2]),
                            'details'     => []
                        ];
                    } elseif ($currProj && (strpos($line, '•') === 0 || strpos($line, '-') === 0 || strpos($line, '*') === 0)) {
                        $currProj['details'][] = trim(ltrim($line, '•-* '));
                    }
                }
                if ($currProj) $projects[] = $currProj;
            }
        }

        // 3. Fallback: Check jobs / ExperienceDetails if no PDF projects were parsed
        if (empty($projects) && !empty($jobs)) {
            foreach ($jobs as $i => $j) {
                $projName = $j['company'] ?? ($j['role'] ?? ("Role #" . ($i + 1)));
                $projects[] = [
                    'name'        => $projName,
                    'description' => "Work role from " . ($j['from'] ?? '') . " to " . ($j['to'] ?? 'present'),
                    'details'     => array_filter([$j['details'] ?? ''])
                ];
            }
        }

        return [
            'raw_text' => $rawText,
            'projects' => $projects
        ];
    }

    // =========================================================================
    // STEP: DETERMINE DIFFICULTY
    // =========================================================================

    private function determineDifficulty($candidate, $vacancy) {
        $expYrs   = (float)($candidate['ExpYrs'] ?? 0);
        $combined = strtolower(($vacancy['JobTitle'] ?? '') . ' ' . ($vacancy['JobDescription'] ?? ''));

        $seniority = 'mid';
        if (preg_match('/\b(intern|trainee|fresher|graduate|entry)\b/', $combined))       $seniority = 'junior';
        elseif (preg_match('/\b(junior|associate)\b/', $combined))                        $seniority = 'junior';
        elseif (preg_match('/\b(senior|lead|sr\.?\s|principal|architect|staff|head)\b/', $combined)) $seniority = 'senior';
        elseif (preg_match('/\b(manager|director|vp|vice president)\b/', $combined))      $seniority = 'senior';

        if ($seniority === 'junior') {
            return ($expYrs <= 1) ? 'Beginner' : 'Intermediate';
        } elseif ($seniority === 'senior') {
            if ($expYrs <= 2)     return 'Intermediate';
            elseif ($expYrs <= 5) return 'Intermediate-Advanced';
            else                  return 'Advanced';
        } else {
            if ($expYrs <= 1.5)   return 'Beginner';
            elseif ($expYrs <= 3.5) return 'Intermediate';
            elseif ($expYrs <= 6) return 'Intermediate-Advanced';
            else                  return 'Advanced';
        }
    }

    // =========================================================================
    // STEP: FETCH PREVIOUS QUESTIONS
    // =========================================================================

    private function fetchPreviousQuestions($candidateId, $vacancyId, $excludeCandidateId = null) {
        $this->ci->db->select('question')->where('vacancy_id', $vacancyId);
        if ($candidateId !== null) $this->ci->db->where('candidate_id', $candidateId);
        if ($excludeCandidateId !== null) $this->ci->db->where('candidate_id !=', (int)$excludeCandidateId);
        $rows = $this->ci->db->get('ai_interview_questions')->result_array();
        return array_filter(array_column($rows, 'question'));
    }

    // =========================================================================
    // STEP: ALLOCATE BUDGET
    // =========================================================================

    private function allocateQuestionBudget($skillGroups) {
        $mustCount = count($skillGroups['must_have']);
        $total     = 12;

        if ($mustCount === 0) {
            return ['must_have' => 0, 'candidate_specific' => 4, 'scenario' => 4, 'behavioral' => 4, 'total' => $total];
        }

        // Guarantee EVERY Must-Have Skill gets a dedicated slot
        $mustSlots = $mustCount;
        $remaining = max(4, $total - $mustSlots);
        $candSlots = (int)ceil($remaining * 0.40);
        $scenSlots = (int)ceil($remaining * 0.35);
        $behSlots  = max(1, $remaining - $candSlots - $scenSlots);

        return [
            'must_have'          => $mustSlots,
            'candidate_specific' => $candSlots,
            'scenario'           => $scenSlots,
            'behavioral'         => $behSlots,
            'total'              => $mustSlots + $candSlots + $scenSlots + $behSlots,
        ];
    }

    // =========================================================================
    // STEP: CALL GEMINI
    // =========================================================================

    private function callGemini($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $prevCand, $prevVacancy, $version) {
        if (!$this->aiEnabled || empty($this->apiKey)) {
            return ['status' => 'error', 'message' => 'AI not configured.'];
        }

        $prompt  = $this->buildGeminiPrompt($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $prevCand, $prevVacancy, $version);
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $payload = json_encode([
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.85, 'topP' => 0.95, 'maxOutputTokens' => 4096],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            return ['status' => 'error', 'message' => "cURL error: {$curlError}", 'skip_retry' => true];
        }

        // Handle model fallback if configured model returned 404
        if ($httpCode === 404 && $this->model !== 'gemini-flash-latest') {
            log_message('error', "[Gemini Model 404] Retrying with model 'gemini-flash-latest'");
            $urlFallback = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$this->apiKey}";
            $chF = curl_init($urlFallback);
            curl_setopt_array($chF, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $rawResponse = curl_exec($chF);
            $httpCode    = curl_getinfo($chF, CURLINFO_HTTP_CODE);
            curl_close($chF);
        }

        if ($httpCode !== 200) {
            log_message('error', "[Gemini] HTTP {$httpCode}: " . substr($rawResponse, 0, 500));
            // Fast fail on rate limit (429), auth error (401/403), or server unavailable (503) so engine uses fallback instantly without timing out PHP
            $isQuotaOrAuth = in_array($httpCode, [429, 401, 403, 503]);
            return ['status' => 'error', 'message' => "Gemini API returned HTTP {$httpCode}.", 'skip_retry' => $isQuotaOrAuth];
        }

        $apiResponse = json_decode($rawResponse, true);
        $aiText      = $apiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (empty($aiText)) {
            return ['status' => 'error', 'message' => 'Gemini returned an empty response.'];
        }

        // Strip markdown code fences if present
        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/i', $aiText, $m)) {
            $aiText = $m[1];
        }
        $aiText = trim($aiText);
        if (preg_match('/(\{[\s\S]+\})/s', $aiText, $jm)) {
            $aiText = $jm[1];
        }

        $parsed = json_decode($aiText, true);
        if (!is_array($parsed) || empty($parsed['questions'])) {
            log_message('error', '[Gemini] Invalid JSON response: ' . substr($aiText, 0, 500));
            return ['status' => 'error', 'message' => 'AI returned invalid JSON structure.'];
        }

        return [
            'status'                     => 'success',
            'questions'                  => $parsed['questions'],
            'covered_must_have_skills'   => $parsed['covered_must_have_skills']   ?? [],
            'covered_projects'           => $parsed['covered_projects']           ?? [],
            'uncovered_must_have_skills' => $parsed['uncovered_must_have_skills'] ?? [],
        ];
    }

    // =========================================================================
    // STEP: BUILD GEMINI PROMPT (With Real Project Requirements)
    // =========================================================================

    private function buildGeminiPrompt($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $prevCand, $prevVacancy, $version) {
        $jobTitle    = $vacancy['JobTitle'] ?? 'Position';
        $jobDesc     = substr($vacancy['JobDescription'] ?? '', 0, 600);
        $expMin      = $vacancy['ExpMin'] ?? 0;
        $expMax      = $vacancy['ExpMax'] ?? 0;
        $mustHave    = $skillGroups['must_have'];
        $niceHave    = $skillGroups['nice_to_have'];
        $evidenceMap = $skillGroups['evidence_map'];
        $candName    = $candidateCtx['name'];
        $expYrs      = $candidateCtx['experience_years'];
        $resumeCtx   = $candidateCtx['resume_context'];
        $projects    = $candidateCtx['projects'];
        $totalQ      = $budget['total'];
        $mustSlots   = $budget['must_have'];
        $candSlots   = $budget['candidate_specific'];
        $scenSlots   = $budget['scenario'];
        $behSlots    = $budget['behavioral'];

        $prevCandStr    = empty($prevCand)    ? 'None' : "- " . implode("\n- ", array_slice(array_values($prevCand), -20));
        $prevVacancyStr = empty($prevVacancy) ? 'None' : "- " . implode("\n- ", array_slice(array_values($prevVacancy), -20));

        $skillEvidenceLns = [];
        foreach ($mustHave as $skill) {
            $ev = $evidenceMap[$skill] ?? 'weak';
            $skillEvidenceLns[] = "- {$skill}: {$ev} evidence";
        }
        $skillEvidenceStr = implode("\n", $skillEvidenceLns) ?: 'N/A';
        $mustHaveStr  = empty($mustHave)  ? 'None specified' : implode(', ', $mustHave);
        $niceHaveStr  = empty($niceHave)  ? 'None'           : implode(', ', $niceHave);
        $candSkillStr = empty($candidateCtx['candidate_skills']) ? 'Not extracted' : implode(', ', $candidateCtx['candidate_skills']);

        // Structured projects string
        $projStrLns = [];
        if (!empty($projects)) {
            foreach ($projects as $p) {
                $pL = "- Project Name: \"{$p['name']}\"\n  Description: {$p['description']}";
                if (!empty($p['details'])) {
                    $pL .= "\n  Bullet Points: " . implode('; ', $p['details']);
                }
                $projStrLns[] = $pL;
            }
        }
        $projStr = implode("\n\n", $projStrLns) ?: 'No explicit projects section found in resume';

        $regenNote = ($version > 1)
            ? "\n\nIMPORTANT: This is REGENERATION #{$version}. Do NOT repeat or substantially paraphrase any previous questions listed above. Generate a genuinely different set covering the same Must-Have Skills."
            : '';

        $isTech = $this->isTechnicalRole($vacancy, $candidateCtx);
        $roleDomain = $isTech ? "Technical/Software Engineering" : "Non-Technical / Human Resources / Management / Business Operations";

        return "You are an expert recruitment and talent assessment specialist.\n"
             . "Generate a personalized interview question set for ONE specific candidate applying for ONE specific vacancy.\n"
             . "Return ONLY valid JSON — no markdown fences, no conversational text.\n\n"
             . "=== ROLE DOMAIN ===\n"
             . "Position: {$jobTitle}\n"
             . "Domain Type: {$roleDomain}\n"
             . ($isTech ? "" : "CRITICAL RULE FOR NON-TECHNICAL ROLES: DO NOT ask software engineering, coding, codebase migration, API design, or database query questions. Tailor all questions strictly to human resources, recruitment, talent management, employee relations, compliance, communication, or business operations domain appropriate for '{$jobTitle}'.\n\n")
             . "=== VACANCY ===\n"
             . "Title: {$jobTitle}\n"
             . "Experience Required: {$expMin}–{$expMax} years\n"
             . "Must-Have Skills: {$mustHaveStr}\n"
             . "Nice-To-Have Skills: {$niceHaveStr}\n"
             . "Description: {$jobDesc}\n\n"
             . "=== CANDIDATE PROFILE ===\n"
             . "Name: {$candName}\n"
             . "Experience: {$expYrs} years\n"
             . "Difficulty Level: {$difficulty}\n"
             . "Candidate Skills (ATS): {$candSkillStr}\n\n"
             . "=== CANDIDATE REAL RESUME PROJECTS (MANDATORY TO USE) ===\n"
             . "{$projStr}\n\n"
             . "=== RESUME / ATS CONTEXT ===\n"
             . "{$resumeCtx}\n\n"
             . "=== MUST-HAVE SKILL EVIDENCE ===\n"
             . "{$skillEvidenceStr}\n\n"
             . "=== PREVIOUS QUESTIONS — THIS CANDIDATE (DO NOT REUSE) ===\n"
             . "{$prevCandStr}\n\n"
             . "=== PREVIOUS QUESTIONS — OTHER CANDIDATES SAME VACANCY (DO NOT SUBSTANTIALLY REUSE) ===\n"
             . "{$prevVacancyStr}"
             . "{$regenNote}\n\n"
             . "=== QUESTION BUDGET ===\n"
             . "Total Questions: {$totalQ}\n"
             . "Must-Have Skill Questions: {$mustSlots}\n"
             . "Candidate-Specific Questions: {$candSlots}\n"
             . "Scenario Questions: {$scenSlots}\n"
             . "Behavioral Questions: {$behSlots}\n\n"
             . "=== MANDATORY PERSONALIZATION & PROJECT RULES ===\n"
             . "1. REAL PROJECT QUESTIONS ARE MANDATORY: If candidate projects are listed above (e.g., LangMaster, Agri 360, etc.), you MUST generate at least 2 candidate-specific questions that explicitly reference the candidate's actual project names, technologies, and implementation details.\n"
             . "   Example format: 'In your LangMaster project, how did you structure JWT authentication and protected routes between React and Node.js?'\n"
             . "2. EVALUATE MUST-HAVE SKILLS THROUGH REAL PROJECTS: Where possible, evaluate Must-Have Skills using the candidate's real project context (e.g. evaluate React or Node.js through their actual project).\n"
             . "3. NEVER INVENT PROJECTS OR TECHNOLOGIES: Only reference projects and technologies actually listed in the candidate's projects/resume above. Never invent company names or technologies the candidate has not used.\n"
             . "4. COVER EVERY MUST-HAVE SKILL: Evaluate every Must-Have Skill with at least 1 dedicated question.\n"
             . "5. EVIDENCE LEVELS: 'strong' → deep practical/architecture question; 'weak' → verification question; 'none' → competency question (do NOT assume they have missing skills).\n"
             . "6. NON-DUPLICATION: Do NOT repeat or substantially paraphrase any previous questions.\n"
             . "7. DIFFICULTY: Adjust to candidate experience ({$expYrs} yrs) and vacancy seniority ({$difficulty}).\n"
             . "8. Return ONLY valid JSON matching the format below.\n\n"
             . "=== REQUIRED JSON OUTPUT FORMAT ===\n"
             . "{\n"
             . '  "questions": [' . "\n"
             . '    {"question": "In your [ProjectName] project, how did you...", "type": "candidate_specific", "difficulty": "' . $difficulty . '", "skills": ["React"], "project": "[ProjectName]", "personalized": true, "reason": "Evaluates candidate real project implementation"}' . "\n"
             . '  ],' . "\n"
             . '  "covered_must_have_skills": ["React", "Node.js"],' . "\n"
             . '  "covered_projects": ["LangMaster"],' . "\n"
             . '  "uncovered_must_have_skills": []' . "\n"
             . "}\n"
             . "Valid types: must_have_skill, candidate_specific, scenario, behavioral, role_specific\n"
             . "Valid difficulties: Beginner, Intermediate, Intermediate-Advanced, Advanced";
    }

    // =========================================================================
    // STEP: VALIDATE + FILL GAPS
    // =========================================================================

    private function validateAndFillGaps($aiQuestions, $skillGroups, $candidateCtx, $difficulty, $allPrev, $budget) {
        $mustHave    = $skillGroups['must_have'];
        $evidenceMap = $skillGroups['evidence_map'];
        $projects    = $candidateCtx['projects'];
        $projNames   = array_map('strtolower', array_column($projects, 'name'));
        $valid       = [];
        $validTypes  = ['must_have_skill','technical','candidate_specific','scenario','behavioral','role_specific'];

        foreach ($aiQuestions as $q) {
            $text = trim($q['question'] ?? '');
            if (empty($text) || strlen($text) < 15) continue;

            $type   = strtolower(trim($q['type'] ?? 'technical'));
            if (!in_array($type, $validTypes)) $type = 'technical';

            $skills = $q['skills'] ?? [$q['skill'] ?? ''];
            if (!is_array($skills)) $skills = [$skills];
            $skills = array_values(array_filter($skills));

            // Validate project claim if question references a project
            $referencedProj = $q['project'] ?? '';
            if (!empty($referencedProj) && !empty($projNames)) {
                if (!in_array(strtolower($referencedProj), $projNames)) {
                    // AI hallucinated a project name not in candidate resume — clean the text
                    $text = str_replace($referencedProj, "your recent project", $text);
                }
            }

            $valid[] = [
                'question'    => $text,
                'type'        => $type,
                'difficulty'  => $q['difficulty'] ?? $difficulty,
                'skills'      => $skills,
                'skill'       => implode(', ', $skills),
                'personalized'=> true,
                'reason'      => $q['reason'] ?? 'AI-generated candidate question.',
            ];
        }

        // Fill missing must-have coverage
        $coveredLower = [];
        foreach ($valid as $q) {
            foreach ($q['skills'] as $s) {
                foreach (explode(',', $s) as $subS) {
                    $c = strtolower(trim($subS));
                    if (!empty($c)) $coveredLower[] = $c;
                }
            }
            foreach ($mustHave as $ms) {
                $msClean = strtolower(trim($ms));
                if (!empty($msClean) && (stripos($q['question'], $msClean) !== false || in_array($msClean, $coveredLower))) {
                    $coveredLower[] = $msClean;
                }
            }
        }
        foreach ($mustHave as $skill) {
            $sClean = strtolower(trim($skill));
            if (!empty($sClean) && !in_array($sClean, $coveredLower)) {
                $ev = $evidenceMap[$skill] ?? 'none';
                $valid[] = $this->buildFallbackSkillQuestion($skill, $ev, $difficulty, $candidateCtx);
            }
        }
        return $valid;
    }

    // =========================================================================
    // STEP: DEDUPLICATE
    // =========================================================================

    private function deduplicateQuestions($questions, $previousQuestions) {
        $accepted  = [];
        $seenTexts = array_values($previousQuestions);
        foreach ($questions as $q) {
            $text = trim($q['question'] ?? '');
            if (empty($text)) continue;
            if (!$this->isDuplicateQuestion($text, $seenTexts)) {
                $accepted[]  = $q;
                $seenTexts[] = $text;
            }
        }
        return $accepted;
    }

    // =========================================================================
    // STEP: FIND UNCOVERED
    // =========================================================================

    private function findUncoveredMustHaveSkills($questions, $mustHaveSkills) {
        if (empty($mustHaveSkills)) return [];
        $coveredLower = [];

        foreach ($questions as $q) {
            $rawSkills = [];
            if (!empty($q['skills'])) {
                $rawSkills = is_array($q['skills']) ? $q['skills'] : explode(',', $q['skills']);
            }
            if (!empty($q['skill'])) {
                $rawSkills = array_merge($rawSkills, explode(',', $q['skill']));
            }

            foreach ($rawSkills as $s) {
                $c = strtolower(trim($s));
                if (!empty($c)) $coveredLower[] = $c;
            }

            foreach ($mustHaveSkills as $ms) {
                $msClean = strtolower(trim($ms));
                if (!empty($msClean) && stripos($q['question'], $msClean) !== false) {
                    $coveredLower[] = $msClean;
                }
            }
        }

        $uncovered = [];
        foreach ($mustHaveSkills as $skill) {
            $sClean = strtolower(trim($skill));
            if (!empty($sClean) && !in_array($sClean, $coveredLower)) {
                $uncovered[] = $skill;
            }
        }
        return array_values(array_unique($uncovered));
    }

    private function isTechnicalRole($vacancy, $candidateCtx = []) {
        $jobTitle    = strtolower($vacancy['JobTitle'] ?? '');
        $roleSummary = strtolower($vacancy['RoleSummary'] ?? '');
        $mustHave    = strtolower(is_array($vacancy['MustHaveSkills'] ?? '') ? implode(' ', $vacancy['MustHaveSkills']) : ($vacancy['MustHaveSkills'] ?? ''));

        // Software / Coding / IT keywords
        $techKeywords = [
            'developer', 'engineer', 'programmer', 'coder', 'software', 'full stack', 'backend',
            'frontend', 'devops', 'architect', 'data engineer', 'php', 'javascript', 'python',
            'java', 'react', 'node', 'c++', 'sql', 'aws', 'docker', 'api', 'codebase'
        ];
        foreach ($techKeywords as $tk) {
            if (strpos($jobTitle, $tk) !== false || strpos($mustHave, $tk) !== false) {
                return true;
            }
        }

        // Non-technical role keywords (HR, Recruiter, Finance, Sales, Admin, etc.)
        $nonTechKeywords = [
            'hr', 'human resource', 'recruiter', 'recruitment', 'talent acquisition',
            'payroll', 'finance', 'accounting', 'sales', 'business development', 'marketing',
            'administration', 'admin', 'office manager', 'customer support', 'legal', 'compliance',
            'people', 'culture', 'operations'
        ];
        foreach ($nonTechKeywords as $nt) {
            if (strpos($jobTitle, $nt) !== false || strpos($roleSummary, $nt) !== false) {
                return false;
            }
        }

        return true;
    }

    // =========================================================================
    // FALLBACK GENERATOR (With Candidate Resume Project Support)
    // =========================================================================

    private function fallbackGenerate($vacancy, $candidate, $candidateCtx, $skillGroups, $difficulty, $budget, $allPrev, $version) {
        $mustHave    = $skillGroups['must_have'];
        $evidenceMap = $skillGroups['evidence_map'];
        $niceHave    = $skillGroups['nice_to_have'];
        $candSkills  = $skillGroups['candidate_skills'];
        $jobTitle    = $vacancy['JobTitle'] ?? 'this role';
        $expYrs      = $candidateCtx['experience_years'];
        $jobs        = $candidateCtx['jobs'];
        $projects    = $candidateCtx['projects'];

        $isTech = $this->isTechnicalRole($vacancy, $candidateCtx);

        for ($retry = 0; $retry < 5; $retry++) {
            $questions   = [];
            // On final retries (attempt >= 3), only check duplicate against current batch to guarantee questions exist
            $currentPrev = ($retry >= 3) ? [] : $allPrev;
            $seed        = abs(crc32((string)($candidate['CandidateId'] ?? 0) . '_' . (string)($vacancy['Jid'] ?? 0) . '_v' . ($version + $retry * 13 + rand(1, 999))));

            // Must-Have Questions
            foreach ($mustHave as $idx => $skill) {
                $ev = $evidenceMap[$skill] ?? 'weak';
                $q  = $this->buildFallbackSkillQuestion($skill, $ev, $difficulty, $candidateCtx, $seed + $idx, $isTech);
                if (!$this->isDuplicateQuestion($q['question'], $currentPrev)) {
                    $questions[] = $q;
                    $currentPrev[] = $q['question'];
                }
            }


            // Candidate-Specific Questions (Use Real Resume Projects if Available)
            $candSlots = $budget['candidate_specific'];
            $topSkills = array_slice(array_unique(array_merge($candSkills, $niceHave)), 0, 5);

            if (!empty($projects)) {
                foreach ($projects as $pi => $proj) {
                    if (count($questions) >= $budget['total']) break;
                    $pName = $proj['name'];
                    $pDesc = $proj['description'] ?? ($isTech ? 'full stack project' : 'key initiative');

                    if ($isTech) {
                        $pTemplates = [
                            "In your {$pName} project ({$pDesc}), how did you structure component communication and handle API requests?",
                            "While building {$pName}, what was the most complex technical challenge you faced, and how did you resolve it?",
                            "In {$pName}, how did you ensure data consistency and manage error handling across the application layers?",
                            "Regarding your work on {$pName}, what performance optimization or database indexing techniques did you implement?",
                        ];
                    } else {
                        $pTemplates = [
                            "In your work on {$pName} ({$pDesc}), how did you structure team communication and handle stakeholder requests?",
                            "While managing {$pName}, what was the most complex operational or process challenge you faced, and how did you resolve it?",
                            "In {$pName}, how did you ensure data accuracy, confidentiality, and smooth workflow execution?",
                            "Regarding your work on {$pName}, what efficiency or quality improvement measures did you implement?",
                        ];
                    }
                    $pIdx  = ($seed + $pi + 50) % count($pTemplates);
                    $qText = $pTemplates[$pIdx];

                    if (!$this->isDuplicateQuestion($qText, $currentPrev)) {
                        $questions[] = [
                            'question'    => $qText,
                            'type'        => 'candidate_specific',
                            'difficulty'  => $difficulty,
                            'skills'      => $topSkills,
                            'skill'       => $pName,
                            'personalized'=> true,
                            'reason'      => "Candidate-specific fallback question based on '{$pName}'."
                        ];
                        $currentPrev[] = $qText;
                    }
                }
            }

            // Fill remaining candidate slots
            $jobParts = [];
            foreach ($jobs as $job) {
                if (!empty($job['from'])) {
                    $jobParts[] = "a role from {$job['from']} to " . ($job['to'] ?? 'present');
                }
            }
            $jobCtx = !empty($jobParts) ? implode(' and ', $jobParts) : 'your recent roles';

            if ($isTech) {
                $candTemplates = [
                    "You have {$expYrs} years of experience. Can you describe the most technically challenging problem you solved in {$jobCtx}?",
                    "Based on your background, walk us through how you approached a complex technical requirement and the solution you implemented.",
                    "In {$jobCtx}, how did you ensure code quality and maintainability under tight delivery timelines?",
                    "What was the most difficult performance or scalability issue you encountered in {$jobCtx}, and how did you diagnose it?",
                ];
            } else {
                $candTemplates = [
                    "You have {$expYrs} years of experience. Can you describe a challenging HR or operational scenario you successfully managed in {$jobCtx}?",
                    "Based on your background, walk us through a key policy, recruitment drive, or process improvement you implemented in {$jobCtx}.",
                    "In {$jobCtx}, how did you ensure process efficiency, compliance, and stakeholder alignment under tight deadlines?",
                    "What was the most difficult team, conflict, or talent management issue you encountered in {$jobCtx}, and how did you resolve it?",
                ];
            }
            for ($i = 0; $i < $candSlots; $i++) {
                $idx   = ($seed + $i + 100) % count($candTemplates);
                $qText = $candTemplates[$idx];
                $skill = $topSkills[$i % max(1, count($topSkills))] ?? $jobTitle;
                if (!$this->isDuplicateQuestion($qText, $currentPrev)) {
                    $questions[] = ['question' => $qText, 'type' => 'candidate_specific', 'difficulty' => $difficulty, 'skills' => [$skill], 'skill' => $skill, 'personalized' => true, 'reason' => 'Candidate-specific fallback question.'];
                    $currentPrev[] = $qText;
                }
            }

            // Scenario Questions
            $scenSlots = $budget['scenario'];
            $scenSkill = $mustHave[0] ?? $jobTitle;

            if ($isTech) {
                $scenTemplates = [
                    "A critical service using {$scenSkill} starts failing under peak load. How would you diagnose and resolve the issue without downtime?",
                    "You discover a serious security vulnerability in a production system relying on {$scenSkill}. What steps do you take immediately?",
                    "A core database query in a {$scenSkill} application degrades under concurrent usage. How would you diagnose and fix the query pipeline?",
                    "You need to migrate a legacy {$scenSkill} codebase to a modern architecture while maintaining active feature delivery. What is your strategy?",
                ];
            } else {
                $scenTemplates = [
                    "A high-priority hiring requirement or employee relations issue arises under tight deadlines involving {$scenSkill}. How do you prioritize and handle it?",
                    "You discover a policy compliance gap or process bottleneck involving {$scenSkill}. What immediate steps do you take to resolve it?",
                    "During an important talent acquisition or onboarding campaign using {$scenSkill}, candidate drop-off increases. How would you analyze and fix the pipeline?",
                    "You need to restructure or modernize an existing {$scenSkill} workflow while maintaining daily operational continuity. What is your strategy?",
                ];
            }
            for ($i = 0; $i < $scenSlots; $i++) {
                $idx   = ($seed + $i + 200) % count($scenTemplates);
                $qText = $scenTemplates[$idx];
                if (!$this->isDuplicateQuestion($qText, $currentPrev)) {
                    $questions[] = ['question' => $qText, 'type' => 'scenario', 'difficulty' => $difficulty, 'skills' => [$scenSkill], 'skill' => $scenSkill, 'personalized' => false, 'reason' => 'Scenario-based problem solving question.'];
                    $currentPrev[] = $qText;
                }
            }

            // Behavioral Questions
            $behSlots = $budget['behavioral'];
            if ($isTech) {
                $behTemplates = [
                    "Tell us about a time when requirements changed mid-sprint. How did you adapt and communicate with stakeholders?",
                    "Describe a technical disagreement you had with a peer or lead. How was it resolved and what was the outcome?",
                    "How do you prioritize your work when managing multiple critical tasks or bug fixes simultaneously?",
                    "Describe a time a feature you built caused a production issue. How did you take ownership and prevent future recurrence?",
                ];
            } else {
                $behTemplates = [
                    "Tell us about a time when hiring or organizational priorities shifted unexpectedly. How did you adapt and communicate with stakeholders?",
                    "Describe a professional disagreement you had with a hiring manager or team member. How was it resolved and what was the outcome?",
                    "How do you prioritize your work when managing multiple urgent recruitment, payroll, or HR requests simultaneously?",
                    "Describe a situation where an HR process or interview drive did not yield expected results. How did you adapt and improve the process?",
                ];
            }
            for ($i = 0; $i < $behSlots; $i++) {
                $idx   = ($seed + $i + 300) % count($behTemplates);
                $qText = $behTemplates[$idx];
                if (!$this->isDuplicateQuestion($qText, $currentPrev)) {
                    $questions[] = ['question' => $qText, 'type' => 'behavioral', 'difficulty' => 'Intermediate', 'skills' => ['Communication'], 'skill' => 'Communication', 'personalized' => false, 'reason' => 'Behavioral question.'];
                    $currentPrev[] = $qText;
                }
            }

            if (!empty($questions)) break;
        }

        return ['questions' => $questions];
    }

    // =========================================================================
    // HELPER: BUILD FALLBACK SKILL QUESTION
    // =========================================================================

    private function buildFallbackSkillQuestion($skill, $evidenceLevel, $difficulty, $candidateCtx, $seed = 0, $isTech = true) {
        $jobs     = $candidateCtx['jobs'] ?? [];
        $projects = $candidateCtx['projects'] ?? [];
        $pName    = !empty($projects[0]['name']) ? $projects[0]['name'] : '';

        if ($isTech) {
            if ($evidenceLevel === 'strong') {
                $frames = [
                    "Based on your experience with {$skill}" . ($pName ? " in projects like {$pName}" : "") . ", describe a complex production problem you encountered and the solution you designed.",
                    "You have demonstrated {$skill} experience" . ($pName ? " in {$pName}" : "") . ". Walk us through an architectural decision you made that had a significant impact.",
                    "In your work with {$skill}, how did you improve system reliability, performance, or scalability?",
                    "What design trade-offs did you make when implementing a {$skill}-based solution?",
                ];
            } elseif ($evidenceLevel === 'weak') {
                $frames = [
                    "You have some exposure to {$skill}. How have you used it in a project, and what were the main challenges?",
                    "Describe your practical experience with {$skill} — what you have built with it and what limitations you encountered.",
                    "How would you implement a core feature using {$skill} based on your current understanding?",
                    "What is your level of hands-on experience with {$skill}, and in which areas do you feel you need further growth?",
                ];
            } else {
                $frames = [
                    "This role requires {$skill}. How would you approach learning and applying {$skill} to deliver a production feature?",
                    "Can you describe your conceptual understanding of {$skill} and how you would use it professionally?",
                    "The position requires proficiency in {$skill}. How would you design a solution using {$skill} for a typical use case in this domain?",
                    "If given a task requiring {$skill}, what initial steps would you take to understand the requirements and deliver a solution?",
                ];
            }
        } else {
            // Non-Technical / HR / Operations / Management
            if ($evidenceLevel === 'strong') {
                $frames = [
                    "Based on your experience with {$skill}" . ($pName ? " in initiatives like {$pName}" : "") . ", describe a complex workplace challenge you encountered and the strategy you implemented.",
                    "You have demonstrated strong {$skill} experience" . ($pName ? " in {$pName}" : "") . ". Walk us through a key operational decision you made that had a positive organizational impact.",
                    "In your work with {$skill}, how did you improve process efficiency, candidate experience, or team collaboration?",
                    "What trade-offs or priorities did you balance when applying {$skill} in a high-demand recruitment or HR environment?",
                ];
            } elseif ($evidenceLevel === 'weak') {
                $frames = [
                    "You have experience with {$skill}. How have you applied it in your previous roles, and what were the main challenges?",
                    "Describe your practical experience with {$skill} — how you utilized it and what lessons you learned.",
                    "How would you handle a complex HR or operational scenario using {$skill} based on your experience?",
                    "What is your level of experience with {$skill}, and in which areas do you feel you can further develop?",
                ];
            } else {
                $frames = [
                    "This position requires {$skill}. How would you apply {$skill} to manage daily responsibilities and deliver results?",
                    "Can you describe your practical understanding of {$skill} and how you apply it in a professional HR/management setting?",
                    "The position requires proficiency in {$skill}. How would you handle a typical workplace requirement using {$skill}?",
                    "If given a key responsibility requiring {$skill}, what steps would you take to ensure successful execution?",
                ];
            }
        }

        $idx   = abs((int)$seed) % count($frames);
        $qText = $frames[$idx];

        return [
            'question'    => $qText,
            'type'        => 'must_have_skill',
            'difficulty'  => $difficulty,
            'skills'      => [$skill],
            'skill'       => $skill,
            'personalized'=> ($evidenceLevel !== 'none'),
            'reason'      => "Must-Have Skill evaluation — {$evidenceLevel} evidence. Required by vacancy.",
        ];
    }

    // =========================================================================
    // HELPER: DUPLICATE DETECTION
    // =========================================================================

    private function isDuplicateQuestion($questionText, $previousQuestions) {
        if (empty($previousQuestions)) return false;

        $cleanNew  = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $questionText));
        $cleanNew  = preg_replace('/\s+/', ' ', trim($cleanNew));
        $newHash   = hash('sha256', preg_replace('/\s/', '', $cleanNew));
        $newWords  = array_unique(array_filter(explode(' ', $cleanNew), function($w) { return strlen($w) > 3; }));

        foreach ($previousQuestions as $prev) {
            if (empty($prev)) continue;
            $cleanPrev = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $prev));
            $cleanPrev = preg_replace('/\s+/', ' ', trim($cleanPrev));
            $prevHash  = hash('sha256', preg_replace('/\s/', '', $cleanPrev));

            if ($newHash === $prevHash) return true;

            similar_text($cleanNew, $cleanPrev, $pct);
            if ($pct >= 75) return true;

            $prevWords    = array_unique(array_filter(explode(' ', $cleanPrev), function($w) { return strlen($w) > 3; }));
            $intersection = count(array_intersect($newWords, $prevWords));
            $union        = count(array_unique(array_merge($newWords, $prevWords)));
            if ($union > 0 && ($intersection / $union) >= 0.70) return true;
        }
        return false;
    }

    // =========================================================================
    // HELPER: PARSE SKILL LIST
    // =========================================================================

    private function parseSkillList($str) {
        if (empty($str)) return [];
        return array_values(array_unique(array_filter(array_map('trim', explode(',', $str)))));
    }
}