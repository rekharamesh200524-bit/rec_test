<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Ats extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('email');
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $check_session = $this->session->userdata('logged_in');
        $this->load->helper('cookie');
        $this->load->helper('string');
        $this->load->library('ATS_Engine');
        $this->load->library('PdfTextExtractor');
 
        $this->load->library('encrypt');
        $this->load->library('user_agent');
        date_default_timezone_set("Asia/Kolkata");
        $this->load->model('admin/admin_model');
 
 
 
        $roleId = $this->session->userdata('EmpRoleId'); 
        
        $menus = $this->admin_model->getMenusByRole($check_session['EmpRoleId']);
       
        $menuTree = [];
 
            foreach ($menus as $menu) {
                if ($menu['ParentId'] === NULL) {
                    $menuTree[$menu['IHMid']] = $menu;
                    $menuTree[$menu['IHMid']]['children'] = [];
                }
            }
 
            foreach ($menus as $menu) {
                if ($menu['ParentId'] !== NULL && isset($menuTree[$menu['ParentId']])) {
                    $menuTree[$menu['ParentId']]['children'][] = $menu;
                }
            }
        $this->load->vars('menuTree', $menuTree);
 
 
    }
 
    public function analyzeResumeModal()
    {
        $Hrms_Session = $this->session->userdata('logged_in');  
        
        if (isset($Hrms_Session) && !empty($Hrms_Session)) {  
            header('Content-Type: application/json');

            $job_id = $this->input->post('job_id');
            if (!$job_id) {
                echo json_encode(['status' => 'error', 'message' => 'Job ID missing']);
                return;
            }

            $vacancy = $this->db->select("jl.*, GROUP_CONCAT(s.SkillName ORDER BY s.SkillName SEPARATOR ', ') AS RequiredSkills", false)
                ->from('IHRJobsList jl')
                ->join('JobSkills js', 'js.Jid = jl.Jid', 'left')
                ->join('IHSkills s', 's.SkillId = js.SkillId', 'left')
                ->where('jl.Jid', $job_id)
                ->group_by('jl.Jid')
                ->get()
                ->row_array();

            if (!$vacancy) {
                echo json_encode(['status' => 'error', 'message' => 'Job not found']);
                return;
            }

            $appStage = $this->db
                ->where('StageGroup', 'Application')
                ->order_by('StageOrder', 'ASC')
                ->limit(1)
                ->get('RecruitmentStages')
                ->row_array();

            if (!$appStage) {
                echo json_encode(['status' => 'error', 'message' => 'Recruitment stage not configured']);
                return;
            }

            // Support both 'resumes' (multiple) and 'resume' (single) input fields
            $fileArray = [];
            $inputKey = isset($_FILES['resumes']) ? 'resumes' : (isset($_FILES['resume']) ? 'resume' : null);

            if ($inputKey && !empty($_FILES[$inputKey])) {
                if (is_array($_FILES[$inputKey]['name'])) {
                    $fileCount = count($_FILES[$inputKey]['name']);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if (!empty($_FILES[$inputKey]['tmp_name'][$i]) && $_FILES[$inputKey]['error'][$i] === UPLOAD_ERR_OK) {
                            $fileArray[] = [
                                'name'     => $_FILES[$inputKey]['name'][$i],
                                'type'     => $_FILES[$inputKey]['type'][$i],
                                'tmp_name' => $_FILES[$inputKey]['tmp_name'][$i],
                                'error'    => $_FILES[$inputKey]['error'][$i],
                                'size'     => $_FILES[$inputKey]['size'][$i]
                            ];
                        }
                    }
                } elseif (!empty($_FILES[$inputKey]['tmp_name']) && $_FILES[$inputKey]['error'] === UPLOAD_ERR_OK) {
                    $fileArray[] = $_FILES[$inputKey];
                }
            }

            if (empty($fileArray)) {
                echo json_encode(['status' => 'error', 'message' => 'No valid resume files selected']);
                return;
            }

            $processedCount = 0;
            $duplicateCount = 0;
            $failedCount    = 0;
            $processedCandidates = [];

            $targetDir = FCPATH . 'atscheck/modal/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            foreach ($fileArray as $fileItem) {
                try {
                    $ext = strtolower(pathinfo($fileItem['name'], PATHINFO_EXTENSION));
                    $uniqueFileName = "CAND" . $job_id . '_' . date('YmdHis') . rand(100, 999) . '.' . $ext;
                    $targetFilePath = $targetDir . $uniqueFileName;

                    $moved = false;
                    if (is_uploaded_file($fileItem['tmp_name'])) {
                        $moved = move_uploaded_file($fileItem['tmp_name'], $targetFilePath);
                    }
                    if (!$moved && file_exists($fileItem['tmp_name'])) {
                        $moved = copy($fileItem['tmp_name'], $targetFilePath);
                    }

                    if (!$moved || !file_exists($targetFilePath)) {
                        $failedCount++;
                        continue;
                    }

                    $result = $this->ats_engine->processResume($targetFilePath, $vacancy);

                    $phoneNo = is_array($result['mobileNumbers']) ? implode(', ', $result['mobileNumbers']) : ($result['mobileNumbers'] ?? '');

                    // Check duplicate candidate for this job safely without empty OR SQL syntax error
                    $existingCandidate = false;
                    $whereOr = [];
                    if (!empty($phoneNo)) {
                        $whereOr[] = "PhoneNo = " . $this->db->escape($phoneNo);
                    }
                    if (!empty($result['email'])) {
                        $whereOr[] = "Email = " . $this->db->escape($result['email']);
                    }

                    if (!empty($whereOr)) {
                        $sqlWhere = "(" . implode(" OR ", $whereOr) . ")";
                        $existingCandidate = $this->db->where('Jid', $vacancy['Jid'])
                            ->where($sqlWhere, null, false)
                            ->get('IHrCandidates')
                            ->row_array();
                    }

                    if ($existingCandidate) {
                        $duplicateCount++;
                        continue;
                    }

                    $this->db->trans_begin();

                    $this->db->insert('IHrCandidates', [
                        'CandidateCode'   => 'CAND-' . $vacancy['Jid'] . '-' . date('Ymd') . rand(100, 999),
                        'Jid'             => $vacancy['Jid'],
                        'JobCode'         => $vacancy['JobCode'],
                        'Fullname'        => $result['name'],
                        'Email'           => $result['email'],
                        'PhoneNo'         => $phoneNo,
                        'ExpYrs'          => $result['expyrs'],
                        'ExperienceDetails' => json_encode($result['experience_details']),
                        'DomainBreakdown' => $result['domain'] ?? 'General',
                        'ResumePath'      => 'atscheck/modal/' . $uniqueFileName,
                        'Source'          => 'Upload',
                        'ProfileMatchPer' => $result['recommendation'],
                        'ATS_Status'      => 'CV Uploaded',
                        'ATS_Stage'       => 1,
                        'MatchedSkills'   => $result['matched_skills'] ?? '',
                        'EducationMatch'  => $result['education_match'],
                        'ExperienceMatch' => $result['experience'],
                        'ScoreBreakdown'  => json_encode([
                            'recommendation'        => $result['recommendation'],
                            'recommendation_reason' => $result['recommendation_reason'],
                            'relevant_evidence'     => $result['relevant_evidence'],
                            'missing_requirements'  => $result['missing_requirements'],
                            'candidate_profile'     => $result['candidate_profile'] ?? null,
                            'domain'                => $result['domain'] ?? 'General',
                            'candidate_domain'      => $result['candidate_domain'] ?? ($result['domain'] ?? 'General'),
                            'job_domain'            => $result['job_domain'] ?? 'General',
                            'domain_status'         => $result['domain_status'] ?? 'UNCLEAR',
                            'domain_analysis'       => $result['domain_analysis'] ?? null,
                            'matched_skills'        => $result['matched_skills'] ?? '',
                            'missing_skills'        => $result['missing_skills'] ?? '',
                            'all_extracted_skills'  => $result['all_extracted_skills'] ?? '',
                            'education_match'       => $result['education_match'] ?? '',
                            'experience'            => $result['experience'] ?? '',
                            'detected_degree'       => $result['detected_degree'] ?? ''
                        ]),
                        'VerifiedBy'      => $Hrms_Session['IUid'],
                        'VerifiedAt'      => date('Y-m-d H:i:s')
                    ]);

                    $candidateId = $this->db->insert_id();

                    $this->db->insert('JobApplications', [
                        'Jid'           => $vacancy['Jid'],
                        'CandidateId'   => $candidateId,
                        'CurrentStage'  => $appStage['StageName'],
                        'CurrentStatus' => 'CV Uploaded'
                    ]);

                    $applicationId = $this->db->insert_id();

                    $this->db->insert('CandidateStageTracking', [
                        'ApplicationId' => $applicationId,
                        'StageId'       => $appStage['StageId'],
                        'Action'        => 'Created',
                        'ActionBy'      => $Hrms_Session['IUid'],
                        'Remarks'       => 'Resume uploaded & ATS recommendation evaluation completed'
                    ]);

                    if ($this->db->trans_status() === FALSE) {
                        $this->db->trans_rollback();
                        $failedCount++;
                    } else {
                        $this->db->trans_commit();
                        $processedCount++;
                        $processedCandidates[] = [
                            'name'           => $result['name'],
                            'recommendation' => $result['recommendation']
                        ];
                    }
                } catch (\Throwable $e) {
                    $failedCount++;
                    log_message('error', 'Batch resume upload item error: ' . $e->getMessage());
                }
            }

            if ($processedCount > 0) {
                $msg = "Successfully uploaded and analyzed {$processedCount} candidate resume(s).";
                if ($duplicateCount > 0) $msg .= " ({$duplicateCount} duplicate resumes skipped).";
                echo json_encode([
                    'status'   => 'success',
                    'message'  => $msg,
                    'count'    => $processedCount,
                    'data'     => $processedCandidates,
                    'redirect' => base_url('admin/CandidateList/' . $vacancy['Jid'])
                ]);
            } else {
                $msg = "No new candidate resumes added.";
                if ($duplicateCount > 0) $msg .= " All {$duplicateCount} uploaded resume(s) were duplicates.";
                if ($failedCount > 0) $msg .= " {$failedCount} file(s) failed to upload.";
                echo json_encode([
                    'status'  => 'error',
                    'message' => $msg
                ]);
            }
        } else {
            $this->session->set_flashdata('error','Invalid Session. Please Login Again.');
            redirect($this->config->item('base_url')."admin/index");
        }
    }
 
 
    private function logStage($stage, $data = null)
    {
        $log  = "\n============================\n";
        $log .= date('Y-m-d H:i:s') . " | " . $stage . "\n";
        if ($data !== null) {
            $log .= print_r($data, true);
        }
        $log .= "\n============================\n";
 
        file_put_contents(APPPATH.'logs/ats_debug.log', $log, FILE_APPEND);
    }
 
}
 
 
 