<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model
{
  

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

    $this->db->from('IHRJobsList jl');
    $this->db->join('resource_requests rr', 'rr.ConvertedJid = jl.Jid', 'left');

    // Only include vacancies that are standalone or converted from an ASSIGNED resource request
    $this->db->where('(rr.RequestId IS NULL OR rr.Status = "ASSIGNED")', null, false);
    $this->db->where('(jl.AssignedRecruiterManagerId IS NOT NULL OR rr.RequestId IS NULL)', null, false);

    $check_session = $this->session->userdata('logged_in');
    if (!empty($check_session) && isset($check_session['EmpRoleId'])) {
        $roleId = (int)$check_session['EmpRoleId'];
        $currentUserId = (int)$check_session['IUid'];

        if ($roleId === 10 || $roleId === 11) { 
            $this->db->group_start();
            $this->db->where('jl.AssignedRecruiterManagerId', $currentUserId);
            $this->db->or_group_start();
            $this->db->where('jl.AssignedRecruiterManagerId IS NULL', null, false);
            $this->db->where('jl.PostedBy', $currentUserId);
            $this->db->group_end();
            $this->db->group_end();
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
    $this->db->join('Departments', 'Departments.Did = jl.Did', 'left');
    $this->db->join('IHUsers', 'IHUsers.IUid = jl.PostedBy', 'left');
    $this->db->join('JobSkills', 'JobSkills.Jid = jl.Jid', 'left');
    $this->db->join('IHSkills', 'IHSkills.SkillId = JobSkills.SkillId', 'left');

    $this->db->group_by('jl.Jid');

    $query = $this->db->get();
    $result = $query->result_array();
      
        
        return $result; 

}
public function getCandidatesList($Jid){

      $this->db->select("
        c.CandidateId, c.CandidateCode, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ResumePath, c.ATS_Status, c.ATS_Stage, c.ProfileMatchPer, c.MatchedSkills, c.EducationMatch, c.ExperienceMatch, c.ScoreBreakdown, ja.ApplicationId, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, ja.UpdatedAt AS ApplicationUpdatedAt, j.JobCode, j.JobTitle, j.EducationRequired, j.ExpMin, j.ExpMax, j.JobLocation,
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

    $this->db->group_by(['c.CandidateId', 'ja.ApplicationId']);
    $this->db->order_by('cst.ActionAt', 'DESC');

    $query = $this->db->get()->result_array();
    
        return $query; 
}




    public function getAllUsers()
    {
        $this->db->select('u.IUid, u.EmpName, u.EmpEmail, u.EmpDesignation, r.RoleName');
        $this->db->from('IHUsers u');
        $this->db->join('emproles r', 'u.Erid = r.Erid', 'left');
        $this->db->where('u.UStatus', 1);
        $this->db->order_by('u.EmpName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getApproverUsers()
    {
        $role = $this->db->select('Erid')->from('emproles')->where('LOWER(RoleName)', 'approver')->get()->row_array();
        
        $this->db->select('u.IUid, u.EmpName, u.EmpEmail, u.EmpDesignation, r.RoleName');
        $this->db->from('IHUsers u');
        $this->db->join('emproles r', 'u.Erid = r.Erid', 'left');
        $this->db->where('u.UStatus', 1);
        
        if (!empty($role)) {
            $this->db->group_start();
            $this->db->where('u.Erid', $role['Erid']);
            $this->db->or_where('LOWER(r.RoleName)', 'management');
            $this->db->group_end();
        }
        
        return $this->db->get()->result_array();
    }

    public function getResourceRequests($filters = [])
    {
        $this->db->select('rr.*, d.Departmentname, req.EmpName AS RequestedByName, req.EmpEmail AS RequestedByEmail, app.EmpName AS ApproverName, app.EmpEmail AS ApproverEmail, ctc.EmpName AS CtcApproverName, ctc.EmpEmail AS CtcApproverEmail');
        $this->db->from('resource_requests rr');
        $this->db->join('departments d', 'rr.Did = d.Did', 'left');
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
        return $this->db->get()->result_array();
    }

    public function getResourceRequestById($id)
    {
        $res = $this->getResourceRequests(['RequestId' => $id]);
        return !empty($res) ? $res[0] : null;
    }

   
public function insertResourceRequest($data)
{
    $result = $this->db->insert('resource_requests', $data);

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
        return $this->db->update('resource_requests', $data);
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
        $this->db->from("resource_requests rr");
        $this->db->join("ihrjobslist j", "j.Jid = rr.ConvertedJid", "left");
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
            ->join("emproles r", "u.Erid = r.Erid", "left")
            ->where_in("LOWER(r.RoleName)", ["recruitment manager", "recruiter"])
            ->order_by("u.EmpName", "ASC")
            ->get()->result_array();
    }

} //Main Class