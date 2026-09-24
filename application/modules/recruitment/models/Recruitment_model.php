<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recruitment_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // VACANCY HELPERS
    // =========================================================================

    /**
     * Get all active/approved vacancies for the public position dropdown.
     * Uses raw SQL to avoid CI query builder alias/escaping conflicts.
     */
    public function getActiveVacancies()
    {
        $sql = "SELECT
                    jl.Jid,
                    jl.JobTitle,
                    jl.JobCode,
                    d.Departmentname
                FROM IHRJobsList jl
                LEFT JOIN Resource_Requests rr ON rr.ConvertedJid = jl.Jid
                LEFT JOIN Departments d ON d.Did = jl.Did
                WHERE (rr.RequestId IS NULL OR rr.Status = 'ASSIGNED')
                  AND jl.JobStatus IN ('Open', 'Re-Open', 'Active', 'active')
                GROUP BY jl.Jid
                ORDER BY jl.JobTitle ASC";

        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        }

        // Fallback: without Resource_Requests join
        $fallback = $this->db->query(
            "SELECT jl.Jid, jl.JobTitle, jl.JobCode, d.Departmentname
             FROM IHRJobsList jl
             LEFT JOIN Departments d ON d.Did = jl.Did
             WHERE jl.JobStatus IN ('Open', 'Re-Open', 'Active', 'active')
             GROUP BY jl.Jid
             ORDER BY jl.JobTitle ASC"
        );

        return ($fallback && $fallback->num_rows() > 0) ? $fallback->result_array() : array();
    }

    /**
     * Fetch a single vacancy by Jid and verify it is approved/active.
     * Used during form submission to validate the selected position.
     *
     * @param  int $jid
     * @return array|null  Full vacancy row, or null if not eligible.
     */
    public function getActiveVacancyById($jid)
    {
        $jid = (int)$jid;
        if ($jid <= 0) {
            return null;
        }

        $sql = "SELECT
                    jl.Jid,
                    jl.JobTitle,
                    jl.JobCode,
                    jl.JobStatus,
                    jl.ExpiryDate,
                    jl.AssignedRecruiterManagerId,
                    d.Departmentname
                FROM IHRJobsList jl
                LEFT JOIN Resource_Requests rr ON rr.ConvertedJid = jl.Jid
                LEFT JOIN Departments d ON d.Did = jl.Did
                WHERE jl.Jid = {$jid}
                  AND (rr.RequestId IS NULL OR rr.Status = 'ASSIGNED')
                  AND jl.JobStatus IN ('Open', 'Re-Open', 'Active', 'active')
                GROUP BY jl.Jid
                LIMIT 1";

        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0) {
            return $query->row_array();
        }

        // Fallback — just check by Jid + JobStatus without resource_request join
        $fallback = $this->db->query(
            "SELECT jl.Jid, jl.JobTitle, jl.JobCode, jl.JobStatus, jl.ExpiryDate,
                    jl.AssignedRecruiterManagerId, d.Departmentname
             FROM IHRJobsList jl
             LEFT JOIN Departments d ON d.Did = jl.Did
             WHERE jl.Jid = {$jid}
               AND jl.JobStatus IN ('Open', 'Re-Open', 'Active', 'active')
             LIMIT 1"
        );

        if ($fallback && $fallback->num_rows() > 0) {
            return $fallback->row_array();
        }

        return null;
    }

    // =========================================================================
    // DUPLICATE APPLICATION CHECK & TOKEN LOOKUP
    // =========================================================================

    /**
     * Check if a candidate has already applied for the SAME vacancy (Jid).
     * Logic mirrors Ats.php: match Email OR PhoneNo in IHrCandidates WHERE Jid = $jid.
     * Same candidate + different vacancy is NOT a duplicate.
     *
     * @param  string $email
     * @param  string $phone
     * @param  int    $jid
     * @return bool
     */
    public function checkDuplicateApplication($email, $phone, $jid)
    {
        $jid   = (int)$jid;
        $where = [];

        $cleanEmail = trim((string)$email);
        $cleanPhone = trim((string)$phone);

        if ($cleanEmail !== '') {
            $where[] = "c.Email = " . $this->db->escape($cleanEmail);
        }
        if ($cleanPhone !== '') {
            $where[] = "c.PhoneNo = " . $this->db->escape($cleanPhone);
        }

        if (empty($where) || $jid <= 0) {
            return false;
        }

        $orClause = '(' . implode(' OR ', $where) . ')';

        $sql = "SELECT c.CandidateId
                FROM IHrCandidates c
                INNER JOIN JobApplications ja ON ja.CandidateId = c.CandidateId
                WHERE c.Jid = {$jid}
                  AND {$orClause}
                LIMIT 1";

        $query = $this->db->query($sql);

        return ($query && $query->num_rows() > 0);
    }

    /**
     * Get existing application details for the "already applied" response.
     *
     * @param  string $email
     * @param  string $phone
     * @param  int    $jid
     * @return array|null
     */
    public function getExistingApplicationInfo($email, $phone, $jid)
    {
        $jid   = (int)$jid;
        $where = [];

        $cleanEmail = trim((string)$email);
        $cleanPhone = trim((string)$phone);

        if ($cleanEmail !== '') {
            $where[] = "c.Email = " . $this->db->escape($cleanEmail);
        }
        if ($cleanPhone !== '') {
            $where[] = "c.PhoneNo = " . $this->db->escape($cleanPhone);
        }

        if (empty($where) || $jid <= 0) {
            return null;
        }

        $orClause = '(' . implode(' OR ', $where) . ')';

        $sql = "SELECT
                    c.CandidateId,
                    c.CandidateCode,
                    c.Fullname,
                    ja.ApplicationId,
                    ja.ApplicationToken,
                    ja.CurrentStatus,
                    ja.CurrentStage,
                    ja.AppliedOn,
                    jl.JobTitle,
                    jl.JobCode
                FROM IHrCandidates c
                INNER JOIN JobApplications ja ON ja.CandidateId = c.CandidateId
                INNER JOIN IHRJobsList jl ON jl.Jid = ja.Jid
                WHERE c.Jid = {$jid}
                  AND ja.Jid = {$jid}
                  AND {$orClause}
                ORDER BY ja.ApplicationId DESC
                LIMIT 1";

        $query = $this->db->query($sql);

        return ($query && $query->num_rows() > 0) ? $query->row_array() : null;
    }

    /**
     * Find JobApplication + Candidate details by secure ApplicationToken.
     * Database is the source of truth.
     *
     * @param  string $token
     * @return array|null
     */
    public function getApplicationByToken($token)
    {
        $cleanToken = trim((string)$token);
        if (empty($cleanToken)) {
            return null;
        }

        $sql = "SELECT 
                    ja.ApplicationId,
                    ja.ApplicationToken,
                    ja.Jid,
                    ja.CandidateId,
                    ja.CurrentStage,
                    ja.CurrentStatus,
                    ja.AppliedOn,
                    c.CandidateCode,
                    c.Fullname,
                    c.Email,
                    c.PhoneNo,
                    c.ExpYrs,
                    c.ResumePath,
                    c.Source,
                    jl.JobTitle,
                    jl.JobCode,
                    d.Departmentname
                FROM JobApplications ja
                INNER JOIN IHrCandidates c ON c.CandidateId = ja.CandidateId
                INNER JOIN IHRJobsList jl ON jl.Jid = ja.Jid
                LEFT JOIN Departments d ON d.Did = jl.Did
                WHERE ja.ApplicationToken = " . $this->db->escape($cleanToken) . "
                LIMIT 1";

        $query = $this->db->query($sql);

        return ($query && $query->num_rows() > 0) ? $query->row_array() : null;
    }

    // =========================================================================
    // CANDIDATE EXPERIENCE FEEDBACK HELPERS
    // =========================================================================

    /**
     * Check if feedback has already been submitted for a specific ApplicationId.
     *
     * @param  int $applicationId
     * @return array|null
     */
    public function getCandidateFeedback($applicationId)
    {
        $appId = (int)$applicationId;
        if ($appId <= 0) {
            return null;
        }

        $sql = "SELECT * FROM CandidateFeedback WHERE ApplicationId = {$appId} LIMIT 1";
        $query = $this->db->query($sql);

        return ($query && $query->num_rows() > 0) ? $query->row_array() : null;
    }

    /**
     * Store candidate experience feedback into CandidateFeedback table.
     * Prevents duplicate insertion for the same ApplicationId.
     *
     * @param  array $data
     * @return array ['success'=>bool, 'already'=>bool, 'error'=>string]
     */
    public function submitCandidateFeedback($data)
    {
        $appId       = (int)$data['application_id'];
        $candidateId = (int)$data['candidate_id'];

        if ($appId <= 0 || $candidateId <= 0) {
            return ['success' => false, 'error' => 'Invalid application reference.'];
        }

        // Check if feedback already exists
        $existing = $this->getCandidateFeedback($appId);
        if (!empty($existing)) {
            return [
                'success'      => false,
                'already'      => true,
                'feedback'     => $existing,
                'submitted_at' => !empty($existing['SubmittedAt']) ? date('d M Y, h:i A', strtotime($existing['SubmittedAt'])) : '',
                'error'        => 'Your feedback has already been recorded.'
            ];
        }

        $row = [
            'ApplicationId'      => $appId,
            'CandidateId'        => $candidateId,
            'OverallExperience'  => max(1, min(5, (int)($data['overall_experience'] ?? 5))),
            'ApplicationProcess' => max(1, min(5, (int)($data['application_process'] ?? 5))),
            'EaseOfApplying'     => max(1, min(5, (int)($data['ease_of_applying'] ?? 5))),
            'Communication'      => max(1, min(5, (int)($data['communication'] ?? 5))),
            'Comments'           => !empty($data['comments']) ? trim($data['comments']) : null,
            'SubmittedAt'        => date('Y-m-d H:i:s')
        ];

        $inserted = $this->db->insert('CandidateFeedback', $row);

        if ($inserted) {
            return [
                'success'      => true,
                'feedback_id'  => $this->db->insert_id(),
                'submitted_at' => date('d M Y, h:i A')
            ];
        }

        return ['success' => false, 'error' => 'Failed to record feedback. Please try again.'];
    }

    // =========================================================================
    // SUBMIT PUBLIC APPLICATION (TRANSACTION)
    // =========================================================================

    /**
     * Atomically create a candidate record, application (with secure token), and initial stage tracking.
     * Mirrors the exact insert pattern from Ats.php::analyzeResumeModal().
     *
     * @param  array $data
     * @return array ['success'=>bool, 'candidate_id'=>int, 'application_id'=>int, 'application_token'=>string, 'error'=>string]
     */
    public function submitPublicApplication($data)
    {
        $vacancy   = $data['vacancy'];
        $atsResult = isset($data['ats_result']) && is_array($data['ats_result']) ? $data['ats_result'] : null;

        // Generate secure random access token for this application
        $appToken = bin2hex(random_bytes(16));

        // ---- Build IHrCandidates row ----
        $candidateCode = 'CAND-' . $vacancy['Jid'] . '-' . date('Ymd') . rand(100, 999);

        // Skills: prefer ATS result if available, else use form-entered skills
        $matchedSkills = '';
        if ($atsResult && !empty($atsResult['all_extracted_skills'])) {
            $matchedSkills = (string)$atsResult['all_extracted_skills'];
        } elseif ($atsResult && !empty($atsResult['matched_skills'])) {
            $matchedSkills = (string)$atsResult['matched_skills'];
        } elseif (!empty($data['skills'])) {
            $matchedSkills = (string)$data['skills'];
        }

        $expYrs = !empty($data['experience_years']) ? (float)$data['experience_years'] : null;
        $expYrsInt = ($expYrs !== null) ? (int)round($expYrs) : null;

        $educationMatch  = ($atsResult && isset($atsResult['education_match']))  ? $atsResult['education_match']  : '';
        $experienceMatch = ($atsResult && isset($atsResult['experience']))        ? $atsResult['experience']       : '';
        $profileMatch    = ($atsResult && isset($atsResult['recommendation']))    ? $atsResult['recommendation']   : 'Review Required';
        $domainBreakdown = ($atsResult && isset($atsResult['domain']))            ? $atsResult['domain']           : 'General';
        $expDetails      = ($atsResult && isset($atsResult['experience_details'])) ? json_encode($atsResult['experience_details']) : null;

        $scoreBreakdown = null;
        if ($atsResult) {
            $scoreBreakdown = json_encode([
                'recommendation'        => $atsResult['recommendation']        ?? '',
                'recommendation_reason' => $atsResult['recommendation_reason'] ?? '',
                'relevant_evidence'     => $atsResult['relevant_evidence']     ?? '',
                'missing_requirements'  => $atsResult['missing_requirements']  ?? '',
                'candidate_profile'     => $atsResult['candidate_profile']     ?? null,
                'domain'                => $atsResult['domain']                ?? 'General',
                'candidate_domain'      => $atsResult['candidate_domain']      ?? ($atsResult['domain'] ?? 'General'),
                'job_domain'            => $atsResult['job_domain']            ?? 'General',
                'domain_status'         => $atsResult['domain_status']         ?? 'UNCLEAR',
                'domain_analysis'       => $atsResult['domain_analysis']       ?? null,
                'matched_skills'        => $atsResult['matched_skills']        ?? '',
                'missing_skills'        => $atsResult['missing_skills']        ?? '',
                'all_extracted_skills'  => $atsResult['all_extracted_skills']  ?? '',
                'education_match'       => $atsResult['education_match']       ?? '',
                'experience'            => $atsResult['experience']            ?? '',
                'detected_degree'       => $atsResult['detected_degree']       ?? '',
                'cover_letter'          => $data['cover_letter']               ?? '',
            ]);
        }

        $candidateRow = [
            'CandidateCode'     => $candidateCode,
            'Jid'               => (int)$vacancy['Jid'],
            'JobCode'           => $vacancy['JobCode'] ?? '',
            'Fullname'          => trim($data['full_name']),
            'Email'             => strtolower(trim($data['email'])),
            'PhoneNo'           => trim($data['phone']),
            'ExpYrs'            => $expYrsInt,
            'ResumePath'        => $data['resume_path'],
            'Source'            => 'Online',
            'ATS_Stage'         => 1,
            'MatchedSkills'     => $matchedSkills,
            'EducationMatch'    => $educationMatch,
            'ExperienceMatch'   => $experienceMatch,
            'ProfileMatchPer'   => $profileMatch,
            'DomainBreakdown'   => $domainBreakdown,
            'ExperienceDetails' => $expDetails,
            'ScoreBreakdown'    => $scoreBreakdown,
            'VerifiedAt'        => date('Y-m-d H:i:s'),
        ];

        // ---- Build JobApplications row ----
        $applicationRow = [
            'Jid'              => (int)$vacancy['Jid'],
            'ApplicationToken' => $appToken,
            'StageId'          => null,
            'CurrentStage'     => 'Application Created',
            'CurrentStatus'    => 'CV Uploaded',
        ];

        // ---- Build CandidateStageTracking row ----
        $trackingRow = [
            'StageId'  => 1,
            'Action'   => 'Created',
            'Remarks'  => 'Public portal application submitted'
                          . (!empty($data['cover_letter']) ? ' | Cover Letter: ' . mb_substr($data['cover_letter'], 0, 200) : ''),
        ];

        // ================================================================
        // TRANSACTION
        // ================================================================
        $this->db->trans_begin();

        try {
            // 1. Insert IHrCandidates
            $this->db->insert('IHrCandidates', $candidateRow);
            $candidateId = $this->db->insert_id();

            if (!$candidateId) {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => 'Failed to create candidate record.'];
            }

            // 2. Insert JobApplications
            $applicationRow['CandidateId'] = $candidateId;
            $this->db->insert('JobApplications', $applicationRow);
            $applicationId = $this->db->insert_id();

            if (!$applicationId) {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => 'Failed to create application record.'];
            }

            // 3. Insert CandidateStageTracking
            $trackingRow['ApplicationId'] = $applicationId;
            $this->db->insert('CandidateStageTracking', $trackingRow);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => 'Database transaction failed. Please try again.'];
            }

            $this->db->trans_commit();

            return [
                'success'           => true,
                'candidate_id'      => $candidateId,
                'candidate_code'    => $candidateCode,
                'application_id'    => $applicationId,
                'application_token' => $appToken,
                'job_title'         => $vacancy['JobTitle'],
                'job_code'          => $vacancy['JobCode'] ?? '',
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Public Application Submission Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'An unexpected error occurred. Please try again.'];
        }
    }

    /**
     * Get raw jobs list query for debugging and diagnostics
     *
     * @return CI_DB_result
     */
    public function getRawJobsQuery()
    {
        return $this->db->query(
            "SELECT jl.Jid, jl.JobTitle, jl.JobCode, jl.JobStatus FROM IHRJobsList jl"
        );
    }
}
