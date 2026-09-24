<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    protected $roleNameCache = [];

	function __construct()
	{
		parent::__construct();
	}

  public function getMenusByRole($roleId)
{
    if(empty($roleId)) return [];

    $roleId = (int)$roleId;

    $activeMenuIds = $this->db
        ->select('IHMid')
        ->from('IHRolePermissions')
        ->where('Erid', $roleId)
        ->where('Status', 1)
        ->get()
        ->result_array();

    if(empty($activeMenuIds)){
        return [];
    }

    $activeIds = array_column($activeMenuIds, 'IHMid');

    $this->db->select('ParentId');
    $this->db->from('IHMenus');
    $this->db->where_in('IHMid', $activeIds);
    $this->db->where('ParentId IS NOT NULL', null, false);
    $parents = $this->db->get()->result_array();

    $parentIds = array_column($parents, 'ParentId');

    $allAllowedIds = array_unique(array_merge($activeIds, $parentIds));

    if(empty($allAllowedIds)){
        return [];
    }

    $this->db->select('*');
    $this->db->from('IHMenus');
    $this->db->where_in('IHMid', $allAllowedIds);
    $this->db->where('MenuStatus', 1);
    $this->db->order_by('ParentId', 'ASC');
    $this->db->order_by('IHMid', 'ASC');

    return $this->db->get()->result_array();
}

 

    private function buildTree(array $elements, $parentId = NULL)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['ParentId'] == $parentId) {
                $children = $this->buildTree($elements, $element['IHMid']);
                if (!empty($children)) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }


public function getBreadcrumb($url)
{
    $child = $this->db
        ->where('LOWER(Menuurl)', strtolower($url))
        ->get('IHMenus')
        ->row_array();

    if (!$child) {
        return ['parent' => null, 'child' => null];
    }

    $parent = null;
    if (!empty($child['ParentId'])) {
        $parent = $this->db
            ->where('IHMid', $child['ParentId'])
            ->get('IHMenus')
            ->row_array();
    }

    return [
        'parent' => $parent,
        'child'  => $child
    ];
}

function getUserDepartments(){

	     $this->db->select('dep.*'); 
         $this->db->where('dep.Status',1);
         $this->db->order_by('dep.Departmentname','ASC');
        $query = $this->db->get('Departments as dep')->result_array();
         
        return $query; 
}

function getDepartments(){

         $this->db->select('dep.*');
         $this->db->order_by('dep.Departmentname','ASC');
        $query = $this->db->get('Departments as dep')->result_array();
      
        return $query; 
}
function getUserRoles(){

	     $this->db->select('er.*');
         $this->db->where('er.Status',1);
         $this->db->order_by('er.RoleName','ASC');
        $query = $this->db->get('EmpRoles as er')->result_array();
         
        return $query; 
}
function getUsers(){

         $this->db->select('ihu.*'); 
          $this->db->order_by('ihu.CreatedAT','ASC');
        $query = $this->db->get('IHUsers as ihu')->result_array();
          
        return $query; 
}

function get_VaccancyList(){ 

    $check_session = $this->session->userdata('logged_in');
    $roleName = '';
    $currentUserId = 0;

    if (!empty($check_session) && isset($check_session['EmpRoleId'])) {
        $roleId = (int)$check_session['EmpRoleId'];
        $currentUserId = isset($check_session['IUid']) ? (int)$check_session['IUid'] : 0;
        $roleName = strtolower(trim($this->getRoleName($roleId)));
        if (empty($roleName) && !empty($currentUserId)) {
            $userRow = $this->db->select('r.RoleName')
                                ->from('IHUsers u')
                                ->join('EmpRoles r', 'u.Erid = r.Erid', 'left')
                                ->where('u.IUid', $currentUserId)
                                ->get()->row_array();
            if (!empty($userRow['RoleName'])) {
                $roleName = strtolower(trim($userRow['RoleName']));
            }
        }
    }

    $this->db->select("
    jl.*,
    Departments.Did,
    Departments.Departmentname,
    IHUsers.IUid,
    IHUsers.EmpName,
    (SELECT COUNT(DISTINCT ja.ApplicationId) FROM JobApplications ja WHERE ja.Jid = jl.Jid) AS CandidateCount,
    GROUP_CONCAT(IHSkills.SkillName ORDER BY IHSkills.SkillName SEPARATOR ', ') AS Skills
    ", false); 
    $this->db->from('IHRJobsList jl');
    $this->db->join('Resource_Requests rr', 'rr.ConvertedJid = jl.Jid', 'left');
    $this->db->join('Departments', 'Departments.Did = jl.Did', 'left');
    $this->db->join('IHUsers', 'IHUsers.IUid = jl.PostedBy', 'left');
    $this->db->join('JobSkills', 'JobSkills.Jid = jl.Jid', 'left');
    $this->db->join('IHSkills', 'IHSkills.SkillId = JobSkills.SkillId', 'left');

    // Only include vacancies that are standalone or converted from an ASSIGNED resource request
    $this->db->where('(rr.RequestId IS NULL OR rr.Status = "ASSIGNED")', null, false);
    $this->db->where('(jl.AssignedRecruiterManagerId IS NOT NULL OR rr.RequestId IS NULL)', null, false);

    if ($roleName === 'recruiter' || $roleName === 'recruitment manager') { 
        $this->db->where('jl.AssignedRecruiterManagerId', $currentUserId);
    } 

    $this->db->group_by('jl.Jid');

    $query = $this->db->get();
    $result = $query->result_array();
      
    return $result; 

}
public function getCandidatesList($Jid, $source = null){

      $this->db->select("
        c.CandidateId, c.CandidateCode, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ResumePath, c.Source, c.ATS_Status, c.ATS_Stage, c.ProfileMatchPer, c.MatchedSkills, c.EducationMatch, c.ExperienceMatch, c.ScoreBreakdown, ja.ApplicationId, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, ja.UpdatedAt AS ApplicationUpdatedAt, j.JobCode, j.JobTitle, j.EducationRequired, j.ExpMin, j.ExpMax, j.JobLocation,
        GROUP_CONCAT(s.SkillName SEPARATOR ', ') AS JobSkills,
        rst.StageName AS CurrentStageName,
        rst.StageOrder AS CurrentStageOrder,  
        cst.Action AS LastAction,
        cst.ActionAt AS LastActionAt,
        u.EmpName AS ActionByUser
    ");  

    $this->db->from('IHrCandidates c');
    $this->db->join('JobApplications ja', 'c.CandidateId = ja.CandidateId', 'inner');
    $this->db->join('IHRJobsList j', 'ja.Jid = j.Jid', 'inner');
    $this->db->join('JobSkills js', 'j.Jid = js.Jid', 'left');
    $this->db->join('IHSkills s', 'js.SkillId = s.SkillId', 'left');
    $this->db->join('CandidateStageTracking cst', 'ja.ApplicationId = cst.ApplicationId', 'left');
    $this->db->join('RecruitmentStages rst', 'rst.StageId = ja.StageId', 'left');
    $this->db->join('IHUsers u', 'u.IUid = cst.ActionBy', 'left');

    $this->db->where('ja.Jid', $Jid);

    if (!empty($source) && strcasecmp($source, 'all') !== 0) {
        if (strtolower($source) === 'online') {
            $this->db->where("LOWER(c.Source) IN ('online', 'portal')", null, false);
        } elseif (strtolower($source) === 'walkin' || strtolower($source) === 'walk-in') {
            $this->db->where("LOWER(c.Source) IN ('walk-in', 'walk_in', 'walkin', 'upload', 'manual')", null, false);
        }
    }

    $this->db->group_by(['c.CandidateId', 'ja.ApplicationId']);
    $this->db->order_by('cst.ActionAt', 'DESC');

    $query = $this->db->get()->result_array();
    
        return $query; 
}




    public function getAllUsers()
    {
        $this->db->select('u.IUid, u.EmpName, u.EmpEmail, u.EmpDesignation, r.RoleName');
        $this->db->from('IHUsers u');
        $this->db->join('EmpRoles r', 'u.Erid = r.Erid', 'left');
        $this->db->where('u.UStatus', 1);
        $this->db->order_by('u.EmpName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getApproverUsers()
    {
        $role = $this->db->select('Erid')->from('EmpRoles')->where('LOWER(RoleName)', 'approver')->get()->row_array();
        
        $this->db->select('u.IUid, u.EmpName, u.EmpEmail, u.EmpDesignation, r.RoleName');
        $this->db->from('IHUsers u');
        $this->db->join('EmpRoles r', 'u.Erid = r.Erid', 'left');
        $this->db->where('u.UStatus', 1);
        
        if (!empty($role)) {
            $this->db->group_start();
            $this->db->where('u.Erid', $role['Erid']);
            $this->db->or_where('LOWER(r.RoleName)', 'management');
            $this->db->group_end();
        }
        
        return $this->db->get()->result_array();
    }

    public function getActiveDepartments()
    {
        return $this->db->select("Did, Departmentname")
            ->from("Departments")
            ->where("Status", 1)
            ->order_by("Departmentname", "ASC")
            ->get()->result_array();
    }

    public function getUserRoleById($roleId)
    {
        if (empty($roleId)) {
            return null;
        }
        return $this->db->select("RoleName")
            ->from("EmpRoles")
            ->where("Erid", $roleId)
            ->get()->row_array();
    }

    public function getResourceRequests($filters = [])
    {
        $this->db->select('rr.*, d.Departmentname, req.EmpName AS RequestedByName, req.EmpEmail AS RequestedByEmail, app.EmpName AS ApproverName, app.EmpEmail AS ApproverEmail, ctc.EmpName AS CtcApproverName, ctc.EmpEmail AS CtcApproverEmail');
        $this->db->from('Resource_Requests rr');
        $this->db->join('Departments d', 'rr.Did = d.Did', 'left');
        $this->db->join('IHUsers req', 'rr.RequestedBy = req.IUid', 'left');
        $this->db->join('IHUsers app', 'rr.ApproverId = app.IUid', 'left');
        $this->db->join('IHUsers ctc', 'rr.CtcApproverId = ctc.IUid', 'left');

        if (!empty($filters['RequestedBy'])) {
            $this->db->where('rr.RequestedBy', $filters['RequestedBy']);
        }
        if (!empty($filters['ApproverId'])) {
            $this->db->where('rr.ApproverId', $filters['ApproverId']);
        }
        if (!empty($filters['Status'])) {
            $this->db->where('rr.Status', $filters['Status']);
        }
        if (!empty($filters['RequestId'])) {
            if (is_numeric($filters['RequestId'])) {
                $this->db->where('rr.RequestId', (int)$filters['RequestId']);
            } else {
                $this->db->where('rr.RequestCode', trim($filters['RequestId']));
            }
        }

        $this->db->order_by('rr.RequestId', 'DESC');
	//echo $this->db->last_query(); exit;
        return $this->db->get()->result_array();
    }

    public function getResourceRequestById($id)
    {
        $res = $this->getResourceRequests(['RequestId' => $id]);
        return !empty($res) ? $res[0] : null;
    }

    public function checkDuplicateResourceRequest($requestedBy, $jobTitle, $did = null, $approverId = null, $positionType = null, $excludeRequestId = null)
    {
        if (is_array($requestedBy)) {
            $data             = $requestedBy;
            $requestedBy      = isset($data['RequestedBy']) ? (int)$data['RequestedBy'] : 0;
            $jobTitle         = isset($data['JobTitle']) ? $data['JobTitle'] : '';
            $did              = isset($data['Did']) ? (int)$data['Did'] : null;
            $approverId       = isset($data['ApproverId']) ? (int)$data['ApproverId'] : null;
            $positionType     = isset($data['PositionType']) ? $data['PositionType'] : null;
            $excludeRequestId = isset($data['RequestId']) ? (int)$data['RequestId'] : null;
        }

        if (empty($requestedBy) || empty($jobTitle)) {
            return null;
        }

        $cleanJobTitle = strtolower(trim($jobTitle));

        $this->db->select('rr.RequestId, rr.RequestCode, rr.JobTitle, rr.Status, rr.RequestedBy, rr.Did, rr.ApproverId, rr.PositionType, rr.ConvertedJid');
        $this->db->from('Resource_Requests rr');
        $this->db->join('IHRJobsList jl', 'jl.Jid = rr.ConvertedJid', 'left');
        $this->db->where('rr.RequestedBy', (int)$requestedBy);
        $this->db->where('LOWER(TRIM(rr.JobTitle))', $cleanJobTitle);

        if (!empty($did)) {
            $this->db->where('rr.Did', (int)$did);
        }
        if (!empty($approverId)) {
            $this->db->where('rr.ApproverId', (int)$approverId);
        }
        if (!empty($positionType)) {
            $this->db->where('rr.PositionType', trim($positionType));
        }
        if (!empty($excludeRequestId)) {
            $this->db->where('rr.RequestId !=', (int)$excludeRequestId);
        }

        // Active / pending condition:
        // Either PENDING APPROVAL or ACCEPTED, OR ASSIGNED where the converted job is still open/active (not closed or completed)
        $this->db->group_start();
            $this->db->where_in('rr.Status', ['PENDING APPROVAL', 'ACCEPTED']);
            $this->db->or_group_start();
                $this->db->where('rr.Status', 'ASSIGNED');
                $this->db->group_start();
                    $this->db->where('jl.JobStatus IS NULL', null, false);
                    $this->db->or_where_not_in('LOWER(jl.JobStatus)', ['closed', 'completed']);
                $this->db->group_end();
            $this->db->group_end();
        $this->db->group_end();

        $this->db->order_by('rr.RequestId', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get();
        return ($query && $query->num_rows() > 0) ? $query->row_array() : null;
    }

    public function acquireResourceRequestLock($userId)
    {
        $lockName = 'rr_create_' . (int)$userId;
        $res = $this->db->query("SELECT GET_LOCK(?, 5) AS lk", array($lockName))->row_array();
        return !empty($res['lk']) && (int)$res['lk'] === 1;
    }

    public function releaseResourceRequestLock($userId)
    {
        $lockName = 'rr_create_' . (int)$userId;
        $this->db->query("SELECT RELEASE_LOCK(?)", array($lockName));
    }

    public function insertResourceRequest($data)
    {
    $result = $this->db->insert('Resource_Requests', $data);

    if (!$result) {
        $error = $this->db->error();

        log_message(
            'error',
            'Resource Request Insert Failed: ' . json_encode($error) .
            ' | DATA: ' . json_encode($data)
        );

        return false;
    }

    return $this->db->insert_id();
}
    public function updateResourceRequest($id, $data)
    {
        if (is_numeric($id)) {
            $this->db->where('RequestId', (int)$id);
        } else {
            $this->db->where('RequestCode', trim($id));
        }
        return $this->db->update('Resource_Requests', $data);
    }




    public function getApprovedResourceRequests()
    {
        $this->db->select("
            rr.*,
            d.Departmentname,
            req.EmpName AS RequestedByName,
            req.EmpEmail AS RequestedByEmail,
            app.EmpName AS ApproverName,
            COALESCE(ctc_job.EmpName, ctc.EmpName) AS CtcApproverName,
            arm.EmpName AS AssignedRecruiterManagerName,
            COALESCE(j.Salary, rr.Salary) AS EffectiveSalary,
            COALESCE(j.JobLocation, rr.JobLocation) AS EffectiveLocation,
            COALESCE(j.EducationRequired, rr.EducationRequired) AS EffectiveEducation,
            COALESCE(j.CtcApproverId, rr.CtcApproverId) AS EffectiveCtcApproverId
        ");
        $this->db->from("Resource_Requests rr");
        $this->db->join("IHRJobsList j", "j.Jid = rr.ConvertedJid", "left");
        $this->db->join("Departments d", "d.Did = rr.Did", "left");
        $this->db->join("IHUsers req", "req.IUid = rr.RequestedBy", "left");
        $this->db->join("IHUsers app", "app.IUid = rr.ApproverId", "left");
        $this->db->join("IHUsers ctc", "ctc.IUid = rr.CtcApproverId", "left");
        $this->db->join("IHUsers ctc_job", "ctc_job.IUid = j.CtcApproverId", "left");
        $this->db->join("IHUsers arm", "arm.IUid = rr.AssignedRecruiterManagerId", "left");
        $this->db->where_in("rr.Status", ["ACCEPTED", "ASSIGNED"]);
        $this->db->order_by("rr.RequestId", "DESC");
        return $this->db->get()->result_array();
    }

    public function getRecruitmentManagers()
    {
        return $this->db->select("u.IUid, u.EmpName, u.EmpEmail, u.EmpDesignation, r.RoleName")
            ->from("IHUsers u")
            ->join("EmpRoles r", "u.Erid = r.Erid", "left")
            ->where_in("LOWER(r.RoleName)", ["recruitment manager", "recruiter"])
            ->order_by("u.EmpName", "ASC")
            ->get()->result_array();
    }



    /* =========================================================================
     * SECTION: AUTHENTICATION & MENUS
     * ========================================================================= */

    public function syncDefaultMenuIcons()
    {
        $this->db->where('LOWER(Menuurl)', 'admin/approvedresources')
                 ->where('(MenuIcon IS NULL OR MenuIcon = "" OR MenuIcon = "far fa-circle")', null, false)
                 ->update('IHMenus', ['MenuIcon' => 'fas fa-check-circle']);
        $this->db->where('LOWER(Menuurl)', 'admin/requestedresources')
                 ->where('(MenuIcon IS NULL OR MenuIcon = "" OR MenuIcon = "far fa-circle")', null, false)
                 ->update('IHMenus', ['MenuIcon' => 'fas fa-clipboard-list']);
        $this->db->where('LOWER(Menuurl)', 'admin/vaccancylist')
                 ->where('(MenuIcon IS NULL OR MenuIcon = "" OR MenuIcon = "far fa-circle")', null, false)
                 ->update('IHMenus', ['MenuIcon' => 'fas fa-briefcase']);
    }

    public function getActiveUserByEmail($email)
    {
        return $this->db->select('IUid, EmpName, EmpEmail')
                        ->where('EmpEmail', $email)
                        ->where('UStatus', 1)
                        ->get('IHUsers')
                        ->row();
    }

    public function updateUserResetToken($userId, $token, $createdAt)
    {
        return $this->db->where('IUid', $userId)
                        ->update('IHUsers', [
                            'ResetToken'          => $token,
                            'ResetTokenCreatedAt' => $createdAt,
                        ]);
    }

    public function getUserByResetToken($token)
    {
        return $this->db->select('IUid, ResetTokenCreatedAt')
                        ->where('ResetToken', $token)
                        ->get('IHUsers')
                        ->row();
    }

    public function clearUserResetToken($userId)
    {
        return $this->db->where('IUid', $userId)
                        ->update('IHUsers', [
                            'ResetToken'          => NULL,
                            'ResetTokenCreatedAt' => NULL,
                        ]);
    }

    public function updateUserPassword($userId, $passwordHash)
    {
        return $this->db->where('IUid', $userId)
                        ->update('IHUsers', [
                            'EmpPass'             => $passwordHash,
                            'ResetToken'          => NULL,
                            'ResetTokenCreatedAt' => NULL,
                        ]);
    }

    public function validateLogin($username, $passwordHash)
    {
        return $this->db->select('ihu.*')
                        ->where('ihu.EmpEmail', $username)
                        ->where('ihu.EmpPass', $passwordHash)
                        ->where('ihu.UStatus', 1)
                        ->get('IHUsers as ihu')
                        ->result_array();
    }

    public function logUserLogin($data)
    {
        return $this->db->insert('IHrmsLogin_Log', $data);
    }

   

    public function getUserDepartmentId($uid)
    {
        $uRow = $this->db->select("Did")->from("IHUsers")->where("IUid", $uid)->get()->row_array();
        return !empty($uRow['Did']) ? (int)$uRow['Did'] : 0;
    }

    public function getInterviewJobIdsForUser($uid)
    {
        $intRows = $this->db->distinct()
                            ->select("ja.Jid")
                            ->from("CandidateInterviews ci")
                            ->join("JobApplications ja", "ja.ApplicationId = ci.ApplicationId", "inner")
                            ->where("ci.InterviewerId", $uid)
                            ->where("ja.Jid IS NOT NULL", null, false)
                            ->get()
                            ->result_array();
        return !empty($intRows) ? array_map('intval', array_column($intRows, 'Jid')) : [];
    }

    public function getAccessibleJobIds($Hrms_Session)
    {
        if (empty($Hrms_Session)) {
            return [0];
        }

        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        $uid    = (int)$Hrms_Session['IUid'];

        $roleName = strtolower(trim($this->getRoleName($roleId)));
        if (empty($roleName) && !empty($uid)) {
            $userRow = $this->db->select('r.RoleName')
                                ->from('IHUsers u')
                                ->join('EmpRoles r', 'u.Erid = r.Erid', 'left')
                                ->where('u.IUid', $uid)
                                ->get()->row_array();
            if (!empty($userRow['RoleName'])) {
                $roleName = strtolower(trim($userRow['RoleName']));
            }
        }

        // Management roles have unrestricted access to all jobs
        if (in_array($roleName, ['management', 'admin', 'super admin'], true)) {
            return null;
        }

        $did = 0;
        if (isset($Hrms_Session['Did']) && !empty($Hrms_Session['Did'])) {
            $did = (int)$Hrms_Session['Did'];
        } elseif (isset($Hrms_Session['DepDid']) && !empty($Hrms_Session['DepDid'])) {
            $did = (int)$Hrms_Session['DepDid'];
        } else {
            $uRow = $this->db->select("Did")->from("IHUsers")->where("IUid", $uid)->get()->row_array();
            if (!empty($uRow['Did'])) {
                $did = (int)$uRow['Did'];
            }
        }

        $interviewJobs = $this->getInterviewJobIdsForUser($uid);

        $this->db->select("jl.Jid")->from("IHRJobsList jl");
        $this->db->join("Resource_Requests rr", "rr.ConvertedJid = jl.Jid", "left");

        // Only include vacancies that are standalone or converted from an ASSIGNED resource request
        $this->db->where('(rr.RequestId IS NULL OR rr.Status = "ASSIGNED")', null, false);
        $this->db->where('(jl.AssignedRecruiterManagerId IS NOT NULL OR rr.RequestId IS NULL)', null, false);

        if ($roleName === 'recruiter' || $roleName === 'recruitment manager') {
            $this->db->where('jl.AssignedRecruiterManagerId', $uid);
        } elseif ($roleName === 'hiring manager') {
            $this->db->group_start();
            $this->db->where('jl.PostedBy', $uid);
            $this->db->or_where('jl.CtcApproverId', $uid);
            if ($did > 0) {
                $this->db->or_where('jl.Did', $did);
            }
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('jl.Jid', $interviewJobs);
            }
            $this->db->group_end();
        } else {
            $this->db->group_start();
            $this->db->where('jl.PostedBy', $uid);
            $this->db->or_where('jl.AssignedRecruiterManagerId', $uid);
            $this->db->or_where('jl.CtcApproverId', $uid);
            if ($did > 0) {
                $this->db->or_where('jl.Did', $did);
            }
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('jl.Jid', $interviewJobs);
            }
            $this->db->group_end();
        }

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return [0];
        }

        $ids = array_map('intval', array_column($rows, 'Jid'));
        if (
            !in_array($roleName, ['recruiter', 'recruitment manager'], true)
            && !empty($interviewJobs)
        ) {
            $ids = array_values(array_unique(array_merge($ids, $interviewJobs)));
        }
        return $ids;
    }

    /* =========================================================================
     * SECTION: DASHBOARD QUERIES
     * ========================================================================= */

    public function getRecruitmentStages()
    {
        return $this->db->order_by('StageGroup', 'ASC')
                        ->order_by('StageOrder', 'ASC')
                        ->get('RecruitmentStages')
                        ->result_array();
    }

    public function getStageCandidateCounts($stages, $accessibleJobIds = null)
    {
        foreach ($stages as &$stage) {
            $this->db->from("JobApplications ja");
            $this->db->where("ja.CurrentStage", $stage["StageId"]);
            if (is_array($accessibleJobIds)) {
                if (empty($accessibleJobIds)) {
                    $stage["count"] = 0;
                    continue;
                }
                $this->db->where_in("ja.Jid", $accessibleJobIds);
            }
            $stage["count"] = $this->db->count_all_results();
        }
        unset($stage);
        return $stages;
    }

    public function getDashboardVacancyCounts($accessibleJobIds = null)
    {
        $this->db->from("IHRJobsList");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $total = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->where("JobStatus", "On-Hold");
        $onhold = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->where("JobStatus", "Open");
        $open = $this->db->count_all_results();

        return ['total' => $total, 'onhold' => $onhold, 'open' => $open];
    }

    public function getPendingOnHoldReminders($today)
    {
        return $this->db->select("jl.*, u.EmpName AS RecruiterName, u.EmpEmail AS RecruiterEmail, pb.EmpName AS PostedByName, pb.EmpEmail AS PostedByEmail, d.Departmentname")
                        ->from("IHRJobsList jl")
                        ->join("IHUsers u", "u.IUid = jl.AssignedRecruiterManagerId", "left")
                        ->join("IHUsers pb", "pb.IUid = jl.PostedBy", "left")
                        ->join("Departments d", "d.Did = jl.Did", "left")
                        ->where("jl.JobStatus", "On-Hold")
                        ->where("jl.HoldUntilDate IS NOT NULL", null, false)
                        ->where("DATEDIFF(jl.HoldUntilDate, '" . $today . "') <=", 2)
                        ->where("DATEDIFF(jl.HoldUntilDate, '" . $today . "') >=", 0)
                        ->group_start()
                            ->where("jl.HoldReminderSentDate IS NULL", null, false)
                            ->or_where("jl.HoldReminderSentDate !=", $today)
                        ->group_end()
                        ->get()
                        ->result_array();
    }

    public function updateHoldReminderSentDate($jid, $today)
    {
        return $this->db->where('Jid', $jid)->update('IHRJobsList', ['HoldReminderSentDate' => $today]);
    }

    public function normalizeLegacyHrStatus()
    {
        $this->db->where('CurrentStatus', 'HR')->or_where('CurrentStatus', 'hr')->update('JobApplications', ['CurrentStatus' => 'Level 1']);
        $this->db->where('ATS_Status', 'HR')->or_where('ATS_Status', 'hr')->update('IHrCandidates', ['ATS_Status' => 'Level 1']);
    }

    public function getResourceRequestCounts()
    {
        $total    = $this->db->count_all_results("Resource_Requests");
        $pending  = $this->db->where("Status", "PENDING APPROVAL")->count_all_results("Resource_Requests");
        $accepted = $this->db->where("Status", "ACCEPTED")->count_all_results("Resource_Requests");
        return ['total' => $total, 'pending' => $pending, 'accepted' => $accepted];
    }

    public function getApplicationStats($accessibleJobIds = null)
    {
        $this->db->from("JobApplications ja");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_start()
                 ->where("ja.CurrentStatus", "Rejected")
                 ->or_like("ja.CurrentStatus", "Reject")
                 ->group_end();
        $rejected = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_start()
                 ->where("ja.CurrentStatus", "Selected")
                 ->or_like("ja.CurrentStatus", "Select")
                 ->or_like("ja.CurrentStatus", "Hired")
                 ->or_like("ja.CurrentStatus", "Offer")
                 ->or_like("ja.CurrentStatus", "Interview")
                 ->or_like("ja.CurrentStatus", "Progress")
                 ->or_like("ja.CurrentStatus", "Scheduled")
                 ->group_end();
        $screened = $this->db->count_all_results();

        return ['rejected' => $rejected, 'screened' => $screened];
    }

    public function getMonthlyApplicationStats($year, $accessibleJobIds = null)
    {
        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->where("YEAR(ja.AppliedOn)", $year);
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $apps_res = $this->db->get()->result_array();

        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->where("YEAR(ja.AppliedOn)", $year);
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_start()
                 ->where("ja.CurrentStatus", "Selected")
                 ->or_like("ja.CurrentStatus", "Select")
                 ->or_like("ja.CurrentStatus", "Hired")
                 ->or_like("ja.CurrentStatus", "Offer")
                 ->group_end();
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $sel_res = $this->db->get()->result_array();

        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->where("YEAR(ja.AppliedOn)", $year);
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_start()
                 ->where("ja.CurrentStatus", "Rejected")
                 ->or_like("ja.CurrentStatus", "Reject")
                 ->group_end();
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $rej_res = $this->db->get()->result_array();

        return ['apps' => $apps_res, 'selected' => $sel_res, 'rejected' => $rej_res];
    }

    public function getRoleUserCounts()
    {
        return $this->db->select("r.RoleName, COUNT(u.IUid) as total_users")
                        ->from("IHUsers u")
                        ->join("EmpRoles r", "r.Erid = u.Erid", "left")
                        ->where("u.UStatus", 1)
                        ->group_by("r.RoleName")
                        ->get()
                        ->result_array();
    }

    public function getDashboardJobs($accessibleJobIds = null)
    {
        $this->db->select("jl.*, d.Departmentname");
        $this->db->from("IHRJobsList jl");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("jl.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->order_by("jl.Jid", "DESC");
        return $this->db->get()->result_array();
    }

    public function getDashboardCandidates($accessibleJobIds = null, $uid = null, $isInterviewerOnly = false)
    {
        if ($isInterviewerOnly) {
            $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, 1 as IsAssignedInterviewer", false);
            $this->db->from("JobApplications ja");
            $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "left");
            $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "left");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId AND ci.InterviewerId = " . (int)$uid, "inner");
            $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");
            $this->db->order_by("ja.ApplicationId", "DESC");
            return $this->db->get()->result_array();
        } else {
            $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, IF(ci.InterviewerId = " . (int)$uid . ", 1, 0) as IsAssignedInterviewer", false);
            $this->db->from("JobApplications ja");
            $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "left");
            $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "left");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId", "left");
            $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");
            if (is_array($accessibleJobIds)) {
                if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
                else $this->db->where("1 = 0", null, false);
            }
            $this->db->order_by("ja.ApplicationId", "DESC");
            return $this->db->get()->result_array();
        }
    }

    public function getDashboardResourceRequests()
    {
        return $this->db->select("rr.*, d.Departmentname, req_u.EmpName as RequestedByName, app_u.EmpName as ApproverName")
                        ->from("Resource_Requests rr")
                        ->join("Departments d", "d.Did = rr.Did", "left")
                        ->join("IHUsers req_u", "req_u.IUid = rr.RequestedBy", "left")
                        ->join("IHUsers app_u", "app_u.IUid = rr.ApproverId", "left")
                        ->order_by("rr.RequestId", "DESC")
                        ->get()
                        ->result_array();
    }

    /* =========================================================================
     * SECTION: ANALYTICS QUERIES
     * ========================================================================= */

    public function getAnalyticsSummaryCounts($accessibleJobIds = null)
    {
        $this->db->from("IHRJobsList");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $total_jobs = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $total_candidates = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $total_applications = $this->db->count_all_results();

        $total_requests = $this->db->count_all_results("Resource_Requests");

        $this->db->from("IHRJobsList");
        $this->db->where("JobStatus", "Open");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $open_jobs = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->where("JobStatus", "Closed");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $closed_jobs = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->where("JobStatus", "On-Hold");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $hold_jobs = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->group_start()
                 ->where("ja.CurrentStatus", "Selected")
                 ->or_like("ja.CurrentStatus", "Select")
                 ->or_like("ja.CurrentStatus", "Hired")
                 ->or_like("ja.CurrentStatus", "Offer")
                 ->group_end();
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $hired_candidates = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->like("ja.CurrentStatus", "Reject");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $rejected_candidates = $this->db->count_all_results();

        return [
            'total_jobs'          => $total_jobs,
            'total_candidates'    => $total_candidates,
            'total_applications'  => $total_applications,
            'total_requests'      => $total_requests,
            'open_jobs'           => $open_jobs,
            'closed_jobs'         => $closed_jobs,
            'hold_jobs'           => $hold_jobs,
            'hired_candidates'    => $hired_candidates,
            'rejected_candidates' => $rejected_candidates,
        ];
    }

    public function getAnalyticsJobsHistory($accessibleJobIds = null)
    {
        $this->db->select("jl.*, d.Departmentname, u.EmpName as RecruiterName");
        $this->db->from("IHRJobsList jl");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->join("IHUsers u", "u.IUid = jl.AssignedRecruiterManagerId", "left");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("jl.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->order_by("jl.Jid", "DESC");
        return $this->db->get()->result_array();
    }

    public function getAnalyticsCandidatesHistory($accessibleJobIds = null)
    {
        $this->db->select("ja.ApplicationId, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo as MobileNumber, c.ExpYrs as TotalExperience, c.ATS_Status, jl.JobTitle, jl.JobCode, d.Departmentname");
        $this->db->from("JobApplications ja");
        $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "left");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "left");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("ja.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->order_by("ja.ApplicationId", "DESC");
        return $this->db->get()->result_array();
    }

    public function getAnalyticsRequestsHistory()
    {
        return $this->db->select("rr.*, d.Departmentname, req_u.EmpName as RequestedByName, app_u.EmpName as ApproverName")
                        ->from("Resource_Requests rr")
                        ->join("Departments d", "d.Did = rr.Did", "left")
                        ->join("IHUsers req_u", "req_u.IUid = rr.RequestedBy", "left")
                        ->join("IHUsers app_u", "app_u.IUid = rr.ApproverId", "left")
                        ->order_by("rr.RequestId", "DESC")
                        ->get()
                        ->result_array();
    }

    public function getDepartmentAnalytics($accessibleJobIds = null)
    {
        $this->db->select("d.Did, d.Departmentname, COUNT(DISTINCT jl.Jid) as total_jobs, COUNT(DISTINCT ja.ApplicationId) as total_apps");
        $this->db->from("Departments d");
        $this->db->join("IHRJobsList jl", "jl.Did = d.Did", "left");
        $this->db->join("JobApplications ja", "ja.Jid = jl.Jid", "left");
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) $this->db->where_in("jl.Jid", $accessibleJobIds);
            else $this->db->where("1 = 0", null, false);
        }
        $this->db->group_by("d.Did, d.Departmentname");
        return $this->db->get()->result_array();
    }

    public function getRecruiterAnalytics($accessibleJobIds = null)
    {
        $jobFilter = "";
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) {
                $jobFilter = " AND jl.Jid IN (" . implode(",", array_map("intval", $accessibleJobIds)) . ") ";
            } else {
                $jobFilter = " AND 1=0 ";
            }
        }
        $sqlRec = "SELECT u.IUid, u.EmpName, u.EmpEmail,
                          COUNT(DISTINCT jl.Jid) as total_assigned_jobs,
                          COUNT(DISTINCT ja.ApplicationId) as total_candidates,
                          SUM(CASE WHEN ja.CurrentStatus LIKE '%Hired%' OR ja.CurrentStatus LIKE '%Select%' OR ja.CurrentStatus LIKE '%Offer%' THEN 1 ELSE 0 END) as total_hired,
                          SUM(CASE WHEN ja.CurrentStatus LIKE '%Reject%' THEN 1 ELSE 0 END) as total_rejected
                   FROM IHUsers u
                   JOIN EmpRoles r ON r.Erid = u.Erid AND (LOWER(r.RoleName) LIKE '%recruiter%' OR LOWER(r.RoleName) LIKE '%manager%')
                   LEFT JOIN IHRJobsList jl ON jl.AssignedRecruiterManagerId = u.IUid" . $jobFilter . "
                   LEFT JOIN JobApplications ja ON ja.Jid = jl.Jid
                   WHERE u.UStatus = 1
                   GROUP BY u.IUid, u.EmpName, u.EmpEmail";
        return $this->db->query($sqlRec)->result_array();
    }

    public function getInterviewerSummaryAnalytics($accessibleJobIds = null)
    {
        $jobFilter = "";
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) {
                $jobFilter = " AND ja.Jid IN (" . implode(",", array_map("intval", $accessibleJobIds)) . ") ";
            } else {
                $jobFilter = " AND 1=0 ";
            }
        }
        $sqlIntSummary = "SELECT u.IUid, u.EmpName, u.EmpEmail,
                                 COUNT(ci.InterviewId) as total_interviews,
                                 SUM(CASE WHEN ci.Result = 'Selected' THEN 1 ELSE 0 END) as total_selected,
                                 SUM(CASE WHEN ci.Result = 'Rejected' THEN 1 ELSE 0 END) as total_rejected,
                                 SUM(CASE WHEN ci.Result = 'On Hold' THEN 1 ELSE 0 END) as total_onhold,
                                 SUM(CASE WHEN ci.Result = 'Assigned' OR ci.Result IS NULL OR ci.Result = '' THEN 1 ELSE 0 END) as total_pending,
                                 ROUND(AVG(NULLIF(ci.OverallScore, 0)), 1) as avg_score
                          FROM IHUsers u
                          JOIN CandidateInterviews ci ON ci.InterviewerId = u.IUid
                          JOIN JobApplications ja ON ja.ApplicationId = ci.ApplicationId" . $jobFilter . "
                          WHERE u.UStatus = 1
                          GROUP BY u.IUid, u.EmpName, u.EmpEmail";
        return $this->db->query($sqlIntSummary)->result_array();
    }

    public function getInterviewerDetailAnalytics($accessibleJobIds = null)
    {
        $jobFilter = "";
        if (is_array($accessibleJobIds)) {
            if (!empty($accessibleJobIds)) {
                $jobFilter = " AND ja.Jid IN (" . implode(",", array_map("intval", $accessibleJobIds)) . ") ";
            } else {
                $jobFilter = " AND 1=0 ";
            }
        }
        $sqlIntDetail = "SELECT ci.InterviewId, ci.InterviewType, ci.ScheduledAt, ci.Result, ci.Feedback,
                                ci.SkillScore, ci.CommunicationScore, ci.ProblemSolvingScore, ci.CultureFitScore, ci.LeadershipScore, ci.OverallScore,
                                u.EmpName as InterviewerName,
                                c.Fullname as CandidateName, c.CandidateId,
                                jl.JobTitle, jl.JobCode,
                                rs.StageName
                         FROM CandidateInterviews ci
                         JOIN IHUsers u ON u.IUid = ci.InterviewerId
                         JOIN JobApplications ja ON ja.ApplicationId = ci.ApplicationId" . $jobFilter . "
                         JOIN IHrCandidates c ON c.CandidateId = ja.CandidateId
                         LEFT JOIN IHRJobsList jl ON jl.Jid = ja.Jid
                         LEFT JOIN RecruitmentStages rs ON rs.StageId = ci.StageId
                         ORDER BY ci.InterviewId DESC";
        return $this->db->query($sqlIntDetail)->result_array();
    }

    /* =========================================================================
     * SECTION: CANDIDATE DETAILS & DECISIONS
     * ========================================================================= */

    public function getCandidateDetailsBasic($candidate_id)
    {
        return $this->db->select('
                c.*,
                j.JobTitle,
                j.JobCode,
                j.EducationRequired,
                j.ExpMin,
                j.ExpMax,
                j.JobLocation,
                j.Salary,
                ja.ApplicationId,
                ja.CurrentStage,
                ja.CurrentStatus
            ')
            ->from('IHrCandidates c')
            ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
            ->join('JobApplications ja', 'ja.CandidateId = c.CandidateId AND ja.Jid = c.Jid', 'left')
            ->where('c.CandidateId', $candidate_id)
            ->get()
            ->row_array();
    }

    public function getCandidate360Info($candidate_id)
    {
        return $this->db->select('
                c.*,
                j.JobCode,
                j.JobTitle,
                j.RoleSummary as Role,
                j.JobLocation,
                j.EmploymentType,
                j.ExpMin,
                j.ExpMax,
                j.MustHaveSkills as VacancyMustHaveSkills,
                j.NiceToHaveSkills as VacancyNiceToHaveSkills,
                ja.ApplicationId,
                ja.CurrentStage,
                ja.CurrentStatus,
                ja.AppliedOn
            ')
            ->from('IHrCandidates c')
            ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
            ->join('JobApplications ja', 'ja.CandidateId = c.CandidateId AND ja.Jid = c.Jid', 'left')
            ->where('c.CandidateId', $candidate_id)
            ->get()
            ->row_array();
    }

    public function getCandidateTrackingStages($applicationId)
    {
        return $this->db->select('
                t.*,
                rs.StageName,
                rs.StageGroup,
                u.EmpName as ActionByName
            ')
            ->from('CandidateStageTracking t')
            ->join('RecruitmentStages rs', 'rs.StageId = t.StageId', 'left')
            ->join('IHUsers u', 'u.IUid = t.ActionBy', 'left')
            ->where('t.ApplicationId', $applicationId)
            ->order_by('t.ActionAt', 'ASC')
            ->get()
            ->result_array();
    }

    public function getCandidateInterviewsWithInterviewers($applicationId)
    {
        return $this->db->select('
                ci.*,
                u.EmpCode as InterviewerEmpCode,
                u.EmpName as InterviewerName,
                u.EmpDesignation as InterviewerDesignation
            ')
            ->from('CandidateInterviews ci')
            ->join('IHUsers u', 'u.IUid = ci.InterviewerId', 'left')
            ->where('ci.ApplicationId', $applicationId)
            ->order_by('ci.InterviewId', 'ASC')
            ->get()
            ->result_array();
    }

    public function getCandidateInterviewsSimple($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->order_by('InterviewId', 'ASC')
                        ->get('CandidateInterviews')
                        ->result_array();
    }

    public function getCandidateOffers($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->get('CandidateOffers')
                        ->result_array();
    }

    public function getCandidateHiring($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->get('CandidateHiring')
                        ->result_array();
    }

    public function getCandidateHiringRow($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->get('CandidateHiring')
                        ->row_array();
    }

    public function getCandidateFollowUps($applicationId)
    {
        return $this->db->select('f.*, u.IUid, u.EmpName as CreatedByName')
                        ->from('CandidateFollowUps f')
                        ->join('IHUsers u', 'u.IUid = f.CreatedBy', 'left')
                        ->where('f.ApplicationId', $applicationId)
                        ->order_by('f.CreatedAt', 'DESC')
                        ->get()
                        ->result_array();
    }

    public function getCandidateAiQuestions($candidate_id)
    {
        return $this->db->where('candidate_id', $candidate_id)
                        ->order_by('id', 'ASC')
                        ->get('AI_Interview_Questions')
                        ->result_array();
    }

    public function getCandidateById($candidateId)
    {
        return $this->db->where('CandidateId', $candidateId)
                        ->get('IHrCandidates')
                        ->row_array();
    }

    public function getCandidateByIdObj($candidateId)
    {
        return $this->db->where('CandidateId', $candidateId)
                        ->get('IHrCandidates')
                        ->row();
    }

    public function getCandidatesForComparison($candidateIds)
    {
        return $this->db->select('CandidateId, CandidateCode, Fullname, Email, PhoneNo, ProfileMatchPer, ScoreBreakdown, MatchedSkills, ExpYrs, ExperienceMatch, EducationMatch, ResumePath, ExperienceDetails')
                        ->where_in('CandidateId', $candidateIds)
                        ->get('IHrCandidates')
                        ->result_array();
    }

    public function getApplicationById($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->get('JobApplications')
                        ->row_array();
    }

    public function getApplicationByCandidateId($candidateId)
    {
        return $this->db->select('ApplicationId')
                        ->from('JobApplications')
                        ->where('CandidateId', $candidateId)
                        ->order_by('ApplicationId', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
    }

    public function getApplicationIdByCandidateId($candidateId)
    {
        $app = $this->db->select('ApplicationId')
                        ->from('JobApplications')
                        ->where('CandidateId', $candidateId)
                        ->order_by('ApplicationId', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
        return $app ? (int)$app->ApplicationId : null;
    }

    public function updateJobApplication($applicationId, $data)
    {
        return $this->db->where('ApplicationId', $applicationId)->update('JobApplications', $data);
    }

    public function insertCandidateStageTracking($data)
    {
        return $this->db->insert('CandidateStageTracking', $data);
    }

    public function getFilteredCandidates($jid, $source = 'all', $status = null)
    {
        $this->db->select('
            c.CandidateId, c.CandidateCode, c.Fullname, c.PhoneNo, c.Email, c.Source,
            c.ProfileMatchPer, c.ResumePath, c.ScoreBreakdown, c.MatchedSkills, c.ExperienceMatch,
            c.VerifiedAt, c.ExperienceDetails, c.ExpYrs,
            ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, ja.Jid,
            rs.StageOrder as CurrentStageOrder,
            (SELECT Action FROM CandidateStageTracking
             WHERE ApplicationId = ja.ApplicationId
             ORDER BY ActionAt DESC LIMIT 1) as LastAction
        ');
        $this->db->from('IHrCandidates c');
        $this->db->join('JobApplications ja', 'ja.CandidateId = c.CandidateId');
        $this->db->join('RecruitmentStages rs', 'rs.StageId = ja.CurrentStage', 'left');
        $this->db->where('ja.Jid', $jid);

        if (!empty($source) && strcasecmp($source, 'all') !== 0) {
            if (strtolower($source) === 'online') {
                $this->db->where("c.Source IN ('Online', 'online', 'portal', 'web')", null, false);
            } elseif (strtolower($source) === 'walkin' || strtolower($source) === 'walk-in') {
                $this->db->where("c.Source IN ('Walk-in', 'walk-in', 'walk_in', 'walkin', 'upload', 'manual', 'ats')", null, false);
            }
        }

        if (!empty($status)) {
            if (strcasecmp($status, 'CV Uploaded') === 0) {
                $this->db->group_start();
                $this->db->where('ja.CurrentStatus', 'CV Uploaded');
                $this->db->or_like('ja.CurrentStatus', 'Upload');
                $this->db->or_like('ja.CurrentStatus', 'Applied');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Selected') === 0) {
                $this->db->group_start();
                $this->db->like('ja.CurrentStatus', 'Selected');
                $this->db->or_like('ja.CurrentStatus', 'Offer');
                $this->db->or_like('ja.CurrentStatus', 'Hired');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Rejected') === 0) {
                $this->db->like('ja.CurrentStatus', 'Rejected');
            } elseif (strcasecmp($status, 'On Hold') === 0) {
                $this->db->like('ja.CurrentStatus', 'Hold');
            } elseif (strcasecmp($status, 'In Progress') === 0) {
                $this->db->group_start();
                $this->db->where('ja.CurrentStatus', 'In Progress');
                $this->db->or_like('ja.CurrentStatus', 'Progress');
                $this->db->or_like('ja.CurrentStatus', 'Scheduled');
                $this->db->or_like('ja.CurrentStatus', 'Shortlisted');
                $this->db->or_like('ja.CurrentStatus', 'Interview');
                $this->db->or_where('ja.CurrentStatus NOT IN ("CV Uploaded", "Selected", "Rejected", "On Hold") AND ja.CurrentStatus NOT LIKE "%Reject%" AND ja.CurrentStatus NOT LIKE "%Selected%"', NULL, FALSE);
                $this->db->group_end();
            } else {
                $this->db->like('ja.CurrentStatus', $status);
            }
        }

        return $this->db->get()->result_array();
    }

    /* =========================================================================
     * SECTION: USERS & DEPARTMENTS CRUD
     * ========================================================================= */

    public function checkUserExistsByCode($code, $excludeUserId = null)
    {
        $this->db->where('EmpCode', $code);
        if (!empty($excludeUserId)) {
            $this->db->where('IUid !=', (int)$excludeUserId);
        }
        return $this->db->count_all_results('IHUsers');
    }

    public function checkUserExistsByEmail($email, $excludeUserId = null)
    {
        $this->db->where('EmpEmail', $email);
        if (!empty($excludeUserId)) {
            $this->db->where('IUid !=', (int)$excludeUserId);
        }
        return $this->db->count_all_results('IHUsers');
    }

    public function checkUserExistsByPhone($phone, $excludeUserId = null)
    {
        $this->db->where('EmpPhone', $phone);
        if (!empty($excludeUserId)) {
            $this->db->where('IUid !=', (int)$excludeUserId);
        }
        return $this->db->count_all_results('IHUsers');
    }

    public function insertUser($data)
    {
        $this->db->insert('IHUsers', $data);
        return $this->db->insert_id();
    }

    public function updateUser($userId, $data)
    {
        return $this->db->where('IUid', $userId)->update('IHUsers', $data);
    }

    public function setUserStatus($userId, $status)
    {
        return $this->db->where('IUid', $userId)->update('IHUsers', ['UStatus' => (int)$status]);
    }

    public function checkDepartmentExists($name)
    {
        return $this->db->where('Departmentname', $name)->get('Departments')->row_array();
    }

    public function insertDepartment($data)
    {
        return $this->db->insert('Departments', $data);
    }

    public function updateDepartment($did, $data)
    {
        return $this->db->where('Did', $did)->update('Departments', $data);
    }

    public function setDepartmentStatus($did, $status)
    {
        return $this->db->where('Did', $did)->update('Departments', ['Status' => (int)$status]);
    }

    public function getUserContact($userId)
    {
        return $this->db->select('EmpName, EmpEmail')->where('IUid', $userId)->get('IHUsers')->row();
    }

    public function getUsersByIds($userIds)
    {
        if (empty($userIds)) return [];
        return $this->db->select('u.EmpName, u.EmpEmail')
                        ->from('IHUsers u')
                        ->where_in('u.IUid', $userIds)
                        ->where('u.UStatus', 1)
                        ->get()
                        ->result_array();
    }

    public function getApproverAndManagementUsers($approverRoleId)
    {
        return $this->db->select('u.EmpName, u.EmpEmail, r.RoleName')
                        ->from('IHUsers u')
                        ->join('EmpRoles r', 'u.Erid = r.Erid', 'left')
                        ->where('u.UStatus', 1)
                        ->group_start()
                            ->where('u.Erid', $approverRoleId)
                            ->or_where('LOWER(r.RoleName)', 'management')
                        ->group_end()
                        ->get()
                        ->result_array();
    }

    /* =========================================================================
     * SECTION: SEARCH & SKILLS
     * ========================================================================= */

    public function searchJobLocations($term)
    {
        $this->db->distinct();
        $this->db->select('JobLocation');
        $this->db->like('JobLocation', $term);
        $this->db->where('JobLocation !=', '');
        $this->db->limit(10);
        return $this->db->get('IHRJobsList')->result_array();
    }

    public function searchJobEducations($term)
    {
        $this->db->distinct();
        $this->db->select('EducationRequired');
        $this->db->like('EducationRequired', $term);
        $this->db->where('EducationRequired !=', '');
        $this->db->limit(10);
        return $this->db->get('IHRJobsList')->result_array();
    }

    public function searchJobLanguages($term)
    {
        $this->db->distinct();
        $this->db->select('CommunicationLang');
        $this->db->like('CommunicationLang', $term);
        $this->db->where('CommunicationLang !=', '');
        $this->db->limit(10);
        return $this->db->get('IHRJobsList')->result_array();
    }

    public function searchSkills($term)
    {
        $this->db->distinct();
        $this->db->select('SkillName');
        $this->db->like('SkillName', $term);
        $this->db->where('SkillName !=', '');
        $this->db->limit(10);
        return $this->db->get('IHSkills')->result_array();
    }

    public function checkSkillExists($skillName)
    {
        return $this->db->where('SkillName', $skillName)->get('IHSkills')->row();
    }

    public function insertSkill($skillName)
    {
        $this->db->insert('IHSkills', ['SkillName' => $skillName]);
        return $this->db->insert_id();
    }

    public function checkJobSkillExists($jid, $skillId)
    {
        return $this->db->get_where('JobSkills', [
            'Jid'     => $jid,
            'SkillId' => $skillId
        ])->num_rows();
    }

    public function insertJobSkill($jid, $skillId)
    {
        return $this->db->insert('JobSkills', [
            'Jid'     => $jid,
            'SkillId' => $skillId
        ]);
    }

    /* =========================================================================
     * SECTION: VACANCY & JOB MANAGEMENT
     * ========================================================================= */

    public function checkJobExists($jobTitle, $did)
    {
        return $this->db->where('JobTitle', $jobTitle)
                        ->where('Did', $did)
                        ->get('IHRJobsList')
                        ->row_array();
    }

    public function getLastJobByCodePrefix($prefix)
    {
        $this->db->like('JobCode', $prefix);
        $this->db->order_by('JobCode', 'DESC');
        return $this->db->get('IHRJobsList')->row();
    }

    public function insertVacancy($data)
    {
        $this->db->insert('IHRJobsList', $data);
        return $this->db->insert_id();
    }

    public function getJobById($jid)
    {
        return $this->db->where('Jid', $jid)->get('IHRJobsList')->row_array();
    }

    public function getJobByCode($jobCode)
    {
        return $this->db->select("Jid")->from("IHRJobsList")->where("JobCode", $jobCode)->get()->row_array();
    }

    public function countJobsWithCode($jobCode)
    {
        return $this->db->where("JobCode", $jobCode)->count_all_results("IHRJobsList");
    }

    public function countAllJobs()
    {
        return $this->db->count_all("IHRJobsList");
    }

    public function getJobWithDetails($jid)
    {
        $this->db->select("
            jl.*,
            d.Departmentname,
            u.EmpName AS RecruiterName,
            u.EmpEmail AS RecruiterEmail,
            ctc.EmpName AS CtcApproverName,
            arm.EmpName AS AssignedRecruiterManagerName,
            GROUP_CONCAT(s.SkillName SEPARATOR ', ') AS Skills
        ");
        $this->db->from('IHRJobsList jl');
        $this->db->join('Departments d', 'd.Did = jl.Did', 'left');
        $this->db->join('JobSkills js', 'js.Jid = jl.Jid', 'left');
        $this->db->join('IHSkills s', 's.SkillId = js.SkillId', 'left');
        $this->db->where('jl.Jid', $jid);
        $this->db->group_by('jl.Jid');
        $this->db->join('IHUsers u', 'u.IUid = jl.PostedBy', 'left');
        $this->db->join('IHUsers ctc', 'ctc.IUid = jl.CtcApproverId', 'left');
        $this->db->join('IHUsers arm', 'arm.IUid = jl.AssignedRecruiterManagerId', 'left');

        $row = $this->db->get()->row_array();

        if ($row && !empty($row['Jid'])) {
            $rr = $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                           ->from('Resource_Requests rr')
                           ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                           ->where('rr.ConvertedJid', $row['Jid'])
                           ->get()->row_array();

            if (empty($rr) && !empty($row['JobTitle'])) {
                $rr = $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                               ->from('Resource_Requests rr')
                               ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                               ->where('rr.JobTitle', $row['JobTitle'])
                               ->get()->row_array();
            }

            if (!empty($rr)) {
                if (empty($row['MustHaveSkills']) && !empty($rr['MustHaveSkills'])) {
                    $row['MustHaveSkills'] = $rr['MustHaveSkills'];
                }
                if (empty($row['NiceToHaveSkills']) && !empty($rr['NiceToHaveSkills'])) {
                    $row['NiceToHaveSkills'] = $rr['NiceToHaveSkills'];
                }
                if (empty($row['CommunicationLang']) && !empty($rr['CommunicationLang'])) {
                    $row['CommunicationLang'] = $rr['CommunicationLang'];
                }
                if (empty($row['CtcApproverId']) && !empty($rr['CtcApproverId'])) {
                    $row['CtcApproverId']   = $rr['CtcApproverId'];
                    $row['CtcApproverName'] = $rr['CtcApproverName'];
                    $this->db->where('Jid', $row['Jid'])->update('IHRJobsList', ['CtcApproverId' => $rr['CtcApproverId']]);
                }
            }
        }
        return $row;
    }

    public function getJobInterviewPanels($jid)
    {
        $rawPanels = $this->db->where('Jid', $jid)->order_by('LevelOrder', 'ASC')->get('JobInterviewPanels')->result_array();
        foreach ($rawPanels as &$panel) {
            $interviewer = $this->db->select('EmpName')->where('IUid', $panel['InterviewerId'])->get('IHUsers')->row_array();
            $panel['InterviewerName'] = $interviewer ? $interviewer['EmpName'] : 'Unknown';
        }
        unset($panel);
        return $rawPanels;
    }

    public function getCandidateInterviewPanelInfoData($jid, $applicationId)
    {
        $panels = $this->db->where('Jid', $jid)->order_by('LevelOrder', 'ASC')->get('JobInterviewPanels')->result_array();
        $stages = $this->db->where('StageGroup', 'Interview')->where('StageStatus', 1)->order_by('StageOrder', 'ASC')->get('RecruitmentStages')->result_array();

        $interviews = [];
        if (!empty($applicationId)) {
            $interviews = $this->db->where('ApplicationId', $applicationId)->order_by('InterviewId', 'ASC')->get('CandidateInterviews')->result_array();
        }

        foreach ($panels as &$p) {
            $u = $this->db->select('EmpName, EmpDesignation')->where('IUid', $p['InterviewerId'])->get('IHUsers')->row_array();
            $p['InterviewerName'] = $u ? $u['EmpName'] : 'Unknown';
            $p['InterviewerDesignation'] = $u ? $u['EmpDesignation'] : '';
        }
        unset($p);

        return [
            'panels'     => $panels,
            'stages'     => $stages,
            'interviews' => $interviews
        ];
    }

    public function updateVacancy($jid, $data)
    {
        return $this->db->where('Jid', $jid)->update('IHRJobsList', $data);
    }

    public function syncJobInterviewPanels($jid, $panelLevels)
    {
        $this->db->where('Jid', $jid)->delete('JobInterviewPanels');
        foreach ($panelLevels as $levelOrder => $interviewerId) {
            if ($interviewerId > 0) {
                $this->db->insert('JobInterviewPanels', [
                    'Jid'           => $jid,
                    'LevelOrder'    => $levelOrder,
                    'InterviewerId' => $interviewerId
                ]);
            }
        }
    }

    public function getFilteredVacancies($roleId, $currentUserId, $department = null, $status = null, $daterange = null)
    {
        $roleName = strtolower(trim($this->getRoleName($roleId)));
        if (empty($roleName) && !empty($currentUserId)) {
            $userRow = $this->db->select('r.RoleName')
                                ->from('IHUsers u')
                                ->join('EmpRoles r', 'u.Erid = r.Erid', 'left')
                                ->where('u.IUid', $currentUserId)
                                ->get()->row_array();
            if (!empty($userRow['RoleName'])) {
                $roleName = strtolower(trim($userRow['RoleName']));
            }
        }

        $this->db->select("jl.*, d.Departmentname, (SELECT COUNT(DISTINCT ja.ApplicationId) FROM JobApplications ja WHERE ja.Jid = jl.Jid) AS CandidateCount", false);
        $this->db->from('IHRJobsList jl');
        $this->db->join('Departments d','d.Did = jl.Did','left');
        $this->db->join('Resource_Requests rr', 'rr.ConvertedJid = jl.Jid', 'left');

        // Only include vacancies that are standalone or converted from an ASSIGNED resource request
        $this->db->where('(rr.RequestId IS NULL OR rr.Status = "ASSIGNED")', null, false);
        $this->db->where('(jl.AssignedRecruiterManagerId IS NOT NULL OR rr.RequestId IS NULL)', null, false);

        if ($roleName === 'recruiter' || $roleName === 'recruitment manager') {
            $this->db->where('jl.AssignedRecruiterManagerId', $currentUserId);
        }

        if (!empty($daterange)) {
            $dates = explode(' - ', $daterange);
            if (count($dates) == 2) {
                $start = date('Y-m-d', strtotime(trim($dates[0])));
                $end   = date('Y-m-d', strtotime(trim($dates[1])));
                $this->db->where('DATE(jl.PostedOn) >=', $start);
                $this->db->where('DATE(jl.PostedOn) <=', $end);
            }
        }

        if (!empty($department)) {
            $this->db->where('d.Departmentname', $department);
        }

        if (!empty($status)) {
            $this->db->where('jl.JobStatus', $status);
        }

        $this->db->order_by('jl.Jid', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getJobWithRecruiterDetails($jid)
    {
        return $this->db->select('jl.*, u.EmpName AS RecruiterName, u.EmpEmail AS RecruiterEmail, pb.EmpName AS PostedByName, pb.EmpEmail AS PostedByEmail, d.Departmentname')
                        ->from('IHRJobsList jl')
                        ->join('IHUsers u', 'u.IUid = jl.AssignedRecruiterManagerId', 'left')
                        ->join('IHUsers pb', 'pb.IUid = jl.PostedBy', 'left')
                        ->join('Departments d', 'd.Did = jl.Did', 'left')
                        ->where('jl.Jid', $jid)
                        ->get()->row_array();
    }

    public function insertJobTracking($data)
    {
        return $this->db->insert('JobTracking', $data);
    }

    public function getExpiredOnHoldJobs($today)
    {
        return $this->db->group_start()
                            ->where('JobStatus', 'On-Hold')
                            ->or_where('JobStatus', 'Hold')
                            ->or_where('JobStatus', 'on-hold')
                        ->group_end()
                        ->where('HoldUntilDate IS NOT NULL', null, false)
                        ->where('HoldUntilDate <', $today)
                        ->get('IHRJobsList')
                        ->result_array();
    }

    public function getLatestTrackingByEvent($jid, $eventType)
    {
        return $this->db->where('Jid', $jid)
                        ->where('EventType', $eventType)
                        ->order_by('TrackId', 'DESC')
                        ->limit(1)
                        ->get('JobTracking')
                        ->row_array();
    }

    public function getJobStatusHistoryRows($tableName = 'JobTracking')
    {
        return $this->db->get($tableName)->result_array();
    }

    public function checkJobTrackingExists($jid, $eventType, $changedAt, $holdDate = null)
    {
        $this->db->where('Jid', $jid)
                 ->where('EventType', $eventType)
                 ->group_start()
                     ->where('ActionAt', $changedAt)
                     ->or_where('HoldUntilDate', $holdDate)
                 ->group_end();
        return $this->db->get('JobTracking')->row_array();
    }

    public function getJobHistoryDetailsData($jid)
    {
        $job = $this->db->select('
                jl.*,
                d.Departmentname,
                u_posted.EmpName AS PostedByName,
                u_rec.EmpName AS AssignedRecruiterManagerName,
                u_ctc.EmpName AS CtcApproverName
            ')
            ->from('IHRJobsList jl')
            ->join('Departments d', 'd.Did = jl.Did', 'left')
            ->join('IHUsers u_posted', 'u_posted.IUid = jl.PostedBy', 'left')
            ->join('IHUsers u_rec', 'u_rec.IUid = jl.AssignedRecruiterManagerId', 'left')
            ->join('IHUsers u_ctc', 'u_ctc.IUid = jl.CtcApproverId', 'left')
            ->where('jl.Jid', $jid)
            ->get()
            ->row_array();

        if (empty($job)) {
            return null;
        }

        $resourceRequest = $this->db->select('
                rr.*,
                d.Departmentname,
                u_req.EmpName AS RequestedByName,
                u_app.EmpName AS ApproverName,
                u_ctc.EmpName AS CtcApproverName
            ')
            ->from('Resource_Requests rr')
            ->join('Departments d', 'd.Did = rr.Did', 'left')
            ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
            ->join('IHUsers u_app', 'u_app.IUid = rr.ApproverId', 'left')
            ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
            ->where('rr.ConvertedJid', $jid)
            ->get()
            ->row_array();

        if (empty($resourceRequest) && !empty($job['JobTitle'])) {
            $resourceRequest = $this->db->select('
                    rr.*,
                    d.Departmentname,
                    u_req.EmpName AS RequestedByName,
                    u_app.EmpName AS ApproverName,
                    u_ctc.EmpName AS CtcApproverName
                ')
                ->from('Resource_Requests rr')
                ->join('Departments d', 'd.Did = rr.Did', 'left')
                ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
                ->join('IHUsers u_app', 'u_app.IUid = rr.ApproverId', 'left')
                ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
                ->where('rr.JobTitle', $job['JobTitle'])
                ->get()
                ->row_array();
        }

        $applications = $this->db->select('ja.ApplicationId, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.CandidateCode')
            ->from('JobApplications ja')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
            ->where('ja.Jid', $jid)
            ->order_by('ja.ApplicationId', 'DESC')
            ->get()
            ->result_array();

        $requestId = !empty($resourceRequest['RequestId']) ? (int)$resourceRequest['RequestId'] : null;

        $this->db->select('jt.*, u.EmpName AS ActionByName')
            ->from('JobTracking jt')
            ->join('IHUsers u', 'u.IUid = jt.ActionBy', 'left')
            ->group_start()
                ->where('jt.Jid', $jid);
        if (!empty($requestId)) {
            $this->db->or_where('jt.RequestId', $requestId);
        }
        $this->db->group_end()
            ->order_by('jt.TrackId', 'ASC');

        $trackingRows = $this->db->get()->result_array();

        $filledCandidates = [];
        $appIds = array_column($applications, 'ApplicationId');
        if (!empty($appIds)) {
            $filledCandidates = $this->db->select('ja.*, c.Fullname AS CandidateName, c.CandidateCode, cst.ActionAt AS FilledAt, u.EmpName AS FilledByName')
                ->from('JobApplications ja')
                ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
                ->join('CandidateStageTracking cst', 'cst.ApplicationId = ja.ApplicationId', 'left')
                ->join('IHUsers u', 'u.IUid = cst.ActionBy', 'left')
                ->where_in('ja.ApplicationId', $appIds)
                ->group_start()
                    ->where('ja.CurrentStatus', 'Selected')
                    ->or_where('ja.CurrentStatus', 'Hired')
                    ->or_like('ja.CurrentStatus', 'Hired')
                    ->or_like('ja.CurrentStatus', 'Select')
                ->group_end()
                ->group_by('ja.ApplicationId')
                ->order_by('cst.ActionAt', 'DESC')
                ->get()
                ->result_array();
        }

        return [
            'job'              => $job,
            'resourceRequest'  => $resourceRequest,
            'applications'     => $applications,
            'trackingRows'     => $trackingRows,
            'filledCandidates' => $filledCandidates
        ];
    }

    /* =========================================================================
     * SECTION: RECRUITMENT STAGES & INTERVIEWS
     * ========================================================================= */

    public function getAllRecruitmentStages()
    {
        return $this->db->order_by('StageGroup', 'ASC')
                        ->order_by('StageOrder', 'ASC')
                        ->get('RecruitmentStages')
                        ->result_array();
    }

    public function checkStageOrderExists($stageGroup, $stageOrder, $excludeStageId = null)
    {
        $this->db->where('StageGroup', $stageGroup)
                 ->where('StageOrder', $stageOrder);
        if ($excludeStageId) {
            $this->db->where('StageId !=', $excludeStageId);
        }
        return $this->db->get('RecruitmentStages')->num_rows();
    }

    public function shiftStageOrdersUp($stageGroup, $fromOrder)
    {
        return $this->db->set('StageOrder', 'StageOrder + 1', FALSE)
                        ->where('StageGroup', $stageGroup)
                        ->where('StageOrder >=', $fromOrder)
                        ->update('RecruitmentStages');
    }

    public function insertStage($data)
    {
        return $this->db->insert('RecruitmentStages', $data);
    }

    public function updateStage($stageId, $data)
    {
        return $this->db->where('StageId', $stageId)->update('RecruitmentStages', $data);
    }

    public function getMaxStageOrder($stageGroup)
    {
        $row = $this->db->select_max('StageOrder')
                        ->where('StageGroup', $stageGroup)
                        ->get('RecruitmentStages')
                        ->row();
        return $row && $row->StageOrder ? (int)$row->StageOrder : 0;
    }

    public function setStageStatus($stageId, $status)
    {
        return $this->db->where('StageId', $stageId)->update('RecruitmentStages', ['StageStatus' => $status]);
    }

    public function getCurrentStageByOrder($stageOrder)
    {
        return $this->db->where('StageOrder', $stageOrder)->get('RecruitmentStages')->row();
    }

    public function getNextRecruitmentStages($currentGroup, $currentOrder)
    {
        $this->db->group_start()
            ->group_start()
                ->where('StageGroup', $currentGroup)
                ->where('StageOrder >', $currentOrder)
            ->group_end()
            ->or_where('StageGroup', 'Rejection')
        ->group_end();

        $this->db->where('StageStatus', 1);
        $this->db->order_by('StageOrder','ASC');
        return $this->db->get('RecruitmentStages')->result();
    }

    public function getStageByGroupAndStatus($group, $status = 1)
    {
        return $this->db->where('StageGroup', $group)
                        ->where('StageStatus', $status)
                        ->order_by('StageOrder', 'ASC')
                        ->get('RecruitmentStages')
                        ->row();
    }

    public function getStageById($stageId)
    {
        return $this->db->where('StageId', $stageId)->get('RecruitmentStages')->row();
    }

    public function getStageByGroupAndName($group, $name)
    {
        return $this->db->where('StageGroup', $group)
                        ->like('StageName', $name)
                        ->get('RecruitmentStages')
                        ->row();
    }

    public function getInterviewStages()
    {
        return $this->db->where('StageGroup', 'Interview')
                        ->where('StageStatus', 1)
                        ->order_by('StageOrder', 'ASC')
                        ->get('RecruitmentStages')
                        ->result_array();
    }

    public function cleanupDuplicateAssignedInterviews()
    {
        return $this->db->query("
            UPDATE CandidateInterviews ci1 
            JOIN CandidateInterviews ci2 
              ON ci1.ApplicationId = ci2.ApplicationId 
             AND ci1.InterviewId < ci2.InterviewId 
            SET ci1.Result = 'Rescheduled' 
            WHERE (ci1.Result = 'Assigned' OR ci1.Result IS NULL OR ci1.Result = '')
        ");
    }

    public function getAssignedInterviewsForUser($userId, $status = null)
    {
        $this->db->select('
            ci.InterviewId,
            ci.InterviewType,
            ci.InterviewLink,
            ci.ScheduledAt,
            ci.Result,
            ci.Feedback,
            ci.SkillScore,
            ci.CommunicationScore,
            ci.ProblemSolvingScore,
            ci.CultureFitScore,
            ci.LeadershipScore,
            ci.OverallScore,
            ja.ApplicationId,
            c.CandidateId,
            c.CandidateCode,
            c.Fullname,
            c.PhoneNo,
            c.Email,
            c.ResumePath,
            c.Source,
            c.ProfileMatchPer,
            c.MatchedSkills,
            c.EducationMatch,
            c.ExperienceMatch,
            j.JobTitle,
            j.JobCode
        ');
        $this->db->from('CandidateInterviews ci');
        $this->db->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId');
        $this->db->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId');
        $this->db->join('IHRJobsList j', 'j.Jid = ja.Jid', 'left');

        $this->db->where('ci.InterviewerId', $userId);
        $this->db->where('ci.ScheduledAt IS NOT NULL', null, false);

        if (!empty($status)) {
            if (strcasecmp($status, 'Assigned') === 0) {
                $this->db->group_start();
                $this->db->where('ci.Result', 'Assigned');
                $this->db->or_where('ci.Result IS NULL', null, false);
                $this->db->or_where('ci.Result', '');
                $this->db->or_like('ci.Result', 'Pending');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Selected') === 0) {
                $this->db->group_start();
                $this->db->like('ci.Result', 'Selected');
                $this->db->or_like('ci.Result', 'Passed');
                $this->db->or_like('ci.Result', 'Shortlist');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Rejected') === 0) {
                $this->db->group_start();
                $this->db->like('ci.Result', 'Rejected');
                $this->db->or_like('ci.Result', 'Failed');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'On Hold') === 0) {
                $this->db->like('ci.Result', 'Hold');
            } elseif (strcasecmp($status, 'Cancelled') === 0) {
                $this->db->like('ci.Result', 'Cancel');
            } else {
                $this->db->like('ci.Result', $status);
            }
        }

        $this->db->order_by('ci.ScheduledAt', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getInterviewCalendarData($userId)
    {
        return $this->db->select('
                c.CandidateId,
                c.CandidateCode,
                c.Fullname,
                c.PhoneNo,
                c.Email,
                c.ResumePath,
                c.Source,
                c.ProfileMatchPer,
                c.ScoreBreakdown,
                c.MatchedSkills,
                c.ExperienceMatch,
                c.EducationMatch,
                c.VerifiedAt,
                c.ExperienceDetails,
                c.ExpYrs,
                ja.ApplicationId,
                ja.CurrentStage,
                ja.CurrentStatus,
                ja.AppliedOn,
                j.JobTitle,
                j.JobCode,
                rs.StageName,
                rs.StageOrder,
                ci.InterviewId,
                ci.InterviewType,
                ci.InterviewLink,
                ci.ScheduledAt,
                ci.Result as InterviewResult,
                ci.Feedback as InterviewFeedback,
                ci.SkillScore,
                ci.CommunicationScore,
                ci.ProblemSolvingScore,
                ci.CultureFitScore,
                ci.LeadershipScore,
                ci.OverallScore
            ')
            ->from('CandidateInterviews ci')
            ->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId', 'inner')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'inner')
            ->join('IHRJobsList j', 'j.Jid = ja.Jid', 'left')
            ->join('RecruitmentStages rs', 'rs.StageId = ci.StageId', 'left')
            ->where('ci.InterviewerId', $userId)
            ->where('ci.ScheduledAt IS NOT NULL', null, false)
            ->order_by('ci.ScheduledAt', 'ASC')
            ->get()
            ->result_array();
    }

    public function getInterviewDetailsById($interviewId)
    {
        return $this->db->select('InterviewId, Result, Feedback, SkillScore, CommunicationScore, ProblemSolvingScore, CultureFitScore, LeadershipScore, OverallScore')
                        ->where('InterviewId', $interviewId)
                        ->get('CandidateInterviews')
                        ->row_array();
    }

    public function getInterviewRecordById($interviewId)
    {
        return $this->db->where('InterviewId', $interviewId)
                        ->get('CandidateInterviews')
                        ->row_array();
    }

    public function updateInterviewResultData($interviewId, $data)
    {
        return $this->db->where('InterviewId', $interviewId)->update('CandidateInterviews', $data);
    }

    public function getApplicationStageInfo($applicationId)
    {
        return $this->db->select('CurrentStage, StageId')
                        ->where('ApplicationId', $applicationId)
                        ->get('JobApplications')
                        ->row_array();
    }

    public function updateCandidateStatus($candidateId, $status)
    {
        return $this->db->where('CandidateId', $candidateId)->update('IHrCandidates', ['ATS_Status' => $status]);
    }

    public function insertCandidateInterview($data)
    {
        $this->db->insert('CandidateInterviews', $data);
        return $this->db->insert_id();
    }

    public function checkCandidateInterviewCount($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)->count_all_results('CandidateInterviews');
    }

    public function insertCandidateOffer($data)
    {
        $this->db->insert('CandidateOffers', $data);
        return $this->db->insert_id();
    }

    public function insertCandidateHiring($data)
    {
        $this->db->insert('CandidateHiring', $data);
        return $this->db->insert_id();
    }

    public function getAiInterviewQuestionsData($interviewId, $source = 'ai')
    {
        $interviewDetails = $this->db->select('c.Fullname as CandidateName, c.ProfileMatchPer, j.JobTitle, j.JobCode, j.MustHaveSkills')
            ->from('CandidateInterviews ci')
            ->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId')
            ->join('IHRJobsList j', 'j.Jid = ja.Jid')
            ->where('ci.InterviewId', $interviewId)
            ->get()
            ->row_array();

        $versionsRes = $this->db->distinct()
            ->select('generation_version')
            ->where('interview_id', $interviewId)
            ->order_by('generation_version', 'ASC')
            ->get('AI_Interview_Questions')
            ->result_array();

        $latestGen = $this->db->select('reason')
            ->where('interview_id', $interviewId)
            ->where('is_active', 1)
            ->where('source', $source)
            ->limit(1)
            ->get('AI_Interview_Questions')
            ->row_array();

        return [
            'interviewDetails' => $interviewDetails,
            'versionsRes'      => $versionsRes,
            'latestGen'        => $latestGen
        ];
    }

    public function updateAiQuestion($questionId, $data)
    {
        return $this->db->where('id', $questionId)->update('AI_Interview_Questions', $data);
    }

    /* =========================================================================
     * SECTION: ROLES & PERMISSIONS
     * ========================================================================= */

    public function getRoleById($roleId)
    {
        return $this->db->select('RoleName')
                        ->from('EmpRoles')
                        ->where('Erid', $roleId)
                        ->get()
                        ->row_array();
    }

    public function getRoleName($roleId)
    {
        $roleIdInt = (int)$roleId;
        if ($roleIdInt <= 0) {
            return '';
        }
        if (isset($this->roleNameCache[$roleIdInt])) {
            return $this->roleNameCache[$roleIdInt];
        }
        $roleRow = $this->db->select('RoleName')
                            ->from('EmpRoles')
                            ->where('Erid', $roleIdInt)
                            ->get()
                            ->row_array();
        $name = !empty($roleRow) ? $roleRow['RoleName'] : '';
        $this->roleNameCache[$roleIdInt] = $name;
        return $name;
    }

    public function getActiveRoles()
    {
        return $this->db->where('Status', 1)->get('EmpRoles')->result_array();
    }

    public function getActiveMenus()
    {
        return $this->db->where('MenuStatus', 1)->get('IHMenus')->result_array();
    }

    public function getAllMenus()
    {
        return $this->db->select('IHMid')->get('IHMenus')->result_array();
    }

    public function getRolePermissionMenuIds($roleId)
    {
        return $this->db->select('IHMid')
                        ->where('Erid', $roleId)
                        ->where('Status', 1)
                        ->get('IHRolePermissions')
                        ->result_array();
    }

    public function checkRolePermissionExists($roleId, $menuId)
    {
        return $this->db->where('Erid', $roleId)
                        ->where('IHMid', $menuId)
                        ->get('IHRolePermissions')
                        ->row_array();
    }

    public function updateRolePermission($roleId, $menuId, $status)
    {
        return $this->db->where('Erid', $roleId)
                        ->where('IHMid', $menuId)
                        ->update('IHRolePermissions', [
                            'Status'    => $status,
                            'UpdatedAT' => date('Y-m-d H:i:s')
                        ]);
    }

    public function insertRolePermission($roleId, $menuId, $status)
    {
        return $this->db->insert('IHRolePermissions', [
            'Erid'      => $roleId,
            'IHMid'     => $menuId,
            'Status'    => $status,
            'CreatedAT' => date('Y-m-d H:i:s'),
            'UpdatedAT' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check whether a given role has permission to access a page by its URL.
     *
     * Looks up the IHMenus entry whose Menuurl matches $url (case-insensitive),
     * then checks IHRolePermissions for an active (Status = 1) entry for that
     * role and menu combination.
     *
     * Returns TRUE  if the role is explicitly permitted (Status = 1).
     * Returns FALSE if the role is denied (Status = 0) or no entry exists.
     *
     * @param  int|null    $roleId  The role's Erid value from the session.
     * @param  string      $url     The page URL as stored in IHMenus.Menuurl
     *                              (e.g. 'admin/VaccancyList').
     * @return bool
     */
    public function hasPagePermission($roleId, $url)
    {
        if (empty($roleId) || empty($url)) {
            return false;
        }

        $menu = $this->db
            ->select('IHMid')
            ->where('LOWER(Menuurl)', strtolower($url))
            ->where('MenuStatus', 1)
            ->get('IHMenus')
            ->row_array();

        if (empty($menu)) {
            // URL not registered in IHMenus — cannot determine permission
            return false;
        }

        $permission = $this->db
            ->where('Erid',  (int)$roleId)
            ->where('IHMid', (int)$menu['IHMid'])
            ->where('Status', 1)
            ->get('IHRolePermissions')
            ->row_array();

        return !empty($permission);
    }

    /* =========================================================================
     * SECTION: RESOURCE REQUESTS EXTENDED
     * ========================================================================= */

    public function countResourceRequests()
    {
        return $this->db->count_all("Resource_Requests");
    }

    public function getNextResourceRequestCode()
    {
        $count = $this->db->count_all("Resource_Requests") + 1;
        return "RR-" . date("Y") . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
    }



    /* =========================================================================
     * SECTION: DASHBOARD EXACT QUERIES
     * ========================================================================= */

    public function getDashboardStagesWithCounts($roleId, $uid, $accessibleJobIds)
    {
        $roleName = strtolower(trim($this->getRoleName($roleId)));
        $isHiringManager = ($roleName === 'hiring manager');

        $stages = $this->db
            ->order_by("StageOrder", "ASC")
            ->get("RecruitmentStages")
            ->result_array();

        foreach ($stages as &$stage) {
            $this->db->from("JobApplications ja");
            $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
            if ($isHiringManager) {
                $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
                $this->db->where("ci.InterviewerId", $uid);
            } elseif ($accessibleJobIds !== null) {
                $this->db->where_in("ja.Jid", $accessibleJobIds);
            }
            $this->db->group_start()
                ->where("ja.CurrentStage", $stage["StageName"])
                ->or_where("ja.CurrentStage", $stage["StageId"])
            ->group_end();
            $stage["count"] = $this->db->count_all_results();
        }
        unset($stage);
        return $stages;
    }

    public function getDashboardVacancyCountsExact($accessibleJobIds)
    {
        $this->db->from("IHRJobsList");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $total = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->group_start()
            ->where("JobStatus", "On-Hold")
            ->or_where("JobStatus", "On Hold")
        ->group_end();
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $onhold = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->where("JobStatus", "Open");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $open = $this->db->count_all_results();

        return [
            'total'  => $total,
            'onhold' => $onhold,
            'open'   => $open
        ];
    }

    public function getOnHoldRemindersExact($reminderDate, $accessibleJobIds)
    {
        $this->db->select("jl.*, u.EmpName AS RecruiterName, u.EmpEmail AS RecruiterEmail, pb.EmpName AS PostedByName, pb.EmpEmail AS PostedByEmail, d.Departmentname");
        $this->db->from("IHRJobsList jl");
        $this->db->join("IHUsers u", "u.IUid = jl.AssignedRecruiterManagerId", "left");
        $this->db->join("IHUsers pb", "pb.IUid = jl.PostedBy", "left");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->group_start();
            $this->db->where("jl.JobStatus", "On-Hold");
            $this->db->or_where("jl.JobStatus", "On Hold");
        $this->db->group_end();
        $this->db->where("jl.HoldUntilDate IS NOT NULL", null, false);
        $this->db->where("jl.HoldUntilDate", $reminderDate);

        if ($accessibleJobIds !== null) {
            $this->db->where_in("jl.Jid", $accessibleJobIds);
        }
        return $this->db->get()->result_array();
    }

    public function getDashboardRejectedCount($accessibleJobIds)
    {
        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->like("ja.CurrentStatus", "Rejected");
        return $this->db->count_all_results();
    }

    public function getDashboardScreenedCount($accessibleJobIds)
    {
        $screenedStage = $this->db
            ->group_start()
                ->where("StageGroup", "Application")
                ->like("StageName", "Screened")
            ->group_end()
            ->get("RecruitmentStages")
            ->row();
        $screenedStageId = $screenedStage ? $screenedStage->StageId : 2;

        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->group_start()
            ->where("ja.CurrentStage", $screenedStageId)
            ->or_where("ja.CurrentStage", "Screened")
            ->or_where("ja.CurrentStage", " Screened")
        ->group_end();
        return $this->db->count_all_results();
    }

    public function getDashboardMonthlyStatsExact($accessibleJobIds)
    {
        $monthly_apps = array_fill(1, 12, 0);
        $monthly_selected = array_fill(1, 12, 0);
        $monthly_rejected = array_fill(1, 12, 0);

        // Apps
        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->where("YEAR(ja.AppliedOn)", date("Y"));
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $apps_res = $this->db->get()->result_array();
        foreach ($apps_res as $row) {
            $m = (int)$row["month"];
            if ($m >= 1 && $m <= 12) $monthly_apps[$m] = (int)$row["total"];
        }

        // Selected
        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->where("YEAR(ja.AppliedOn)", date("Y"));
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->group_start()
            ->like("ja.CurrentStatus", "Selected")
            ->or_like("ja.CurrentStatus", "Accepted")
            ->or_like("ja.CurrentStatus", "Released")
            ->or_like("ja.CurrentStatus", "Boarding")
        ->group_end();
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $sel_res = $this->db->get()->result_array();
        foreach ($sel_res as $row) {
            $m = (int)$row["month"];
            if ($m >= 1 && $m <= 12) $monthly_selected[$m] = (int)$row["total"];
        }

        // Rejected
        $this->db->select("MONTH(ja.AppliedOn) as month, COUNT(ja.ApplicationId) as total");
        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->where("YEAR(ja.AppliedOn)", date("Y"));
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->like("ja.CurrentStatus", "Rejected");
        $this->db->group_by("MONTH(ja.AppliedOn)");
        $rej_res = $this->db->get()->result_array();
        foreach ($rej_res as $row) {
            $m = (int)$row["month"];
            if ($m >= 1 && $m <= 12) $monthly_rejected[$m] = (int)$row["total"];
        }

        return [
            'apps'     => $monthly_apps,
            'selected' => $monthly_selected,
            'rejected' => $monthly_rejected
        ];
    }

    public function getDashboardRoleUserCounts()
    {
        $this->db->select("r.RoleName, COUNT(u.IUid) as total_users");
        $this->db->from("IHUsers u");
        $this->db->join("EmpRoles r", "u.Erid = r.Erid", "left");
        $this->db->group_by("u.Erid");
        $result = $this->db->get()->result_array();

        $userLabels = [];
        $userCounts = [];
        foreach ($result as $row) {
            $userLabels[] = !empty($row["RoleName"]) ? $row["RoleName"] : "Unassigned";
            $userCounts[] = (int)$row["total_users"];
        }
        return [
            'labels' => $userLabels,
            'counts' => $userCounts
        ];
    }

    public function getDashboardAllJobsExact($roleId, $uid, $accessibleJobIds)
    {
        $roleName = strtolower(trim($this->getRoleName($roleId)));
        $isHiringManager = ($roleName === 'hiring manager');

        if ($isHiringManager) {
            $this->db->distinct();
            $this->db->select("jl.*, d.Departmentname");
            $this->db->from("IHRJobsList jl");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            $this->db->join("JobApplications ja", "ja.Jid = jl.Jid", "inner");
            $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
            $this->db->where("ci.InterviewerId", $uid);
            $this->db->order_by("jl.PostedOn", "DESC");
            return $this->db->get()->result_array();
        } else {
            $this->db->select("jl.*, d.Departmentname");
            $this->db->from("IHRJobsList jl");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            if ($accessibleJobIds !== null) {
                $this->db->where_in("jl.Jid", $accessibleJobIds);
            }
            $this->db->order_by("jl.PostedOn", "DESC");
            return $this->db->get()->result_array();
        }
    }

    public function getDashboardAllCandidatesExact($roleId, $uid, $accessibleJobIds)
    {
        $roleName = strtolower(trim($this->getRoleName($roleId)));
        $isHiringManager = ($roleName === 'hiring manager');

        if ($isHiringManager) {
            $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, 1 as IsAssignedInterviewer", false);
            $this->db->from("JobApplications ja");
            $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
            $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
            $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");
            $this->db->where("ci.InterviewerId", $uid);
            $this->db->order_by("ja.AppliedOn", "DESC");
            return $this->db->get()->result_array();
        } else {
            $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, IF(ci.InterviewerId = " . (int)$uid . ", 1, 0) as IsAssignedInterviewer", false);
            $this->db->from("JobApplications ja");
            $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
            $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
            $this->db->join("Departments d", "d.Did = jl.Did", "left");
            $this->db->join("CandidateInterviews ci", "ci.ApplicationId = ja.ApplicationId AND (ci.Result = 'Assigned' OR ci.ScheduledAt IS NOT NULL)", "left");
            $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");

            if ($accessibleJobIds !== null) {
                $this->db->where_in("ja.Jid", $accessibleJobIds);
            }

            $this->db->order_by("ja.AppliedOn", "DESC");
            return $this->db->get()->result_array();
        }
    }

    public function getDashboardResourceRequestsList()
    {
        return $this->db->select("rr.*, d.Departmentname, req_u.EmpName as RequestedByName, app_u.EmpName as ApproverName")
            ->from("Resource_Requests rr")
            ->join("Departments d", "d.Did = rr.Did", "left")
            ->join("IHUsers req_u", "req_u.IUid = rr.RequestedBy", "left")
            ->join("IHUsers app_u", "app_u.IUid = rr.ApproverId", "left")
            ->order_by("rr.CreatedAt", "DESC")
            ->get()
            ->result_array();
    }

    /* =========================================================================
     * SECTION: ANALYTICS EXACT QUERIES
     * ========================================================================= */

    public function getAnalyticsSummaryCountsExact($accessibleJobIds)
    {
        $this->db->from("IHRJobsList");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $total_jobs = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $total_candidates = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $total_applications = $this->db->count_all_results();

        $total_requests = $this->db->count_all_results("Resource_Requests");
        
        $this->db->from("IHRJobsList");
        $this->db->where_in("JobStatus", ["Open", "Re-Open"]);
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $open_jobs = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->where_in("JobStatus", ["Closed", "Dropped"]);
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $closed_jobs = $this->db->count_all_results();

        $this->db->from("IHRJobsList");
        $this->db->where_in("JobStatus", ["On-Hold", "On Hold"]);
        if ($accessibleJobIds !== null) {
            $this->db->where_in("Jid", $accessibleJobIds);
        }
        $hold_jobs = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->group_start()
            ->like("ja.CurrentStatus", "Selected")
            ->or_like("ja.CurrentStatus", "Accepted")
            ->or_like("ja.CurrentStatus", "Boarding")
            ->or_like("ja.CurrentStatus", "Hired")
        ->group_end();
        $hired_candidates = $this->db->count_all_results();

        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->like("ja.CurrentStatus", "Rejected");
        $rejected_candidates = $this->db->count_all_results();

        return [
            'total_jobs'          => $total_jobs,
            'total_candidates'    => $total_candidates,
            'total_applications'  => $total_applications,
            'total_requests'      => $total_requests,
            'open_jobs'           => $open_jobs,
            'closed_jobs'         => $closed_jobs,
            'hold_jobs'           => $hold_jobs,
            'hired_candidates'    => $hired_candidates,
            'rejected_candidates' => $rejected_candidates,
        ];
    }

    public function getAnalyticsJobsHistoryExact($accessibleJobIds)
    {
        $this->db->select("jl.*, d.Departmentname, u.EmpName as RecruiterName");
        $this->db->from("IHRJobsList jl");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->join("IHUsers u", "u.IUid = jl.AssignedRecruiterManagerId", "left");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("jl.Jid", $accessibleJobIds);
        }
        $this->db->order_by("jl.PostedOn", "DESC");
        return $this->db->get()->result_array();
    }

    public function getAnalyticsCandidatesHistoryExact($accessibleJobIds)
    {
        $this->db->select("ja.ApplicationId, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo as MobileNumber, c.ExpYrs as TotalExperience, c.ATS_Status, jl.JobTitle, jl.JobCode, d.Departmentname");
        $this->db->from("JobApplications ja");
        $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }
        $this->db->order_by("ja.AppliedOn", "DESC");
        return $this->db->get()->result_array();
    }

    public function getAnalyticsDeptAnalyticsExact()
    {
        $this->db->select("d.Did, d.Departmentname, COUNT(DISTINCT jl.Jid) as total_jobs, COUNT(DISTINCT ja.ApplicationId) as total_apps");
        $this->db->from("Departments d");
        $this->db->join("IHRJobsList jl", "jl.Did = d.Did", "left");
        $this->db->join("JobApplications ja", "ja.Jid = jl.Jid", "left");
        $this->db->group_by("d.Did");
        return $this->db->get()->result_array();
    }

    public function getAnalyticsRecruiterAnalyticsExact()
    {
        $sqlRec = "SELECT u.IUid, u.EmpName, u.EmpCode, u.EmpDesignation,
                    COUNT(DISTINCT jl.Jid) as assigned_jobs,
                    SUM(CASE WHEN jl.JobStatus IN ('Open','Re-Open') THEN 1 ELSE 0 END) as active_jobs,
                    SUM(CASE WHEN jl.JobStatus IN ('Closed') THEN 1 ELSE 0 END) as closed_jobs,
                    COUNT(DISTINCT ja.ApplicationId) as managed_candidates
                   FROM IHUsers u
                   JOIN IHRJobsList jl ON jl.AssignedRecruiterManagerId = u.IUid
                   LEFT JOIN JobApplications ja ON ja.Jid = jl.Jid
                   GROUP BY u.IUid
                   HAVING assigned_jobs > 0
                   ORDER BY assigned_jobs DESC";
        return $this->db->query($sqlRec)->result_array();
    }

    public function getAnalyticsInterviewerSummaryExact()
    {
        $sqlIntSummary = "SELECT u.IUid, u.EmpName, u.EmpCode, u.EmpDesignation,
                            COUNT(ci.InterviewId) as total_interviews,
                            SUM(CASE WHEN ci.Result IN ('Passed','Selected','Accepted') THEN 1 ELSE 0 END) as passed_interviews,
                            SUM(CASE WHEN ci.Result IN ('Failed','Rejected') THEN 1 ELSE 0 END) as failed_interviews,
                            SUM(CASE WHEN ci.Result IS NULL OR ci.Result = '' OR ci.Result = 'Scheduled' THEN 1 ELSE 0 END) as pending_interviews
                           FROM IHUsers u
                           LEFT JOIN CandidateInterviews ci ON ci.InterviewerId = u.IUid
                           LEFT JOIN JobInterviewPanels p ON p.InterviewerId = u.IUid
                           WHERE ci.InterviewId IS NOT NULL OR p.PanelId IS NOT NULL
                           GROUP BY u.IUid
                           ORDER BY total_interviews DESC";
        return $this->db->query($sqlIntSummary)->result_array();
    }

    public function getAnalyticsInterviewerDetailsExact()
    {
        $sqlIntDetail = "SELECT ci.InterviewId, ci.InterviewRound, ci.InterviewType, ci.ScheduledAt, ci.CompletedAt, ci.Result, ci.Feedback, ci.MeetLink,
                          u.EmpName as InterviewerName, u.EmpCode as InterviewerCode,
                          c.Fullname as CandidateName, c.Email as CandidateEmail, c.PhoneNo as CandidatePhone, c.ExpYrs,
                          jl.JobTitle, jl.JobCode, d.Departmentname
                         FROM CandidateInterviews ci
                         JOIN IHUsers u ON u.IUid = ci.InterviewerId
                         JOIN JobApplications ja ON ja.ApplicationId = ci.ApplicationId
                         JOIN IHrCandidates c ON c.CandidateId = ja.CandidateId
                         JOIN IHRJobsList jl ON jl.Jid = ja.Jid
                         LEFT JOIN Departments d ON d.Did = jl.Did
                         ORDER BY ci.ScheduledAt DESC";
        return $this->db->query($sqlIntDetail)->result_array();
    }


    public function getJobDetailsRow($jid)
    {
        $this->db->select("
            jl.*,
            d.Departmentname,
            jl.RoleSummary,
            u.EmpName AS PostedByName,
            ctc.EmpName AS CtcApproverName,
            arm.EmpName AS AssignedRecruiterManagerName,
            GROUP_CONCAT(s.SkillName SEPARATOR ',') AS Skills
        ");
        $this->db->from('IHRJobsList jl');
        $this->db->join('Departments d', 'd.Did = jl.Did', 'left');
        $this->db->join('JobSkills js', 'js.Jid = jl.Jid', 'left');
        $this->db->join('IHSkills s', 's.SkillId = js.SkillId', 'left');
        $this->db->where('jl.Jid', $jid);
        $this->db->group_by('jl.Jid');
        $this->db->join('IHUsers u', 'u.IUid = jl.PostedBy', 'left');
        $this->db->join('IHUsers ctc', 'ctc.IUid = jl.CtcApproverId', 'left');
        $this->db->join('IHUsers arm', 'arm.IUid = jl.AssignedRecruiterManagerId', 'left');
        return $this->db->get()->row_array();
    }

    public function getResourceRequestByConvertedJid($jid)
    {
        return $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                        ->from('Resource_Requests rr')
                        ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                        ->where('rr.ConvertedJid', $jid)
                        ->get()
                        ->row_array();
    }

    public function getResourceRequestByJobTitle($jobTitle)
    {
        return $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                        ->from('Resource_Requests rr')
                        ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                        ->where('rr.JobTitle', $jobTitle)
                        ->order_by('rr.RequestId', 'DESC')
                        ->get()
                        ->row_array();
    }

    public function getLatestApplicationByCandidateId($candidateId)
    {
        return $this->db->select('ja.ApplicationId, ja.Jid')
                        ->from('JobApplications ja')
                        ->where('ja.CandidateId', $candidateId)
                        ->order_by('ja.ApplicationId', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
    }

    public function getInterviewPanelsWithInterviewers($jid)
    {
        return $this->db->select('p.LevelOrder, p.InterviewerId, u.EmpName')
                        ->from('JobInterviewPanels p')
                        ->join('IHUsers u', 'u.IUid = p.InterviewerId', 'left')
                        ->where('p.Jid', $jid)
                        ->order_by('p.LevelOrder', 'ASC')
                        ->get()
                        ->result_array();
    }

    public function getResourceRequestByCode($requestCode)
    {
        return $this->db->select('RequestId, ConvertedJid')
                        ->from('Resource_Requests')
                        ->where('RequestCode', $requestCode)
                        ->get()
                        ->row_array();
    }

    public function updateVacancyWithResult($jid, $data)
    {
        $updateResult = $this->db->where('Jid', $jid)->update('IHRJobsList', $data);
        if (!$updateResult) {
            $lastError = $this->db->error();
            return [
                'status'  => false,
                'message' => $lastError['message'] ?? 'Unknown error',
                'sql'     => $this->db->last_query()
            ];
        }
        return ['status' => true];
    }

    public function updateResourceRequestSync($requestId, $jid, $rrSync)
    {
        if (empty($rrSync)) {
            return false;
        }
        if ($requestId > 0) {
            return $this->db->where('RequestId', $requestId)->update('Resource_Requests', $rrSync);
        } else {
            return $this->db->where('ConvertedJid', $jid)->update('Resource_Requests', $rrSync);
        }
    }

    public function ensureVacancyForResourceRequest($requestId, $postedBy = null)
    {
        $req = $this->getResourceRequestById($requestId);
        if (empty($req)) {
            return null;
        }

        // 1. If already linked, verify vacancy exists
        if (!empty($req['ConvertedJid'])) {
            $existingJob = $this->getJobById((int)$req['ConvertedJid']);
            if (!empty($existingJob)) {
                return (int)$existingJob['Jid'];
            }
        }

        // 2. Generate unique JobCode
        $year = date("Y");
        $count = $this->countAllJobs() + 1;
        do {
            $jobCode = "JOB-" . $year . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
            $exists  = $this->countJobsWithCode($jobCode);
            if ($exists) $count++;
        } while ($exists > 0);

        // 3. Build vacancy data from Resource_Requests fields
        $salary = !empty($req["Salary"]) ? $req["Salary"] : '';
        if (empty($salary) && (!empty($req["ExpectedSalaryMin"]) || !empty($req["ExpectedSalaryMax"]))) {
            $minSal = !empty($req["ExpectedSalaryMin"]) ? (float)$req["ExpectedSalaryMin"] : 0;
            $maxSal = !empty($req["ExpectedSalaryMax"]) ? (float)$req["ExpectedSalaryMax"] : 0;
            if ($minSal > 0 && $maxSal > 0) {
                $salary = $minSal . " - " . $maxSal . " LPA";
            } elseif ($minSal > 0) {
                $salary = "Min " . $minSal . " LPA";
            } elseif ($maxSal > 0) {
                $salary = "Up to " . $maxSal . " LPA";
            }
        }

        $creatorId = (!empty($postedBy) && $this->getUserByIdRowArray((int)$postedBy)) 
            ? (int)$postedBy 
            : (!empty($req["RequestedBy"]) ? (int)$req["RequestedBy"] : null);
        if (empty($creatorId)) {
            $fallbackUser = $this->db->select('IUid')->from('IHUsers')->where('UStatus', 1)->limit(1)->get()->row_array();
            $creatorId = !empty($fallbackUser) ? (int)$fallbackUser['IUid'] : 17;
        }

        $vacancyData = [
            "JobCode"              => $jobCode,
            "JobTitle"             => isset($req["JobTitle"]) ? $req["JobTitle"] : '',
            "RoleSummary"          => !empty($req["FunctionalRole"]) ? $req["FunctionalRole"] : (isset($req["JobTitle"]) ? $req["JobTitle"] : ''),
            "Did"                  => !empty($req["Did"]) ? $req["Did"] : null,
            "EmploymentType"       => "Full-Time",
            "WorkMode"             => "Onsite",
            "EducationRequired"    => !empty($req["EducationRequired"]) ? $req["EducationRequired"] : "Bachelor Degree",
            "ExpMin"               => isset($req["ExpMin"]) ? $req["ExpMin"] : 0,
            "ExpMax"               => isset($req["ExpMax"]) ? $req["ExpMax"] : 0,
            "Salary"               => $salary,
            "NoofOpenings"         => !empty($req["NoofOpenings"]) ? (int)$req["NoofOpenings"] : 1,
            "JobStatus"            => "Open",
            "JobDescription"       => isset($req["JobDescription"]) ? $req["JobDescription"] : '',
            "Responsibilities"     => isset($req["Responsibilities"]) ? $req["Responsibilities"] : '',
            "MustHaveSkills"       => isset($req["MustHaveSkills"]) ? $req["MustHaveSkills"] : '',
            "NiceToHaveSkills"     => isset($req["NiceToHaveSkills"]) ? $req["NiceToHaveSkills"] : '',
            "JobLocation"          => isset($req["JobLocation"]) ? $req["JobLocation"] : '',
            "CommunicationLang"    => isset($req["CommunicationLang"]) ? $req["CommunicationLang"] : '',
            "TargetOnboardingDate" => !empty($req["TargetOnboardingDate"]) ? $req["TargetOnboardingDate"] : null,
            "PostedBy"             => $creatorId,
            "AssignedRecruiterManagerId" => !empty($req["AssignedRecruiterManagerId"]) ? (int)$req["AssignedRecruiterManagerId"] : null,
            "PostedOn"             => date("Y-m-d H:i:s")
        ];

        $jid = $this->insertVacancy($vacancyData);

        if ($jid) {
            // Sync skills if present
            $skillNames = [];
            if (!empty($req["MustHaveSkills"])) {
                $skillNames = array_merge($skillNames, array_map('trim', explode(',', $req["MustHaveSkills"])));
            }
            if (!empty($req["NiceToHaveSkills"])) {
                $skillNames = array_merge($skillNames, array_map('trim', explode(',', $req["NiceToHaveSkills"])));
            }
            $skillNames = array_filter(array_unique($skillNames));
            if (!empty($skillNames)) {
                $this->syncJobSkillsList($jid, $skillNames);
            }

            // Update Resource_Requests with ConvertedJid
            $this->updateResourceRequest((int)$requestId, ["ConvertedJid" => $jid]);
            return (int)$jid;
        }

        return null;
    }

    public function syncJobSkillsList($jid, $skills)
    {
        $this->db->where('Jid', $jid)->delete('JobSkills');
        if (!empty($skills) && is_array($skills)) {
            foreach ($skills as $skillName) {
                $skillName = trim($skillName);
                if ($skillName === '') continue;

                $row = $this->db->where('SkillName', $skillName)->get('IHSkills')->row();
                if (!$row) {
                    $this->db->insert('IHSkills', ['SkillName' => $skillName]);
                    $skillId = $this->db->insert_id();
                } else {
                    $skillId = $row->SkillId;
                }

                $this->db->insert('JobSkills', [
                    'Jid'     => $jid,
                    'SkillId' => $skillId
                ]);
            }
        }
    }


    public function getFilteredCandidatesStatus($jid, $status)
    {
        $this->db->select('
            c.CandidateId, c.CandidateCode, c.Fullname, c.PhoneNo, c.Email,
            c.ProfileMatchPer, c.ResumePath, c.ScoreBreakdown, c.MatchedSkills, c.ExperienceMatch,
            ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, ja.Jid,
            rs.StageOrder as CurrentStageOrder,
            (SELECT Action FROM CandidateStageTracking
             WHERE ApplicationId = ja.ApplicationId
             ORDER BY ActionAt DESC LIMIT 1) as LastAction
        ');
        $this->db->from('IHrCandidates c');
        $this->db->join('JobApplications ja', 'ja.CandidateId = c.CandidateId');
        $this->db->join('RecruitmentStages rs', 'rs.StageId = ja.CurrentStage', 'left');
        $this->db->where('ja.Jid', $jid);

        if (!empty($status)) {
            if (strcasecmp($status, 'CV Uploaded') === 0) {
                $this->db->group_start();
                $this->db->where('ja.CurrentStatus', 'CV Uploaded');
                $this->db->or_like('ja.CurrentStatus', 'Upload');
                $this->db->or_like('ja.CurrentStatus', 'Applied');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Selected') === 0) {
                $this->db->group_start();
                $this->db->like('ja.CurrentStatus', 'Selected');
                $this->db->or_like('ja.CurrentStatus', 'Offer');
                $this->db->or_like('ja.CurrentStatus', 'Hired');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Rejected') === 0) {
                $this->db->like('ja.CurrentStatus', 'Rejected');
            } elseif (strcasecmp($status, 'On Hold') === 0) {
                $this->db->like('ja.CurrentStatus', 'Hold');
            } elseif (strcasecmp($status, 'In Progress') === 0) {
                $this->db->group_start();
                $this->db->where('ja.CurrentStatus', 'In Progress');
                $this->db->or_like('ja.CurrentStatus', 'Progress');
                $this->db->or_like('ja.CurrentStatus', 'Scheduled');
                $this->db->or_like('ja.CurrentStatus', 'Shortlisted');
                $this->db->or_like('ja.CurrentStatus', 'Interview');
                $this->db->or_where('ja.CurrentStatus NOT IN ("CV Uploaded", "Selected", "Rejected", "On Hold") AND ja.CurrentStatus NOT LIKE "%Reject%" AND ja.CurrentStatus NOT LIKE "%Selected%"', NULL, FALSE);
                $this->db->group_end();
            } else {
                $this->db->like('ja.CurrentStatus', $status);
            }
        }

        return $this->db->get()->result_array();
    }


    public function getJobHistoryDetailsJob($jid)
    {
        return $this->db
            ->select('
                jl.*,
                d.Departmentname,
                u_posted.EmpName AS PostedByName,
                u_posted.EmpEmail AS PostedByEmail,
                u_arm.EmpName AS AssignedManagerName,
                u_arm.EmpEmail AS AssignedManagerEmail,
                u_ctc.EmpName AS CtcApproverName,
                u_ctc.EmpEmail AS CtcApproverEmail
            ')
            ->from('IHRJobsList jl')
            ->join('Departments d', 'd.Did = jl.Did', 'left')
            ->join('IHUsers u_posted', 'u_posted.IUid = jl.PostedBy', 'left')
            ->join('IHUsers u_arm', 'u_arm.IUid = jl.AssignedRecruiterManagerId', 'left')
            ->join('IHUsers u_ctc', 'u_ctc.IUid = jl.CtcApproverId', 'left')
            ->where('jl.Jid', $jid)
            ->get()
            ->row_array();
    }

    public function getJobHistoryResourceRequest($jid, $jobTitle = null)
    {
        $rr = $this->db
            ->select('
                rr.*,
                d.Departmentname,
                u_req.EmpName AS RequestedByName,
                u_req.EmpEmail AS RequestedByEmail,
                u_arm.EmpName AS AssignedManagerName,
                u_ctc.EmpName AS CtcApproverName
            ')
            ->from('Resource_Requests rr')
            ->join('Departments d', 'd.Did = rr.Did', 'left')
            ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
            ->join('IHUsers u_arm', 'u_arm.IUid = rr.AssignedRecruiterManagerId', 'left')
            ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
            ->where('rr.ConvertedJid', $jid)
            ->get()
            ->row_array();

        if (empty($rr) && !empty($jobTitle)) {
            $rr = $this->db
                ->select('
                    rr.*,
                    d.Departmentname,
                    u_req.EmpName AS RequestedByName,
                    u_ctc.EmpName AS CtcApproverName
                ')
                ->from('Resource_Requests rr')
                ->join('Departments d', 'd.Did = rr.Did', 'left')
                ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
                ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
                ->where('rr.JobTitle', $jobTitle)
                ->order_by('rr.RequestId', 'DESC')
                ->get()
                ->row_array();
        }

        return $rr;
    }

    public function getJobHistoryApplications($jid)
    {
        return $this->db
            ->select('ja.ApplicationId, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.CandidateCode')
            ->from('JobApplications ja')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
            ->where('ja.Jid', $jid)
            ->get()
            ->result_array();
    }

    public function getJobTrackingRows($jid)
    {
        return $this->db
            ->select('jt.*, u.EmpName AS ActionByName')
            ->from('JobTracking jt')
            ->join('IHUsers u', 'u.IUid = jt.ActionBy', 'left')
            ->where('jt.Jid', $jid)
            ->order_by('jt.ActionAt', 'ASC')
            ->get()
            ->result_array();
    }

    public function getJobFilledCandidates($appIds)
    {
        if (empty($appIds)) return [];
        return $this->db
            ->select('ja.*, c.Fullname AS CandidateName, c.CandidateCode, cst.ActionAt AS FilledAt, u.EmpName AS FilledByName')
            ->from('JobApplications ja')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
            ->join('CandidateStageTracking cst', 'cst.ApplicationId = ja.ApplicationId AND (LOWER(cst.Action) LIKE "%selected%" OR LOWER(cst.Action) LIKE "%offer%")', 'left')
            ->join('IHUsers u', 'u.IUid = cst.ActionBy', 'left')
            ->where_in('ja.ApplicationId', $appIds)
            ->group_by('ja.ApplicationId')
            ->get()
            ->result_array();
    }


    public function insertCandidateFollowUp($data)
    {
        return $this->db->insert('CandidateFollowUps', $data);
    }

    public function rescheduleAssignedInterviews($applicationId)
    {
        return $this->db->where('ApplicationId', $applicationId)
                        ->group_start()
                            ->where('Result', 'Assigned')
                            ->or_where('Result IS NULL', null, false)
                            ->or_where('Result', '')
                        ->group_end()
                        ->update('CandidateInterviews', ['Result' => 'Rescheduled']);
    }

    public function getCandidateWithJob($candidateId)
    {
        return $this->db->select('c.Fullname, c.CandidateCode, c.Email, j.JobCode, j.JobTitle')
                        ->from('IHrCandidates c')
                        ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
                        ->where('c.CandidateId', $candidateId)
                        ->get()
                        ->row();
    }

    public function getCandidateJobInfo($candidateId)
    {
        return $this->db->select('j.JobTitle, j.JobCode')
                        ->from('IHrCandidates c')
                        ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
                        ->where('c.CandidateId', $candidateId)
                        ->get()
                        ->row();
    }

    public function getMyInterviewsList($uid)
    {
        return $this->db
            ->select('
                c.CandidateId,
                c.CandidateCode,
                c.Fullname,
                c.Email,
                c.PhoneNo,
                c.ProfileMatchPer,
                ja.CurrentStage,
                ja.CurrentStatus,
                ja.AppliedOn,
                rs.StageOrder as CurrentStageOrder,
                j.Jid,
                j.JobCode,
                j.JobTitle,
                j.RoleSummary as Role,
                ci.InterviewId,
                ci.InterviewType,
                ci.MeetLink,
                ci.Result,
                ci.ScheduledAt,
                (
                   SELECT Action
                   FROM CandidateStageTracking
                   WHERE ApplicationId = ja.ApplicationId
                   ORDER BY ActionAt DESC
                   LIMIT 1
                ) as LastAction
            ')
            ->from('CandidateInterviews ci')
            ->join('JobApplications ja','ja.ApplicationId = ci.ApplicationId')
            ->join('IHrCandidates c','c.CandidateId = ja.CandidateId')
            ->join('IHRJobsList j','j.Jid = ja.Jid')
            ->join('RecruitmentStages rs','rs.StageId = ja.CurrentStage','left')
            ->where('ci.InterviewerId',$uid)
            ->where('ci.ScheduledAt IS NOT NULL', null, false)
            ->group_by('ci.InterviewId')
            ->order_by('ci.ScheduledAt', 'ASC')
            ->get()
            ->result_array();
    }

    public function getInterviewCalendarDataExact($uid)
    {
        return $this->db
            ->select('
                c.CandidateId,
                c.CandidateCode,
                c.Fullname,
                c.Email,
                c.PhoneNo,
                c.ProfileMatchPer,
                ja.CurrentStage,
                ja.CurrentStatus,
                ja.AppliedOn,
                j.Jid,
                j.JobTitle,
                ci.InterviewId,
                ci.Result,
                ci.ScheduledAt,
                (SELECT COUNT(*) FROM CandidateInterviews ci2 WHERE ci2.ApplicationId = ci.ApplicationId AND ci2.InterviewId <= ci.InterviewId) AS InterviewRound
            ')
            ->from('CandidateInterviews ci')
            ->join('JobApplications ja','ja.ApplicationId = ci.ApplicationId')
            ->join('IHrCandidates c','c.CandidateId = ja.CandidateId')
            ->join('IHRJobsList j','j.Jid = ja.Jid')
            ->where('ci.InterviewerId', $uid)
            ->where('ci.ScheduledAt IS NOT NULL', null, false)
            ->order_by('ci.ScheduledAt', 'ASC')
            ->get()
            ->result_array();
    }

    public function getAssignedInterviewsFiltered($userId, $status = null)
    {
        $this->db->select('
            ci.InterviewId,
            ci.InterviewType,
            ci.MeetLink,
            ci.Result,
            ci.ScheduledAt,
            ja.AppliedOn,
            c.CandidateId,
            c.Fullname,
            c.PhoneNo,
            c.Email,
            c.ProfileMatchPer,
            c.CandidateCode,
            j.JobTitle,
            j.RoleSummary as Role,
            j.JobCode
        ');
        $this->db->from('CandidateInterviews ci');
        $this->db->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId');
        $this->db->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId');
        $this->db->join('IHRJobsList j', 'j.Jid = ja.Jid', 'left');

        $this->db->where('ci.InterviewerId', $userId);
        $this->db->where('ci.ScheduledAt IS NOT NULL', null, false);

        if (!empty($status)) {
            if (strcasecmp($status, 'Assigned') === 0) {
                $this->db->group_start();
                $this->db->where('ci.Result', 'Assigned');
                $this->db->or_where('ci.Result IS NULL', null, false);
                $this->db->or_where('ci.Result', '');
                $this->db->or_like('ci.Result', 'Pending');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Selected') === 0) {
                $this->db->group_start();
                $this->db->like('ci.Result', 'Selected');
                $this->db->or_like('ci.Result', 'Passed');
                $this->db->or_like('ci.Result', 'Shortlist');
                $this->db->group_end();
            } elseif (strcasecmp($status, 'Rejected') === 0) {
                $this->db->group_start();
                $this->db->like('ci.Result', 'Rejected');
                $this->db->or_like('ci.Result', 'Failed');
                $this->db->group_end();
            } else {
                $this->db->like('ci.Result', $status);
            }
        }

        $this->db->group_by('ci.InterviewId');
        $this->db->order_by('ci.ScheduledAt', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getStageByGroup($group)
    {
        return $this->db->where('StageGroup', $group)->get('RecruitmentStages')->row();
    }

    public function getAiInterviewDetails($interviewId)
    {
        return $this->db
            ->select('c.Fullname as CandidateName, c.ProfileMatchPer, j.JobTitle, j.JobCode, j.MustHaveSkills')
            ->from('CandidateInterviews ci')
            ->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId', 'left')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
            ->join('IHRJobsList j', 'j.Jid = ja.Jid', 'left')
            ->where('ci.InterviewId', $interviewId)
            ->get()
            ->row_array();
    }

    public function getAiQuestionVersions($interviewId)
    {
        $versionsRes = $this->db
            ->distinct()
            ->select('generation_version')
            ->where('interview_id', $interviewId)
            ->order_by('generation_version', 'DESC')
            ->get('AI_Interview_Questions')
            ->result_array();
        return !empty($versionsRes) ? array_column($versionsRes, 'generation_version') : [];
    }

    public function getAiQuestionLatestReason($interviewId)
    {
        return $this->db->select('reason')
            ->where('interview_id', $interviewId)
            ->where('is_active', 1)
            ->limit(1)
            ->get('AI_Interview_Questions')
            ->row_array();
    }

    public function getUserByIdRowArray($userId)
    {
        return $this->db->select('u.IUid, u.EmpName, u.EmpEmail')
            ->from('IHUsers u')
            ->where('u.IUid', $userId)
            ->get()
            ->row_array();
    }

    public function getResourceRequestForApproverEmail($requestId)
    {
        return $this->db
            ->select('rr.*, d.Departmentname,
                      req.EmpName AS RequesterName, req.EmpEmail AS RequesterEmail,
                      app.EmpName AS ApproverName, app.EmpEmail AS ApproverEmail, app.EmpGender AS ApproverGender')
            ->from('Resource_Requests rr')
            ->join('Departments d', 'd.Did = rr.Did', 'left')
            ->join('IHUsers req', 'req.IUid = rr.RequestedBy', 'left')
            ->join('IHUsers app', 'app.IUid = rr.ApproverId', 'left')
            ->where('rr.RequestId', (int)$requestId)
            ->get()
            ->row_array();
    }

    public function getRecruiterAndManagerUsersForEmail()
    {
        return $this->db
            ->select('u.EmpName, u.EmpEmail, r.RoleName')
            ->from('IHUsers u')
            ->join('EmpRoles r', 'u.Erid = r.Erid', 'left')
            ->group_start()
                ->where_in('u.Erid', [9, 10])
                ->or_where_in('LOWER(r.RoleName)', ['recruitment manager', 'hiring manager', 'recruiter', 'recruitment manager / recruiter', 'hr manager'])
                ->or_like('LOWER(r.RoleName)', 'recruiter')
                ->or_like('LOWER(r.RoleName)', 'hiring manager')
                ->or_like('LOWER(r.RoleName)', 'recruitment')
            ->group_end()
            ->get()
            ->result_array();
    }

    public function getUserNameAndEmail($userId)
    {
        return $this->db->select('EmpName, EmpEmail')
            ->where('IUid', (int)$userId)
            ->get('IHUsers')
            ->row();
    }


    public function getDbError()
    {
        return $this->db->error();
    }

    /* =========================================================================
     * SECTION: TWO-STEP OTP AUTHENTICATION
     * Table: Ih_User_Otps
     * ========================================================================= */

    public function createOtpRecord($userId, $otpHash, $expiresAt, $ipAddress = null, $userAgent = null)
    {
        $data = array(
            'user_id'       => (int)$userId,
            'otp_hash'      => $otpHash,
            'created_at'    => date('Y-m-d H:i:s'),
            'expires_at'    => $expiresAt,
            'verified_at'   => null,
            'status'        => 0,
            'attempt_count' => 0,
            'ip_address'    => $ipAddress,
            'user_agent'    => $userAgent
        );

        $this->db->insert('Ih_User_Otps', $data);
        return $this->db->insert_id();
    }

    public function getLatestActiveOtp($userId)
    {
        return $this->db->where('user_id', (int)$userId)
                        ->where('status', 0)
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get('Ih_User_Otps')
                        ->row();
    }

    public function invalidatePendingOtps($userId)
    {
        return $this->db->where('user_id', (int)$userId)
                        ->where('status', 0)
                        ->update('Ih_User_Otps', array('status' => 2));
    }

    public function incrementOtpAttempts($otpId)
    {
        $this->db->set('attempt_count', 'attempt_count + 1', FALSE);
        $this->db->where('id', (int)$otpId);
        return $this->db->update('Ih_User_Otps');
    }

    public function markOtpVerified($otpId)
    {
        return $this->db->where('id', (int)$otpId)
                        ->update('Ih_User_Otps', array(
                            'status'      => 1,
                            'verified_at' => date('Y-m-d H:i:s')
                        ));
    }

    public function markOtpExpired($otpId)
    {
        return $this->db->where('id', (int)$otpId)
                        ->update('Ih_User_Otps', array(
                            'status' => 2
                        ));
    }

    public function getRecentOtpCount($userId, $seconds = 60)
    {
        $since = date('Y-m-d H:i:s', time() - (int)$seconds);
        return $this->db->where('user_id', (int)$userId)
                        ->where('created_at >=', $since)
                        ->count_all_results('Ih_User_Otps');
    }
}
