<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->helper('string');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->model('recruitment/recruitment_model');
    }

    // =========================================================================
    // PUBLIC APPLY PAGE
    // =========================================================================

    /**
     * Public Candidate Application Page
     * Backend determines if an active session application token exists.
     */
    public function apply()
    {
        // Check if session has an active application token
        $activeToken = $this->session->userdata('active_app_token');
        if (!empty($activeToken)) {
            $app = $this->recruitment_model->getApplicationByToken($activeToken);
            if (!empty($app)) {
                // Application exists in DB! Redirect to state-based application page
                redirect(base_url('recruitment/user/application/' . $activeToken));
                return;
            }
        }

        $data = array();
        $data['page_title'] = 'Apply for a Position | Recruitment Portal';
        $data['vacancies']  = $this->recruitment_model->getActiveVacancies();

        $this->load->view('apply', $data);
    }

    // =========================================================================
    // SECURE APPLICATION TOKEN ACCESS & STATE DETERMINATION
    // =========================================================================

    /**
     * Candidate Application Page / State Router
     * URL: /recruitment/user/application/{token}
     *
     * Database is the SINGLE SOURCE OF TRUTH:
     * 1. Validates token in DB.
     * 2. Checks if feedback already submitted.
     * 3. Renders:
     *    - feedback.php (if feedback pending)
     *    - feedback_submitted.php (if feedback submitted)
     *    - apply.php (if token invalid)
     */
    public function application($token = null)
    {
        $token = trim((string)$token);

        if (empty($token)) {
            // Fallback to session active token
            $token = $this->session->userdata('active_app_token');
        }

        if (empty($token)) {
            redirect(base_url('recruitment/user/apply'));
            return;
        }

        // 1. Database lookup by secure token
        $app = $this->recruitment_model->getApplicationByToken($token);

        if (empty($app)) {
            // Invalid token
            $this->session->set_flashdata('error', 'Invalid application link.');
            redirect(base_url('recruitment/user/apply'));
            return;
        }

        // Store active application token in session for convenience
        $this->session->set_userdata('active_app_token', $token);

        // 2. Check if feedback is already submitted in database
        $feedback = $this->recruitment_model->getCandidateFeedback($app['ApplicationId']);

        $data = array();
        $data['app']        = $app;
        $data['token']      = $token;
        $data['feedback']   = $feedback;
        $data['page_title'] = 'Candidate Portal | ' . htmlspecialchars($app['JobTitle']);

        // Check if candidate arrived immediately after submitting application
        $isNewSubmit = $this->input->get('new') == 1 || $this->session->flashdata('just_applied') == 1;
        $data['is_new_submit'] = $isNewSubmit;

        if (!empty($feedback)) {
            // State: Feedback already submitted
            $data['submitted_at_formatted'] = !empty($feedback['SubmittedAt']) 
                ? date('d M Y, h:i A', strtotime($feedback['SubmittedAt'])) 
                : date('d M Y');
            $this->load->view('feedback_submitted', $data);
        } else {
            // State: Application exists, feedback pending
            $this->load->view('feedback', $data);
        }
    }

    // =========================================================================
    // RESUME UPLOAD (STEP 1 — AJAX)
    // =========================================================================

    /**
     * Secure public resume upload.
     * Stores file in atscheck/portal/temp/ with a session-backed token.
     */
    public function upload_resume()
    {
        $this->output->set_content_type('application/json');

        $fileInputKey = null;
        if (isset($_FILES['resume_file'])) {
            $fileInputKey = 'resume_file';
        } elseif (isset($_FILES['resume'])) {
            $fileInputKey = 'resume';
        }

        if (empty($fileInputKey) || empty($_FILES[$fileInputKey]['name']) || $_FILES[$fileInputKey]['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a valid resume file to upload.']);
            return;
        }

        $file         = $_FILES[$fileInputKey];
        $originalName = basename($file['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only PDF, DOC, and DOCX files are allowed.']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 5MB limit.']);
            return;
        }

        // Temp directory (files moved to permanent path during submitApplication)
        $uploadDir = FCPATH . 'atscheck/portal/temp/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $safeFileName = 'TMP_PUB_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetPath   = $uploadDir . $safeFileName;

        $moved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $moved = move_uploaded_file($file['tmp_name'], $targetPath);
        } elseif (file_exists($file['tmp_name'])) {
            $moved = copy($file['tmp_name'], $targetPath);
        }

        if (!$moved || !file_exists($targetPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file. Please try again.']);
            return;
        }

        // Generate a secure token; store real path in session (never expose path to client)
        $fileToken = md5(uniqid('pub_res_', true));
        $this->session->set_userdata('pub_resume_' . $fileToken,      $targetPath);
        $this->session->set_userdata('pub_resume_orig_' . $fileToken, $originalName);
        $this->session->set_userdata('pub_resume_ext_' . $fileToken,  $ext);

        $formattedSize = round($file['size'] / 1024, 1) . ' KB';
        if ($file['size'] >= 1048576) {
            $formattedSize = round($file['size'] / 1048576, 2) . ' MB';
        }

        echo json_encode([
            'status'        => 'success',
            'message'       => 'Resume uploaded successfully.',
            'file_token'    => $fileToken,
            'original_name' => htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8'),
            'file_size'     => $formattedSize,
            'extension'     => strtoupper($ext),
        ]);
    }

    // =========================================================================
    // RESUME PARSING (STEP 2 — AJAX, optional)
    // =========================================================================

    /**
     * Public ATS resume parsing.
     * Reuses ATS_Engine::processResume() without exposing admin endpoints.
     */
    public function parse_resume()
    {
        $this->output->set_content_type('application/json');

        $fileToken = $this->input->post('file_token', true);
        if (empty($fileToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Resume reference missing.']);
            return;
        }

        $filePath = $this->session->userdata('pub_resume_' . $fileToken);
        if (empty($filePath) || !file_exists($filePath)) {
            echo json_encode(['status' => 'error', 'message' => 'Uploaded resume file not found or session expired.']);
            return;
        }

        try {
            $this->load->library('ATS_Engine');
            // processResume with empty vacancy = general profile extraction
            $parsed = $this->ats_engine->processResume($filePath, array());

            $phone = '';
            if (!empty($parsed['mobileNumbers'])) {
                $phone = is_array($parsed['mobileNumbers'])
                    ? implode(', ', $parsed['mobileNumbers'])
                    : (string)$parsed['mobileNumbers'];
            }

            $fullName = !empty($parsed['name']) && strtolower(trim($parsed['name'])) !== 'candidate'
                ? trim($parsed['name']) : '';

            $expYears = !empty($parsed['expyrs']) ? floatval($parsed['expyrs']) : '';

            $skills = '';
            if (!empty($parsed['all_extracted_skills'])) {
                $skills = (string)$parsed['all_extracted_skills'];
            } elseif (!empty($parsed['matched_skills'])) {
                $skills = (string)$parsed['matched_skills'];
            }

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'full_name'        => htmlspecialchars($fullName,            ENT_QUOTES, 'UTF-8'),
                    'email'            => htmlspecialchars($parsed['email'] ?? '', ENT_QUOTES, 'UTF-8'),
                    'phone'            => htmlspecialchars($phone,               ENT_QUOTES, 'UTF-8'),
                    'experience_years' => $expYears,
                    'skills'           => htmlspecialchars($skills,              ENT_QUOTES, 'UTF-8'),
                    'education'        => htmlspecialchars($parsed['detected_degree'] ?? '', ENT_QUOTES, 'UTF-8'),
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', 'Public Resume Parsing Error: ' . $e->getMessage());
            echo json_encode([
                'status'  => 'error',
                'message' => 'Resume parsing completed with limited details. You can enter details manually.',
            ]);
        }
    }

    // =========================================================================
    // SUBMIT APPLICATION (STEP 3 — AJAX POST)
    // =========================================================================

    /**
     * Public application submission endpoint.
     * Creates candidate + JobApplication + token, returns redirect URL.
     */
    public function submitApplication()
    {
        $this->output->set_content_type('application/json');

        // -------------------------------------------------------------------
        // 1. Read & sanitise POST inputs
        // -------------------------------------------------------------------
        $fullName    = trim($this->input->post('full_name',         true));
        $email       = strtolower(trim($this->input->post('email',  true)));
        $phone       = trim($this->input->post('phone',             true));
        $expYears    = $this->input->post('experience_years',        true);
        $jid         = (int)$this->input->post('position',          true);
        $skills      = trim($this->input->post('skills',            true));
        $coverLetter = trim($this->input->post('cover_letter',      true));
        $fileToken   = trim($this->input->post('resume_file_token', true));

        // -------------------------------------------------------------------
        // 2. Server-side field validation
        // -------------------------------------------------------------------
        $errors = [];

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }
        if ($expYears === '' || $expYears === null || $expYears === false) {
            $errors[] = 'Years of experience is required.';
        }
        if ($jid <= 0) {
            $errors[] = 'Please select a valid position.';
        }
        if (empty($fileToken)) {
            $errors[] = 'Resume file is required. Please upload your resume first.';
        }

        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
            return;
        }

        // -------------------------------------------------------------------
        // 3. Resolve uploaded resume path from session token
        // -------------------------------------------------------------------
        $tempFilePath  = $this->session->userdata('pub_resume_' . $fileToken);
        $originalName  = $this->session->userdata('pub_resume_orig_' . $fileToken);
        $fileExt       = $this->session->userdata('pub_resume_ext_' . $fileToken);

        if (empty($tempFilePath) || !file_exists($tempFilePath)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Resume session expired or file not found. Please re-upload your resume.',
            ]);
            return;
        }

        // -------------------------------------------------------------------
        // 4. Validate the selected Jid is active/approved
        // -------------------------------------------------------------------
        $vacancy = $this->recruitment_model->getActiveVacancyById($jid);

        if (empty($vacancy)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'The selected position is no longer available or has been closed.',
            ]);
            return;
        }

        // -------------------------------------------------------------------
        // 5. Duplicate application check
        //    Same email OR phone + same Jid = already applied
        // -------------------------------------------------------------------
        $isDuplicate = $this->recruitment_model->checkDuplicateApplication($email, $phone, $jid);

        if ($isDuplicate) {
            $existingInfo = $this->recruitment_model->getExistingApplicationInfo($email, $phone, $jid);

            $appId     = !empty($existingInfo['ApplicationId']) ? 'APP-' . $existingInfo['ApplicationId'] : '';
            $appToken  = !empty($existingInfo['ApplicationToken']) ? $existingInfo['ApplicationToken'] : '';
            $jobTitle  = !empty($existingInfo['JobTitle'])      ? $existingInfo['JobTitle']               : $vacancy['JobTitle'];
            $appStatus = !empty($existingInfo['CurrentStatus']) ? $existingInfo['CurrentStatus']          : 'CV Uploaded';
            $appliedOn = !empty($existingInfo['AppliedOn'])     ? date('d M Y', strtotime($existingInfo['AppliedOn'])) : '';

            $redirectUrl = !empty($appToken) ? base_url('recruitment/user/application/' . $appToken) : '';

            echo json_encode([
                'status'            => 'already_applied',
                'message'           => 'You have already applied for this position.',
                'application_id'    => $appId,
                'application_token' => $appToken,
                'redirect_url'      => $redirectUrl,
                'job_title'         => $jobTitle,
                'current_status'    => $appStatus,
                'applied_on'        => $appliedOn,
            ]);
            return;
        }

        // -------------------------------------------------------------------
        // 6. Move resume from temp to permanent portal directory
        // -------------------------------------------------------------------
        $portalDir = FCPATH . 'atscheck/portal/';
        if (!is_dir($portalDir)) {
            @mkdir($portalDir, 0755, true);
        }

        $safeFileName  = 'PUB_' . $jid . '_' . date('YmdHis') . rand(100, 999) . '.' . $fileExt;
        $permanentPath = $portalDir . $safeFileName;
        $resumeRelPath = 'atscheck/portal/' . $safeFileName;

        if (!rename($tempFilePath, $permanentPath)) {
            if (!copy($tempFilePath, $permanentPath)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Failed to process resume file. Please try again.',
                ]);
                return;
            }
            @unlink($tempFilePath);
        }

        // -------------------------------------------------------------------
        // 7. ATS Resume Processing (if ATS_Engine available)
        // -------------------------------------------------------------------
        $atsResult = null;
        try {
            $this->load->library('ATS_Engine');
            $atsResult = $this->ats_engine->processResume($permanentPath, $vacancy);
        } catch (Exception $e) {
            log_message('error', 'Public ATS Parsing Error on submit: ' . $e->getMessage());
            $atsResult = null;
        }

        // -------------------------------------------------------------------
        // 8. Merge ATS results with form data
        // -------------------------------------------------------------------
        $submissionData = [
            'vacancy'          => $vacancy,
            'full_name'        => $fullName,
            'email'            => $email,
            'phone'            => $phone,
            'experience_years' => $expYears,
            'skills'           => $skills,
            'cover_letter'     => $coverLetter,
            'resume_path'      => $resumeRelPath,
            'ats_result'       => $atsResult,
        ];

        // -------------------------------------------------------------------
        // 9. Database transaction: insert Candidate + Application + Tracking
        // -------------------------------------------------------------------
        $result = $this->recruitment_model->submitPublicApplication($submissionData);

        if (!$result['success']) {
            echo json_encode([
                'status'  => 'error',
                'message' => $result['error'] ?? 'Application submission failed. Please try again.',
            ]);
            return;
        }

        // -------------------------------------------------------------------
        // 10. Save active application token in session
        // -------------------------------------------------------------------
        $appToken = $result['application_token'];
        $this->session->set_userdata('active_app_token', $appToken);
        $this->session->set_flashdata('just_applied', 1);

        // Clean temp resume session
        $this->session->unset_userdata('pub_resume_' . $fileToken);
        $this->session->unset_userdata('pub_resume_orig_' . $fileToken);
        $this->session->unset_userdata('pub_resume_ext_' . $fileToken);

        // -------------------------------------------------------------------
        // 11. Return success JSON with redirect URL to /recruitment/user/application/{token}
        // -------------------------------------------------------------------
        $applicationIdFormatted = 'APP-' . $result['application_id'];
        $redirectUrl = base_url('recruitment/user/application/' . $appToken . '?new=1');

        echo json_encode([
            'status'            => 'success',
            'message'           => 'Your application has been submitted successfully.',
            'application_id'    => $applicationIdFormatted,
            'application_token' => $appToken,
            'candidate_code'    => $result['candidate_code'],
            'job_title'         => $result['job_title'],
            'job_code'          => $result['job_code'],
            'current_status'    => 'CV Uploaded',
            'applied_on'        => date('d M Y'),
            'redirect_url'      => $redirectUrl,
        ]);
    }

    // =========================================================================
    // SUBMIT CANDIDATE FEEDBACK (AJAX POST)
    // =========================================================================

    /**
     * Submit candidate application experience feedback.
     * Records feedback in candidate_feedback table.
     * Prevents duplicate feedback submission.
     */
    public function submit_feedback()
    {
        $this->output->set_content_type('application/json');

        $token = trim($this->input->post('token', true));
        if (empty($token)) {
            $token = $this->session->userdata('active_app_token');
        }

        if (empty($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Application access token missing.']);
            return;
        }

        // Validate token against database (Database is source of truth!)
        $app = $this->recruitment_model->getApplicationByToken($token);

        if (empty($app)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired application session.']);
            return;
        }

        // Check if feedback ALREADY submitted
        $existing = $this->recruitment_model->getCandidateFeedback($app['ApplicationId']);
        if (!empty($existing)) {
            $submittedAt = !empty($existing['SubmittedAt']) ? date('d M Y, h:i A', strtotime($existing['SubmittedAt'])) : date('d M Y');
            echo json_encode([
                'status'       => 'already_submitted',
                'message'      => 'Your feedback has already been recorded.',
                'submitted_at' => $submittedAt
            ]);
            return;
        }

        // Collect ratings (1 to 5 stars)
        $overallExp  = max(1, min(5, (int)$this->input->post('overall_experience', true)));
        $appProcess  = max(1, min(5, (int)$this->input->post('application_process', true)));
        $easeApply   = max(1, min(5, (int)$this->input->post('ease_of_applying', true)));
        $comm        = max(1, min(5, (int)$this->input->post('communication', true)));
        $comments    = trim($this->input->post('comments', true));

        $feedbackData = [
            'application_id'      => $app['ApplicationId'],
            'candidate_id'        => $app['CandidateId'],
            'overall_experience'  => $overallExp,
            'application_process' => $appProcess,
            'ease_of_applying'     => $easeApply,
            'communication'      => $comm,
            'comments'           => $comments
        ];

        $res = $this->recruitment_model->submitCandidateFeedback($feedbackData);

        if ($res['success']) {
            echo json_encode([
                'status'       => 'success',
                'message'      => 'Thank you! Your feedback has been recorded successfully.',
                'submitted_at' => $res['submitted_at'] ?? date('d M Y, h:i A')
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => $res['error'] ?? 'Failed to record feedback. Please try again.'
            ]);
        }
    }

    // =========================================================================
    // DEBUG ENDPOINT
    // =========================================================================

    /**
     * DEBUG ONLY — Shows vacancies returned by model.
     * Access via: /recruitment/user/debug_vacancies
     */
    public function debug_vacancies()
    {
        header('Content-Type: text/plain; charset=utf-8');

        $vacancies = $this->recruitment_model->getActiveVacancies();

        echo "=== getActiveVacancies() returned " . count($vacancies) . " row(s) ===\n\n";
        foreach ($vacancies as $v) {
            echo print_r($v, true) . "\n";
        }

        $q = $this->recruitment_model->getRawJobsQuery();
        echo "\n=== RAW IHRJobsList ===\n";
        echo "Total rows: " . $q->num_rows() . "\n\n";
        foreach ($q->result_array() as $row) {
            echo print_r($row, true) . "\n";
        }
        die();
    }
}
