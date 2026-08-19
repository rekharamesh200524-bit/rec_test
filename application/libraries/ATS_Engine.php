<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

class ATS_Engine {

    public function processResume($file, $vacancy)
    {
        $this->logStage('START', [
            'resumePath' => $file,
            'vacancyId'  => $vacancy['Jid'] ?? null,
            'jobTitle'   => $vacancy['JobTitle'] ?? null
        ]);

        // 1. EXTRACT RAW RESUME TEXT
        $rawText = (string)$this->extractText($file);
        
        // Normalize PDF font artifacts:
        // A. Fix date range separators like '{' => '-' (e.g., Oct 2023 { Present => Oct 2023 - Present)
        $rawText = str_replace('{', '-', $rawText);
        $rawText = preg_replace('/(\d{4}|\bjan|\bfeb|\bmar|\bapr|\bmay|\bjun|\bjul|\baug|\bsep|\bsept|\boct|\bnov|\bdec)\s*\{+\s*(present|current|now|\d{4}|\bjan|\bfeb|\bmar|\bapr|\bmay|\bjun|\bjul|\baug|\bsep|\bsept|\boct|\bnov|\bdec)/i', '$1 - $2', $rawText);
        // B. Fix form-feed/ligature characters \x0C => fi
        $rawText = str_replace("\x0C", "fi", $rawText);
        // C. Fix font kerning spaces inside common words
        $rawText = preg_replace('/\b(dev)\s+(elop\s*er[s]?)\b/i', 'Developer', $rawText);
        $rawText = preg_replace('/\b(prese)\s*(n)\s*(t)\b/i', 'Present', $rawText);
        $rawText = preg_replace('/\b(in)\s+(tern[s]?)\b/i', 'Intern', $rawText);
        $rawText = preg_replace('/\b(pro)\s+(ject[s]?)\b/i', 'Projects', $rawText);
        $rawText = preg_replace('/\b(certi)\s*(fica\s*tion[s]?)\b/i', 'Certifications', $rawText);
        $rawText = preg_replace('/\b(sci)\s+(ence)\b/i', 'Science', $rawText);
        $rawText = preg_replace('/\b(y)\s+(ears?)\b/i', '$1$2', $rawText);
        $rawText = preg_replace('/\b(exp)\s+(erience[s]?)\b/i', '$1$2', $rawText);
        $rawText = preg_replace('/\b(hand)\s*-\s*(s)\b/i', 'hands', $rawText);
        $rawText = preg_replace('/\b(l)\s*(w)\s*(c)\b/i', 'LWC', $rawText);
        $rawText = preg_replace('/\b(b)\s*\.\s*(sc|tech|ca|e|com|ba)\b/i', 'B.$2', $rawText);
        $rawText = preg_replace('/\b(m)\s*\.\s*(tech|ca|e|com|ba|sc)\b/i', 'M.$2', $rawText);

        $text = strtolower($rawText);

        $this->logStage('RAW_RESUME_TEXT', substr($text, 0, 2000));

        // 2. EXTRACT WORK EXPERIENCE DETAILS
        $experienceDetails = $this->extractExperienceDetails($text);
        $this->logStage('EXPERIENCE_DETAILS', $experienceDetails);

        // 3. EXTRACT CONTACT / PERSONAL INFORMATION
        $email = '';
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $rawText, $matches)) {
            $email = strtolower($matches[0]);
        }

        // Extract Candidate Name with Job Title exclusion & Email Username matching
        $name  = '';
        $emailPrefix = '';
        if (!empty($email)) {
            $eParts = explode('@', $email);
            $emailPrefix = preg_replace('/[^a-z]/', '', strtolower($eParts[0]));
        }

        $jobTitlePattern = '/\b(developer|engineer|administrator|analyst|architect|consultant|manager|lead|executive|specialist|officer|designer|tester|programmer|intern|associate|trainee|director|coordinator|head|software|full stack|frontend|backend|salesforce|python|java|php|react|angular|node|dotnet|\.net|devops|data|cloud|qa|qc|ui\/ux|curriculum vitae|resume|profile|application|candidacy|personal details|contact)\b/i';
        $locationPattern = '/road|street|nagar|district|state|india|tamil nadu|chennai|mumbai|delhi|bangalore|hyderabad|andhra|telangana|karnataka|kerala|pincode|dob|gender|male|female|email|mobile|phone|aggregate/i';

        $candidateName = '';
        $bestMatchName = '';
        $lines = preg_split('/\r\n|\r|\n/', $rawText);

        foreach ($lines as $line) {
            $line = preg_replace('/[^\x20-\x7E]/', '', $line);
            $line = trim($line);
            if ($line == '' || preg_match('/^\d+$/', $line)) continue;
            if (filter_var($line, FILTER_VALIDATE_EMAIL) || strpos($line, '@') !== false) continue;
            if (preg_match('/(\+91|91)?\s?[6-9]\d{9}/', $line)) continue;
            if (preg_match('/career|objective|summary|profile|education|experience|skills|projects/i', $line)) continue;
            if (preg_match($jobTitlePattern, $line)) continue;
            if (preg_match($locationPattern, $line)) continue;

            $cleanedLine = preg_replace('/\s+/', ' ', $line);
            if (preg_match('/^([A-Za-z][A-Za-z.\s]{2,40})$/', $cleanedLine, $m)) {
                $possible = ucwords(strtolower(trim($m[1])));
                if (empty($candidateName)) {
                    $candidateName = $possible;
                }

                if (!empty($emailPrefix)) {
                    $nameWords = explode(' ', strtolower($possible));
                    foreach ($nameWords as $nw) {
                        if (strlen($nw) >= 3 && strpos($emailPrefix, $nw) !== false) {
                            $bestMatchName = $possible;
                            break 2;
                        }
                    }
                }
            }
        }
        $name = !empty($bestMatchName) ? $bestMatchName : (!empty($candidateName) ? $candidateName : 'Candidate');

        // Extract Mobile Number(s) with support for spaces/hyphens (e.g. +91 96007 42358, 81483 14442)
        $mobileNumbers = null;
        $extractedMobiles = [];
        $phoneRegex = '/(?:\+?91[\s\-]?)?(?:0)?\b([6-9]\d{4}[\s\-]?\d{5}|[6-9]\d{2}[\s\-]?\d{3}[\s\-]?\d{4}|[6-9]\d{9})\b/';

        if (preg_match_all($phoneRegex, $rawText, $phoneMatches)) {
            foreach ($phoneMatches[0] as $rawPhone) {
                $digits = preg_replace('/[^\d]/', '', $rawPhone);
                if (strlen($digits) >= 10) {
                    $clean10 = substr($digits, -10);
                    if (preg_match('/^[6-9]\d{9}$/', $clean10)) {
                        $extractedMobiles[] = $clean10;
                    }
                }
            }
        }

        if (!empty($extractedMobiles)) {
            $uniqueMobiles = array_unique($extractedMobiles);
            $mobileNumbers = implode(', ', $uniqueMobiles);
        }

        // 4. EXTRACT TOTAL YEARS OF EXPERIENCE
        $candidateExp = 0.0;
        $expPatterns = [
            '/overall\s+(\d+(?:\.\d+)?)\+?\s*years?\s+(?:of\s+)?(?:hands?\s*-\s*on\s+)?experience/i',
            '/total\s+(\d+(?:\.\d+)?)\+?\s*years?\s+(?:of\s+)?(?:hands?\s*-\s*on\s+)?experience/i',
            '/(\d+(?:\.\d+)?)\+?\s*years?\s+(?:of\s+)?(?:hands?\s*-\s*on\s+)?experience/i',
            '/experience\s*[:\-]?\s*(\d+(?:\.\d+)?)/i'
        ];
        foreach ($expPatterns as $pattern) {
            if (preg_match($pattern, $rawText, $m)) {
                $candidateExp = (float)$m[1];
                break;
            }
        }

        // Fallback to experience details calculation if zero
        if ($candidateExp == 0.0 && !empty($experienceDetails['total'])) {
            if (preg_match('/(\d+)\s*Years/i', $experienceDetails['total'], $ym)) {
                $candidateExp += (float)$ym[1];
            }
            if (preg_match('/(\d+)\s*Months/i', $experienceDetails['total'], $mm)) {
                $candidateExp += round(((float)$mm[1]) / 12, 1);
            }
        }
        if ($candidateExp == 0.0 && stripos($text, 'intern') !== false) {
            $candidateExp = 1.0;
        }
        $expyrs = $candidateExp;

        $this->logStage('Email', $email);
        $this->logStage('Name', $name);
        $this->logStage('Mobile', $mobileNumbers);
        $this->logStage('ExperienceYears', $expyrs);

        // 5. EXTRACT ALL CANDIDATE SKILLS FROM DICTIONARY
        $allExtractedSkills = $this->extractAllSkillsFromText($text);

        // 6. ANALYZE VACANCY SKILLS
        $vacancySkillsRaw = '';
        if (!empty($vacancy['RequiredSkills'])) {
            $vacancySkillsRaw = $vacancy['RequiredSkills'];
        } elseif (!empty($vacancy['MustHaveSkills'])) {
            $vacancySkillsRaw = $vacancy['MustHaveSkills'] . (!empty($vacancy['NiceToHaveSkills']) ? ', ' . $vacancy['NiceToHaveSkills'] : '');
        } elseif (!empty($vacancy['Skills'])) {
            $vacancySkillsRaw = $vacancy['Skills'];
        }

        $requiredSkillsList = array_unique(array_filter(array_map('trim', explode(',', strtolower($vacancySkillsRaw)))));

        $matchedSkills = [];
        $missingSkills = [];

        foreach ($requiredSkillsList as $reqSkill) {
            if (!$reqSkill) continue;
            $found = false;

            if (preg_match('/\b' . preg_quote($reqSkill, '/') . '\b/i', $text)) {
                $matchedSkills[] = $reqSkill;
                $found = true;
            } elseif ($this->synonymMatch($reqSkill, $text)) {
                $matchedSkills[] = $reqSkill;
                $found = true;
            } elseif ($this->fuzzyMatch($reqSkill, $text)) {
                $matchedSkills[] = $reqSkill;
                $found = true;
            }

            if (!$found) {
                $missingSkills[] = $reqSkill;
            }
        }

        // 7. ANALYZE EDUCATION
        $eduRequired = strtolower(trim($vacancy['EducationRequired'] ?? 'Any'));
        $eduMatch = false;
        $detectedDegree = 'Not Found';

        $degreeGroups = [
            'Doctorate' => ['phd', 'doctorate', 'doctor of philosophy'],
            'Master'    => ['mtech', 'm.tech', 'me', 'm.e', 'mba', 'm.b.a', 'msc', 'm.sc', 'mca', 'm.c.a', 'mcom', 'm.com', 'ma', 'm.a', 'master', 'master of technology', 'master of engineering', 'master of science', 'master of business administration'],
            'Bachelor'  => ['btech', 'b.tech', 'be', 'b.e', 'bachelor', 'bsc', 'b.sc', 'bca', 'b.c.a', 'bcom', 'b.com', 'ba', 'b.a', 'bba', 'b.b.a', 'bachelor of technology', 'bachelor of engineering', 'bachelor of science', 'bachelor of commerce', 'bachelor of arts'],
            'Diploma'   => ['diploma', 'polytechnic']
        ];

        $normText = preg_replace('/[^a-z0-9 ]/', ' ', $text);
        $normText = preg_replace('/\s+/', ' ', $normText);

        if ($eduRequired === 'any' || empty($eduRequired)) {
            $eduMatch = true;
            $detectedDegree = 'Any qualification accepted';
        } else {
            foreach ($degreeGroups as $groupLevel => $degrees) {
                foreach ($degrees as $deg) {
                    if (stripos($normText, $deg) !== false) {
                        $detectedDegree = strtoupper($deg);
                        if (stripos($eduRequired, strtolower($groupLevel)) !== false || stripos($eduRequired, $deg) !== false || $eduRequired === 'any') {
                            $eduMatch = true;
                            break 2;
                        }
                    }
                }
            }
            if (!$eduMatch) {
                // Direct substring fallback check
                $reqParts = explode(',', $eduRequired);
                foreach ($reqParts as $part) {
                    $part = trim($part);
                    if (!empty($part) && stripos($normText, $part) !== false) {
                        $eduMatch = true;
                        $detectedDegree = strtoupper($part);
                        break;
                    }
                }
            }
        }

        // 8. ANALYZE DOMAIN / ROLE FIT
        $candidateDomain = $this->detectCandidateDomain($text, $allExtractedSkills, $vacancy);
        $jobDomain = $this->detectJobDomain($vacancy);
        $domainAnalysis = $this->analyzeDomainMismatch($candidateDomain, $jobDomain, $text, $allExtractedSkills, $vacancy);

        // 9. EXPERIENCE COMPARISON
        $minExpRequired = (float)($vacancy['ExpMin'] ?? 0.0);
        $expMatch = ($candidateExp >= $minExpRequired || $minExpRequired == 0.0);

        // 10. EVIDENCE-BASED RECOMMENDATION EVALUATION ENGINE
        $relevantEvidence    = [];
        $missingRequirements = [];

        // Build Evidence
        if ($expyrs > 0) {
            $relevantEvidence[] = "Work Experience: {$expyrs} Years total experience" . ($minExpRequired > 0 ? " (Vacancy Required: Min {$minExpRequired} Years)" : "");
        } elseif ($minExpRequired == 0) {
            $relevantEvidence[] = "Entry level / Fresher candidate (Vacancy accepts fresher)";
        }

        if (!empty($matchedSkills)) {
            $relevantEvidence[] = "Matched Core Skills: " . implode(', ', array_map('ucwords', array_slice($matchedSkills, 0, 10)));
        }

        if ($eduMatch) {
            $relevantEvidence[] = "Education: Matches required level" . ($detectedDegree !== 'Not Found' ? " ({$detectedDegree})" : "");
        }

        if ($candidateDomain && $candidateDomain !== 'General' && $candidateDomain !== 'Other / General') {
            $relevantEvidence[] = "Domain Fit: Background in {$candidateDomain}";
        }

        if (!empty($allExtractedSkills)) {
            $relevantEvidence[] = "Extracted Resume Skills: " . implode(', ', array_map('ucwords', array_slice($allExtractedSkills, 0, 8)));
        }

        // Build Missing Requirements
        if (!empty($missingSkills)) {
            $missingRequirements[] = "Required Skills Not Found: " . implode(', ', array_map('ucwords', $missingSkills));
        }

        if ($minExpRequired > 0 && $candidateExp < $minExpRequired) {
            $missingRequirements[] = "Experience Shortfall: Candidate has {$candidateExp} Years (Required: Min {$minExpRequired} Years)";
        }

        if (!$eduMatch && $eduRequired !== 'any') {
            $missingRequirements[] = "Education Gap: Required qualification ({$vacancy['EducationRequired']}) not clearly identified in resume";
        }

        // 11. DETERMINE RECOMMENDATION CATEGORY
        $totalReqCount = count($requiredSkillsList);
        $matchedCount  = count($matchedSkills);
        $skillMatchRatio = $totalReqCount > 0 ? ($matchedCount / $totalReqCount) : 1.0;

        $jobTitleStr = strtolower($vacancy['JobTitle'] ?? '');
        $roleMatch = false;
        if (!empty($jobTitleStr)) {
            $titleWords = array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $jobTitleStr)), function($w) {
                return strlen($w) > 2 && !in_array($w, ['executive', 'manager', 'lead', 'senior', 'junior', 'associate', 'trainee', 'developer', 'engineer', 'specialist', 'officer']);
            });
            foreach ($titleWords as $word) {
                if (stripos($text, $word) !== false) {
                    $roleMatch = true;
                    break;
                }
            }
        }

        if (($expMatch || $minExpRequired <= 1.0) && ($skillMatchRatio >= 0.5 || $totalReqCount == 0) && ($eduMatch || $eduRequired === 'any')) {
            $recommendation = 'Strong Match';
            $recommendationReason = "The candidate's profile aligns well with the requirements of " . ($vacancy['JobTitle'] ?? 'the position') . ". ";
            if ($expyrs > 0) {
                $recommendationReason .= "They possess {$expyrs} years of relevant experience" . (!empty($matchedSkills) ? " and demonstrate key skills in " . implode(', ', array_map('ucwords', array_slice($matchedSkills, 0, 4))) : "") . ". ";
            } else {
                $recommendationReason .= "They demonstrate the required foundational qualifications and skills. ";
            }
            $recommendationReason .= "Their background provides concrete evidence for strong candidate suitability.";
        } elseif (($skillMatchRatio > 0 || $expyrs >= ($minExpRequired * 0.7) || $roleMatch) && count($missingRequirements) <= 2) {
            $recommendation = 'Potential Match';
            $recommendationReason = "The candidate has a relevant background for " . ($vacancy['JobTitle'] ?? 'this role') . ", but specific requirements need verification during screening. ";
            if (!empty($missingSkills)) {
                $recommendationReason .= "Skill(s) like " . implode(', ', array_map('ucwords', array_slice($missingSkills, 0, 3))) . " were not clearly identified in the resume text. ";
            }
            if ($minExpRequired > 0 && $candidateExp < $minExpRequired) {
                $recommendationReason .= "Total experience ({$candidateExp} Yrs) is slightly below the preferred minimum ({$minExpRequired} Yrs). ";
            }
            $recommendationReason .= "Recruiter review and phone screening are recommended.";
        } else {
            $recommendation = 'Low Match';
            $recommendationReason = "The candidate's resume does not provide sufficient evidence of meeting core requirements for " . ($vacancy['JobTitle'] ?? 'this vacancy') . ". ";
            if (!empty($missingSkills)) {
                $recommendationReason .= "Key required skills (" . implode(', ', array_map('ucwords', array_slice($missingSkills, 0, 3))) . ") are missing. ";
            }
            if ($minExpRequired > 0 && $candidateExp < $minExpRequired) {
                $recommendationReason .= "Work experience ({$candidateExp} Yrs) falls substantially below the minimum required ({$minExpRequired} Yrs). ";
            }
            $recommendationReason .= "The profile is substantially unrelated or does not meet minimum thresholds.";
        }

        if ($domainAnalysis['domain_status'] === 'WRONG_DOMAIN') {
            $recommendation = 'Not Suitable';
            $recommendationReason = $domainAnalysis['domain_reason'];
        }

        $this->logStage('RECOMMENDATION_ANALYSIS', [
            'matchedSkillsCount' => $matchedCount,
            'requiredSkillsCount' => $totalReqCount,
            'candidateExp' => $candidateExp,
            'minExpRequired' => $minExpRequired,
            'eduMatch' => $eduMatch,
            'domainStatus' => $domainAnalysis['domain_status'] ?? 'UNCLEAR',
            'candidateDomain' => $candidateDomain,
            'jobDomain' => $jobDomain
        ]);
        $this->logStage('RECOMMENDATION', $recommendation);
        $this->logStage('RECOMMENDATION_REASON', $recommendationReason);

        // Extract Recruiter Candidate Profile (Informational Only - Zero impact on ATS decision)
        $candidateProfile = $this->extractCandidateProfile(
            $rawText,
            $text,  
            $allExtractedSkills,
            $experienceDetails,
            $expyrs,
            $candidateDomain,
            $detectedDegree
        );

        // 12. RETURN STRICT NO-SCORE EVALUATION RESULT
        return [
            'recommendation'        => $recommendation,
            'recommendation_reason' => $recommendationReason,
            'relevant_evidence'     => $relevantEvidence,
            'missing_requirements'  => $missingRequirements,
            'status'                => $recommendation, // Backward compatibility
            'email'                 => $email,
            'name'                  => $name,
            'mobileNumbers'         => $mobileNumbers,
            'expyrs'                => $expyrs,
            'experience_details'    => $experienceDetails,
            'domain'                => $candidateDomain ?: 'General',
            'job_domain'            => $jobDomain ?: 'General',
            'candidate_domain'      => $candidateDomain ?: 'General',
            'domain_status'         => $domainAnalysis['domain_status'] ?? 'UNCLEAR',
            'domain_analysis'       => $domainAnalysis,
            'matched_skills'        => implode(', ', array_map('ucwords', $matchedSkills)),
            'missing_skills'        => implode(', ', array_map('ucwords', $missingSkills)),
            'all_extracted_skills'  => implode(', ', array_map('ucwords', array_unique($allExtractedSkills))),
            'education_match'       => $eduMatch ? 'Yes' : 'No',
            'experience'            => $expMatch ? 'Yes' : 'No',
            'detected_degree'       => $detectedDegree,
            'candidate_profile'     => $candidateProfile
        ];
    }

    private function extractCandidateProfile($rawText, $text, $allExtractedSkills, $experienceDetails, $expyrs, $candidateDomain, $detectedDegree)
    {
        // 1. Professional Headline Detection
        $headline = 'Not specified in resume';
        $roleKeywords = [
            'Full Stack Developer', 'MERN Stack Developer', 'MEAN Stack Developer', 'Software Developer',
            'Software Engineer', 'Frontend Developer', 'Backend Developer', 'Web Developer',
            'PHP Developer', 'Python Developer', 'Java Developer', 'React Developer', 'Angular Developer',
            'Node.js Developer', 'Accounts Executive', 'Senior Accountant', 'Junior Accountant',
            'Finance Executive', 'HR Recruiter', 'HR Executive', 'HR Manager', 'Talent Acquisition Specialist',
            'Project Manager', 'Data Analyst', 'DevOps Engineer', 'QA Engineer', 'Sales Executive',
            'Business Development Executive', 'Customer Support Executive'
        ];

        foreach ($roleKeywords as $rk) {
            if (stripos($rawText, $rk) !== false) {
                $headline = $rk;
                break;
            }
        }

        if ($headline === 'Not specified in resume') {
            if ($candidateDomain === 'Software & IT') {
                $headline = 'Software / IT Professional';
            } elseif ($candidateDomain === 'Finance & Accounting') {
                $headline = 'Finance & Accounting Professional';
            } elseif ($candidateDomain === 'Human Resources') {
                $headline = 'Human Resources Professional';
            } else {
                $headline = ($candidateDomain !== 'General' && $candidateDomain !== 'Other / General' ? $candidateDomain : 'Professional') . ' Candidate';
            }
        }

        // 2. Current / Recent Role and Company
        $currentRole = 'Not specified in resume';
        $currentCompany = 'Not specified in resume';

        if (preg_match('/([A-Z0-9\.\&\-\s]{3,50}\s+(?:Pvt\.?\s*Ltd\.?|Ltd\.?|Inc\.?|Technologies|Labs|Solutions|Services|Software|Infotech|Systems|Corporation))/i', $rawText, $cm)) {
            $companyLines = preg_split('/\r\n|\r|\n/', trim($cm[1]));
            $rawCompany = trim(end($companyLines));
            if (preg_match('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $rawCompany)) {
                $cParts = preg_split('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $rawCompany);
                $rawCompany = trim($cParts[0], " \t\n\r\0\x0B.,-");
            }
            if (!empty($rawCompany) && strlen($rawCompany) >= 3) {
                $currentCompany = $rawCompany;
            }
        }

        if ($headline !== 'Not specified in resume' && strpos($headline, 'Candidate') === false) {
            $currentRole = $headline;
        }

        // 3. Education Details
        $degree = $detectedDegree !== 'Not Found' ? $detectedDegree : 'Not identified';
        $institution = 'Not specified in resume';
        $gradYear = 'Not specified in resume';

        if (preg_match('/(20\d{2}|19\d{2})/', $rawText, $ym)) {
            if (preg_match('/(?:education|bca|mca|b\.tech|m\.tech|b\.e|m\.e|bba|mba|bsc|msc|bcom|mcom|degree|diploma)[\s\S]{0,100}?(20\d{2}|19\d{2})/i', $rawText, $eym)) {
                $gradYear = $eym[1];
            }
        }

        if (preg_match('/([A-Z][A-Za-z0-9\.\,\s]{3,60}\s+(?:University|College|Institute|Academy|School))/i', $rawText, $um)) {
            $uLines = preg_split('/\r\n|\r|\n/', trim($um[1]));
            $institution = trim(end($uLines));
        }

        // 4. Training / Courses / Specializations
        $trainings = [];
        $trainingKeywords = [
            'MERN Full Stack', 'MEAN Stack', 'Python Full Stack', 'Java Full Stack', 'Full Stack Web Development',
            'Full Stack Development', 'AWS Certified', 'Azure Certified', 'Digital Marketing', 'Data Science',
            'Machine Learning', 'Tally GST', 'Tally Prime', 'SAP Certification', 'Agile Scrum'
        ];
        foreach ($trainingKeywords as $tk) {
            if (stripos($rawText, $tk) !== false) {
                $trainings[] = $tk;
            }
        }
        $trainingStr = !empty($trainings) ? implode(', ', array_unique($trainings)) : 'Not specified in resume';

        // 5. Categorized Technical Profile
        $skillsList = array_map('strtolower', $allExtractedSkills);

        $catMap = [
            'Frontend' => ['react', 'angular', 'vue', 'nextjs', 'nuxtjs', 'redux', 'html', 'css', 'javascript', 'typescript', 'bootstrap', 'tailwind', 'jquery', 'sass', 'material ui'],
            'Backend' => ['nodejs', 'express', 'php', 'laravel', 'codeigniter', 'python', 'django', 'flask', 'java', 'spring boot', 'c#', 'dotnet', 'golang', 'ruby', 'rest api', 'graphql'],
            'Database' => ['mongodb', 'mysql', 'postgresql', 'sql server', 'oracle', 'sqlite', 'redis', 'firebase', 'sql'],
            'API / Security' => ['rest api', 'graphql', 'jwt'],
            'DevOps / Tools' => ['docker', 'kubernetes', 'aws', 'azure', 'gcp', 'jenkins', 'git', 'ci/cd', 'linux'],
            'HR / Operations' => ['recruitment', 'payroll', 'hrms', 'onboarding', 'attendance', 'performance management', 'employee relations', 'exit process'],
            'Finance & Accounts' => ['accounting', 'billing', 'gst', 'taxation', 'audit', 'tally', 'financial reporting', 'accounts payable', 'accounts receivable'],
            'Sales & Marketing' => ['business development', 'lead generation', 'client acquisition', 'crm', 'sales', 'marketing', 'digital marketing', 'seo', 'sem', 'negotiation']
        ];

        $categorizedSkills = [];
        $assignedSkills = [];

        foreach ($catMap as $catName => $catSkills) {
            $foundInCat = [];
            foreach ($catSkills as $cs) {
                if (in_array($cs, $skillsList)) {
                    $foundInCat[] = ucwords($cs);
                    $assignedSkills[] = $cs;
                }
            }
            if (!empty($foundInCat)) {
                $categorizedSkills[$catName] = implode(', ', array_unique($foundInCat));
            }
        }

        $otherSkills = [];
        foreach ($skillsList as $sk) {
            if (!in_array($sk, $assignedSkills)) {
                $otherSkills[] = ucwords($sk);
            }
        }
        if (!empty($otherSkills)) {
            $categorizedSkills['Other Skills'] = implode(', ', array_unique($otherSkills));
        }

        // 6. Professional Summary (Strictly Factual)
        $summary = '';
        if ($headline !== 'Not specified in resume') {
            $summary .= $headline;
        } else {
            $summary .= 'Candidate';
        }
        if ($expyrs > 0) {
            $summary .= " with {$expyrs} years of total professional experience";
        }
        if (!empty($allExtractedSkills)) {
            $topSkills = array_slice(array_map('ucwords', array_unique($allExtractedSkills)), 0, 10);
            $summary .= " and hands-on exposure to " . implode(', ', $topSkills) . ".";
        } else {
            $summary .= ".";
        }

        // 7. Work History
        $workHistory = [];
        if (!empty($experienceDetails['jobs'])) {
            $jIndex = 1;
            foreach ($experienceDetails['jobs'] as $j) {
                $period = $j['from'] . ' - ' . $j['to'];

                $roleStr = !empty($j['role']) && strlen(trim($j['role'])) >= 2 ? trim($j['role']) : ($currentRole !== 'Not specified in resume' ? $currentRole : "Position #{$jIndex}");
                $compStr = !empty($j['company']) && strlen(trim($j['company'])) >= 2 ? trim($j['company']) : ($currentCompany !== 'Not specified in resume' ? $currentCompany : "Company");

                if (strlen($compStr) > 60 || preg_match('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $compStr)) {
                    $cParts = preg_split('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $compStr);
                    $compStr = trim($cParts[0], " \t\n\r\0\x0B.,-");
                    if (empty($compStr) || strlen($compStr) < 3) {
                        $compStr = ($currentCompany !== 'Not specified in resume' && !preg_match('/\b(ability|learn|adapt)\b/i', $currentCompany)) ? $currentCompany : 'Company';
                    }
                }

                if (strlen($roleStr) > 60 || preg_match('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $roleStr)) {
                    $rParts = preg_split('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $roleStr);
                    $roleStr = trim($rParts[0], " \t\n\r\0\x0B.,-");
                    if (empty($roleStr) || strlen($roleStr) < 3) {
                        $roleStr = ($currentRole !== 'Not specified in resume') ? $currentRole : "Role #{$jIndex}";
                    }
                }

                $workHistory[] = [
                    'role'     => $roleStr,
                    'company'  => $compStr,
                    'period'   => $period,
                    'duration' => $j['years'] . ' Yrs ' . $j['months'] . ' Mos'
                ];
                $jIndex++;
            }
        }

        // 8. Projects with Technology Stack Extraction
        $projects = [];
        $knownTechs = ['Salesforce', 'Apex', 'LWC', 'Flow', 'Sales Cloud', 'Service Cloud', 'Health Cloud', 'OmniStudio', 'Gearset', 'SOQL', 'JavaScript', 'HTML', 'CSS', 'React', 'Node', 'Express', 'Java', 'Spring Boot', 'Python', 'Django', 'Flask', 'PHP', 'CodeIgniter', 'Laravel', 'MySQL', 'PostgreSQL', 'MongoDB', 'Oracle', 'REST API', 'Basware', 'Tally', 'AWS', 'Azure', 'Docker', 'Kubernetes'];

        if (preg_match_all('/project[s]?\s*[:\-]\s*([A-Za-z0-9\s\,\&\.]{3,60})(?=\n|\r|role|$)/i', $rawText, $pm, PREG_OFFSET_CAPTURE)) {
            foreach ($pm[1] as $pMatch) {
                $pName = preg_replace('/\s+/', ' ', trim($pMatch[0]));
                $offset = $pMatch[1];

                if (strlen($pName) > 2 && !preg_match('/summary|experience|skills|education|details/i', $pName)) {
                    $snippet = substr($rawText, $offset, 400);

                    $techs = [];
                    if (preg_match('/(?:technologies|environment|tech stack|tools|built with)\s*[:\-]\s*(.*?)(?=\n|\r|$)/i', $snippet, $tm)) {
                        $techs[] = trim($tm[1]);
                    }

                    foreach ($knownTechs as $kt) {
                        if (stripos($snippet, $kt) !== false && !in_array($kt, $techs)) {
                            $techs[] = $kt;
                        }
                    }

                    $techStr = !empty($techs) ? implode(', ', array_slice(array_unique($techs), 0, 6)) : '';

                    $projects[] = [
                        'title'      => ucwords($pName),
                        'technology' => $techStr
                    ];
                }
            }
        }

        return [
            'headline'           => $headline,
            'current_role'       => $currentRole,
            'current_company'    => $currentCompany,
            'summary'            => $summary,
            'degree'             => $degree,
            'institution'        => $institution,
            'grad_year'          => $gradYear,
            'training'           => $trainingStr,
            'categorized_skills' => $categorizedSkills,
            'work_history'       => $workHistory,
            'projects'           => $projects,
            'domain'             => $candidateDomain ?: 'General'
        ];
    }

    private function analyzeDomainMismatch($candidateDomain, $jobDomain, $text, $allExtractedSkills, $vacancy)
    {
        $candidateDomain = $this->normalizeDomainName($candidateDomain);
        $jobDomain = $this->normalizeDomainName($jobDomain);

        $candidateEvidence = $this->collectDomainEvidence($candidateDomain, $text, $allExtractedSkills, $vacancy, true);
        $jobEvidence = $this->collectDomainEvidence($jobDomain, $text, $allExtractedSkills, $vacancy, false);

        $candidateConfidence = $this->scoreDomainConfidence($candidateEvidence);
        $jobConfidence = $this->scoreDomainConfidence($jobEvidence);

        if (empty($candidateDomain) || $candidateDomain === 'General' || $candidateDomain === 'Other / General') {
            return [
                'candidate_domain' => $candidateDomain ?: 'Other / General',
                'candidate_domain_confidence' => $candidateConfidence,
                'job_domain' => $jobDomain ?: 'Other / General',
                'job_domain_confidence' => $jobConfidence,
                'domain_status' => 'UNCLEAR',
                'domain_reason' => 'Candidate domain could not be confidently determined from the resume profile.',
                'domain_evidence' => array_values(array_unique(array_merge($candidateEvidence, $jobEvidence)))
            ];
        }

        if ($candidateDomain === $jobDomain) {
            return [
                'candidate_domain' => $candidateDomain,
                'candidate_domain_confidence' => $candidateConfidence,
                'job_domain' => $jobDomain,
                'job_domain_confidence' => $jobConfidence,
                'domain_status' => 'MATCHED',
                'domain_reason' => 'Candidate profile and vacancy belong to the same professional domain.',
                'domain_evidence' => array_values(array_unique(array_merge($candidateEvidence, $jobEvidence)))
            ];
        }

        $related = $this->isRelatedDomain($candidateDomain, $jobDomain);
        if ($related) {
            return [
                'candidate_domain' => $candidateDomain,
                'candidate_domain_confidence' => $candidateConfidence,
                'job_domain' => $jobDomain,
                'job_domain_confidence' => $jobConfidence,
                'domain_status' => 'RELATED',
                'domain_reason' => 'Candidate profile is broadly related to the vacancy domain but not a direct domain match.',
                'domain_evidence' => array_values(array_unique(array_merge($candidateEvidence, $jobEvidence)))
            ];
        }

        if ($candidateDomain !== 'Other / General' && $jobDomain !== 'Other / General' && $candidateDomain !== 'General' && $jobDomain !== 'General' && $candidateDomain !== $jobDomain && !$related && $candidateConfidence !== 'Low') {
            return [
                'candidate_domain' => $candidateDomain,
                'candidate_domain_confidence' => $candidateConfidence,
                'job_domain' => $jobDomain,
                'job_domain_confidence' => $jobConfidence,
                'domain_status' => 'WRONG_DOMAIN',
                'domain_reason' => "Wrong domain: The candidate's resume indicates a {$candidateDomain} background, while this vacancy belongs to {$jobDomain}. The candidate's professional domain does not align with the requirements of this position.",
                'domain_evidence' => array_values(array_unique(array_merge($candidateEvidence, $jobEvidence)))
            ];
        }

        return [
            'candidate_domain' => $candidateDomain,
            'candidate_domain_confidence' => $candidateConfidence,
            'job_domain' => $jobDomain,
            'job_domain_confidence' => $jobConfidence,
            'domain_status' => 'UNCLEAR',
            'domain_reason' => 'Domain evidence is not strong enough to confirm a mismatch or a clear match.',
            'domain_evidence' => array_values(array_unique(array_merge($candidateEvidence, $jobEvidence)))
        ];
    }

    private function normalizeDomainName($domain)
    {
        if (empty($domain)) {
            return 'Other / General';
        }

        $domain = trim((string)$domain);
        $domainMap = [
            'finance & accounts' => 'Finance & Accounting',
            'finance and accounting' => 'Finance & Accounting',
            'human resources' => 'Human Resources',
            'hr' => 'Human Resources',
            'software & it' => 'Software & IT',
            'software and it' => 'Software & IT',
            'sales & marketing' => 'Sales & Marketing',
            'sales and marketing' => 'Sales & Marketing',
            'operations & logistics' => 'Operations',
            'operations and logistics' => 'Operations',
            'supply chain / logistics' => 'Operations',
            'supply chain' => 'Operations',
            'customer support' => 'Customer Support',
            'design / creative' => 'Design / Creative',
            'design and creative' => 'Design / Creative',
            'healthcare & medical' => 'Healthcare',
            'healthcare and medical' => 'Healthcare',
            'legal' => 'Legal',
            'education' => 'Education',
            'other / general' => 'Other / General',
            'general' => 'Other / General',
            'administration' => 'Administration',
            'engineering' => 'Engineering'
        ];

        $lower = strtolower($domain);
        return $domainMap[$lower] ?? $domain;
    }

    private function collectDomainEvidence($domain, $text, $allExtractedSkills, $vacancy, $isCandidateDomain)
    {
        $domain = $this->normalizeDomainName($domain);
        $all = $this->getDomainCatalog();
        if (!isset($all[$domain])) {
            return [];
        }

        $evidence = [];
        $keywordList = $all[$domain]['keywords'];

        foreach ($keywordList as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $evidence[] = $keyword;
            }
        }

        foreach ((array)$allExtractedSkills as $skill) {
            $skillLower = strtolower(trim($skill));
            foreach ($keywordList as $keyword) {
                if (strtolower($keyword) === $skillLower) {
                    $evidence[] = $skill;
                    break;
                }
            }
        }

        if ($isCandidateDomain && !empty($vacancy['JobTitle'] ?? '')) {
            $jobTitleText = strtolower((string)$vacancy['JobTitle']);
            if (strpos($jobTitleText, strtolower($domain)) !== false) {
                $evidence[] = $vacancy['JobTitle'];
            }
        }

        return array_values(array_unique(array_filter(array_map(function($v) {
            return is_string($v) ? trim($v) : $v;
        }, $evidence))));
    }

    private function scoreDomainConfidence($evidence)
    {
        $count = is_array($evidence) ? count($evidence) : 0;
        if ($count >= 4) return 'High';
        if ($count >= 2) return 'Medium';
        return 'Low';
    }

    private function isRelatedDomain($candidateDomain, $jobDomain)
    {
        $relatedGroups = [
            'Software & IT' => ['Software & IT', 'Engineering', 'Design / Creative'],
            'Engineering' => ['Software & IT', 'Engineering'],
            'Design / Creative' => ['Software & IT', 'Design / Creative'],
            'Sales & Marketing' => ['Sales & Marketing'],
            'Human Resources' => ['Human Resources'],
            'Finance & Accounting' => ['Finance & Accounting'],
            'Operations' => ['Operations'],
            'Customer Support' => ['Customer Support'],
            'Healthcare' => ['Healthcare'],
            'Education' => ['Education'],
            'Legal' => ['Legal'],
            'Administration' => ['Administration']
        ];

        $candidateGroup = $relatedGroups[$candidateDomain] ?? [$candidateDomain];
        $jobGroup = $relatedGroups[$jobDomain] ?? [$jobDomain];
        foreach ($candidateGroup as $group) {
            if (in_array($group, $jobGroup, true)) {
                return true;
            }
        }

        return false;
    }

    private function detectCandidateDomain($text, $allExtractedSkills, $vacancy)
    {
        $resumeText = strtolower((string)$text);
        $scoreTable = [
            'Software & IT' => 0,
            'Human Resources' => 0,
            'Finance & Accounting' => 0,
            'Sales & Marketing' => 0,
            'Operations' => 0,
            'Administration' => 0,
            'Engineering' => 0,
            'Customer Support' => 0,
            'Healthcare' => 0,
            'Legal' => 0,
            'Design / Creative' => 0,
            'Education' => 0,
            'Other / General' => 0
        ];

        $domainRules = [
            'Software & IT' => [
                'frontend developer', 'full stack developer', 'mern stack developer', 'software developer', 'software engineer', 'php developer', 'java developer', 'python developer', 'devops engineer', 'backend developer', 'web developer', 'react', 'javascript', 'node.js', 'nodejs', 'mongodb', 'mysql', 'php', 'java', 'python', 'docker', 'kubernetes', 'aws', 'azure', 'html', 'css'
            ],
            'Human Resources' => [
                'hr executive', 'hr recruiter', 'talent acquisition specialist', 'hr manager', 'recruitment executive', 'recruiter', 'recruitment', 'onboarding', 'payroll', 'employee relations', 'interviewing'
            ],
            'Finance & Accounting' => [
                'accounts executive', 'accountant', 'finance executive', 'tax accountant', 'gst executive', 'audit executive', 'finance', 'accounting', 'accounts', 'gst', 'taxation', 'tally', 'audit', 'payroll', 'invoice'
            ],
            'Sales & Marketing' => [
                'sales executive', 'business development executive', 'digital marketing', 'marketing', 'sales', 'crm', 'lead generation', 'business development'
            ],
            'Operations' => [
                'operations executive', 'logistics', 'supply chain', 'inventory', 'warehouse', 'procurement'
            ],
            'Administration' => [
                'admin executive', 'administration', 'front desk', 'receptionist', 'office assistant'
            ],
            'Engineering' => [
                'mechanical engineer', 'civil engineer', 'electrical engineer', 'design engineer', 'engineering', 'manufacturing'
            ],
            'Customer Support' => [
                'customer support executive', 'customer support', 'customer service', 'helpdesk', 'support executive', 'technical support'
            ],
            'Healthcare' => [
                'doctor', 'nurse', 'healthcare', 'hospital', 'medical', 'pharmacy'
            ],
            'Legal' => [
                'lawyer', 'advocate', 'legal', 'litigation', 'compliance'
            ],
            'Design / Creative' => [
                'graphic designer', 'ui ux designer', 'product designer', 'designer', 'photoshop', 'figma', 'creative'
            ],
            'Education' => [
                'teacher', 'lecturer', 'professor', 'academics', 'education', 'faculty'
            ]
        ];

        foreach ($domainRules as $domain => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($resumeText, $keyword) !== false) {
                    $scoreTable[$domain] += 8;
                }
            }
        }

        foreach ((array)$allExtractedSkills as $skill) {
            $skillLower = strtolower(trim((string)$skill));
            foreach ($domainRules as $domain => $keywords) {
                if (in_array($skillLower, $keywords, true)) {
                    $scoreTable[$domain] += 5;
                }
            }
        }

        $bestDomain = 'Other / General';
        $bestScore = 0;

        foreach ($scoreTable as $domain => $score) {
            if ($score > $bestScore) {
                $bestDomain = $domain;
                $bestScore = $score;
            }
        }

        if ($bestScore < 5) {
            return 'Other / General';
        }

        return $this->normalizeDomainName($bestDomain);
    }

    private function detectJobDomain($vacancy)
    {
        $jobText = strtolower(implode(' ', [
            $vacancy['JobTitle'] ?? '',
            $vacancy['Departmentname'] ?? $vacancy['Department'] ?? '',
            $vacancy['RequiredSkills'] ?? '',
            $vacancy['MustHaveSkills'] ?? '',
            $vacancy['NiceToHaveSkills'] ?? '',
            $vacancy['EducationRequired'] ?? '',
            $vacancy['Description'] ?? '',
            $vacancy['Responsibilities'] ?? ''
        ]));

        $catalog = $this->getDomainCatalog();
        $scoreTable = [];
        foreach ($catalog as $domain => $data) {
            $scoreTable[$domain] = 0;
            foreach ($data['keywords'] as $keyword) {
                if (stripos($jobText, strtolower($keyword)) !== false) {
                    $scoreTable[$domain] += 2;
                }
            }
        }

        $bestDomain = 'Other / General';
        $bestScore = 0;
        foreach ($scoreTable as $domain => $score) {
            if ($score > $bestScore) {
                $bestDomain = $domain;
                $bestScore = $score;
            }
        }

        return $bestScore > 0 ? $this->normalizeDomainName($bestDomain) : 'Other / General';
    }

    private function getDomainCatalog()
    {
        return [
            'Software & IT' => [
                'keywords' => [
                    'frontend developer', 'react developer', 'full stack developer', 'mern stack developer', 'software engineer', 'software developer', 'php developer', 'java developer', 'python developer', 'devops engineer', 'backend developer', 'web developer', 'developer', 'engineer', 'software', 'programming', 'javascript', 'react', 'node.js', 'nodejs', 'php', 'java', 'python', 'mysql', 'mongodb', 'aws', 'azure', 'docker', 'kubernetes', 'git', 'qa', 'testing'
                ]
            ],
            'Human Resources' => [
                'keywords' => [
                    'human resources', 'hr executive', 'hr recruiter', 'talent acquisition', 'recruitment executive', 'recruiter', 'recruitment', 'payroll', 'onboarding', 'employee relations', 'hr manager', 'hr operations', 'performance management', 'training', 'interview', 'employee engagement'
                ]
            ],
            'Finance & Accounting' => [
                'keywords' => [
                    'accounts executive', 'accountant', 'finance executive', 'tax accountant', 'gst executive', 'audit executive', 'finance', 'accounting', 'accounts', 'gst', 'taxation', 'tally', 'audit', 'payroll', 'invoice', 'ledger', 'financial reporting'
                ]
            ],
            'Sales & Marketing' => [
                'keywords' => [
                    'sales', 'marketing', 'business development', 'sales executive', 'bdm', 'digital marketing', 'lead generation', 'crm', 'branding', 'customer acquisition', 'social media marketing'
                ]
            ],
            'Operations' => [
                'keywords' => [
                    'operations', 'warehouse', 'procurement', 'inventory', 'supply chain', 'logistics', 'vendor management', 'dispatch', 'distribution', 'facility management'
                ]
            ],
            'Administration' => [
                'keywords' => [
                    'admin', 'administration', 'office assistant', 'front desk', 'receptionist', 'document management', 'clerical', 'executive assistant', 'secretarial'
                ]
            ],
            'Engineering' => [
                'keywords' => [
                    'civil engineer', 'mechanical engineer', 'electrical engineer', 'production engineer', 'design engineer', 'engineering', 'manufacturing', 'maintenance'
                ]
            ],
            'Customer Support' => [
                'keywords' => [
                    'customer support', 'customer service', 'helpdesk', 'technical support', 'call center', 'support executive', 'service desk', 'client support'
                ]
            ],
            'Healthcare' => [
                'keywords' => [
                    'healthcare', 'hospital', 'medical', 'nurse', 'doctor', 'pharmacy', 'clinic', 'patient care', 'care coordinator'
                ]
            ],
            'Legal' => [
                'keywords' => [
                    'legal', 'lawyer', 'advocate', 'litigation', 'compliance', 'contract review', 'legal drafting'
                ]
            ],
            'Design / Creative' => [
                'keywords' => [
                    'designer', 'ui ux', 'graphic designer', 'web designer', 'photoshop', 'illustrator', 'creative', 'branding', 'figma', 'adobe'
                ]
            ],
            'Education' => [
                'keywords' => [
                    'teacher', 'lecturer', 'professor', 'training', 'academics', 'education', 'school', 'faculty', 'student counselor'
                ]
            ],
            'Other / General' => [
                'keywords' => []
            ]
        ];
    }

    private function extractAllSkillsFromText($text)
    {
        $foundSkills = [];
        $synonyms = $this->getSkillDictionary();

        foreach ($synonyms as $skill => $words) {
            foreach ($words as $word) {
                if (preg_match('/(?<!\w)' . preg_quote($word, '/') . '(?!\w)/i', $text)) {
                    $foundSkills[] = $skill;
                    break;
                }
            }
        }
        return array_unique($foundSkills);
    }

    private function getSkillDictionary()
    {
        return [
            // Programming Languages
            'php' => ['php'],
            'javascript' => ['javascript', 'js', 'ecmascript'],
            'typescript' => ['typescript', 'ts'],
            'python' => ['python'],
            'java' => ['java'],
            'c#' => ['c#', 'csharp', '.net'],
            'c++' => ['c++'],
            'golang' => ['golang', 'go'],
            'ruby' => ['ruby'],
            'swift' => ['swift'],
            'kotlin' => ['kotlin'],

            // Salesforce & Cloud CRM
            'salesforce' => ['salesforce', 'salesforce.com', 'sfdc'],
            'lwc' => ['lwc', 'lightning web components', 'lightning web component'],
            'apex' => ['apex', 'apex code', 'apex trigger', 'apex class'],
            'aura' => ['aura', 'aura components'],
            'soql' => ['soql', 'sosl'],
            'salesforce dx' => ['salesforce dx', 'sfdx'],
            'copado' => ['copado'],
            'gearset' => ['gearset'],

            // Frontend
            'html' => ['html', 'html5'],
            'css' => ['css', 'css3'],
            'bootstrap' => ['bootstrap'],
            'tailwind' => ['tailwind', 'tailwind css'],
            'sass' => ['sass', 'scss'],
            'jquery' => ['jquery'],
            'react' => ['react', 'reactjs', 'react.js'],
            'angular' => ['angular', 'angularjs'],
            'vue' => ['vue', 'vuejs'],
            'nextjs' => ['nextjs', 'next.js'],
            'nuxtjs' => ['nuxtjs', 'nuxt.js'],
            'redux' => ['redux'],
            'material ui' => ['material ui', 'mui'],

            // Backend
            'nodejs' => ['node', 'nodejs', 'node.js'],
            'express' => ['express', 'expressjs'],
            'laravel' => ['laravel'],
            'codeigniter' => ['codeigniter', 'ci'],
            'django' => ['django'],
            'flask' => ['flask'],
            'spring boot' => ['spring boot', 'springboot'],
            'dotnet' => ['.net', 'asp.net', 'dotnet'],
            'rest api' => ['rest', 'rest api', 'api'],
            'graphql' => ['graphql'],

            // Database
            'mysql' => ['mysql'],
            'postgresql' => ['postgres', 'postgresql'],
            'sql server' => ['sql server', 'mssql'],
            'oracle' => ['oracle'],
            'mongodb' => ['mongodb', 'mongo'],
            'sqlite' => ['sqlite'],
            'redis' => ['redis'],
            'firebase' => ['firebase'],
            'sql' => ['sql', 'structured query language'],

            // Cloud & DevOps
            'aws' => ['aws', 'amazon web services'],
            'azure' => ['azure', 'microsoft azure'],
            'gcp' => ['gcp', 'google cloud', 'google cloud platform'],
            'docker' => ['docker'],
            'kubernetes' => ['kubernetes', 'k8s'],
            'jenkins' => ['jenkins'],
            'git' => ['git', 'github', 'gitlab', 'bitbucket'],
            'ci/cd' => ['ci/cd', 'continuous integration', 'continuous deployment'],
            'linux' => ['linux', 'ubuntu', 'centos'],

            // HR
            'recruitment' => ['recruitment', 'hiring', 'talent acquisition', 'staffing', 'sourcing'],
            'payroll' => ['payroll', 'salary processing'],
            'hrms' => ['hrms', 'human resource management system'],
            'onboarding' => ['onboarding', 'employee onboarding'],
            'attendance' => ['attendance management'],
            'performance management' => ['performance management', 'appraisal', 'performance appraisal'],
            'employee relations' => ['employee relations'],
            'exit process' => ['exit interview', 'offboarding'],

            // Finance & Accounting
            'accounting' => ['accounting', 'accounts'],
            'billing' => ['billing', 'invoice', 'invoicing'],
            'gst' => ['gst'],
            'taxation' => ['tax', 'taxation'],
            'audit' => ['audit', 'financial audit'],
            'tally' => ['tally'],
            'financial reporting' => ['financial reporting', 'financial statements'],
            'accounts payable' => ['accounts payable'],
            'accounts receivable' => ['accounts receivable'],

            // Sales & Marketing
            'business development' => ['business development', 'bdm'],
            'lead generation' => ['lead generation'],
            'client acquisition' => ['client acquisition'],
            'crm' => ['crm', 'customer relationship management'],
            'sales' => ['sales'],
            'marketing' => ['marketing'],
            'digital marketing' => ['digital marketing'],
            'seo' => ['seo', 'search engine optimization'],
            'sem' => ['sem'],
            'content marketing' => ['content marketing'],
            'social media' => ['social media'],
            'negotiation' => ['negotiation'],

            // Healthcare
            'healthcare' => ['healthcare', 'medical', 'hospital'],
            'patient care' => ['patient care', 'patient management'],
            'pharmacy' => ['pharmacy'],
            'ehr' => ['ehr', 'emr'],

            // Procurement & Supply Chain
            'procurement' => ['procurement', 'purchasing'],
            'purchase order' => ['purchase order', 'po'],
            'vendor management' => ['vendor management'],
            'inventory management' => ['inventory', 'stock management'],
            'logistics' => ['logistics', 'supply chain'],

            // Project & Management
            'project management' => ['project management'],
            'agile' => ['agile', 'scrum'],
            'scrum master' => ['scrum master'],
            'kanban' => ['kanban'],
            'pmp' => ['pmp'],

            // Soft Skills & General
            'communication' => ['communication', 'verbal communication'],
            'leadership' => ['leadership', 'team lead', 'team leadership'],
            'problem solving' => ['problem solving'],
            'analytical skills' => ['analytical'],
            'time management' => ['time management'],
            'documentation' => ['documentation']
        ];
    }

    private function synonymMatch($skill, $text)
    {
        $skill = strtolower(trim($skill));
        $text  = strtolower($text);

        if (preg_match('/(?<!\w)' . preg_quote($skill, '/') . '(?!\w)/i', $text)) {
            return true;
        }

        $synonyms = $this->getSkillDictionary();

        if (!isset($synonyms[$skill])) {
            return false;
        }

        foreach ($synonyms[$skill] as $word) {
            if (preg_match('/(?<!\w)' . preg_quote($word, '/') . '(?!\w)/i', $text)) {
                return true;
            }
        }

        return false;
    }

    private function fuzzyMatch($word, $text, $threshold = 70)
    {
        foreach (explode(' ', $text) as $w) {
            similar_text($word, $w, $percent);
            if ($percent >= $threshold) return true;
        }
        return false;
    }

    private function extractText($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        try {
            if ($ext == 'txt') {
                return trim(file_get_contents($file));
            } elseif ($ext == 'docx') {
                return trim($this->readDocx($file));
            } elseif ($ext == 'pdf') {
                if (!file_exists($file)) {
                    $this->logStage('PDF_ERROR', 'PDF file not found : ' . $file);
                    return '';
                }
                $text = '';
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf    = $parser->parseFile($file);
                    $text   = $pdf->getText();
                } catch (\Exception $e) {
                    $this->logStage('PDF_SMALOT_EXCEPTION', $e->getMessage());
                }

                if (empty(trim($text))) {
                    $text = $this->fallbackPdfText($file);
                }

                if (empty(trim($text))) {
                    $this->logStage('PDF_ERROR', 'No text extracted from PDF.');
                    return '';
                }
                $this->logStage('PDF_EXTRACT_SUCCESS', substr($text, 0, 1000));
                return trim($text);
            } else {
                $this->logStage('ERROR', 'Unsupported file type : ' . $ext);
                return '';
            }
        } catch (\Exception $e) {
            $this->logStage('PDF_EXCEPTION', $e->getMessage());
            return '';
        }
    }

    private function fallbackPdfText($file)
    {
        $content = @file_get_contents($file);
        if (!$content) return '';

        $text = '';
        if (preg_match_all('/stream[\r\n]+([\s\S]*?)[\r\n]+endstream/i', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $uncompressed = @gzuncompress($stream);
                if ($uncompressed === false) {
                    $uncompressed = @gzinflate($stream);
                }
                $dataToParse = ($uncompressed !== false) ? $uncompressed : $stream;

                if (preg_match_all('/\((.*?)\)\s*Tj/i', $dataToParse, $tjMatches)) {
                    foreach ($tjMatches[1] as $t) {
                        $text .= $t . " ";
                    }
                }
                if (preg_match_all('/\[\s*(.*?)\s*\]\s*TJ/i', $dataToParse, $tjMatches)) {
                    foreach ($tjMatches[1] as $tArr) {
                        if (preg_match_all('/\((.*?)\)/', $tArr, $subMatches)) {
                            foreach ($subMatches[1] as $sub) {
                                $text .= $sub . " ";
                            }
                        }
                    }
                }
            }
        }

        $text = preg_replace_callback('/\\\\([0-7]{1,3})/', function($m) {
            return chr(octdec($m[1]));
        }, $text);
        $text = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $text);

        if (strlen(trim($text)) < 50) {
            preg_match_all('/[\x20-\x7E]{3,}/', $content, $rawStrings);
            $cleanWords = [];
            foreach ($rawStrings[0] as $str) {
                if (!preg_match('/^\/|^<|^>|Obj|endobj|stream|endstream|Catalog|Pages|Font|Length/i', $str)) {
                    $cleanWords[] = $str;
                }
            }
            $text = implode(" ", $cleanWords);
        }

        return trim($text);
    }

    private function readDocx($file)
    {
        $zip = new ZipArchive;
        $content = '';
        if ($zip->open($file) === TRUE) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $xml = str_replace(['</w:p>', '<w:br/>', '<w:br>', '</w:tr>'], "\n", $xml);
                $content = strip_tags($xml);
            }
            $zip->close();
        }
        return $content;
    }

    private function extractExperienceDetails($text)
    {
        $expText = $text;
        if (preg_match('/(?:professional experience|work experience|employment history|career history|work history)(.*?)(?=education|declaration|certifications|$)/is', $text, $em)) {
            $expText = $em[1];
        } else {
            $start = false;
            $expKeywords = [
                'experience', 'work experience', 'professional experience', 'employment',
                'internship', 'career history', 'employment history', 'work history', 'professional background'
            ];

            foreach ($expKeywords as $word) {
                $pos = stripos($text, $word);
                if ($pos !== false) {
                    $start = $pos;
                    break;
                }
            }

            if ($start !== false) {
                $expText = substr($text, $start);
            }
        }

        $jobs = [];
        $cleanText = str_replace(['–', '—', '&ndash;', '&mdash;'], '-', $expText);
        $cleanText = preg_replace('/(\d+)\s*[\t\r\n]+\s*(\d+)/', '$1$2', $cleanText);
        $cleanText = preg_replace('/(\d{1,2})(st|nd|rd|th)/i', '$1', $cleanText);
        preg_match_all('/
        (
        (jan|feb|mar|apr|may|jun|jul|aug|sep|sept|oct|nov|dec)[a-z]*[\s\-\.]*\d{4}
        |
        \d{1,2}[\/\-\.]\d{4}
        |
        \d{4}
        )
        \s*(?:to|-)\s*
        (
        present|current|now
        |
        (jan|feb|mar|apr|may|jun|jul|aug|sep|sept|oct|nov|dec)[a-z]*[\s\-\.]*\d{4}
        |
        \d{1,2}[\/\-\.]\d{4}
        |
        \d{4}
        )
        /ix', $cleanText, $matches, PREG_OFFSET_CAPTURE);

        $eduKeywordsPattern = '/\b(bachelor|master|b\.?tech|m\.?tech|b\.?e|m\.?e|b\.?sc|m\.?sc|b\.?com|m\.?com|bba|mba|bca|mca|phd|diploma|degree|college|university|institute|school|academy|sslc|hsc|10th|12th|education|academic|passed out|cgpa|percentage)\b/i';

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $rangeStr = $match[0];
                $offset   = $match[1];

                $parts = preg_split('/\s*(?:to|-)\s*/i', $rangeStr);
                if (count($parts) < 2) continue;

                $from = trim($parts[0], " \t\n\r\0\x0B()[],.-");
                $to   = trim($parts[1], " \t\n\r\0\x0B()[],.-");

                if (preg_match('/^\d{4}$/', $from)) $from .= " jan";
                if (preg_match('/^\d{4}$/', $to))   $to   .= " jan";

                $fromDate = strtotime($from);
                $toDate   = (stripos($to, 'present') !== false || stripos($to, 'current') !== false || stripos($to, 'now') !== false) ? time() : strtotime($to);

                if (!$fromDate || !$toDate || $toDate < $fromDate) continue;

                $months = floor(($toDate - $fromDate) / (60*60*24*30));
                $years  = floor($months / 12);
                $remMon = $months % 12;

                // Extract preceding lines for specific role & company
                $beforeText = substr($cleanText, 0, $offset);
                $lines = array_values(array_filter(array_map('trim', explode("\n", $beforeText))));

                $compStr = isset($lines[count($lines)-1]) ? $lines[count($lines)-1] : '';
                $roleStr = isset($lines[count($lines)-2]) ? $lines[count($lines)-2] : '';

                // Clean bullet symbols and unprintable unicode characters
                $compStr = preg_replace('/[^\x20-\x7E]/', '', $compStr);
                $roleStr = preg_replace('/[^\x20-\x7E]/', '', $roleStr);
                $compStr = preg_replace('/\s+/', ' ', trim($compStr));
                $roleStr = preg_replace('/\s+/', ' ', trim($roleStr));

                // Filter out Education entries
                if (preg_match($eduKeywordsPattern, $roleStr) || preg_match($eduKeywordsPattern, $compStr)) {
                    continue;
                }

                if (preg_match('/employer\s*[:\-]\s*(.*)/i', $compStr, $empMatch)) {
                    $compStr = trim($empMatch[1]);
                } elseif (preg_match('/tenure|period|employment/i', $compStr)) {
                    if (preg_match('/employer\s*[:\-]\s*(.*)/i', $beforeText, $empMatch2)) {
                        $compStr = trim($empMatch2[1]);
                    }
                }
                if (preg_match('/designation\s*[:\-]\s*(.*)/i', $roleStr, $desMatch)) {
                    $roleStr = trim($desMatch[1]);
                }

                if (empty($roleStr) || strlen($roleStr) < 3 || preg_match('/developments|experience|summary|skills|projects|tenure|employer/i', $roleStr)) {
                    $afterText = substr($cleanText, $offset + strlen($rangeStr));
                    $afterLines = array_values(array_filter(array_map('trim', explode("\n", $afterText))));
                    $checkAfter = isset($afterLines[0]) ? preg_replace('/[^\x20-\x7E]/', '', $afterLines[0]) : '';
                    if (preg_match('/designation\s*[:\-]\s*(.*)/i', $checkAfter, $desMatch2)) {
                        $roleStr = trim($desMatch2[1]);
                    } elseif (preg_match('/(.*?)\s*(?:-|–|—|at|@)\s*(.*)/i', $compStr, $splitM)) {
                        $roleStr = trim($splitM[1]);
                        $compStr = trim($splitM[2]);
                    }
                }

                if (preg_match('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $compStr)) {
                    $cParts = preg_split('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $compStr);
                    $compStr = trim($cParts[0], " \t\n\r\0\x0B.,-");
                }
                if (strlen($compStr) > 50) $compStr = substr($compStr, 0, 50);

                if (preg_match('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $roleStr)) {
                    $rParts = preg_split('/\b(ability|learn|adapt|technologies|experience|responsible|skills|worked|working|soft skills|technical skills)\b/i', $roleStr);
                    $roleStr = trim($rParts[0], " \t\n\r\0\x0B.,-");
                }
                if (strlen($roleStr) > 50) $roleStr = substr($roleStr, 0, 50);

                $jobs[] = [
                    "from"    => $from,
                    "to"      => $to,
                    "years"   => $years,
                    "months"  => $remMon,
                    "role"    => ucwords(strtolower($roleStr)),
                    "company" => ucwords(strtolower($compStr))
                ];
            }
        }

        if (empty($jobs)) {
            if (preg_match('/employer\s*[:\-]\s*(.*?)(?=\n|\r|$)/i', $text, $empM)) {
                $comp = preg_replace('/\s+/', ' ', trim($empM[1]));
                $role = 'Professional';
                if (preg_match('/designation\s*[:\-]\s*(.*?)(?=\n|\r|$)/i', $text, $desM)) {
                    $role = preg_replace('/\s+/', ' ', trim($desM[1]));
                }
                $from = 'Jan 2022';
                $to = 'Present';
                if (preg_match('/tenure\s*[:\-]\s*(.*?)(?=\n|\r|$)/i', $text, $tenM)) {
                    $tenStr = str_replace(['–', '—'], '-', trim($tenM[1]));
                    $parts = explode('-', $tenStr);
                    if (count($parts) >= 2) {
                        $from = trim($parts[0]);
                        $to = trim($parts[1]);
                    }
                }
                $fromDate = strtotime($from) ?: time();
                $toDate   = (stripos($to, 'present') !== false || stripos($to, 'current') !== false || stripos($to, 'now') !== false) ? time() : (strtotime($to) ?: time());
                $months   = max(1, floor(($toDate - $fromDate) / (60*60*24*30)));
                $years    = floor($months / 12);
                $remMon   = $months % 12;

                $jobs[] = [
                    "from"    => $from,
                    "to"      => $to,
                    "years"   => $years,
                    "months"  => $remMon,
                    "role"    => ucwords($role),
                    "company" => ucwords($comp)
                ];
            }
        }

        $uniqueJobs = [];
        foreach ($jobs as $job) {
            $key = $job['from'] . '-' . $job['to'];
            if (!isset($uniqueJobs[$key])) {
                $uniqueJobs[$key] = $job;
            }
        }
        $jobs = array_values($uniqueJobs);
        $totalMonths = 0;

        foreach ($jobs as $job) {
            $fromDate = strtotime($job['from']);
            $toDate   = (stripos($job['to'], 'present') !== false || stripos($job['to'], 'current') !== false || stripos($job['to'], 'now') !== false) ? time() : strtotime($job['to']);
            $months   = floor(($toDate - $fromDate) / (60*60*24*30));
            $totalMonths += $months;
        }

        $totalYears = floor($totalMonths / 12);
        $totalRem   = $totalMonths % 12;

        return [
            "jobs"  => $jobs,
            "total" => $totalYears . " Years " . $totalRem . " Months"
        ];
    }

    private function logStage($stage, $data)
    {
        $log = "\n==============================\n";
        $log .= date('Y-m-d H:i:s') . " | " . $stage . "\n";

        if (is_array($data)) {
            $log .= print_r($data, true);
        } else {
            $log .= $data;
        }

        file_put_contents(
            APPPATH . 'logs/ats_debug.log',
            $log,
            FILE_APPEND
        );
    }
}