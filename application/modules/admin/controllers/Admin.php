<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MX_Controller
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
		// $this->load->library('pdf');
		$check_session = $this->session->userdata('logged_in');
		$this->load->helper('cookie');
		$this->load->helper('string');
		$this->load->library('encrypt');
		$this->load->library('user_agent');
		date_default_timezone_set("Asia/Kolkata");
	 	$this->load->model('admin/admin_model');



		$roleId = (!empty($check_session) && isset($check_session['EmpRoleId'])) ? $check_session['EmpRoleId'] : null;

		if ($this->db->table_exists('IHMenus')) {
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

		$menus = !empty($roleId) ? $this->admin_model->getMenusByRole($roleId) : [];
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


	public function index()
	{
 		
		$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
		$this->template->write_view('content', 'admin/index');
		$this->template->render();
	}

	public function ForgotPassword()
	{
		$inps = $this->input->post();
		if (!empty($inps)) {
			$email = trim($inps['EmailInput']);
			
			
			$user = $this->db->select('IUid, EmpName, EmpEmail')
							 ->where('EmpEmail', $email)
							 ->where('UStatus', 1)
							 ->get('IHUsers')
							 ->row();

			if (empty($user)) {
				$this->session->set_flashdata('error', 'Email address not found.');
				redirect($this->config->item('base_url').'admin/ForgotPassword');
			} else {
				
				$token = bin2hex(random_bytes(32));

				
				$this->db->where('IUid', $user->IUid)
						 ->update('IHUsers', [
							 'ResetToken'          => $token,
							 'ResetTokenCreatedAt' => date('Y-m-d H:i:s')
						 ]);

				
				$objs = new InetMailer();
				$mail = $objs->load();
				$mail->setFrom('inet@inetcsc.com', 'I-NET Secure Labs');
				$mail->addAddress(trim($email));
				
// 							  "We received a request to reset the password for your account.<br><br>" .
// 							  "To create a new password, please click the link below:<br><br>" .
// 							  "<a href=\"" . $resetLink . "\">" . $resetLink . "</a><br><br>" .
// 							  "For security reasons, this password reset link is valid for a limited time.<br><br>";
// 							  "If you did not raise this password reset request, please contact the Support Team immediately to secure your account.<br><br> 
					$mail->isHTML(true);
					$mail->Subject = "Password Reset Request - Recruitment";
					$resetLink = $this->config->item('base_url').'admin/ResetPassword/' . $token;
					$mail->Body = '
					<!DOCTYPE html>
					<html>
					<head>
					<meta charset="UTF-8">
					<style>
					    body{
					        margin:0;
					        padding:0;
					        background:#f4f6f9;
					        font-family:Arial, Helvetica, sans-serif;
					    }
					    .container{
					        max-width:600px;
					        margin:30px auto;
					        background:#ffffff;
					        border-radius:8px;
					        overflow:hidden;
					        box-shadow:0 2px 8px rgba(0,0,0,.08);
					    }
					    .header{
					        background:#0d6efd;
					        color:#ffffff;
					        padding:20px;
					        text-align:center;
					        font-size:24px;
					        font-weight:bold;
					    }
					    .content{
					        padding:30px;
					        color:#333333;
					        font-size:15px;
					        line-height:1.7;
					    }
					    .button{
					        display:inline-block;
					        background:#0d6efd;
					        color:#ffffff !important;
					        text-decoration:none;
					        padding:12px 28px;
					        border-radius:5px;
					        font-weight:bold;
					        margin:20px 0;
					    }
					    .note{
					        background:#fff8e5;
					        border-left:4px solid #ffc107;
					        padding:15px;
					        margin-top:20px;
					        color:#555;
					    }
					    .footer{
					        background:#f8f9fa;
					        padding:15px;
					        text-align:center;
					        font-size:13px;
					        color:#777;
					    }
					</style>
					</head>

					<body>

					<div class="container">

					    <div class="header">
					        Password Reset Request
					    </div>

					    <div class="content">

					        <p>Hello <strong>'.htmlspecialchars($user->EmpName).'</strong>,</p>

					        <p>
					            We received a request to reset the password for your account.
					        </p>

					        <p>
					            Click the button below to create a new password:
					        </p>

					        <p style="text-align:center;">
					            <a href="' . $resetLink . '" class="button" target="_blank">
					                Reset Password
					            </a>
					        </p>

					        <p>
					            If the button above does not work, copy and paste the following link into your browser:
					        </p>

					        <p>
					            <a href="'.$resetLink.'">'.$resetLink.'</a>
					        </p>

					        <div class="note">
					            <strong>Security Notice</strong><br><br>
					            This password reset link is valid for a limited time.<br><br>

					            If you did not raise this password reset request, please contact the Support Team immediately to secure your account.<br><br>

					            If you require any assistance, please reach out to the Support Team.
					        </div>

					        <p>
					            Thank you,<br>
					            <strong>REC Support Team</strong>
					        </p>

					    </div>

					    <div class="footer">
					        � '.date('Y').' REC. All Rights Reserved.
					    </div>

					</div>

					</body>
					</html>';

				if ($mail->send()) {
					$this->session->set_flashdata('true', 'A password reset link has been sent to your email address.');
				} else {
					$this->session->set_flashdata('error', 'Failed to send reset link email: ' . $mail->ErrorInfo);
				}
				redirect($this->config->item('base_url').'admin/ForgotPassword');
			}
		} else {
			$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
			$this->template->write_view('content', 'admin/ForgotPassword');
			$this->template->render();
		}
	}

	public function ResetPassword($token)
	{
		
		$user = $this->db->select('IUid, ResetTokenCreatedAt')
						 ->where('ResetToken', $token)
						 ->get('IHUsers')
						 ->row();

		if (empty($user)) {
			$this->session->set_flashdata('error', 'Invalid or expired reset token.');
			redirect($this->config->item('base_url')."admin/index");
		}

		
		$expiry = strtotime($user->ResetTokenCreatedAt . ' +1 hour');
		if (time() > $expiry) {
			
			$this->db->where('IUid', $user->IUid)
					 ->update('IHUsers', [
						 'ResetToken'          => NULL,
						 'ResetTokenCreatedAt' => NULL
					 ]);
			$this->session->set_flashdata('error', 'Invalid or expired reset token.');
			redirect($this->config->item('base_url')."admin/index");
		}

		$inps = $this->input->post();
		if (!empty($inps)) {
			$newPassword = $inps['NewPassword'];
			$confirmPassword = $inps['ConfirmPassword'];

			
			if (empty($newPassword) || empty($confirmPassword)) {
				$this->session->set_flashdata('error', 'All fields are required.');
				redirect($this->config->item('base_url').'admin/ResetPassword/' . $token);
			}

			if (strlen($newPassword) < 6) {
				$this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
				redirect($this->config->item('base_url').'admin/ResetPassword/' . $token);
			}

			if ($newPassword !== $confirmPassword) {
				$this->session->set_flashdata('error', 'Passwords do not match.');
				redirect($this->config->item('base_url').'admin/ResetPassword/' . $token);
			}

			
			$this->db->where('IUid', $user->IUid)
					 ->update('IHUsers', [
						 'EmpPass'             => md5($newPassword),
						 'ResetToken'          => NULL,
						 'ResetTokenCreatedAt' => NULL,
						 'LastPasswordResetAt' => date('Y-m-d H:i:s'),
						 'LastResetTokenUsedAt'=> date('Y-m-d H:i:s')
					 ]);

			$this->session->set_flashdata('true', 'Password updated successfully. Please login with your new password.');
			redirect($this->config->item('base_url').'admin/index');
		} else {
			$data['token'] = $token;
			$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
			$this->template->write_view('content', 'admin/ResetPassword', $data);
			$this->template->render();
		}
	}

	public function CheckLoginData(){

		   $inps = $this->input->post();
		   $Username = isset($inps['EmailInput']) ? trim($inps['EmailInput']) : '';
		   $LogPassword = isset($inps['PassInput']) ? trim($inps['PassInput']) : '';
		 
		  if($Username!="")
			{ 

			$this->db->select('ihu.*');
			$this->db->where('ihu.EmpEmail',$Username);
			$this->db->where('ihu.EmpPass',md5($LogPassword));
			$this->db->where('ihu.UStatus',1); 

			$IUidquery = $this->db->get('IHUsers as ihu')->result_array();
			
				
				if(!empty($IUidquery)){
 

					$sess_array = array('IUid'=>$IUidquery[0]['IUid'],'EmpCode'=>$IUidquery[0]['EmpCode'],'EmpName'=>$IUidquery[0]['EmpName'],'EmpEmail'=>$IUidquery[0]['EmpEmail'],'EmpPhone'=>$IUidquery[0]['EmpPhone'],'EmpDOB'=>$IUidquery[0]['EmpDOB'],'EmpGender'=>$IUidquery[0]['EmpGender'],'EmpRoleId'=>$IUidquery[0]['Erid'],'DepDid'=>$IUidquery[0]['Did'],'UStatus'=>$IUidquery[0]['UStatus']);
                    $this->session->set_userdata('logged_in',$sess_array);
                    $IHRMS_Data = $this->session->userdata('logged_in');
                    $data['Logdescription'] = "User ".$IHRMS_Data['IUid']." Logged in Successfully on ".date("Y M d H i s");
                    $ip_address = $this->input->ip_address();
                    $data['IUid'] = $IHRMS_Data['IUid'];
                    $data['EmpRole'] = $IHRMS_Data['EmpRoleId'];
                    $data['Ipaddress'] = $ip_address;
                    $curr_time=time();
                    $login_time = date("Y-m-d H:i:s",$curr_time);
                    $data['LogInTime'] = $login_time;
                    $data['LogOutTime'] ='';
                     $data['Status'] ='1';
                     $this->db->insert('IHrmsLogin_Log',$data);
                   
                     redirect($this->config->item('base_url').'admin/dashboard');
 
                 } else {

                 	$this->session->set_flashdata('error', 'Invalid Credentials or User Inactive.!');
	   			    redirect($this->config->item('base_url').'admin/index');


                 }
				
			}
		  	$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
			$this->template->write_view('content', 'admin/index');
			$this->template->render();

	}

 
    private function _getAccessibleJobIds($Hrms_Session)
    {
        if (empty($Hrms_Session)) {
            return [0];
        }

        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        $uid    = (int)$Hrms_Session['IUid'];
        
        // Ensure Did is fetched if not present in session array
        $did = 0;
        if (isset($Hrms_Session['Did']) && !empty($Hrms_Session['Did'])) {
            $did = (int)$Hrms_Session['Did'];
        } else {
            $uRow = $this->db->select("Did")->from("IHUsers")->where("IUid", $uid)->get()->row_array();
            if (!empty($uRow['Did'])) {
                $did = (int)$uRow['Did'];
            }
        }

        // Admin / Super Admin / Management (Role 1 or Role 2): Return null for unrestricted full system access
        if ($roleId === 1 || $roleId === 2) {
            return null;
        }

        // Check if user has interviews assigned in candidateinterviews table
        $interviewJobs = [];
        $intRows = $this->db->distinct()
                            ->select("ja.Jid")
                            ->from("candidateinterviews ci")
                            ->join("JobApplications ja", "ja.ApplicationId = ci.ApplicationId", "inner")
                            ->where("ci.InterviewerId", $uid)
                            ->get()
                            ->result_array();
        if (!empty($intRows)) {
            $interviewJobs = array_map('intval', array_column($intRows, 'Jid'));
        }

        $this->db->select("Jid")->from("IHRJobsList");

        if ($roleId === 11) {
            // Recruiter: Assigned to them OR posted by them OR assigned interviewer
            $this->db->group_start();
            $this->db->where('AssignedRecruiterManagerId', $uid);
            $this->db->or_where('PostedBy', $uid);
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('Jid', $interviewJobs);
            }
            $this->db->group_end();
        } elseif ($roleId === 10) {
            // Recruiter Manager: Assigned to them OR posted by them OR assigned interviewer
            $this->db->group_start();
            $this->db->where('AssignedRecruiterManagerId', $uid);
            $this->db->or_where('PostedBy', $uid);
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('Jid', $interviewJobs);
            }
            $this->db->group_end();
        } elseif ($roleId === 9) {
            // Hiring Manager: Posted by them, assigned CTC approver, in their department, OR assigned interviewer
            $this->db->group_start();
            $this->db->where('PostedBy', $uid);
            $this->db->or_where('CtcApproverId', $uid);
            if ($did > 0) {
                $this->db->or_where('Did', $did);
            }
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('Jid', $interviewJobs);
            }
            $this->db->group_end();
        } else {
            // Other roles: Filter by assignment
            $this->db->group_start();
            $this->db->where('PostedBy', $uid);
            $this->db->or_where('AssignedRecruiterManagerId', $uid);
            $this->db->or_where('CtcApproverId', $uid);
            if ($did > 0) {
                $this->db->or_where('Did', $did);
            }
            if (!empty($interviewJobs)) {
                $this->db->or_where_in('Jid', $interviewJobs);
            }
            $this->db->group_end();
        }

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return !empty($interviewJobs) ? $interviewJobs : [0];
        }

        $jobIds = array_map('intval', array_column($rows, 'Jid'));
        if (!empty($interviewJobs)) {
            $jobIds = array_unique(array_merge($jobIds, $interviewJobs));
        }

        return array_values($jobIds);
    }

public function dashboard()
{
    $Hrms_Session = $this->session->userdata("logged_in");

    if (empty($Hrms_Session)) {
        redirect($this->config->item("base_url") . "admin/index");
        return;
    }

    $roleId = $Hrms_Session["EmpRoleId"];
    $uid    = $Hrms_Session["IUid"];

    $currentUrl = strtolower(uri_string());
    $data["currentUrlArray"] = $this->admin_model->getBreadcrumb($currentUrl);

    // Get role-scoped accessible vacancy IDs for backend filtering
    $accessibleJobIds = $this->_getAccessibleJobIds($Hrms_Session);

    // 1. RECRUITMENT STAGES (Filtered by accessible jobs)
    $stages = $this->db
        ->order_by("StageOrder", "ASC")
        ->get("RecruitmentStages")
        ->result_array();

    foreach ($stages as &$stage) {
        $this->db->from("JobApplications ja");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        if ((int)$roleId === 9) {
            $this->db->join("candidateinterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
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
    $data["recruitment_stages"] = $stages;

    $this->_checkAutoUnholdExpiredJobs();

    // 2. VACANCY COUNTS (Filtered by accessible jobs)
    $this->db->from("IHRJobsList");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["total_vacancies"] = $this->db->count_all_results();

    $this->db->from("IHRJobsList");
    $this->db->group_start()
        ->where("JobStatus", "On-Hold")
        ->or_where("JobStatus", "On Hold")
    ->group_end();
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["onhold_vacancies"] = $this->db->count_all_results();

    $this->db->from("IHRJobsList");
    $this->db->where("JobStatus", "Open");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["open_vacancies"] = $this->db->count_all_results();

    // 3. ON-HOLD REMINDERS
    $today = date('Y-m-d');
    $reminderDate = date('Y-m-d', strtotime('+3 days')); 
    if ($this->db->field_exists('HoldUntilDate', 'IHRJobsList')) {
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
        $onHoldReminders = $this->db->get()->result_array();
        $data["onhold_reminders"] = $onHoldReminders;

        foreach ($onHoldReminders as $remJob) {
            if (empty($remJob['HoldReminderSentDate']) || $remJob['HoldReminderSentDate'] !== $today) {
                $sent = $this->_sendHoldReminderEmail($remJob);
                if ($sent && !empty($remJob['Jid'])) {
                    $this->db->where('Jid', $remJob['Jid'])->update('IHRJobsList', ['HoldReminderSentDate' => $today]);
                }
            }
        }
    } else {
        $data["onhold_reminders"] = [];
    }

    // Normalize legacy 'HR' status to 'Level 1'
    $this->db->where('CurrentStatus', 'HR')->or_where('CurrentStatus', 'hr')->update('JobApplications', ['CurrentStatus' => 'Level 1']);
    $this->db->where('ATS_Status', 'HR')->or_where('ATS_Status', 'hr')->update('IHrCandidates', ['ATS_Status' => 'Level 1']);

    // 4. RESOURCE REQUESTS
    $data["total_resource_requests"]   = $this->db->count_all_results("resource_requests");
    $data["pending_resource_requests"] = $this->db->where("Status", "PENDING APPROVAL")->count_all_results("resource_requests");
    $data["accepted_resource_requests"]= $this->db->where("Status", "ACCEPTED")->count_all_results("resource_requests");

    // 5. CANDIDATE METRICS (Filtered by accessible jobs)
    $this->db->from("JobApplications ja");
    $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("ja.Jid", $accessibleJobIds);
    }
    $this->db->like("ja.CurrentStatus", "Rejected");
    $rejected = $this->db->count_all_results();

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
    $screened = $this->db->count_all_results();

    $data["donut_labels"] = json_encode(["Total Vacancies", "On Hold", "Rejected", "Screened"]);
    $data["donut_values"] = json_encode([$data["total_vacancies"], $data["onhold_vacancies"], $rejected, $screened]);

    // 6. MONTHLY APPLICATION TRENDS (Filtered by accessible jobs)
    $monthly_apps = array_fill(1, 12, 0);
    $monthly_selected = array_fill(1, 12, 0);
    $monthly_rejected = array_fill(1, 12, 0);

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

    $data["monthly_labels"] = json_encode(["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]);
    $data["monthly_values"] = json_encode(array_values($monthly_apps));
    $data["area_posted"]    = json_encode(array_values($monthly_selected));
    $data["area_rejected"]  = json_encode(array_values($monthly_rejected));

    // 7. USER DISTRIBUTION
    $this->db->select("r.RoleName, COUNT(u.IUid) as total_users");
    $this->db->from("IHUsers u");
    $this->db->join("emproles r", "u.Erid = r.Erid", "left");
    $this->db->group_by("u.Erid");
    $result = $this->db->get()->result_array();

    $userLabels = [];
    $userCounts = [];
    foreach ($result as $row) {
        $userLabels[] = !empty($row["RoleName"]) ? $row["RoleName"] : "Unassigned";
        $userCounts[] = (int)$row["total_users"];
    }
    $data["user_labels"] = json_encode($userLabels);
    $data["user_counts"] = json_encode($userCounts);

    // 8. JOBS SUMMARY TABLE (Filtered by assigned interviews for Hiring Manager)
    if ((int)$roleId === 9) {
        $this->db->distinct();
        $this->db->select("jl.*, d.Departmentname");
        $this->db->from("IHRJobsList jl");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->join("JobApplications ja", "ja.Jid = jl.Jid", "inner");
        $this->db->join("candidateinterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
        $this->db->where("ci.InterviewerId", $uid);
        $this->db->order_by("jl.PostedOn", "DESC");
        $data["all_jobs"] = $this->db->get()->result_array();
    } else {
        $this->db->select("jl.*, d.Departmentname");
        $this->db->from("IHRJobsList jl");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        if ($accessibleJobIds !== null) {
            $this->db->where_in("jl.Jid", $accessibleJobIds);
        }
        $this->db->order_by("jl.PostedOn", "DESC");
        $data["all_jobs"] = $this->db->get()->result_array();
    }

    // 9. RECENT CANDIDATES TABLE (Filtered strictly by assigned interviews for Hiring Manager)
    if ((int)$roleId === 9) {
        $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, 1 as IsAssignedInterviewer", false);
        $this->db->from("JobApplications ja");
        $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->join("candidateinterviews ci", "ci.ApplicationId = ja.ApplicationId", "inner");
        $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");
        $this->db->where("ci.InterviewerId", $uid);
        $this->db->order_by("ja.AppliedOn", "DESC");
        $data["all_candidates"] = $this->db->get()->result_array();
    } else {
        $this->db->select("DISTINCT ja.ApplicationId, ja.Jid, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo, c.ExpYrs, c.ProfileMatchPer, c.ATS_Status, jl.JobCode, jl.JobTitle, jl.Did, d.Departmentname, ci.InterviewId, ci.InterviewerId, ci.ScheduledAt, ci.Result as InterviewResult, u_int.EmpName as InterviewerName, IF(ci.InterviewerId = " . (int)$uid . ", 1, 0) as IsAssignedInterviewer", false);
        $this->db->from("JobApplications ja");
        $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
        $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
        $this->db->join("Departments d", "d.Did = jl.Did", "left");
        $this->db->join("candidateinterviews ci", "ci.ApplicationId = ja.ApplicationId AND (ci.Result = 'Assigned' OR ci.ScheduledAt IS NOT NULL)", "left");
        $this->db->join("IHUsers u_int", "u_int.IUid = ci.InterviewerId", "left");

        if ($accessibleJobIds !== null) {
            $this->db->where_in("ja.Jid", $accessibleJobIds);
        }

        $this->db->order_by("ja.AppliedOn", "DESC");
        $data["all_candidates"] = $this->db->get()->result_array();
    }

    // Candidate Counts for Hiring Manager Dashboard
    $data["candidate_total"] = count($data["all_candidates"]);
    $data["candidate_screened"] = $screened;
    $data["candidate_rejected"] = $rejected;

    // 10. RESOURCE REQUESTS LIST
    $this->db->select("rr.*, d.Departmentname, req_u.EmpName as RequestedByName, app_u.EmpName as ApproverName");
    $this->db->from("resource_requests rr");
    $this->db->join("Departments d", "d.Did = rr.Did", "left");
    $this->db->join("IHUsers req_u", "req_u.IUid = rr.RequestedBy", "left");
    $this->db->join("IHUsers app_u", "app_u.IUid = rr.ApproverId", "left");
    $this->db->order_by("rr.CreatedAt", "DESC");
    $data["resource_requests_list"] = $this->db->get()->result_array();

    $data["departments"] = $this->admin_model->getDepartments();

    $this->template->set_master_template("../../themes/" . $this->config->item("active_template") . "/bo_template.php");
    $this->template->write_view("content", "admin/Dashboard", $data);
    $this->template->render();
}

public function Analytics()
{
    $Hrms_Session = $this->session->userdata("logged_in");

    if (empty($Hrms_Session)) {
        redirect($this->config->item("base_url") . "admin/index");
        return;
    }

    $currentUrl = strtolower(uri_string());
    $data["currentUrlArray"] = $this->admin_model->getBreadcrumb($currentUrl);

    $accessibleJobIds = $this->_getAccessibleJobIds($Hrms_Session);

    // Total Jobs
    $this->db->from("IHRJobsList");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["total_jobs"] = $this->db->count_all_results();

    // Total Candidates for accessible jobs
    $this->db->from("JobApplications ja");
    $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("ja.Jid", $accessibleJobIds);
    }
    $data["total_candidates"] = $this->db->count_all_results();

    // Total Applications for accessible jobs
    $this->db->from("JobApplications ja");
    $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("ja.Jid", $accessibleJobIds);
    }
    $data["total_applications"] = $this->db->count_all_results();

    $data["total_requests"] = $this->db->count_all_results("resource_requests");
    
    // Open Jobs
    $this->db->from("IHRJobsList");
    $this->db->where_in("JobStatus", ["Open", "Re-Open"]);
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["open_jobs"] = $this->db->count_all_results();

    // Closed Jobs
    $this->db->from("IHRJobsList");
    $this->db->where_in("JobStatus", ["Closed", "Dropped"]);
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["closed_jobs"] = $this->db->count_all_results();

    // Hold Jobs
    $this->db->from("IHRJobsList");
    $this->db->where_in("JobStatus", ["On-Hold", "On Hold"]);
    if ($accessibleJobIds !== null) {
        $this->db->where_in("Jid", $accessibleJobIds);
    }
    $data["hold_jobs"] = $this->db->count_all_results();

    // Hired Candidates
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
    $data["hired_candidates"] = $this->db->count_all_results();

    // Rejected Candidates
    $this->db->from("JobApplications ja");
    $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("ja.Jid", $accessibleJobIds);
    }
    $this->db->like("ja.CurrentStatus", "Rejected");
    $data["rejected_candidates"] = $this->db->count_all_results();

    // All Jobs History
    $this->db->select("jl.*, d.Departmentname, u.EmpName as RecruiterName");
    $this->db->from("IHRJobsList jl");
    $this->db->join("Departments d", "d.Did = jl.Did", "left");
    $this->db->join("IHUsers u", "u.IUid = jl.AssignedRecruiterManagerId", "left");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("jl.Jid", $accessibleJobIds);
    }
    $this->db->order_by("jl.PostedOn", "DESC");
    $data["all_jobs_history"] = $this->db->get()->result_array();

    // All Candidates History
    $this->db->select("ja.ApplicationId, ja.CurrentStage, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.Email, c.PhoneNo as MobileNumber, c.ExpYrs as TotalExperience, c.ATS_Status, jl.JobTitle, jl.JobCode, d.Departmentname");
    $this->db->from("JobApplications ja");
    $this->db->join("IHrCandidates c", "c.CandidateId = ja.CandidateId", "inner");
    $this->db->join("IHRJobsList jl", "jl.Jid = ja.Jid", "inner");
    $this->db->join("Departments d", "d.Did = jl.Did", "left");
    if ($accessibleJobIds !== null) {
        $this->db->where_in("ja.Jid", $accessibleJobIds);
    }
    $this->db->order_by("ja.AppliedOn", "DESC");
    $data["all_candidates_history"] = $this->db->get()->result_array();

    
    $this->db->select("rr.*, d.Departmentname, req_u.EmpName as RequestedByName, app_u.EmpName as ApproverName");
    $this->db->from("resource_requests rr");
    $this->db->join("Departments d", "d.Did = rr.Did", "left");
    $this->db->join("IHUsers req_u", "req_u.IUid = rr.RequestedBy", "left");
    $this->db->join("IHUsers app_u", "app_u.IUid = rr.ApproverId", "left");
    $this->db->order_by("rr.CreatedAt", "DESC");
    $data["all_requests_history"] = $this->db->get()->result_array();


    $this->db->select("d.Did, d.Departmentname, COUNT(DISTINCT jl.Jid) as total_jobs, COUNT(DISTINCT ja.ApplicationId) as total_apps");
    $this->db->from("Departments d");
    $this->db->join("IHRJobsList jl", "jl.Did = d.Did", "left");
    $this->db->join("JobApplications ja", "ja.Jid = jl.Jid", "left");
    $this->db->group_by("d.Did");
    $data["dept_analytics"] = $this->db->get()->result_array();

  
    $sqlRec = "SELECT u.IUid, u.EmpName, u.EmpCode, u.EmpDesignation,
                COUNT(DISTINCT jl.Jid) as assigned_jobs,
                SUM(CASE WHEN jl.JobStatus IN ('Open','Re-Open') THEN 1 ELSE 0 END) as active_jobs,
                SUM(CASE WHEN jl.JobStatus IN ('Closed') THEN 1 ELSE 0 END) as closed_jobs,
                COUNT(DISTINCT ja.ApplicationId) as managed_candidates
               FROM ihusers u
               JOIN IHRJobsList jl ON jl.AssignedRecruiterManagerId = u.IUid
               LEFT JOIN JobApplications ja ON ja.Jid = jl.Jid
               GROUP BY u.IUid
               HAVING assigned_jobs > 0
               ORDER BY assigned_jobs DESC";
    $data["recruiter_analytics"] = $this->db->query($sqlRec)->result_array();

  
    $sqlIntSummary = "SELECT u.IUid, u.EmpName, u.EmpCode, u.EmpDesignation,
                        COUNT(ci.InterviewId) as total_interviews,
                        SUM(CASE WHEN ci.Result IN ('Passed','Selected','Accepted') THEN 1 ELSE 0 END) as passed_interviews,
                        SUM(CASE WHEN ci.Result IN ('Failed','Rejected') THEN 1 ELSE 0 END) as failed_interviews,
                        SUM(CASE WHEN ci.Result IS NULL OR ci.Result = '' OR ci.Result = 'Scheduled' THEN 1 ELSE 0 END) as pending_interviews
                       FROM ihusers u
                       LEFT JOIN candidateinterviews ci ON ci.InterviewerId = u.IUid
                       LEFT JOIN jobinterviewpanels p ON p.InterviewerId = u.IUid
                       WHERE ci.InterviewId IS NOT NULL OR p.PanelId IS NOT NULL
                       GROUP BY u.IUid
                       ORDER BY total_interviews DESC";
    $data["interviewer_summary"] = $this->db->query($sqlIntSummary)->result_array();

    $sqlIntDetail = "SELECT ci.InterviewId, ci.InterviewRound, ci.InterviewType, ci.ScheduledAt, ci.CompletedAt, ci.Result, ci.Feedback, ci.MeetLink,
                      u.EmpName as InterviewerName, u.EmpCode as InterviewerCode,
                      c.Fullname as CandidateName, c.Email as CandidateEmail, c.PhoneNo as CandidatePhone, c.ExpYrs,
                      jl.JobTitle, jl.JobCode, d.Departmentname
                     FROM candidateinterviews ci
                     JOIN ihusers u ON u.IUid = ci.InterviewerId
                     JOIN JobApplications ja ON ja.ApplicationId = ci.ApplicationId
                     JOIN IHrCandidates c ON c.CandidateId = ja.CandidateId
                     JOIN IHRJobsList jl ON jl.Jid = ja.Jid
                     LEFT JOIN Departments d ON d.Did = jl.Did
                     ORDER BY ci.ScheduledAt DESC";
    $data["interviewer_details"] = $this->db->query($sqlIntDetail)->result_array();

    $data["departments"] = $this->admin_model->getDepartments();


    $this->template->set_master_template("../../themes/" . $this->config->item("active_template") . "/bo_template.php");
    $this->template->write_view("content", "admin/Analytics", $data);
    $this->template->render();
}

public function ManageUsers(){

		$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{	
			
        $currentUrl = strtolower(uri_string());

      
       	 $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);
       	 

        
	            $data['users'] = $this->admin_model->getUsers(); 
	            $data['department'] = $this->admin_model->getUserDepartments();
        $data['ctc_approvers'] = $this->admin_model->getAllUsers();
	            $data['role']       = $this->admin_model->getUserRoles();
				$this->template->write_view('content', 'admin/ManageUsers', $data);
				$this->template->render();
       
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
 
public function Candidatelist($Jid){

		$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{	
		
        $currentUrl = strtolower(uri_string());

        
       	 $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);
       	 

        
        $data['ctc_approvers'] = $this->admin_model->getAllUsers();
	            $data['Candidatelist']       = $this->admin_model->getCandidatesList($Jid);
                $data['jobdetails'] = $this->db
    ->where('Jid',$Jid)
    ->get('IHRJobsList')
    ->row_array();
				$this->template->write_view('content', 'admin/Candidatelist', $data);
				$this->template->render();
       
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
 


public function getCandidateIdDetails()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $candidate_id = $this->input->post('candidate_id');
        if ($candidate_id === null || $candidate_id === '') {
            echo json_encode(['status' => 'error', 'message' => 'Candidate ID missing']);
            return;
        }

        $this->db->select('
            c.*,
            j.JobTitle,
            j.RoleSummary as Role,
            j.JobLocation,
            j.EmploymentType,
            j.ExpMin,
            j.ExpMax,
            ja.ApplicationId,
            ja.CurrentStage,
            ja.CurrentStatus,
            ja.AppliedOn
        ');

        $this->db->from('IHrCandidates c');
        $this->db->join('IHRJobsList j', 'j.Jid = c.Jid', 'left');
        $this->db->join('JobApplications ja', 'ja.CandidateId = c.CandidateId AND ja.Jid = c.Jid', 'left');
        $this->db->where('c.CandidateId', $candidate_id);

        $candidate = $this->db->get()->row_array();

        if (!$candidate) {
            echo json_encode(['status' => 'error', 'message' => 'Candidate not found']);
            return;
        }

        if (!empty($candidate['ExperienceDetails'])) {
            $candidate['experience_details'] = json_decode($candidate['ExperienceDetails'], true);
        }
        $applicationId = $candidate['ApplicationId'];

    	$this->db->select('
    	    t.*,
    	    rs.StageName,
    	    u.IUid,
    	    u.EmpName as ActionByName
    	');

    	$this->db->from('CandidateStageTracking t'); 
    	$this->db->join('RecruitmentStages rs', 'rs.StageId = t.StageId', 'left'); 
    	$this->db->join('IHUsers u', 'u.IUid = t.ActionBy', 'left'); 
    	$this->db->where('t.ApplicationId', $applicationId); 
    	$this->db->order_by('t.ActionAt', 'ASC'); 
    	$stages = $this->db->get()->result_array();

        $interviews = $this->db
            ->where('ApplicationId', $applicationId)
            ->order_by('InterviewId', 'ASC')
            ->get('CandidateInterviews')
            ->result_array();

        foreach ($interviews as $idx => &$iv) {
            $iv['InterviewRound'] = $idx + 1;
        }
        unset($iv);

        $offers = $this->db
            ->where('ApplicationId', $applicationId)
            ->get('CandidateOffers')
            ->result_array();

        $this->db->select('f.*, u.IUid,u.EmpName as CreatedByName');
        $this->db->from('CandidateFollowUps f');
        $this->db->join('IHUsers u', 'u.IUid = f.CreatedBy', 'left');
        $this->db->where('f.ApplicationId', $applicationId);
        $this->db->order_by('f.CreatedAt', 'DESC');

        $followups = $this->db->get()->result_array();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'candidate'  => $candidate,
                'stages'     => $stages,
                'interviews' => $interviews,
                'offers'     => $offers,
                'followups'  => $followups
            ]
        ]);

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function getCandidate360Details()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $candidate_id = $this->input->post('candidate_id');
        if ($candidate_id === null || $candidate_id === '') {
            echo json_encode(['status' => 'error', 'message' => 'Candidate ID missing']);
            return;
        }

        $this->db->select('
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
        ');

        $this->db->from('IHrCandidates c');
        $this->db->join('IHRJobsList j', 'j.Jid = c.Jid', 'left');
        $this->db->join('JobApplications ja', 'ja.CandidateId = c.CandidateId AND ja.Jid = c.Jid', 'left');
        $this->db->where('c.CandidateId', $candidate_id);

        $candidate = $this->db->get()->row_array();

        if (!$candidate) {
            echo json_encode(['status' => 'error', 'message' => 'Candidate not found']);
            return;
        }

        if (!empty($candidate['ExperienceDetails'])) {
            $candidate['experience_details'] = json_decode($candidate['ExperienceDetails'], true);
        }

        if (!empty($candidate['ScoreBreakdown'])) {
            $candidate['score_breakdown'] = json_decode($candidate['ScoreBreakdown'], true);
        }

        $applicationId = $candidate['ApplicationId'];

       
        $this->db->select('
            t.*,
            rs.StageName,
            u.IUid,
            u.EmpName as ActionByName
        ');
        $this->db->from('CandidateStageTracking t'); 
        $this->db->join('RecruitmentStages rs', 'rs.StageId = t.StageId', 'left'); 
        $this->db->join('IHUsers u', 'u.IUid = t.ActionBy', 'left'); 
        $this->db->where('t.ApplicationId', $applicationId); 
        $this->db->order_by('t.ActionAt', 'ASC'); 
        $stages = $this->db->get()->result_array();

       
        $this->db->select('
            ci.*,
            u.EmpCode as InterviewerEmpCode,
            u.EmpName as InterviewerName,
            u.EmpDesignation as InterviewerDesignation
        ');
        $this->db->from('CandidateInterviews ci');
        $this->db->join('IHUsers u', 'u.IUid = ci.InterviewerId', 'left');
        $this->db->where('ci.ApplicationId', $applicationId);
        $this->db->order_by('ci.InterviewId', 'ASC');
        $interviews = $this->db->get()->result_array();

        $panelScores = [];
        $disagreements = [];
        $sumSkill = 0; $sumComm = 0; $sumProb = 0; $sumCult = 0; $sumLead = 0; $evalCount = 0;

        foreach ($interviews as $idx => &$iv) {
            $iv['InterviewRound'] = $idx + 1;
            if (!empty($iv['SkillScore']) && $iv['SkillScore'] > 0) {
                $evalCount++;
                $sumSkill += (int)$iv['SkillScore'];
                $sumComm  += (int)$iv['CommunicationScore'];
                $sumProb  += (int)$iv['ProblemSolvingScore'];
                $sumCult  += (int)$iv['CultureFitScore'];
                $sumLead  += (int)$iv['LeadershipScore'];
                
                $panelScores[] = [
                    'interviewer'  => !empty($iv['InterviewerName']) ? $iv['InterviewerName'] : 'Interviewer #' . ($idx + 1),
                    'emp_code'     => $iv['InterviewerEmpCode'] ?? '',
                    'designation'  => $iv['InterviewerDesignation'] ?? '',
                    'round'        => 'Round ' . ($idx + 1) . ' (' . ($iv['InterviewType'] ?? 'Interview') . ')',
                    'skill'        => (int)$iv['SkillScore'],
                    'comm'         => (int)$iv['CommunicationScore'],
                    'prob'         => (int)$iv['ProblemSolvingScore'],
                    'cult'         => (int)$iv['CultureFitScore'],
                    'lead'         => (int)$iv['LeadershipScore'],
                    'overall'      => (float)($iv['OverallScore'] ?? 0),
                    'result'       => $iv['Result'] ?? 'Assigned',
                    'feedback'     => $iv['Feedback'] ?? ''
                ];
            }
        }
        unset($iv);

       
        if (count($panelScores) >= 2) {
            $categories = [
                'skill' => 'Technical & Role Skill',
                'comm'  => 'Communication Skill',
                'prob'  => 'Problem Solving & Aptitude',
                'cult'  => 'Culture Fit & Adaptability',
                'lead'  => 'Leadership & Initiative'
            ];

            foreach ($categories as $catKey => $catName) {
                $vals = array_column($panelScores, $catKey);
                $minV = min($vals);
                $maxV = max($vals);
                if (($maxV - $minV) >= 2) {
                    $disagreements[] = [
                        'category' => $catName,
                        'min'      => $minV,
                        'max'      => $maxV,
                        'diff'     => ($maxV - $minV),
                        'details'  => "Interviewers provided different ratings for {$catName} ({$minV}/5 vs {$maxV}/5)."
                    ];
                }
            }
        }

       
        $panelSummary = null;
        if ($evalCount > 0) {
            $avgSkill = round($sumSkill / $evalCount, 1);
            $avgComm  = round($sumComm / $evalCount, 1);
            $avgProb  = round($sumProb / $evalCount, 1);
            $avgCult  = round($sumCult / $evalCount, 1);
            $avgLead  = round($sumLead / $evalCount, 1);
            $overallAvg = round(($avgSkill + $avgComm + $avgProb + $avgCult + $avgLead) / 5.0, 2);
            $overallPct = round(($overallAvg / 5.0) * 100, 1);

            $panelSummary = [
                'eval_count'   => $evalCount,
                'avg_skill'    => $avgSkill,
                'avg_comm'     => $avgComm,
                'avg_prob'     => $avgProb,
                'avg_cult'     => $avgCult,
                'avg_lead'     => $avgLead,
                'overall_avg'  => $overallAvg,
                'overall_pct'  => $overallPct
            ];
        }

        
        $aiQuestions = $this->db
            ->where('candidate_id', $candidate_id)
            ->order_by('id', 'ASC')
            ->get('ai_interview_questions')
            ->result_array();

        
        $offers = $this->db
            ->where('ApplicationId', $applicationId)
            ->get('CandidateOffers')
            ->result_array();

        
        $hiring = $this->db
            ->where('ApplicationId', $applicationId)
            ->get('CandidateHiring')
            ->row_array();

       
        $this->db->select('f.*, u.IUid, u.EmpName as CreatedByName');
        $this->db->from('CandidateFollowUps f');
        $this->db->join('IHUsers u', 'u.IUid = f.CreatedBy', 'left');
        $this->db->where('f.ApplicationId', $applicationId);
        $this->db->order_by('f.CreatedAt', 'DESC');
        $followups = $this->db->get()->result_array();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'candidate'     => $candidate,
                'stages'        => $stages,
                'interviews'    => $interviews,
                'panelScores'   => $panelScores,
                'disagreements' => $disagreements,
                'panelSummary'  => $panelSummary,
                'aiQuestions'   => $aiQuestions,
                'offers'        => $offers,
                'hiring'        => $hiring,
                'followups'     => $followups
            ]
        ]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid session']);
    }
}

public function saveCandidate360Decision()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $applicationId = trim($this->input->post('application_id') ?? '');
        $decision      = trim($this->input->post('decision') ?? '');
        $remarks       = trim($this->input->post('remarks') ?? '');

        if (empty($applicationId) || empty($decision)) {
            echo json_encode(['status' => 'error', 'msg' => 'Application ID and Decision are required']);
            return;
        }

        $app = $this->db->where('ApplicationId', $applicationId)->get('JobApplications')->row_array();
        if (!$app) {
            echo json_encode(['status' => 'error', 'msg' => 'Application record not found']);
            return;
        }

        $uid = !empty($Hrms_Session['IUid']) ? $Hrms_Session['IUid'] : 17;
        $statusText = 'HR Final Decision: ' . ucwords(strtolower($decision));

        
        $this->db->where('ApplicationId', $applicationId)
                 ->update('JobApplications', [
                     'CurrentStatus' => $statusText
                 ]);

        
        $trackData = [
            'ApplicationId' => $applicationId,
            'StageId'       => $app['StageId'] ?? 1,
            'Action'        => 'HR Final Decision - ' . ucwords(strtolower($decision)),
            'ActionBy'      => $uid,
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => $remarks
        ];
        $this->db->insert('CandidateStageTracking', $trackData);

       
        $this->load->model('Notification_model');
        $notifTitle = 'Candidate Final Decision Recorded';
        $notifMsg   = "Final hiring decision for Application ID {$applicationId} recorded as: {$decision}. Rationale: {$remarks}";
        $this->Notification_model->addNotification($notifTitle, $notifMsg, 'info', null, 1);
        $this->Notification_model->addNotification($notifTitle, $notifMsg, 'info', null, 2);

        echo json_encode([
            'status' => 'success',
            'msg'    => 'Candidate hiring decision saved successfully!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Session expired. Please log in again.']);
    }
}

public function ManageDepartments(){

		$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{	
			
        $currentUrl = strtolower(uri_string());

       
       	 $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);
       	 

        
 	            $data['department'] = $this->admin_model->getDepartments();
 				$this->template->write_view('content', 'admin/ManageDepartments', $data);
				$this->template->render();
       
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
 
   public function SaveUser()
    {
    
    $Hrms_Session = $this->session->userdata('logged_in');
    if(isset($Hrms_Session) && !empty($Hrms_Session))
            {	
                $inps = $this->input->post();

                $EmpCode        = trim($inps['val-empid']);
                $EmpName        = trim($inps['val-username']);
                $EmpEmail       = trim($inps['val-email']);
                $EmpPhone       = trim($inps['val-phoneus']);
                $EmpDOB         = $inps['val-dob'];
                $EmpGender      = $inps['val-gender'];
                $EmpDesignation = trim($inps['val-designation']);
                $EmpRole        = $inps['val-Role'];
                $EmpDept        = $inps['val-department'];

               
                $formValues = [
                    'val-empid'       => $EmpCode,
                    'val-username'    => $EmpName,
                    'val-email'       => $EmpEmail,
                    'val-phoneus'     => $EmpPhone,
                    'val-dob'         => $EmpDOB,
                    'val-gender'      => $EmpGender,
                    'val-designation' => $EmpDesignation,
                    'val-Role'        => $EmpRole,
                    'val-department'  => $EmpDept,
                ];

                
                $existsCode = $this->db
                    ->where('EmpCode', $EmpCode)
                    ->count_all_results('IHUsers');
                if ($existsCode > 0) {
                    $this->session->set_flashdata('error', 'Employee Code already exists. Please use a different Employee Code.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                    return;
                }

               
                $existsEmail = $this->db
                    ->where('EmpEmail', $EmpEmail)
                    ->count_all_results('IHUsers');
                if ($existsEmail > 0) {
                    $this->session->set_flashdata('error', 'Email Address already exists. Please use a different email.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                    return;
                }

              
                $existsPhone = $this->db
                    ->where('EmpPhone', $EmpPhone)
                    ->count_all_results('IHUsers');
                if ($existsPhone > 0) {
                    $this->session->set_flashdata('error', 'Mobile Number already exists. Please use a different mobile number.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                    return;
                }

                $insertData = [
                    'EmpCode'        => $EmpCode,
                    'EmpName'        => $EmpName,
                    'EmpEmail'       => $EmpEmail,
                    'EmpPass'        => md5($EmpPhone),
                    'EmpPhone'       => $EmpPhone,
                    'EmpDOB'         => $EmpDOB,
                    'EmpGender'      => $EmpGender,
                    'EmpDesignation' => $EmpDesignation,
                    'Erid'           => $EmpRole,
                    'Did'            => $EmpDept,
                    'UStatus'        => 1
                ];

                try {
                    $this->db->insert('IHUsers', $insertData);
                    $UsrId = $this->db->insert_id();

                    if ($UsrId > 0) {
                        $this->session->set_flashdata('success', 'User added successfully.');
                        redirect($this->config->item('base_url').'admin/ManageUsers');
                    } else {
                        $this->session->set_flashdata('error', 'User creation failed. Please try again.');
                        $this->session->set_flashdata('form_values', $formValues);
                        redirect($this->config->item('base_url').'admin/ManageUsers');
                    }
                } catch (Exception $e) {
                    $this->session->set_flashdata('error', 'An unexpected error occurred while saving the user. Please try again.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                }

            }else
            {
            $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
                redirect($this->config->item('base_url')."admin/index");
            }

    }
 
 
public function VaccancyList(){

    $Hrms_Session = $this->session->userdata('logged_in');  

    if (isset($Hrms_Session) && !empty($Hrms_Session))
    {	
        $roleId = isset($Hrms_Session['EmpRoleId']) ? $Hrms_Session['EmpRoleId'] : null;
        $roleRow = $this->db->select('RoleName')->from('emproles')->where('Erid', $roleId)->get()->row_array();
        $roleName = !empty($roleRow) ? strtolower($roleRow['RoleName']) : '';

        if ($roleName === 'hiring manager' || $roleId == 9) {
            $this->session->set_flashdata('error', 'Hiring Managers do not have access to the Vacancy List page.');
            redirect($this->config->item('base_url') . 'admin/RequestedResources');
            return;
        }  

        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);
        $data['vaclist'] = $this->admin_model->get_VaccancyList();	
        $data['department'] = $this->admin_model->getUserDepartments();
        $data['ctc_approvers'] = $this->admin_model->getAllUsers();

        $this->template->write_view('content', 'admin/VaccancyList', $data);
        $this->template->render();
    } else {
        $this->session->set_flashdata('error', 'Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url') . "admin/index");
    }
}

public function searchLocation()
{
	$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{
			    $q = $this->input->get('q');

			    if (strlen($q) < 3) {
			        echo json_encode([]);
			        return;
			    }

			 	 $this->db->distinct();
				$this->db->select('JobLocation');
				$this->db->like('JobLocation', $q);
				$this->db->where('JobLocation !=', '');
				$this->db->limit(10);

				$result = $this->db->get('IHRJobsList')->result_array();

			    echo json_encode($result);
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
public function searchEducation()
{
	$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{
			    $q = $this->input->get('q');

			    if (strlen($q) < 3) {
			        echo json_encode([]);
			        return;
			    }

			 	 $this->db->distinct();
				$this->db->select('EducationRequired');
				$this->db->like('EducationRequired', $q);
				$this->db->where('EducationRequired !=', '');
				$this->db->limit(10);

				$result = $this->db->get('IHRJobsList')->result_array();

			    echo json_encode($result);
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
public function searchLanguage()
{
	$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{
			    $q = $this->input->get('q');

			    if (strlen($q) < 3) {
			        echo json_encode([]);
			        return;
			    }

			 	 $this->db->distinct();
				$this->db->select('CommunicationLang');
				$this->db->like('CommunicationLang', $q);
				$this->db->where('CommunicationLang !=', '');
				$this->db->limit(10);

				$result = $this->db->get('IHRJobsList')->result_array();

			    echo json_encode($result);
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
public function searchSkills()
{
	$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{
			    $q = $this->input->get('q');

			    if (strlen($q) < 3) {
			        echo json_encode([]);
			        return;
			    }

			 	 $this->db->distinct();
				$this->db->select('SkillName');
				$this->db->like('SkillName', $q);
				$this->db->where('SkillName !=', '');
				$this->db->limit(10);

				$result = $this->db->get('IHSkills')->result_array();

			    echo json_encode($result);
		} else
		{
		$this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."admin/index");
		}

}
 
public function saveVacancy(){

    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $roleId = isset($Hrms_Session['EmpRoleId']) ? $Hrms_Session['EmpRoleId'] : null;
        $roleRow = $this->db->select('RoleName')->from('emproles')->where('Erid', $roleId)->get()->row_array();
        $roleName = !empty($roleRow) ? strtolower($roleRow['RoleName']) : '';
        $approverId = $this->input->post('approverId');

        // If submitted by Hiring Manager OR if an Approver is selected, route to Resource Request workflow
        if ($roleName === 'hiring manager' || !empty($approverId)) {
            $count = $this->db->count_all("resource_requests") + 1;
            $requestCode = "RR-" . date("Y") . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);

            $reqData = [
                "RequestCode"          => $requestCode,
                "JobTitle"             => trim($this->input->post('jobTitle')),
                "FunctionalRole"       => trim($this->input->post('role')),
                "Did"                  => (int)$this->input->post('department'),
                "NoofOpenings"         => (int)($this->input->post('positions') ? $this->input->post('positions') : 1),
                "PositionType"         => $this->input->post('positionType') ? $this->input->post('positionType') : "New Position",
                "ExpMin"               => (int)$this->input->post('expMin'),
                "ExpMax"               => (int)$this->input->post('expMax'),
                "SalMin"               => (int)($this->input->post('salaryMin') ? $this->input->post('salaryMin') : 0),
                "SalMax"               => (int)($this->input->post('salaryMax') ? $this->input->post('salaryMax') : 0),
                "RecruitmentStartDate" => $this->input->post('recruitmentStartDate') ? $this->input->post('recruitmentStartDate') : null,
                "TargetOnboardingDate" => $this->input->post('targetOnboardingDate') ? $this->input->post('targetOnboardingDate') : null,
                "ReasonForRequirement" => "",
                "JobDescription"       => trim($this->input->post('JD')),
                "Responsibilities"     => trim($this->input->post('RR')),
                "RequestedBy"          => $Hrms_Session['IUid'],
                "ApproverId"           => (int)$approverId,
                "Status"               => "PENDING APPROVAL",
                "CreatedAt"            => date("Y-m-d H:i:s")
            ];

            $requestId = $this->admin_model->insertResourceRequest($reqData);
            if ($requestId) {
                $this->_sendResourceRequestEmailToApprover($requestId);
                $this->session->set_flashdata('true', 'Resource Request submitted successfully and sent for approval.');
            } else {
                $this->session->set_flashdata('error', 'Failed to submit Resource Request.');
            }

            redirect($this->config->item('base_url') . 'admin/RequestedResources');
            return;
        }

        $jobTitle   = trim($this->input->post('jobTitle'));
        $department = $this->input->post('department');

       
        $exists = $this->db
            ->where('JobTitle', $jobTitle)
            ->where('Did', $department)
            ->get('IHRJobsList')
            ->row();

        if($exists){
            $this->session->set_flashdata('job_exists', 'This job title already exists in this department');
            redirect($this->config->item('base_url')."admin/VaccancyList");
            return;
        }

        $jobCode = $this->generateJobCode();
        if (!$this->db->field_exists('MustHaveSkills', 'IHRJobsList')) {
            $this->db->query("ALTER TABLE IHRJobsList ADD COLUMN MustHaveSkills TEXT NULL");
        }
        if (!$this->db->field_exists('NiceToHaveSkills', 'IHRJobsList')) {
            $this->db->query("ALTER TABLE IHRJobsList ADD COLUMN NiceToHaveSkills TEXT NULL");
        }

        $mustHaveSkills = $this->input->post('mustHaveSkills');
        $niceToHaveSkills = $this->input->post('niceToHaveSkills');

        $mustHaveStr = is_array($mustHaveSkills) ? implode(', ', $mustHaveSkills) : trim((string)$mustHaveSkills);
        $niceToHaveStr = is_array($niceToHaveSkills) ? implode(', ', $niceToHaveSkills) : trim((string)$niceToHaveSkills);

        if (empty($mustHaveStr) && $this->input->post('skills')) {
            $mustHaveStr = trim($this->input->post('skills'));
        }

        $allSkillsList = array_unique(array_filter(array_map('trim', explode(',', $mustHaveStr . ', ' . $niceToHaveStr))));
        $combinedSkillsCsv = implode(', ', $allSkillsList);

        $jobData = [
            'JobCode'           => $jobCode,
            'JobTitle'          => $this->input->post('jobTitle'),
            'RoleSummary'       => $this->input->post('roleSummary'),
            'Did'               => $this->input->post('department'),
            'WorkMode'          => $this->input->post('workMode'),
            'EmploymentType'    => $this->input->post('employmentType'),
            'EducationRequired' => $this->input->post('education'),
            'ExpMin'            => $this->input->post('expMin'),
            'ExpMax'            => $this->input->post('expMax'),
            'SalMin'            => $this->input->post('salaryMin'),
            'SalMax'            => $this->input->post('salaryMax'),
            'Currency'          => 'INR',
            'NoofOpenings'      => $this->input->post('positions'),
            'JobStatus'         => 'Open',
            'JobDescription'    => $this->input->post('JD'),
            'Responsibilities'  => $this->input->post('RR'),
            'Qualifications'    => $this->input->post('education'),
            'JobLocation'       => $this->input->post('jobLocation'),
            'CommunicationLang' => $this->input->post('comLanguage'),
            'MustHaveSkills'    => $mustHaveStr,
            'NiceToHaveSkills'  => $niceToHaveStr,
            'Skills'            => $combinedSkillsCsv,
            'PostedBy'          => $Hrms_Session['IUid'],
            'CtcApproverId'     => $this->input->post('CtcApproverId') ? (int)$this->input->post('CtcApproverId') : null,
            'ExpiryDate'        => date('Y-m-d', strtotime($this->input->post('ExpiryDate'))),
            'SkillScore'           => ($this->input->post('SkillScore') !== null && $this->input->post('SkillScore') !== '') ? $this->input->post('SkillScore') : 50,
            'EducationScore'       => ($this->input->post('EducationScore') !== null && $this->input->post('EducationScore') !== '') ? $this->input->post('EducationScore') : 20,
            'ExperienceScore'      => ($this->input->post('ExperienceScore') !== null && $this->input->post('ExperienceScore') !== '') ? $this->input->post('ExperienceScore') : 20,
            'ProjectScore'         => ($this->input->post('ProjectScore') !== null && $this->input->post('ProjectScore') !== '') ? $this->input->post('ProjectScore') : 5,
            'CertificationScore'   => ($this->input->post('CertificationScore') !== null && $this->input->post('CertificationScore') !== '') ? $this->input->post('CertificationScore') : 10,
            'ResumeQualityScore'   => ($this->input->post('ResumeQualityScore') !== null && $this->input->post('ResumeQualityScore') !== '') ? $this->input->post('ResumeQualityScore') : 5,
            'DomainKnowledgeScore' => ($this->input->post('DomainKnowledgeScore') !== null && $this->input->post('DomainKnowledgeScore') !== '') ? $this->input->post('DomainKnowledgeScore') : 5
        ];

        $this->db->insert('IHRJobsList', $jobData);
        $jobId = $this->db->insert_id();

        
        $this->saveSkills($combinedSkillsCsv);
        $this->saveJobSkills($jobId, $combinedSkillsCsv);

        if($jobId > 0){

            $this->session->set_flashdata('success','Job Vacancy Added Successfully');
            redirect($this->config->item('base_url')."admin/VaccancyList");

        } else {

            $this->session->set_flashdata('error','Failed to Save');
            redirect($this->config->item('base_url')."admin/VaccancyList");

        }

    } else {

        $this->session->set_flashdata('error','Invalid Session.Please Login Again');
        redirect($this->config->item('base_url')."admin/index");

    }
}
private function generateJobCode()
{
    $prefix = '#IHRMS-' . date('Ym') . '-';

    $this->db->like('JobCode', $prefix);
    $this->db->order_by('JobCode', 'DESC');
    $last = $this->db->get('IHRJobsList')->row();

    if ($last) {
        $lastNo = (int) substr($last->JobCode, -4);
        $newNo  = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);

    } else {
        $newNo = '0001';
    }

    return $prefix . $newNo;
}

private function saveSkills($skillsCsv)
{
    if (!$skillsCsv) return;

    $skills = array_unique(array_map('trim', explode(',', $skillsCsv)));

    foreach ($skills as $skill) {
        if ($skill === '') continue;

        $exists = $this->db
            ->where('SkillName', $skill)
            ->get('IHSkills')
            ->row();

        if (!$exists) {
            $this->db->insert('IHSkills', [
                'SkillName' => $skill
            ]);
        }
    }
}

public function saveJobSkills($jobId, $skills)
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        if (empty($skills)) return;

        $skillsArr = array_unique(array_map('trim', explode(',', $skills)));

        foreach ($skillsArr as $skillName) {

            
            $skill = $this->db->get_where('IHSkills', [
                'SkillName' => $skillName
            ])->row();

            if ($skill) {
                $skillId = $skill->SkillId;
            } else {
               
                $this->db->insert('IHSkills', [
                    'SkillName' => $skillName
                ]);
                $skillId = $this->db->insert_id();
            }

            
            $exists = $this->db->get_where('JobSkills', [
                'Jid'     => $jobId,
                'SkillId'=> $skillId
            ])->num_rows();

            if ($exists == 0) {
                $this->db->insert('JobSkills', [
                    'Jid'     => $jobId,
                    'SkillId'=> $skillId
                ]);
            }
        }

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}
public function updateJobStatus(){

 $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $inps = $this->input->post();

         $jid = $this->input->post('jid');
	     $status = $this->input->post('status');

        $statusLower = strtolower(trim($status));

        if (!$this->db->field_exists('HoldUntilDate', 'IHRJobsList')) {
            $this->db->query("ALTER TABLE IHRJobsList ADD COLUMN HoldUntilDate DATE NULL");
        }
        if (!$this->db->field_exists('HoldReminderSentDate', 'IHRJobsList')) {
            $this->db->query("ALTER TABLE IHRJobsList ADD COLUMN HoldReminderSentDate DATE NULL");
        }

        $updateData = [
            'JobStatus' => $status,
            'UpdatedOn' => date('Y-m-d H:i:s')
        ];

        if ($statusLower === 'on-hold' || $statusLower === 'on hold') {
            $holdUntilDate = $this->input->post('holdUntilDate');
            if (!empty($holdUntilDate)) {
                $updateData['HoldUntilDate'] = date('Y-m-d', strtotime($holdUntilDate));
            } else {
                $updateData['HoldUntilDate'] = date('Y-m-d', strtotime('+1 day'));
            }
            $updateData['HoldReminderSentDate'] = null;
        } else {
            $updateData['HoldUntilDate'] = null;
            $updateData['HoldReminderSentDate'] = null;
        }

        $this->db->where('Jid', $jid)->update('IHRJobsList', $updateData);

      
        if ($statusLower === 'on-hold' || $statusLower === 'on hold') {
            $this->_addJobTrackingLog(
                $jid,
                'JOB_ON_HOLD',
                'Job Placed On-Hold',
                'Job status updated to On-Hold until: ' . ($updateData['HoldUntilDate'] ?? 'Not specified'),
                $updateData['HoldUntilDate'] ?? null
            );
        } elseif ($statusLower === 'open' || $statusLower === 're-open' || $statusLower === 'reopen' || $statusLower === 'unhold') {
            $this->_addJobTrackingLog(
                $jid,
                'JOB_UNHELD',
                'Job Reopened / Unheld',
                'Job status updated from On-Hold back to Open / Active.'
            );
        } elseif ($statusLower === 'closed' || $statusLower === 'dropped' || $statusLower === 'drop') {
            $this->_addJobTrackingLog(
                $jid,
                'JOB_DROPPED',
                'Job Dropped',
                'Job position dropped.'
            );
        }

      
        if ($statusLower === 'on-hold' || $statusLower === 'on hold') {
            $this->_sendVacancyOnHoldEmailToRecruiter($jid);
        }

			    echo json_encode([
		   		 'status'  => 'success',
		   		 'message' => 'Job status updated successfully'
				]);

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

private function _addJobTrackingLog($jid, $eventType, $eventTitle, $eventDescription = null, $holdUntilDate = null, $actionBy = null, $requestId = null)
{
    if (!$this->db->table_exists('JobTracking')) {
        $this->db->query("CREATE TABLE IF NOT EXISTS JobTracking (
            TrackId INT AUTO_INCREMENT PRIMARY KEY,
            Jid INT NULL,
            RequestId INT NULL,
            EventType VARCHAR(50) NOT NULL,
            EventTitle VARCHAR(255) NOT NULL,
            EventDescription TEXT NULL,
            HoldUntilDate DATE NULL,
            ActionBy INT NULL,
            ActionAt DATETIME NOT NULL,
            CreatedOn DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    $Hrms_Session = $this->session->userdata('logged_in');
    if (empty($actionBy) && !empty($Hrms_Session['IUid'])) {
        $actionBy = $Hrms_Session['IUid'];
    }

    $this->db->insert('JobTracking', [
        'Jid'              => !empty($jid) ? (int)$jid : null,
        'RequestId'        => !empty($requestId) ? (int)$requestId : null,
        'EventType'        => $eventType,
        'EventTitle'       => $eventTitle,
        'EventDescription' => $eventDescription,
        'HoldUntilDate'    => $holdUntilDate,
        'ActionBy'         => $actionBy,
        'ActionAt'         => date('Y-m-d H:i:s')
    ]);
}


private function _checkAutoUnholdExpiredJobs()
{
    if (!$this->db->table_exists('IHRJobsList') || !$this->db->field_exists('HoldUntilDate', 'IHRJobsList')) {
        return;
    }

    $today = date('Y-m-d');
    $expiredJobs = $this->db
        ->group_start()
            ->where('JobStatus', 'On-Hold')
            ->or_where('JobStatus', 'On Hold')
        ->group_end()
        ->where('HoldUntilDate IS NOT NULL', null, false)
        ->where('HoldUntilDate <', $today)
        ->get('IHRJobsList')
        ->result_array();

    foreach ($expiredJobs as $job) {
        $jid = (int)$job['Jid'];

     
        $this->db->where('Jid', $jid)->update('IHRJobsList', [
            'JobStatus'            => 'Open',
            'HoldUntilDate'        => null,
            'HoldReminderSentDate' => null,
            'UpdatedOn'            => date('Y-m-d H:i:s')
        ]);

     
        $latestTracking = $this->db
            ->where('Jid', $jid)
            ->where('EventType', 'JOB_UNHELD')
            ->order_by('TrackId', 'DESC')
            ->limit(1)
            ->get('JobTracking')
            ->row_array();

        $alreadyLogged = false;
        if (!empty($latestTracking) && strpos($latestTracking['EventDescription'], 'Hold date') !== false) {
            $alreadyLogged = true;
        }

        if (!$alreadyLogged) {
            $this->_addJobTrackingLog(
                $jid,
                'JOB_UNHELD',
                'Job Automatically Unheld (Hold Period Expired)',
                'Hold date (' . ($job['HoldUntilDate'] ?? 'Expired') . ') reached. Job status automatically restored from On-Hold back to Open.'
            );
        }
    }
}


public function migrateJobStatusHistoryToTracking()
{
    $tableName = null;
    if ($this->db->table_exists('JobStatusHistory')) {
        $tableName = 'JobStatusHistory';
    } elseif ($this->db->table_exists('jobstatushistory')) {
        $tableName = 'jobstatushistory';
    }

    if (!$tableName) {
        echo json_encode(['status' => 'info', 'message' => 'JobStatusHistory table does not exist.']);
        return;
    }

    if (!$this->db->table_exists('JobTracking') && !$this->db->table_exists('jobtracking')) {
        $this->_addJobTrackingLog(null, 'INIT', 'System Init');
    }

    $historyRows = $this->db->get($tableName)->result_array();
    $migratedCount = 0;
    $skippedCount  = 0;

    foreach ($historyRows as $sh) {
        $jid         = (int)$sh['Jid'];
        $statusLower = strtolower(trim($sh['Status']));
        $changedAt   = $sh['ChangedAt'] ?? date('Y-m-d H:i:s');
        $changedBy   = !empty($sh['ChangedBy']) ? (int)$sh['ChangedBy'] : null;
        $holdDate    = !empty($sh['HoldUntilDate']) ? $sh['HoldUntilDate'] : null;

        $eventType   = 'JOB_STATUS_CHANGE';
        $eventTitle  = 'Job Status Changed';
        $eventDesc   = 'Status changed to: ' . $sh['Status'];

        if (strpos($statusLower, 'hold') !== false && strpos($statusLower, 'unhold') === false) {
            $eventType  = 'JOB_ON_HOLD';
            $eventTitle = 'Job Placed On-Hold';
            $eventDesc  = 'Job status updated to On-Hold until: ' . ($holdDate ?? 'Not specified');
        } elseif ($statusLower === 'open' || $statusLower === 're-open' || $statusLower === 'unhold') {
            $eventType  = 'JOB_UNHELD';
            $eventTitle = 'Job Reopened / Unheld';
            $eventDesc  = 'Job status updated from On-Hold back to Open / Active.';
        } elseif ($statusLower === 'closed' || $statusLower === 'dropped' || $statusLower === 'drop') {
            $eventType  = 'JOB_DROPPED';
            $eventTitle = 'Job Dropped';
            $eventDesc  = 'Job position dropped.';
        }

        $exists = $this->db
            ->where('Jid', $jid)
            ->where('EventType', $eventType)
            ->group_start()
                ->where('ActionAt', $changedAt)
                ->or_where('HoldUntilDate', $holdDate)
            ->group_end()
            ->get('JobTracking')
            ->row_array();

        if (empty($exists)) {
            $this->db->insert('JobTracking', [
                'Jid'              => $jid,
                'RequestId'        => null,
                'EventType'        => $eventType,
                'EventTitle'       => $eventTitle,
                'EventDescription' => $eventDesc,
                'HoldUntilDate'    => $holdDate,
                'ActionBy'         => $changedBy,
                'ActionAt'         => $changedAt,
                'CreatedOn'        => $changedAt
            ]);
            $migratedCount++;
        } else {
            $skippedCount++;
        }
    }

    echo json_encode([
        'status'         => 'success',
        'message'        => 'Migration completed successfully.',
        'migrated_rows'  => $migratedCount,
        'skipped_rows'   => $skippedCount,
        'total_analyzed' => count($historyRows)
    ]);
}

public function UpdateUser()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $inps = $this->input->post();

        
       

        $this->db->where('IUid', $inps['IUid']);
        $this->db->update('IHUsers', [
            'EmpName'        => $inps['val-username'],
            'EmpEmail'       => $inps['val-email'],
            'EmpPhone'       => $inps['val-phoneus'],
            'EmpDOB'         => $inps['val-dob'],
            'EmpGender'      => $inps['val-gender'],
            'EmpDesignation' => $inps['val-designation'],
            'Erid'           => $inps['val-Role'],
            'Did'            => $inps['val-department'],
        
        ]);

    
        

        $this->session->set_flashdata('success', 'User updated successfully');
        redirect($this->config->item('base_url').'admin/ManageUsers');

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
    }
}

public function SaveDepartment()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $deptName = trim($this->input->post('val-depname'));

       
        $exists = $this->db
                       ->where('Departmentname', $deptName)
                       ->get('Departments')
                       ->row();

        if ($exists) {
            $this->session->set_flashdata('error', 'Department already exists');
            redirect($this->config->item('base_url') . 'admin/ManageDepartments');
            return;
        }

        $data = [
            'Departmentname' => $deptName,
            'Status'         => 1,
            'CreatedDate'    => date('Y-m-d H:i:s')
        ];

        $this->db->insert('Departments', $data);

        $this->session->set_flashdata('success', 'Department added successfully');
        redirect($this->config->item('base_url') . 'admin/ManageDepartments');

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function UpdateDepartment()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $inps = $this->input->post();

        
        

        $this->db->where('Did', $inps['Did']);
        $this->db->update('Departments', [
            'Departmentname'        => $inps['val-username'] 
        
        ]);

    
       

        $this->session->set_flashdata('success', 'Department updated successfully');
        redirect($this->config->item('base_url').'admin/ManageDepartments');

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}
 

public function ActivateDepartment($id)
{
	    $Hrms_Session = $this->session->userdata('logged_in');

	    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

	    	$this->db->where('Did', $id);
            $this->db->update('Departments', [
                'Status' => 1
            ]);

		   
		   $this->session->set_flashdata('success', 'Department activated successfully');
		   redirect($this->config->item('base_url').'admin/ManageDepartments');

	    } else {

        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
} 

public function ActivateUser($id)
{
	    $Hrms_Session = $this->session->userdata('logged_in');

	    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

	    	$this->db->where('IUid', $id);
            $this->db->update('IHUsers', [
                'UStatus' => 1
            ]);

		    
		   $this->session->set_flashdata('success', 'User activated successfully');
		   redirect($this->config->item('base_url').'admin/ManageUsers');

	    } else {

        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function DeactivateUser($id)
{
    $Hrms_Session = $this->session->userdata('logged_in');
    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

    		
   			$this->db->where('IUid', $id);
            $this->db->update('IHUsers', [
                'UStatus'   => 0
            ]);

            
             
            $this->session->set_flashdata('success', 'User deactivated successfully');
            redirect($this->config->item('base_url').'admin/ManageUsers');

	   } else {

        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }

}
public function DeactivateDepartment($id)
{
    $Hrms_Session = $this->session->userdata('logged_in');
    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

    		
   			$this->db->where('Did', $id);
            $this->db->update('Departments', [
                'Status'   => 0
            ]);

            
           
            $this->session->set_flashdata('success', 'Department deactivated successfully');
            redirect($this->config->item('base_url').'admin/ManageDepartments');

	   } else {

        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }

 } public function getJobDetails(){

    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

     $jid = $this->input->post('jid');

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
     $this->db->join('Departments d','d.Did = jl.Did','left');
     $this->db->join('JobSkills js','js.Jid = jl.Jid','left');
     $this->db->join('IHSkills s','s.SkillId = js.SkillId','left');
     $this->db->where('jl.Jid',$jid);
     $this->db->group_by('jl.Jid');
     $this->db->join('IHUsers u','u.IUid = jl.PostedBy','left');
     $this->db->join('IHUsers ctc','ctc.IUid = jl.CtcApproverId','left');
     $this->db->join('IHUsers arm','arm.IUid = jl.AssignedRecruiterManagerId','left');

     $row = $this->db->get()->row_array();

     if ($row && !empty($row['Jid'])) {
         $rr = $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                        ->from('resource_requests rr')
                        ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                        ->where('rr.ConvertedJid', $row['Jid'])
                        ->get()
                        ->row_array();

         if (empty($rr) && !empty($row['JobTitle'])) {
             $rr = $this->db->select('rr.*, ctc.EmpName AS CtcApproverName')
                            ->from('resource_requests rr')
                            ->join('IHUsers ctc', 'ctc.IUid = rr.CtcApproverId', 'left')
                            ->where('rr.JobTitle', $row['JobTitle'])
                            ->order_by('rr.RequestId', 'DESC')
                            ->get()
                            ->row_array();
         }

         if (!empty($rr)) {
             if ((empty($row['SalMin']) || $row['SalMin'] == 0) && isset($rr['SalMin'])) $row['SalMin'] = $rr['SalMin'];
             if ((empty($row['SalMax']) || $row['SalMax'] == 0) && isset($rr['SalMax'])) $row['SalMax'] = $rr['SalMax'];
             if ((empty($row['ExpMin']) || $row['ExpMin'] == 0) && isset($rr['ExpMin'])) $row['ExpMin'] = $rr['ExpMin'];
             if ((empty($row['ExpMax']) || $row['ExpMax'] == 0) && isset($rr['ExpMax'])) $row['ExpMax'] = $rr['ExpMax'];
             if (empty($row['Salary']) && !empty($rr['Salary'])) $row['Salary'] = $rr['Salary'];
             if (empty($row['JobLocation']) && !empty($rr['JobLocation'])) $row['JobLocation'] = $rr['JobLocation'];
             if (empty($row['EducationRequired']) && !empty($rr['EducationRequired'])) $row['EducationRequired'] = $rr['EducationRequired'];
             if (empty($row['MustHaveSkills']) && !empty($rr['MustHaveSkills'])) $row['MustHaveSkills'] = $rr['MustHaveSkills'];
             if (empty($row['NiceToHaveSkills']) && !empty($rr['NiceToHaveSkills'])) $row['NiceToHaveSkills'] = $rr['NiceToHaveSkills'];
             if (empty($row['CommunicationLang']) && !empty($rr['CommunicationLang'])) $row['CommunicationLang'] = $rr['CommunicationLang'];
             if (empty($row['JobDescription']) && !empty($rr['JobDescription'])) $row['JobDescription'] = $rr['JobDescription'];
             if (empty($row['Responsibilities']) && !empty($rr['Responsibilities'])) $row['Responsibilities'] = $rr['Responsibilities'];
             if (empty($row['CtcApproverId']) && !empty($rr['CtcApproverId'])) {
                 $row['CtcApproverId']   = $rr['CtcApproverId'];
                 $row['CtcApproverName'] = $rr['CtcApproverName'];
                 $this->db->where('Jid', $row['Jid'])->update('IHRJobsList', ['CtcApproverId' => $rr['CtcApproverId']]);
             }
         }

         $this->db->query("CREATE TABLE IF NOT EXISTS JobInterviewPanels (
             PanelId INT AUTO_INCREMENT PRIMARY KEY,
             Jid INT NOT NULL,
             LevelOrder INT NOT NULL,
             InterviewerId INT NOT NULL,
             CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
             INDEX (Jid)
         )");
         $rawPanels = $this->db->where('Jid', $row['Jid'])->order_by('LevelOrder', 'ASC')->get('JobInterviewPanels')->result_array();
         foreach ($rawPanels as &$panel) {
             $interviewer = $this->db->select('EmpName')->where('IUid', $panel['InterviewerId'])->get('IHUsers')->row_array();
             $panel['InterviewerName'] = $interviewer ? $interviewer['EmpName'] : 'Unknown';
         }
         unset($panel);
         $row['interviewPanels'] = $rawPanels;
     }

     echo json_encode($row);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function getCandidateInterviewPanelInfo()
{
    $candidateId = $this->input->post('candidateId');
    if (empty($candidateId)) {
        echo json_encode(['status' => 'error', 'msg' => 'Candidate ID missing']);
        return;
    }

    $app = $this->db->select('ja.ApplicationId, ja.Jid')
                    ->from('JobApplications ja')
                    ->where('ja.CandidateId', $candidateId)
                    ->order_by('ja.ApplicationId', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();

    $jid = $app ? $app->Jid : 0;
    if (!$jid) {
        $cand = $this->db->select('Jid')->where('CandidateId', $candidateId)->get('IHrCandidates')->row();
        $jid = $cand ? $cand->Jid : 0;
    }

    $panels = [];
    if ($jid) {
        $this->db->query("CREATE TABLE IF NOT EXISTS JobInterviewPanels (
            PanelId INT AUTO_INCREMENT PRIMARY KEY,
            Jid INT NOT NULL,
            LevelOrder INT NOT NULL,
            InterviewerId INT NOT NULL,
            CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (Jid)
        )");

        $panels = $this->db->select('p.LevelOrder, p.InterviewerId, u.EmpName')
                           ->from('JobInterviewPanels p')
                           ->join('IHUsers u', 'u.IUid = p.InterviewerId', 'left')
                           ->where('p.Jid', $jid)
                           ->order_by('p.LevelOrder', 'ASC')
                           ->get()
                           ->result_array();
    }

    echo json_encode([
        'status' => 'success',
        'jid'    => $jid,
        'panels' => $panels
    ]);
}

public function updateVacancy()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $jid = (int)$this->input->post('jid');
        $rawRequestId = $this->input->post('requestId');
        $rawRequestCode = $this->input->post('requestCode') ?: $this->input->post('jobCode');
        $requestId = is_numeric($rawRequestId) ? (int)$rawRequestId : 0;

        if ($jid <= 0 && $requestId <= 0) {
            $codeToSearch = (!empty($rawRequestId) && !is_numeric($rawRequestId)) ? $rawRequestId : $rawRequestCode;
            if (!empty($codeToSearch)) {
                $codeRow = $this->db->select('RequestId, ConvertedJid')->from('resource_requests')->where('RequestCode', trim($codeToSearch))->get()->row_array();
                if (!empty($codeRow)) {
                    $requestId = (int)$codeRow['RequestId'];
                    if (!empty($codeRow['ConvertedJid']) && (int)$codeRow['ConvertedJid'] > 0) {
                        $jid = (int)$codeRow['ConvertedJid'];
                    }
                }
            }
        }

        if ($jid <= 0 && $requestId > 0) {
            $req = $this->db->get_where('resource_requests', ['RequestId' => $requestId])->row_array();
            if (!empty($req)) {
                if (!empty($req['ConvertedJid']) && (int)$req['ConvertedJid'] > 0) {
                    $jid = (int)$req['ConvertedJid'];
                } else {
                    $year = date("Y");
                    $count = $this->db->count_all("ihrjobslist") + 1;
                    do {
                        $jobCode = "JOB-" . $year . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
                        $exists  = $this->db->where("JobCode", $jobCode)->count_all_results("ihrjobslist");
                        if ($exists) $count++;
                    } while ($exists > 0);

                    $vacancyData = [
                        "JobCode"              => $jobCode,
                        "JobTitle"             => isset($req["JobTitle"]) ? $req["JobTitle"] : '',
                        "RoleSummary"          => !empty($req["FunctionalRole"]) ? $req["FunctionalRole"] : (isset($req["JobTitle"]) ? $req["JobTitle"] : ''),
                        "Did"                  => !empty($req["Did"]) ? $req["Did"] : null,
                        "EmploymentType"       => $this->input->post('employmentType') ?: 'Full-Time',
                        "WorkMode"             => $this->input->post('workMode') ?: 'Onsite',
                        "EducationRequired"    => $this->input->post('education') ?: ($req["EducationRequired"] ?? ''),
                        "ExpMin"               => $this->input->post('expMin') ?: ($req["ExpMin"] ?? 0),
                        "ExpMax"               => $this->input->post('expMax') ?: ($req["ExpMax"] ?? 0),
                        "Salary"               => $this->input->post('salary') ? trim($this->input->post('salary')) : ($req["Salary"] ?? ''),
                        "NoofOpenings"         => $this->input->post('positions') ?: ($req["NoofOpenings"] ?? 1),
                        "JobStatus"            => "Open",
                        "JobDescription"       => $this->input->post('JD') ?: ($req["JobDescription"] ?? ''),
                        "Responsibilities"     => $this->input->post('RR') ?: ($req["Responsibilities"] ?? ''),
                        "PostedBy"             => $Hrms_Session["IUid"],
                        "CtcApproverId"        => $this->input->post('CtcApproverId') ? (int)$this->input->post('CtcApproverId') : null,
                        "PostedOn"             => date("Y-m-d H:i:s")
                    ];

                    $this->db->insert("ihrjobslist", $vacancyData);
                    $jid = $this->db->insert_id();

                    if ($jid) {
                        $this->db->where('RequestId', $requestId)->update('resource_requests', ['ConvertedJid' => $jid]);
                    }
                }
            }
        }

        if (empty($jid)){
            echo json_encode(['status'=>'error','msg'=>'JID missing']);
            return;
        }

        $data = [];
        $data['UpdatedOn'] = date('Y-m-d H:i:s');

        $post_employmentType = $this->input->post('employmentType');
        if ($post_employmentType !== null && $post_employmentType !== '') $data['EmploymentType'] = $post_employmentType;

        $post_workMode = $this->input->post('workMode');
        if ($post_workMode !== null && $post_workMode !== '') $data['WorkMode'] = $post_workMode;

        $post_education = $this->input->post('education');
        if ($post_education !== null) $data['EducationRequired'] = $post_education;

        $post_positions = $this->input->post('positions');
        if ($post_positions !== null && $post_positions !== '') $data['NoofOpenings'] = (int)$post_positions;

        $post_expMin = $this->input->post('expMin');
        if ($post_expMin !== null && $post_expMin !== '') $data['ExpMin'] = $post_expMin;

        $post_expMax = $this->input->post('expMax');
        if ($post_expMax !== null && $post_expMax !== '') $data['ExpMax'] = $post_expMax;

        $post_jobLocation = $this->input->post('jobLocation');
        if ($post_jobLocation !== null) $data['JobLocation'] = $post_jobLocation;

        $post_commLang = $this->input->post('comLanguage');
        if ($post_commLang !== null) $data['CommunicationLang'] = $post_commLang;

        $post_JD = $this->input->post('JD');
        if ($post_JD !== null) $data['JobDescription'] = $post_JD;

        $post_RR = $this->input->post('RR');
        if ($post_RR !== null) $data['Responsibilities'] = $post_RR;

        $salaryInput = $this->input->post('salary');
        $data['Salary'] = ($salaryInput !== null) ? trim((string)$salaryInput) : '';

        $mustHaveSkills   = $this->input->post('mustHaveSkills');
        $niceToHaveSkills = $this->input->post('niceToHaveSkills');
        if ($mustHaveSkills !== null)   $data['MustHaveSkills']   = is_array($mustHaveSkills)   ? implode(', ', $mustHaveSkills)   : trim((string)$mustHaveSkills);
        if ($niceToHaveSkills !== null) $data['NiceToHaveSkills'] = is_array($niceToHaveSkills) ? implode(', ', $niceToHaveSkills) : trim((string)$niceToHaveSkills);

        $mustHaveVal   = isset($data['MustHaveSkills'])   ? $data['MustHaveSkills']   : '';
        $niceToHaveVal = isset($data['NiceToHaveSkills']) ? $data['NiceToHaveSkills'] : '';
        $allSkillsList = array_unique(array_filter(array_map('trim', explode(',', $mustHaveVal . ', ' . $niceToHaveVal))));
        if (!empty($allSkillsList)) {
            $combinedSkillsCsv = implode(', ', $allSkillsList);
            $this->saveSkills($combinedSkillsCsv);
            $this->saveJobSkills($jid, $combinedSkillsCsv);
        }

        $ctcInput = $this->input->post('CtcApproverId');
        $data['CtcApproverId'] = ($ctcInput !== null && $ctcInput !== '') ? (int)$ctcInput : null;

        $updateResult = $this->db->where('Jid', $jid)->update('IHRJobsList', $data);
        if (!$updateResult) {
            $lastError = $this->db->error();
            echo json_encode(['status' => 'error', 'msg' => 'DB update failed: ' . ($lastError['message'] ?? 'Unknown error'), 'sql' => $this->db->last_query()]);
            return;
        }

        $rrSync = [];
        if (isset($data['Salary'])) $rrSync['Salary'] = $data['Salary'];
        if (isset($data['JobLocation'])) $rrSync['JobLocation'] = $data['JobLocation'];
        if (isset($data['EducationRequired'])) $rrSync['EducationRequired'] = $data['EducationRequired'];
        if (isset($data['MustHaveSkills'])) $rrSync['MustHaveSkills'] = $data['MustHaveSkills'];
        if (isset($data['NiceToHaveSkills'])) $rrSync['NiceToHaveSkills'] = $data['NiceToHaveSkills'];
        if (isset($data['CommunicationLang'])) $rrSync['CommunicationLang'] = $data['CommunicationLang'];
        if (isset($data['JobDescription'])) $rrSync['JobDescription'] = $data['JobDescription'];
        if (isset($data['Responsibilities'])) $rrSync['Responsibilities'] = $data['Responsibilities'];
        if (isset($data['CtcApproverId'])) $rrSync['CtcApproverId'] = $data['CtcApproverId'];
        if (isset($data['ExpMin'])) $rrSync['ExpMin'] = $data['ExpMin'];
        if (isset($data['ExpMax'])) $rrSync['ExpMax'] = $data['ExpMax'];

        if (!empty($rrSync)) {
            if ($requestId > 0) {
                $this->db->where('RequestId', $requestId)->update('resource_requests', $rrSync);
            } else {
                $this->db->where('ConvertedJid', $jid)->update('resource_requests', $rrSync);
            }
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS JobInterviewPanels (
            PanelId INT AUTO_INCREMENT PRIMARY KEY,
            Jid INT NOT NULL,
            LevelOrder INT NOT NULL,
            InterviewerId INT NOT NULL,
            CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (Jid)
        )");

        $interviewPanel = $this->input->post('interviewPanel');
        if ($interviewPanel !== null) {
            $this->db->where('Jid', $jid)->delete('JobInterviewPanels');
            if (is_array($interviewPanel)) {
                foreach ($interviewPanel as $lvl => $interviewerId) {
                    $interviewerId = (int)$interviewerId;
                    if ($interviewerId > 0) {
                        $this->db->insert('JobInterviewPanels', [
                            'Jid' => $jid,
                            'LevelOrder' => (int)$lvl,
                            'InterviewerId' => $interviewerId
                        ]);
                    }
                }
            }
        }

       

        $skills = $this->input->post('skills'); 

        $this->db->where('Jid',$jid)->delete('JobSkills');

        if(!empty($skills) && is_array($skills)){

           foreach($skills as $skillName){

     $skillName = trim($skillName);
     if($skillName=='') continue;

  
     $row = $this->db->where('SkillName',$skillName)
                     ->get('IHSkills')
                     ->row();

    
     if(!$row){

       $this->db->insert('IHSkills',[
         'SkillName'=>$skillName
       ]);

       $skillId = $this->db->insert_id();

     }else{

       $skillId = $row->SkillId;
     }

    
     $this->db->insert('jobskills',[
       'Jid'=>$jid,
       'SkillId'=>$skillId
     ]);
    }
        }

        echo json_encode(['status'=>'success']);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}



public function getNextStages()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $currentOrder = (int)$this->input->post('currentOrder');

       
        $currentStage = $this->db
            ->where('StageOrder', $currentOrder)
            ->get('RecruitmentStages')
            ->row();

        $currentGroup = $currentStage ? $currentStage->StageGroup : 'Application';

       
        $this->db->group_start()
            ->group_start()
                ->where('StageGroup', $currentGroup)
                ->where('StageOrder >', $currentOrder)
            ->group_end()
            ->or_where('StageGroup', 'Rejection')
        ->group_end();

        $this->db->where('StageStatus', 1);
        $this->db->order_by('StageOrder','ASC');

        echo json_encode($this->db->get('RecruitmentStages')->result());

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}




public function saveCandidateStage()
{

    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $candidateId   = $this->input->post('candidateId');
        $stageId       = $this->input->post('stageId');
        $action        = $this->input->post('action');
        $remarks       = $this->input->post('remarks');
        $followupType  = $this->input->post('followupType');
        $nextDate      = $this->input->post('nextFollowupDate');
        $interviewDate = $this->input->post('interviewDate');

       
        if (!empty($interviewDate)) {
           
            $interviewDate = str_replace('T', ' ', $interviewDate);
            
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $interviewDate)) {
                $interviewDate .= ':00';
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $interviewDate)) {
              
                $interviewDate .= ' 00:00:00';
            }
        }
        $level         = $this->input->post('interviewLevel');
        $interviewType = $this->input->post('interviewType');
        $interviewerId = $this->input->post('interviewerId');

        $actionLower = strtolower(trim($action));

        if ($actionLower == 'shortlisted' || $actionLower == 'reschedule') {
            if (empty($interviewDate)) {
                echo json_encode(['status' => 'error', 'msg' => 'Interview schedule date is required.']);
                return;
            }
        }

       
        $debugLog  = date('Y-m-d H:i:s') . " saveCandidateStage POST:\n";
        $debugLog .= "  action        = " . $this->input->post('action')        . "\n";
        $debugLog .= "  interviewDate = " . $this->input->post('interviewDate')  . "\n";
        $debugLog .= "  interviewDate_normalized = " . $interviewDate . "\n";
        $debugLog .= "  interviewType = " . $interviewType . "\n";
        $debugLog .= "  interviewerId = " . $interviewerId . "\n";
        $debugLog .= "  interviewLevel= " . $level . "\n";
        $debugLog .= "---\n";
        file_put_contents(FCPATH . 'interview_debug.log', $debugLog, FILE_APPEND);
       

        $actionLower = strtolower(trim($action));

        if(($actionLower == 'shortlisted' || $actionLower == 'reschedule') && !empty($level)){
            $stageId = $level;
        }

        if($actionLower == 'rejected' && empty($stageId)){
           
            $fallback = $this->db
                ->where('StageGroup', 'Rejection')
                ->where('StageStatus', 1)
                ->order_by('StageOrder', 'ASC')
                ->get('RecruitmentStages')
                ->row();
            if($fallback){
                $stageId = $fallback->StageId;
            }
        }

     
        $app = $this->db->select('ApplicationId')->from('JobApplications')->where('CandidateId',$candidateId)
                        ->order_by('ApplicationId','DESC')->limit(1)->get()->row();

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = $app->ApplicationId;

      
        $this->db->insert('CandidateStageTracking',[
            'ApplicationId' => $applicationId,
            'StageId'       => $stageId,
            'Action'        => $action,
            'ActionBy'      => $Hrms_Session['IUid'],
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => $remarks
        ]);

  
    $currentStatus = 'In Progress';

    $actLower = strtolower(trim($action));
    if($actLower == 'cv screened' || $actLower == 'screened'){
        $currentStatus = 'CV Screened';
    }
    elseif($actLower == 'cv rejected' || $actLower == 'not interested' || $actLower == 'not intrested'){
        $currentStatus = 'CV Rejected';
    }
    elseif($actLower == 'rejected'){
        $currentStatus = 'Rejected';
    }
    elseif($actLower == 'on hold'){
        $currentStatus = 'On Hold';
    }
    elseif($actLower == 'reschedule'){
        $currentStatus = 'Rescheduled';
    }
    elseif($actLower == 'shortlisted' && !empty($level)){
        if (is_numeric($level)) {
            $stageRow = $this->db->where('StageId', $level)->get('recruitmentstages')->row();
            if($stageRow && strtolower(trim($stageRow->StageName)) !== 'hr'){
                $currentStatus = $stageRow->StageName;   
            } else {
                $currentStatus = 'Level ' . $level;
            }
        } else {
            $currentStatus = $level;
        }
    }
    elseif(!empty($stageId)){
        $stageRow = $this->db->where('StageId', $stageId)->get('recruitmentstages')->row();
        if($stageRow){
            $sNameLower = strtolower(trim($stageRow->StageName));
            if(strpos($sNameLower, 'screen') !== false){
                $currentStatus = 'CV Screened';
            } else if(strpos($sNameLower, 'reject') !== false){
                $currentStatus = 'CV Rejected';
            } else {
                $currentStatus = $stageRow->StageName;
            }
        }
    }

    $updateJobApp = ['CurrentStatus' => $currentStatus];
    if(!empty($stageId)){
        $updateJobApp['CurrentStage'] = $stageId;
    }
    $this->db->where('ApplicationId',$applicationId)->update('JobApplications', $updateJobApp);
    $this->db->where('CandidateId', $candidateId)->update('IHrCandidates', ['ATS_Status' => $currentStatus]);

                


        
        $isFollowupStage = false;
        if(!empty($stageId)){
            $stageRow = $this->db->where('StageId', $stageId)->get('RecruitmentStages')->row();
            if($stageRow){
                $stageNameLower = strtolower(trim($stageRow->StageName));
                if($stageNameLower === 'switch off' || $stageNameLower === 'rnr'){
                    $isFollowupStage = true;
                }
            }
        }

        if($isFollowupStage && !empty($followupType)){
            $this->db->insert('CandidateFollowUps',[
                'ApplicationId'     => $applicationId,
                'FollowUpType'      => $followupType,
                'FollowUpNotes'     => $remarks,
                'NextFollowUpDate'  => $nextDate,
                'CreatedBy'         => $Hrms_Session['IUid'],
                'CreatedAt'         => date('Y-m-d H:i:s')
            ]);
        }

        if ($actLower == 'cv screened' || $actLower == 'screened' || $actLower == 'on hold' || $actLower == 'cv rejected') {
            echo json_encode([
                'status' => 'success',
                'msg'    => 'Candidate status updated to "' . $currentStatus . '" successfully.'
            ]);
            return;
        }

      
    if((strtolower($action) == 'shortlisted' || strtolower($action) == 'reschedule') && empty($interviewerId)){

        $this->db->where('ApplicationId',$applicationId)
                 ->update('JobApplications', [
                     'CurrentStatus' => 'Rejected'
                 ]);

        $data['candidatelist'] = $this->db
            ->where('CandidateId',$candidateId)
            ->get('IHRCandidates')
            ->row();

        if(empty($data['candidatelist']) || empty($data['candidatelist']->Email)){
            echo json_encode(['status'=>'error','msg'=>'Candidate email missing']);
            return;
        }

        $data['action'] = 'rejected';

        try {

            $to = $data['candidatelist']->Email;

            $subject = "Application Status - I-Net Secure Labs Pvt Ltd.";

            require(APPPATH.'libraries/InetMailer.php');
            $objs = new InetMailer();
            $mail = $objs->load();

            $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
            $mail->addAddress(trim($to));

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $this->load->view('admin/CandisateEmail',$data,TRUE);

            if(!$mail->send()){
                echo json_encode(['status'=>'error','msg'=>$mail->ErrorInfo]);
            } else {
                echo json_encode(['status'=>'rejected','msg'=>'Rejected mail sent']);
            }

        } catch (\Exception $e) {
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }

        return;
    }
            
            $existingCount = $this->db->where('ApplicationId', $applicationId)->count_all_results('CandidateInterviews');
            $isReschedule = (strtolower($action) == 'reschedule');

            if ($isReschedule) {
                $this->db->where('ApplicationId', $applicationId)
                         ->group_start()
                             ->where('Result', 'Assigned')
                             ->or_where('Result IS NULL', null, false)
                             ->or_where('Result', '')
                         ->group_end()
                         ->update('CandidateInterviews', ['Result' => 'Rescheduled']);

                $targetRound = max(1, $existingCount + 1);
            } else {
                $targetRound = $existingCount + 1;
            }

            $meetLink = '';
            if (!empty($interviewType) && strtolower($interviewType) === 'online') {
                if (!$this->db->field_exists('MeetLink', 'CandidateInterviews')) {
                    $this->db->query("ALTER TABLE CandidateInterviews ADD COLUMN MeetLink TEXT NULL");
                }
                $candMeta = $this->db
                    ->select('c.Fullname, c.CandidateCode, j.JobCode, j.JobTitle')
                    ->from('IHRCandidates c')
                    ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
                    ->where('c.CandidateId', $candidateId)
                    ->get()->row();

                $candName = $candMeta ? trim($candMeta->Fullname) : 'Candidate';
                $jobTitle = $candMeta ? trim($candMeta->JobTitle) : 'Position';
                $subject  = "Interview - {$candName} ({$jobTitle})";

                $this->load->library('ms_graph_teams');
                $teamsResult = $this->ms_graph_teams->createTeamsMeeting($subject, $interviewDate, 45);

                if (!empty($teamsResult['status']) && !empty($teamsResult['joinWebUrl'])) {
                    $meetLink = $teamsResult['joinWebUrl'];
                } else {
                    $errMessage = !empty($teamsResult['message']) ? $teamsResult['message'] : 'Unable to create Microsoft Teams meeting. Please try again.';
                    echo json_encode(['status' => 'error', 'message' => $errMessage, 'msg' => $errMessage]);
                    return;
                }
            }

            $interviewDataToSave = [
                'ApplicationId'  => $applicationId,
                'InterviewRound' => $targetRound,
                'InterviewType'  => $interviewType,
                'InterviewerId'  => $interviewerId,
                'Result'         => 'Assigned'
            ];

            if (!empty($interviewDate) && $interviewDate !== '0000-00-00 00:00:00') {
                $interviewDataToSave['ScheduledAt'] = $interviewDate;
            }

            if (!empty($meetLink)) {
                $interviewDataToSave['MeetLink'] = $meetLink;
            }

            $this->db->insert('CandidateInterviews', $interviewDataToSave);
            $interviewId = $this->db->insert_id();
            $insertOrUpdateLog = "INSERT CandidateInterviews (ID: $interviewId, Rescheduled)";

            $debugLog  = date('Y-m-d H:i:s') . " saveCandidateStage DB Save Details:\n";
            $debugLog .= "  Operation        = " . $insertOrUpdateLog . "\n";
            $debugLog .= "  ApplicationId    = " . $applicationId . "\n";
            $debugLog .= "  InterviewRound   = " . $targetRound . "\n";
            $debugLog .= "  InterviewType    = " . $interviewType . "\n";
            $debugLog .= "  ScheduledAt      = " . ($interviewDataToSave['ScheduledAt'] ?? 'OMITTED/NULL') . "\n";
            $debugLog .= "  InterviewerId    = " . $interviewerId . "\n";
            $debugLog .= "  MeetLink         = " . ($meetLink ?: 'N/A') . "\n";
            $debugLog .= "---\n";
            file_put_contents(FCPATH . 'interview_debug.log', $debugLog, FILE_APPEND);

           
            $this->load->model('Notification_model');
            $notifTitle = ($isReschedule) ? 'Interview Rescheduled' : 'New Interview Scheduled';
            $notifMsg = "An interview ($interviewType) has been scheduled/rescheduled on " . ($interviewDataToSave['ScheduledAt'] ?? '-') . ".";
            $this->Notification_model->addNotification($notifTitle, $notifMsg, 'info', $interviewerId, null);
         

           
            $this->db->where('ApplicationId',$applicationId)->update('JobApplications',['CurrentStage' => $level]);
            


                $data['candidatelist'] = $this->db->select('*')->from('IHRCandidates')
                    ->where('CandidateId', $candidateId)->get()->row();
                $data['action'] = strtolower($action);

                $jobRow = $this->db
                    ->select('j.JobTitle, j.JobCode')
                    ->from('IHRCandidates c')
                    ->join('IHRJobsList j', 'j.Jid = c.Jid', 'left')
                    ->where('c.CandidateId', $candidateId)
                    ->get()->row();

                $data['jobTitle']      = $jobRow ? $jobRow->JobTitle : 'Job Position';
                $data['interviewDate'] = $interviewDate;
                $data['interviewTime'] = $this->input->post('interviewTime');
                $data['interviewMode'] = $interviewType;
                $data['meetLink']      = $meetLink;

                $interviewer = $this->db->select('EmpName, EmpEmail')
                    ->where('IUid', $interviewerId)->get('IHUsers')->row();
                $data['interviewerName']  = $interviewer ? $interviewer->EmpName  : 'Interviewer';
                $data['interviewerEmail'] = $interviewer ? trim($interviewer->EmpEmail) : '';

                $levelRow = $this->db->where('StageId', $level)->get('RecruitmentStages')->row();
                $data['interviewLevelName'] = $levelRow ? $levelRow->StageName : 'Interview';

                try {
                    require_once(APPPATH . 'libraries/InetMailer.php');
                    $candidateEmail = trim($data['candidatelist']->Email);
                    $modeLower = strtolower(trim($interviewType));

                    $emailLog = date('Y-m-d H:i:s') . " Interview Email:\n";
                    $emailLog .= "  Mode          = " . $modeLower . "\n";
                    $emailLog .= "  CandidateMail = " . $candidateEmail . "\n";
                    $emailLog .= "  InterviewerMail = " . $data['interviewerEmail'] . "\n";

                    if ($modeLower === 'online' || $modeLower === 'offline') {
                        $objs = new InetMailer();
                        $mail = $objs->load();
                        $mail->Timeout = 15; 

                        $isRescheduleAction = (strtolower($action) === 'reschedule');

                        $candidateSent = false;
                        try {
                            if ($isRescheduleAction) {
                                $candSubject = ($modeLower === 'offline')
                                    ? "Interview Rescheduled (Call Letter) \xe2\x80\x93 " . $data['jobTitle'] . " | I-NET CSC"
                                    : "Interview Rescheduled \xe2\x80\x93 " . $data['jobTitle'] . " | I-NET CSC";
                            } else {
                                $candSubject = ($modeLower === 'offline') 
                                    ? "Interview Call Letter \xe2\x80\x93 " . $data['jobTitle'] . " | I-NET CSC"
                                    : "Interview Scheduled \xe2\x80\x93 " . $data['jobTitle'] . " | I-NET CSC";
                            }

                            $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
                            $mail->addAddress($candidateEmail);
                            $mail->isHTML(true);
                            $mail->Subject = $candSubject;
                            $mail->Body    = $this->load->view('admin/CandisateEmail', $data, TRUE);
                            $candidateSent = $mail->send();
                            $emailLog .= "  CandidateSend = " . ($candidateSent ? 'OK' : $mail->ErrorInfo) . "\n";
                        } catch (\Exception $ce) {
                            $emailLog .= "  CandidateSend = EXCEPTION: " . $ce->getMessage() . "\n";
                        }

                        $interviewerSent = false;
                        if (!empty($data['interviewerEmail'])) {
                            try {
                                $mail->clearAddresses();
                                $mail->clearAttachments();
                                $mail->addAddress($data['interviewerEmail']);
                                $mail->Subject = ($isRescheduleAction ? "Interview Rescheduled \xe2\x80\x93 " : "Interview Assignment \xe2\x80\x93 ") . $data['candidatelist']->Fullname . " | " . $data['jobTitle'];
                                $mail->Body    = $this->load->view('admin/InterviewerEmail', $data, TRUE);
                                $interviewerSent = $mail->send();
                                $emailLog .= "  InterviewerSend = " . ($interviewerSent ? 'OK' : $mail->ErrorInfo) . "\n";
                            } catch (\Exception $ie) {
                                $emailLog .= "  InterviewerSend = EXCEPTION: " . $ie->getMessage() . "\n";
                            }
                        } else {
                            $emailLog .= "  InterviewerSend = SKIPPED (no email address)\n";
                        }

                        file_put_contents(FCPATH . 'interview_debug.log', $emailLog . "---\n", FILE_APPEND);

                        if ($isRescheduleAction) {
                            $msg = 'Interview rescheduled. Notification sent to candidate and interviewer.';
                        } else {
                            $msg = ($modeLower === 'offline')
                                ? 'Call letter sent to candidate and notification sent to interviewer.'
                                : 'Interview scheduled. Meet link sent to candidate and interviewer.';
                        }
                        echo json_encode(['status' => 'success', 'msg' => $msg]);

                    } else {
                        try {
                            $objs = new InetMailer();
                            $mail = $objs->load();
                            $mail->Timeout = 15;
                            $subject = (strtolower($action) === 'reschedule')
                                ? "Interview Rescheduled \xe2\x80\x93 I-NET CSC"
                                : "Congratulations! You are Shortlisted \xe2\x80\x93 I-NET CSC";
                            $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
                            $mail->addAddress($candidateEmail);
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            $mail->Body    = $this->load->view('admin/CandisateEmail', $data, TRUE);
                            $mail->send();
                        } catch (\Exception $fe) {}

                        file_put_contents(FCPATH . 'interview_debug.log', $emailLog . "---\n", FILE_APPEND);
                        echo json_encode(['status' => 'success', 'msg' => 'Email sent successfully.']);
                    }

                } catch (\Exception $e) {
                    echo json_encode(['status' => 'failed', 'msg' => 'Stage saved but email failed: ' . $e->getMessage()]);
                }

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
} 

    public function getInterviewLevels()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $this->db->where('StageGroup', 'Interview');
        $this->db->where('StageStatus', 1);
        $this->db->order_by('StageOrder','ASC');

        echo json_encode($this->db->get('RecruitmentStages')->result_array());

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function MyInterviews()
{



    $Hrms_Session = $this->session->userdata('logged_in');
    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $uid = $Hrms_Session['IUid'];

        $this->db->query("
            UPDATE CandidateInterviews ci1 
            JOIN CandidateInterviews ci2 
              ON ci1.ApplicationId = ci2.ApplicationId 
             AND ci1.InterviewId < ci2.InterviewId 
            SET ci1.Result = 'Rescheduled' 
            WHERE (ci1.Result = 'Assigned' OR ci1.Result IS NULL OR ci1.Result = '')
        ");

        $data['Candidatelist'] = $this->db
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

        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);

        $this->template->write_view('content','admin/my_interviews',$data);
        $this->template->render();

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function interviewCalendar()
{
    $Hrms_Session = $this->session->userdata('logged_in');
    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $uid = $Hrms_Session['IUid'];

        $this->db->query("
            UPDATE CandidateInterviews ci1 
            JOIN CandidateInterviews ci2 
              ON ci1.ApplicationId = ci2.ApplicationId 
             AND ci1.InterviewId < ci2.InterviewId 
            SET ci1.Result = 'Rescheduled' 
            WHERE (ci1.Result = 'Assigned' OR ci1.Result IS NULL OR ci1.Result = '')
        ");

        $interviews = $this->db
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
            ->group_by('ci.InterviewId')
            ->order_by('ci.ScheduledAt', 'ASC')
            ->get()
            ->result_array();

        
        $events = [];
        foreach($interviews as $iv) {
            if(empty($iv['ScheduledAt'])) continue;

            $result = strtolower(trim($iv['Result'] ?? ''));
            if($result == 'selected') {
                $color = '#28a745';
            } elseif($result == 'rejected') {
                $color = '#dc3545';
            } elseif($result == 'on hold') {
                $color = '#fd7e14';
            } elseif($result == 'rescheduled') {
                $color = '#ffc107'; 
            } else {
                $color = '#007bff';
            }

            $eventTitle = $iv['Fullname'] . ' (' . $iv['CandidateCode'] . ')';
            if ($result === 'rescheduled') {
                $eventTitle .= ' [Rescheduled]';
            }

            $events[] = [
                'id'             => $iv['InterviewId'],
                'title'          => $eventTitle,
                'start'          => $iv['ScheduledAt'],
                'end'            => date('Y-m-d H:i:s', strtotime($iv['ScheduledAt']) + 3600),
                'color'          => $color,
                'extendedProps'  => [
                    'candidateId'   => $iv['CandidateId'],
                    'email'         => $iv['Email'],
                    'phone'         => $iv['PhoneNo'],
                    'jobTitle'      => $iv['JobTitle'],
                    'result'        => $iv['Result'] ?? 'Assigned',
                    'round'         => $iv['InterviewRound'] ?? 1,
                    'interviewId'   => $iv['InterviewId'],
                    'status'        => $iv['CurrentStatus'],
                ]
            ];
        }

        $data['calendarEvents']  = json_encode($events);
        $data['Candidatelist']   = $interviews;

        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);

        $this->template->write_view('content','admin/interview_calendar',$data);
        $this->template->render();

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}





public function getInterviewDetails()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $interviewId = trim($this->input->post('interviewId') ?? '');

        if (empty($interviewId)) {
            echo json_encode(['status' => 'error', 'msg' => 'Interview ID is required']);
            return;
        }

        $interview = $this->db
            ->select('InterviewId, Result, Feedback, SkillScore, CommunicationScore, ProblemSolvingScore, CultureFitScore, LeadershipScore, OverallScore')
            ->where('InterviewId', $interviewId)
            ->get('CandidateInterviews')
            ->row_array();

        if (!$interview) {
            echo json_encode(['status' => 'error', 'msg' => 'Interview record not found']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data'   => $interview
        ]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid session. Please login again.']);
    }
}

public function updateInterviewResult()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $interviewId      = trim($this->input->post('interviewId') ?? '');
        $result           = trim($this->input->post('result') ?? '');
        $feedback         = trim($this->input->post('feedback') ?? '');

        $skill          = filter_var($this->input->post('skillScore'), FILTER_VALIDATE_INT);
        $communication  = filter_var($this->input->post('communicationScore'), FILTER_VALIDATE_INT);
        $problemSolving = filter_var($this->input->post('problemSolvingScore'), FILTER_VALIDATE_INT);
        $cultureFit     = filter_var($this->input->post('cultureFitScore'), FILTER_VALIDATE_INT);
        $leadership     = filter_var($this->input->post('leadershipScore'), FILTER_VALIDATE_INT);

        if (empty($interviewId)) {
            echo json_encode(['status' => 'error', 'msg' => 'Missing Interview ID']);
            return;
        }

        if (empty($result)) {
            echo json_encode(['status' => 'error', 'msg' => 'Please select an Opinion / Result']);
            return;
        }

        
        $scores = [
            'Skill'           => $skill,
            'Communication'   => $communication,
            'Problem Solving' => $problemSolving,
            'Culture Fit'     => $cultureFit,
            'Leadership'      => $leadership
        ];

        foreach ($scores as $label => $val) {
            if ($val === false || $val < 1 || $val > 5) {
                echo json_encode([
                    'status' => 'error',
                    'msg'    => "Please provide ratings for all interview evaluation criteria. Invalid or missing rating for {$label}."
                ]);
                return;
            }
        }

        $overallScore = round(($skill + $communication + $problemSolving + $cultureFit + $leadership) / 5.0, 2);

        $interview = $this->db
            ->where('InterviewId', $interviewId)
            ->get('CandidateInterviews')  
            ->row();

        if (!$interview) {
            echo json_encode(['status' => 'error', 'msg' => 'Interview record not found']);
            return;
        }

        $applicationId = $interview->ApplicationId;

        if (empty($applicationId)) {
            echo json_encode(['status' => 'error', 'msg' => 'Application ID missing']);
            return;
        }

        $this->db->where('InterviewId', $interviewId)
                 ->update('CandidateInterviews', [
                    'Result'               => $result,
                    'Feedback'             => $feedback,
                    'SkillScore'           => $skill,
                    'CommunicationScore'   => $communication,
                    'ProblemSolvingScore'  => $problemSolving,
                    'CultureFitScore'      => $cultureFit,
                    'LeadershipScore'      => $leadership,
                    'OverallScore'         => $overallScore
                 ]);

        $app = $this->db
            ->select('CurrentStage, StageId')
            ->where('ApplicationId', $applicationId)
            ->get('jobapplications')
            ->row();

        $stageIdToUse = '';
        if ($app) {
            if (!empty($app->CurrentStage)) {
                $stageIdToUse = $app->CurrentStage;
            } elseif (!empty($app->StageId)) {
                $stageIdToUse = $app->StageId;
            }
        }

        $stageName = '';
        if (!empty($stageIdToUse)) {
            $stageRow = $this->db
                ->where('StageId', $stageIdToUse)
                ->get('RecruitmentStages')
                ->row();

            if ($stageRow) {
                $stageName = $stageRow->StageName;
            }
        }

        if (empty($stageName)) {
            $statusToSave = ucwords(strtolower($result));
        } else {
            $statusToSave = $stageName . ' Round Completed - ' . ucwords(strtolower($result));
        }

        $this->db->where('ApplicationId', $applicationId)
                 ->update('JobApplications', [   
                    'CurrentStatus' => $statusToSave
                 ]);

        $this->load->model('Notification_model');
        $notifTitle = 'Interview Evaluation Submitted';
        $notifMsg   = "Interview evaluation for Application ID $applicationId has been submitted with Overall Score: $overallScore / 5 ($result).";

        $this->Notification_model->addNotification($notifTitle, $notifMsg, 'success', null, 1);
        $this->Notification_model->addNotification($notifTitle, $notifMsg, 'success', null, 2);

        echo json_encode([
            'status'       => 'success',
            'saved_status' => $statusToSave,
            'overallScore' => $overallScore
        ]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid Session. Please Login Again..!!']);
    }
}

public function vacancies()
{
   
    $Hrms_Session = $this->session->userdata('logged_in');

	if (isset($Hrms_Session) && !empty($Hrms_Session)) {
 
    $this->db->select("jl.*, d.Departmentname, (SELECT COUNT(DISTINCT ja.ApplicationId) FROM JobApplications ja WHERE ja.Jid = jl.Jid) AS CandidateCount", false);
    $this->db->from('IHRJobsList jl');
    $this->db->join('Departments d','d.Did = jl.Did','left');

    $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
    $currentUserId = (int)$Hrms_Session['IUid'];

    if ($roleId === 10 || $roleId === 11) { 
        $this->db->group_start();
        $this->db->where('jl.AssignedRecruiterManagerId', $currentUserId);
        $this->db->or_group_start();
        $this->db->where('jl.AssignedRecruiterManagerId IS NULL', null, false);
        $this->db->where('jl.PostedBy', $currentUserId);
        $this->db->group_end();
        $this->db->group_end();
    }
 
    $dateRange  = $this->input->post('dateRange', TRUE) ?: $this->input->get('dateRange', TRUE);
    $department = $this->input->post('department', TRUE) ?: $this->input->get('department', TRUE);
    $status     = $this->input->post('status', TRUE) ?: $this->input->get('status', TRUE);
 
   
    if (!empty($dateRange)) {
 
        $dates = explode(' - ', $dateRange);
 
        if (count($dates) == 2) {
            $start = $dates[0];
            $end   = $dates[1];
 
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
 
    $data['vaclist'] = $this->db->get()->result_array();
    $data['department'] = $this->admin_model->getUserDepartments();
        $data['ctc_approvers'] = $this->admin_model->getAllUsers();
 
    $currentUrl = strtolower(uri_string());
    $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);
 
    $this->template->write_view('content', 'admin/VaccancyList', $data);
    $this->template->render();}
    else{
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function filterAssignedInterviews()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $status = trim($this->input->post('status') ?? '');

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

        $session = $this->session->userdata('logged_in');
        $this->db->where('ci.InterviewerId', $session['IUid']);
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

        $data = $this->db->get()->result_array();

        if (empty($data)) {
            echo "<tr><td colspan='12' class='text-center text-muted font-weight-bold py-4'><i class='fas fa-info-circle mr-2 text-info'></i>No scheduled interviews found for the selected status.</td></tr>";
            return;
        }

        $i = 1;
        foreach ($data as $cl) {
            $mode     = !empty($cl['InterviewType']) ? trim($cl['InterviewType']) : '';
            $meetLink = !empty($cl['MeetLink']) ? trim($cl['MeetLink']) : '';
            $resultVal   = !empty($cl['Result']) ? trim($cl['Result']) : 'Assigned';
            $resultLower = strtolower($resultVal);
            $isRescheduledRow = ($resultLower === 'rescheduled');
            $trClass = $isRescheduledRow ? 'style="background-color: #fff9e6;"' : '';
            $jobTitleStr = !empty($cl['JobTitle']) ? htmlspecialchars($cl['JobTitle']) : 'N/A';
            $roleStr     = !empty($cl['Role']) ? htmlspecialchars($cl['Role']) : '';

            echo "<tr {$trClass}>";
            echo "<td class='text-center font-weight-bold'>".$i++."</td>";
            echo "<td style='white-space: nowrap;'><a href='".base_url('admin/viewResume/'.$cl['CandidateId'])."' target='_blank' class='text-dark font-weight-bold'>".htmlspecialchars($cl['CandidateCode'])."</a></td>";
            echo "<td style='white-space: nowrap;'><a href='javascript:void(0);' class='viewCandidateDetails text-dark font-weight-bold' data-id='".$cl['CandidateId']."'>".htmlspecialchars($cl['Fullname'])."</a></td>";
            echo "<td style='white-space: nowrap;'>";
            echo "<div class='font-weight-bold text-dark mb-0'>{$jobTitleStr}</div>";
            if (!empty($roleStr)) {
                echo "<div class='small text-muted'>{$roleStr}</div>";
            }
            echo "</td>";
            echo "<td style='white-space: nowrap;'><span class='text-dark font-weight-bold'>".htmlspecialchars($cl['PhoneNo'])."</span></td>";
            echo "<td style='white-space: nowrap;'><span class='text-muted small'>".htmlspecialchars($cl['Email'])."</span></td>";

            $recVal = !empty($cl['ProfileMatchPer']) ? $cl['ProfileMatchPer'] : 'Potential Match';
            if ($recVal === 'Recommended') $recVal = 'Strong Match';
            if ($recVal === 'Review Required') $recVal = 'Potential Match';
            if ($recVal === 'Not Recommended') $recVal = 'Low Match';
            $badgeClass = (in_array($recVal, ['Strong Match', 'Strongly Match', 'Recommended'])) ? 'badge-success' : (in_array($recVal, ['Low Match', 'Not Recommended']) ? 'badge-danger' : 'badge-warning');
            echo "<td class='text-center' style='white-space: nowrap;'><span class='badge {$badgeClass} font-weight-bold px-2 py-1'>".htmlspecialchars($recVal)."</span></td>";

            echo "<td class='text-center' style='white-space: nowrap;'>";
            if (strtolower($mode) === 'online') {
                echo "<span class='badge badge-success px-2 py-1'><i class='fas fa-video mr-1'></i>Online</span>";
                if (!empty($meetLink) && !$isRescheduledRow) {
                    echo " <a href='".htmlspecialchars($meetLink)."' target='_blank' class='btn btn-xs btn-outline-success ml-1' title='Join Video Meeting'><i class='fas fa-video mr-1'></i>Join</a>";
                }
            } elseif (strtolower($mode) === 'offline') {
                echo "<span class='badge badge-primary px-2 py-1'><i class='fas fa-building mr-1'></i>Offline</span>";
            } else {
                echo "<span class='badge badge-light px-2 py-1'>".(!empty($mode) ? htmlspecialchars($mode) : 'N/A')."</span>";
            }
            echo "</td>";

            $sAt = $cl['ScheduledAt'] ?? '';
            $sTs = (!empty($sAt) && $sAt !== '0000-00-00 00:00:00') ? strtotime($sAt) : 0;
            $dateFormatted = ($sTs > 0) ? date('d M Y, h:i A', $sTs) : 'Not Scheduled';
            echo "<td style='white-space: nowrap;'>".($sTs > 0 ? "<span class='text-dark font-weight-bold'><i class='fas fa-calendar-alt text-info mr-1'></i>{$dateFormatted}</span>" : "<span class='text-muted small'>Not Scheduled</span>")."</td>";

            echo "<td class='text-center' style='white-space: nowrap;'>";
            if ($isRescheduledRow) {
                echo '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-history mr-1"></i>Rescheduled</span>';
            } elseif ($resultLower === 'selected') {
                echo '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Selected</span>';
            } elseif ($resultLower === 'rejected') {
                echo '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
            } elseif ($resultLower === 'on hold') {
                echo '<span class="badge badge-warning px-2 py-1"><i class="fas fa-pause-circle mr-1"></i>On Hold</span>';
            } else {
                echo '<span class="badge badge-primary px-2 py-1"><i class="fas fa-clock mr-1"></i>' . htmlspecialchars($resultVal) . '</span>';
            }
            echo "</td>";

            echo "<td style='white-space: nowrap;'><span class='small text-muted'>".(!empty($cl['AppliedOn']) ? date('d M Y, h:i A', strtotime($cl['AppliedOn'])) : '-')."</span></td>";

            echo "<td class='text-center'>";
            echo "<button type='button' class='btn btn-xs btn-info viewCandidateDetails mr-1 mb-1' data-id='".$cl['CandidateId']."' title='View Candidate Track Timeline'><i class='fas fa-eye mr-1'></i> View Track</button>";
            echo "<button type='button' class='btn btn-xs btn-primary openAiQuestionsModal mr-1 mb-1' data-interview='".(int)($cl['InterviewId'] ?? 0)."' data-candidate='".htmlspecialchars($cl['Fullname'] ?? '')."' data-job='".htmlspecialchars($cl['JobTitle'] ?? '')."' data-role='".htmlspecialchars($cl['Role'] ?? '')."' data-score='".htmlspecialchars($cl['ProfileMatchPer'] ?? 'N/A')."' title='AI Personalized Interview Questions'><i class='fas fa-brain mr-1'></i> AI Questions</button>";
            if(($resultLower == '' || $resultLower == 'assigned' || $resultLower == 'on hold') && !$isRescheduledRow){
                echo "<button type='button' class='btn btn-xs btn-warning openInterviewUpdate mb-1' data-interview='".$cl['InterviewId']."' title='Update Interview Status'><i class='fas fa-edit mr-1'></i> Update Status</button>";
            }
            echo "</td>";
            echo "</tr>";
        }
    } else {
        $this->session->set_flashdata('error', 'Invalid Session. Please Login Again..!!');
        redirect($this->config->item('base_url') . "admin/index");
    }
}

public function filterCandidates()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $status = trim($this->input->post('status') ?? '');
        $jid    = $this->input->post('jid');

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

        $data = $this->db->get()->result_array();

        if (empty($data)) {
            echo "<tr><td colspan='9' class='text-center text-muted font-weight-bold py-4'><i class='fas fa-info-circle mr-2 text-info'></i>No candidates found for the selected status.</td></tr>";
            return;
        }

        $i = 1;
        foreach ($data as $cl) {
            echo "<tr data-candidate-id='".$cl['CandidateId']."'>";
            echo "<td class='text-center font-weight-bold'>".$i++." <input type='checkbox' class='candidate-select-chk chk-input d-none ml-1' data-candidate-id='".$cl['CandidateId']."' value='".$cl['CandidateId']."'></td>";
            echo "<td><a href='".base_url($cl['ResumePath'])."' target='_blank' class='text-warning font-weight-bold'>".htmlspecialchars($cl['CandidateCode'])."</a></td>";
            echo "<td><a href='javascript:void(0);' class='viewCandidateSimple text-primary font-weight-bold' data-id='".$cl['CandidateId']."'>".htmlspecialchars($cl['Fullname'])."</a></td>";
            echo "<td>".htmlspecialchars($cl['PhoneNo'])."</td>";
            echo "<td>".htmlspecialchars($cl['Email'])."</td>";

            $recommendation = trim($cl['ProfileMatchPer'] ?? '');
            if (in_array($recommendation, ['Strong Match', 'Strongly Match', 'Recommended'])) {
                $badgeClass = 'badge-success';
                $recommendation = 'Strong Match';
            } elseif (in_array($recommendation, ['Potential Match', 'Review Required'])) {
                $badgeClass = 'badge-warning';
                $recommendation = 'Potential Match';
            } elseif (in_array($recommendation, ['Low Match', 'Not Recommended'])) {
                $badgeClass = 'badge-danger';
                $recommendation = 'Low Match';
            } else {
                $badgeClass = 'badge-secondary';
                $recommendation = $recommendation !== '' ? $recommendation : 'Potential Match';
            }

            $analysisData = [];
            if (!empty($cl['ScoreBreakdown'])) {
                if (is_string($cl['ScoreBreakdown'])) {
                    $decoded = json_decode($cl['ScoreBreakdown'], true);
                    if (is_array($decoded)) $analysisData = $decoded;
                } elseif (is_array($cl['ScoreBreakdown'])) {
                    $analysisData = $cl['ScoreBreakdown'];
                }
            }
            $analysisData['recommendation'] = $analysisData['recommendation'] ?? $recommendation;
            $analysisData['matched_skills'] = $analysisData['matched_skills'] ?? ($cl['MatchedSkills'] ?? '');
            $analysisData['experience'] = $analysisData['experience'] ?? ($cl['ExperienceMatch'] ?? '');

            if (empty($analysisData['candidate_profile'])) {
                $expDetails = [];
                if (!empty($cl['ExperienceDetails'])) {
                    $expDetails = is_string($cl['ExperienceDetails']) ? json_decode($cl['ExperienceDetails'], true) : $cl['ExperienceDetails'];
                }

                $wHist = [];
                $eduPattern = '/\b(bachelor|master|b\.?tech|m\.?tech|b\.?e|m\.?e|b\.?sc|m\.?sc|b\.?com|m\.?com|bba|mba|bca|mca|phd|diploma|degree|college|university|institute|school|academy|sslc|hsc|10th|12th|education|academic|passed out|cgpa|percentage)\b/i';
                if (!empty($expDetails['jobs'])) {
                    $jIdx = 1;
                    foreach ($expDetails['jobs'] as $jItem) {
                        $rStr = !empty($jItem['role']) ? $jItem['role'] : (!empty($cl['RoleSummary']) ? $cl['RoleSummary'] : "Position #{$jIdx}");
                        $cStr = !empty($jItem['company']) ? $jItem['company'] : "Company";
                        if (preg_match($eduPattern, $rStr) || preg_match($eduPattern, $cStr)) {
                            continue;
                        }
                        $pStr = ($jItem['from'] ?? '') . ' - ' . ($jItem['to'] ?? '');
                        $dur = ($jItem['years'] ?? 0) . ' Yrs ' . ($jItem['months'] ?? 0) . ' Mos';
                        $wHist[] = [
                            'role' => $rStr,
                            'company' => $cStr,
                            'period' => $pStr,
                            'duration' => $dur
                        ];
                        $jIdx++;
                    }
                }

                $expStr = (!empty($cl['ExpYrs']) && is_numeric($cl['ExpYrs']) && $cl['ExpYrs'] > 0) ? ($cl['ExpYrs'] . ' years of total professional experience') : 'hands-on experience';

                $analysisData['candidate_profile'] = [
                    'headline' => !empty($cl['RoleSummary']) ? $cl['RoleSummary'] : (!empty($cl['JobTitle']) ? $cl['JobTitle'] : 'Candidate'),
                    'current_role' => !empty($cl['RoleSummary']) ? $cl['RoleSummary'] : 'Not specified in resume',
                    'current_company' => 'Not specified in resume',
                    'summary' => (!empty($cl['Fullname']) ? $cl['Fullname'] : 'Candidate') . " with " . $expStr . (!empty($cl['MatchedSkills']) ? " and skills in " . $cl['MatchedSkills'] : '') . '.',
                    'degree' => (!empty($cl['EducationMatch']) && $cl['EducationMatch'] !== 'Yes' && $cl['EducationMatch'] !== 'No') ? $cl['EducationMatch'] : 'Qualifications identified',
                    'institution' => 'Not specified in resume',
                    'grad_year' => 'Not specified in resume',
                    'training' => 'Not specified in resume',
                    'categorized_skills' => !empty($cl['MatchedSkills']) ? ['Core Skills' => $cl['MatchedSkills']] : [],
                    'work_history' => $wHist,
                    'projects' => []
                ];
            }

            $analysisJson = htmlspecialchars(json_encode($analysisData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

            echo "<td>";
            echo "<span class='badge {$badgeClass} p-2'>".htmlspecialchars($recommendation)."</span>";
            echo "<button type='button' class='btn btn-xs btn-outline-info btnScoreHelp d-block mt-1' title='View ATS Recommendation Analysis' data-analysis='{$analysisJson}'><i class='fas fa-search'></i> View Analysis</button>";
            echo "</td>";

            echo "<td>".htmlspecialchars($cl['CurrentStatus'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($cl['AppliedOn'] ?? '')."</td>";

            echo "<td>";
            echo "<div class='btn-group' role='group'>";
            echo "<button type='button' class='btn btn-sm btn-success viewCandidateDetails' data-id='".$cl['CandidateId']."' title='View Candidate'><i class='fas fa-eye'></i></button>";
            echo "<button class='btn btn-sm btn-primary openCandidateStage' data-id='".$cl['CandidateId']."' data-stage='".($cl['CurrentStageOrder'] ?? 1)."' data-status='".htmlspecialchars($cl['CurrentStatus'] ?? '', ENT_QUOTES)."' title='Update Stage'><i class='fas fa-edit'></i></button>";

            $currentStatusLower = strtolower(trim($cl['CurrentStatus'] ?? ''));
            $showOffer = false;
            $showOnboarding = false;
            $showHiring = false;
            $isHired = false;

            if (strpos($currentStatusLower, 'selected') !== false || $currentStatusLower == 'offer pending' || $currentStatusLower == 'offer accepted' || $currentStatusLower == 'offer rejected' || $currentStatusLower == 'offer released') {
                $showOffer = true;
            }
            if ($currentStatusLower == 'offer accepted') {
                $showOnboarding = true;
            }
            if ($currentStatusLower == 'on boarding') {
                $showHiring = true;
            }
            if (strpos($currentStatusLower, 'hired') !== false) {
                $isHired = true;
            }

            if ($showOffer) {
                echo "<button class='btn btn-sm btn-warning openOfferModal' data-id='".$cl['CandidateId']."' title='Offer Details'><i class='fas fa-phone'></i></button>";
            }
            if ($showOnboarding) {
                echo "<button class='btn btn-sm btn-info openOnboardingModal' data-id='".$cl['CandidateId']."' title='Onboarding'><i class='fas fa-user-check'></i></button>";
            }
            if ($showHiring) {
                echo "<button class='btn btn-sm btn-success openHiringModal' data-id='".$cl['CandidateId']."' title='Hire Candidate'><i class='fas fa-briefcase'></i></button>";
            }
            if ($isHired) {
                echo "<span class='badge badge-success p-2'>HIRED</span>";
            }
            echo "</div>";
            echo "</td>";

            echo "</tr>";
        }
    } else {
        $this->session->set_flashdata('error', 'Invalid Session. Please Login Again..!!');
        redirect($this->config->item('base_url') . "admin/index");
    }
}


public function saveOnboarding()
{
   

    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $candidateId = $this->input->post('candidateId');
        $documents   = $this->input->post('documentsSubmitted');
        $remarks     = $this->input->post('remarks');

       
        $app = $this->db->select('ApplicationId')
                        ->from('JobApplications')
                        ->where('CandidateId', $candidateId)
                        ->order_by('ApplicationId','DESC')
                        ->limit(1)
                        ->get()
                        ->row();

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = $app->ApplicationId;

        $onboardingStage = $this->db
            ->where('StageGroup', 'Hiring')
            ->like('StageName', 'On Boarding')
            ->get('RecruitmentStages')
            ->row();
        $onboardingStageId = $onboardingStage ? $onboardingStage->StageId : 11;

       
        $this->db->insert('CandidateStageTracking', [
            'ApplicationId' => $applicationId,
            'StageId'       => $onboardingStageId,
            'Action'        => 'On Boarding',
            'ActionBy'      => $Hrms_Session['IUid'],
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => "Documents Submitted: " . $documents . " | " . $remarks
        ]);

        
        $this->db->where('ApplicationId', $applicationId)
             ->update('JobApplications', [
                 'CurrentStage'  => $onboardingStageId,
                 'CurrentStatus' => 'On Boarding'
             ]);

        echo json_encode(['status'=>'success']);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function RecruitmentStages()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

       
        $data['stages'] = $this->db
            ->order_by('StageGroup', 'ASC')
            ->order_by('StageOrder', 'ASC')
            ->get('RecruitmentStages')
            ->result_array();

      
        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);

   
        $this->template->write_view('content', 'admin/RecruitmentStages', $data);
        $this->template->render();

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function SaveStage()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $stageGroup = $this->input->post('StageGroup');
        $stageName  = $this->input->post('StageName');
        $stageOrder = $this->input->post('StageOrder');

        if (!is_numeric($stageOrder) || (int)$stageOrder <= 0) {
            $this->session->set_flashdata('error', 'Stage Order must be a number greater than 0.');
            redirect('admin/RecruitmentStages');
        }
        $stageOrder = (int)$stageOrder;

      
        $existing = $this->db->where('StageGroup', $stageGroup)
                             ->where('StageOrder', $stageOrder)
                             ->get('RecruitmentStages')
                             ->num_rows();
        if ($existing > 0) {
            $this->db->set('StageOrder', 'StageOrder + 1', FALSE)
                     ->where('StageGroup', $stageGroup)
                     ->where('StageOrder >=', $stageOrder)
                     ->update('RecruitmentStages');
        }

        $data = [
            'StageGroup'  => $stageGroup,
            'StageName'   => $stageName,
            'StageOrder'  => $stageOrder,
            'StageStatus' => 1,
            'IsFinal'     => 0,
            'CreatedAT'   => date('Y-m-d H:i:s')
        ];

        $this->db->insert('RecruitmentStages',$data);

        $this->session->set_flashdata('success', 'Recruitment stage added successfully.');
        redirect($this->config->item('base_url').'admin/RecruitmentStages');

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function UpdateStage()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $stageId    = $this->input->post('StageId');
        $stageGroup = $this->input->post('StageGroup');
        $stageName  = $this->input->post('StageName');
        $stageOrder = $this->input->post('StageOrder');

        // Validation
        if (!is_numeric($stageOrder) || (int)$stageOrder <= 0) {
            $this->session->set_flashdata('error', 'Stage Order must be a number greater than 0.');
            redirect('admin/RecruitmentStages');
        }
        $stageOrder = (int)$stageOrder;

        
        $existing = $this->db->where('StageGroup', $stageGroup)
                             ->where('StageOrder', $stageOrder)
                             ->where('StageId !=', $stageId)
                             ->get('RecruitmentStages')
                             ->num_rows();
        if ($existing > 0) {
            $this->db->set('StageOrder', 'StageOrder + 1', FALSE)
                     ->where('StageGroup', $stageGroup)
                     ->where('StageOrder >=', $stageOrder)
                     ->where('StageId !=', $stageId)
                     ->update('RecruitmentStages');
        }

        $data = [
            'StageGroup' => $stageGroup,
            'StageName'  => $stageName,
            'StageOrder' => $stageOrder
        ];

        $this->db->where('StageId',$stageId)
                 ->update('RecruitmentStages',$data);

        $this->session->set_flashdata('success', 'Recruitment stage updated successfully.');
        redirect('admin/RecruitmentStages');

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function getNextStageOrder()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $group = $this->input->post('StageGroup');
        $row = $this->db->select_max('StageOrder')
                        ->where('StageGroup', $group)
                        ->get('RecruitmentStages')
                        ->row();
        
        $maxOrder = $row ? (int)$row->StageOrder : 0;
        echo json_encode(['status' => 'success', 'nextOrder' => $maxOrder + 1]);
    }
    else
    {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid session']);
    }
}

public function ChangeStageStatus($id,$action)
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $status = ($action == 'activate') ? 1 : 0;

        $this->db->where('StageId',$id)
                 ->update('RecruitmentStages',[
                    'StageStatus' => $status
                 ]);

        $msg = ($action == 'activate')
            ? 'Recruitment stage activated successfully.'
            : 'Recruitment stage deactivated successfully.';
        $this->session->set_flashdata('success', $msg);
        redirect($this->config->item('base_url').'admin/RecruitmentStages');

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function getRolePermissions()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $roleId = $this->input->post('roleId');

        $menus = $this->db
            ->select('IHMid')
            ->where('Erid',   $roleId)
            ->where('Status', 1)
            ->get('IHRolePermissions')
            ->result_array();

        $menuIds = array_column($menus, 'IHMid');

        echo json_encode($menuIds);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}


public function RolePermissions()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $data['roles'] = $this->db
            ->where('Status',1)
            ->get('EmpRoles')
            ->result_array();

        $data['menus'] = $this->db
            ->where('MenuStatus',1)
            ->get('IHMenus')
            ->result_array();

        $data['selectedRole'] = $this->input->get('role');

        $this->template->write_view('content', 'admin/role_permissions', $data);
        $this->template->render();
    }

    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function saveRolePermissions()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $roleId = $this->input->post('roleId');
        $menus  = $this->input->post('menus');

        if(empty($roleId)){
            echo json_encode(['status' => 'error', 'msg' => 'Role ID missing']);
            return;
        }

        $allMenus = $this->db->select('IHMid')->get('IHMenus')->result_array();

        foreach($allMenus as $menu){
            $menuId     = $menu['IHMid'];
            $isSelected = (!empty($menus) && in_array($menuId, $menus)) ? 1 : 0;

            $exists = $this->db
                ->where('Erid',  $roleId)
                ->where('IHMid', $menuId)
                ->get('IHRolePermissions')
                ->row();

            if($exists){
                $this->db->where('Erid',  $roleId)
                         ->where('IHMid', $menuId)
                         ->update('IHRolePermissions', [
                             'Status'    => $isSelected,
                             'UpdatedAT' => date('Y-m-d H:i:s')
                         ]);
            } else {
                $this->db->insert('IHRolePermissions', [
                    'Erid'      => $roleId,
                    'IHMid'     => $menuId,
                    'Status'    => $isSelected,
                    'CreatedAT' => date('Y-m-d H:i:s'),
                    'UpdatedAT' => date('Y-m-d H:i:s')
                ]);
            }
        }

        echo json_encode(['status' => 'success', 'msg' => 'Saved']);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}
public function saveOffer()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $candidateId = $this->input->post('candidateId');
        $offerDate   = $this->input->post('offerDate');
        $noticeDays  = $this->input->post('noticeDays');
        $remarks     = $this->input->post('remarks');
        $offerStatus = $this->input->post('offerStatus');

        $app = $this->db->select('ApplicationId')
                        ->from('JobApplications')
                        ->where('CandidateId',$candidateId)
                        ->order_by('ApplicationId','DESC')
                        ->limit(1)
                        ->get()
                        ->row();

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = $app->ApplicationId;

     
        $expectedJoining = date('Y-m-d', strtotime($offerDate.' +'.$noticeDays.' days'));

       

    $this->db->insert('CandidateOffers',[
        'ApplicationId'      => $applicationId,
        'OfferDate'          => $offerDate,
        'NoticePeriodDays'   => $noticeDays,
        'ExpectedJoiningDate'=> $expectedJoining,
        'OfferStatus'        => $offerStatus,
        'OfferActionAt'      => date('Y-m-d H:i:s')
    ]);

        $offerStage = $this->db
            ->where('StageGroup', 'Offer')
            ->get('RecruitmentStages')
            ->row();
        $offerStageId = $offerStage ? $offerStage->StageId : 12;

       
        $this->db->insert('CandidateStageTracking',[
            'ApplicationId' => $applicationId,
            'StageId'       => $offerStageId,
            'Action'        => 'Offer',
            'ActionBy'      => $Hrms_Session['IUid'],
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => $remarks
        ]);

       
        $this->db->where('ApplicationId',$applicationId)
             ->update('JobApplications',[
                 'CurrentStage'  => $offerStageId,
                 'CurrentStatus' => 'Offer ' . $offerStatus
             ]);



            
    $candidate = $this->db
        ->where('CandidateId',$candidateId)
        ->get('IHRCandidates')
        ->row();

    if(empty($candidate) || empty($candidate->Email)){
        echo json_encode(['status'=>'error','msg'=>'Candidate email missing']);
        return;
    }

  
    $data['candidatelist'] = $candidate;
    $data['action'] = 'offer';  

    try {

   
    $to = $candidate->Email;

        $subject = "Offer Update - I-Net Secure Labs Pvt Ltd.";

        require(APPPATH.'libraries/InetMailer.php');
        $objs = new InetMailer();
        $mail = $objs->load();

        $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
        $mail->addAddress(trim($to));

        $mail->isHTML(true);
        $mail->Subject = $subject;

        
        $mail->Body = $this->load->view('admin/CandisateEmail',$data,TRUE);

       if(!$mail->send()){
        echo json_encode([
            'status'=>'error',
            'msg'=>$mail->ErrorInfo
        ]);
        return;
    }

    } catch (\Exception $e) {
        
    }
        echo json_encode(['status'=>'success']);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}
 

public function saveHiring()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $userId = $Hrms_Session['IUid'] ?? 1;

        $candidateId = $this->input->post('candidateId');
        $joiningDate = $this->input->post('joiningDate');
        $salary      = $this->input->post('salaryOffered');
        $remarks     = $this->input->post('remarks');

      

        $app = $this->db->select('ApplicationId')
                        ->from('JobApplications')
                        ->where('CandidateId', $candidateId)
                        ->order_by('ApplicationId', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();

        if(!$app){
            echo "Application not found";
            return;
        }

    

        $hiredStage = $this->db
            ->where('StageGroup', 'Hiring')
            ->like('StageName', 'Hired')
            ->get('RecruitmentStages')
            ->row();
        $hiredStageId = $hiredStage ? $hiredStage->StageId : 13;

        $this->db->insert('CandidateStageTracking', [

            'ApplicationId' => $app->ApplicationId,
            'StageId'       => $hiredStageId,
            'Action'        => 'Hired',
            'ActionBy'      => $userId,
            'Remarks'       => 'Candidate successfully hired',
            'ActionAt'      => date('Y-m-d H:i:s')

        ]);

       

        $this->db->insert('CandidateHiring', [

            'ApplicationId' => $app->ApplicationId,
            'CandidateId'   => $candidateId,
            'HiringDate'    => date('Y-m-d'),
            'JoiningDate'   => $joiningDate,
            'SalaryOffered' => $salary,
            'Remarks'       => $remarks,
            'CreatedBy'     => $userId,
            'CreatedAt'     => date('Y-m-d H:i:s')

        ]);

       

        $this->db->where('ApplicationId', $app->ApplicationId);

        $this->db->update('JobApplications', [

            'CurrentStage'  => $hiredStageId,
            'CurrentStatus' => 'Hired'

        ]);

        echo "success";
    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}
private function getApplicationId($candidateId)
{
    $app = $this->db->select('ApplicationId')
                    ->from('JobApplications')
                    ->where('CandidateId',$candidateId)
                    ->order_by('ApplicationId','DESC')
                    ->limit(1)
                    ->get()
                    ->row();

    return $app ? $app->ApplicationId : null;
}

public function viewResume($candidateId)
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $candidate = $this->db
            ->where('CandidateId',$candidateId)
            ->get('IHrCandidates')   
            ->row_array();

        if(!empty($candidate['ResumePath']))
        {
            redirect(base_url($candidate['ResumePath']));
        }
        else
        {
            echo "Resume not found";
        }

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function logout()
{
    $this->session->sess_destroy();
    redirect('admin/index');
}



public function get_notifications() {
    $this->load->model('Notification_model');
    $Hrms_Session = $this->session->userdata('logged_in');
    
    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $userId = $Hrms_Session['IUid'];
        $roleId = $Hrms_Session['EmpRoleId'];

        $notifications = $this->Notification_model->getUnreadNotifications($userId, $roleId);
        $count = count($notifications);

        echo json_encode([
            'status' => 'success',
            'count' => $count,
            'data' => $notifications
        ]);

    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function mark_notification_read() {
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $this->load->model('Notification_model');
        $nid = $this->input->post('notification_id');
        
        if ($Hrms_Session && $nid) {
            $this->Notification_model->markAsRead($nid, $Hrms_Session['IUid']);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function mark_all_notifications_read() {
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $this->load->model('Notification_model');
        $this->Notification_model->markAllAsRead($Hrms_Session['IUid'], $Hrms_Session['EmpRoleId']);
        echo json_encode(['status' => 'success']);
    }
    else
    {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}




    public function RequestedResources()
    {
        $check_session = $this->session->userdata("logged_in");
        if (empty($check_session)) {
            redirect($this->config->item("base_url") . "admin/index");
            return;
        }

        $roleId = isset($check_session["EmpRoleId"]) ? $check_session["EmpRoleId"] : null;
        $userId = isset($check_session["IUid"]) ? $check_session["IUid"] : null;

        $roleRow = $this->db->select("RoleName")->from("emproles")->where("Erid", $roleId)->get()->row_array();
        $roleName = !empty($roleRow) ? strtolower($roleRow["RoleName"]) : "";

       

        $adminRoles   = [1, 3]; 
        $approverRole = 12;    

        $filters = [];
        if (in_array($roleId, $adminRoles)) {
          
        } elseif ($roleId == $approverRole) {
            $filters["ApproverId"] = $userId;
        } else {
           
            $filters["RequestedBy"] = $userId;
        }

        $data["employee_det"] = $check_session;
        $data["requests"] = $this->admin_model->getResourceRequests($filters);
        $data["approvers"] = $this->admin_model->getApproverUsers();
        $data["ctc_approvers"] = $this->admin_model->getAllUsers();
        $data["department"] = $this->db->select("Did, Departmentname")->from("departments")->where("Status", 1)->get()->result_array();
        $data["userRoleName"] = !empty($roleRow) ? $roleRow["RoleName"] : "";

        $currentUrl = strtolower(uri_string());
        $data["currentUrlArray"] = $this->admin_model->getBreadcrumb($currentUrl);

        $this->template->set_master_template("../../themes/" . $this->config->item("active_template") . "/bo_template.php");
        $this->template->write_view("content", "admin/RequestedResources", $data);
        $this->template->render();
    }

    public function generateJobContent()
    {
        if (ob_get_length()) { @ob_clean(); }

        $inputs = $this->input->post();
        if (empty($inputs)) {
            $rawInput = file_get_contents('php://input');
            $inputs = !empty($rawInput) ? json_decode($rawInput, true) : [];
        }

        $jobTitle       = isset($inputs['JobTitle']) ? trim($inputs['JobTitle']) : '';
        $functionalRole = isset($inputs['FunctionalRole']) ? trim($inputs['FunctionalRole']) : '';

        if (empty($jobTitle) && empty($functionalRole)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => 'error',
                    'message' => 'Please enter a Job Title or Functional Role before generating.'
                ]));
            return;
        }

        try {
            $this->load->library('JobContentGenerator');

            $data = [
                'JobTitle'          => $jobTitle,
                'FunctionalRole'    => $functionalRole,
                'Department'        => isset($inputs['Department']) ? trim($inputs['Department']) : '',
                'ExpMin'            => isset($inputs['ExpMin']) ? (float)$inputs['ExpMin'] : 0,
                'ExpMax'            => isset($inputs['ExpMax']) ? (float)$inputs['ExpMax'] : 0,
                'MustHaveSkills'    => isset($inputs['MustHaveSkills']) ? trim($inputs['MustHaveSkills']) : '',
                'NiceToHaveSkills'  => isset($inputs['NiceToHaveSkills']) ? trim($inputs['NiceToHaveSkills']) : '',
                'JobLocation'       => isset($inputs['JobLocation']) ? trim($inputs['JobLocation']) : '',
                'CommunicationLang' => isset($inputs['CommunicationLang']) ? trim($inputs['CommunicationLang']) : ''
            ];

            $result = $this->jobcontentgenerator->generate($data);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        } catch (Exception $e) {
            log_message('error', 'JobContentGenerator Error: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => 'error',
                    'message' => 'Unable to generate job content. Please enter the details manually.'
                ]));
        }
    }

    public function saveResourceRequest()
    {
        if (ob_get_length()) { @ob_clean(); }

        $db_debug_orig = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $check_session = $this->session->userdata("logged_in");
            if (empty($check_session)) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }
                redirect($this->config->item("base_url") . "admin/index");
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            $inps = $this->input->post();
            if (empty($inps["JobTitle"]) || empty($inps["ApproverId"])) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Job Title and Approver Name are required."]);
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }
                $this->session->set_flashdata("error", "Job Title and Approver Name are required.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            $requestId = isset($inps["RequestId"]) ? (int)$inps["RequestId"] : 0;
            $sessionRoleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
            $sessionUserId = isset($check_session["IUid"]) ? (int)$check_session["IUid"] : 0;

            if (!$this->db->field_exists('MustHaveSkills', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN MustHaveSkills TEXT NULL");
            }
            if (!$this->db->field_exists('NiceToHaveSkills', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN NiceToHaveSkills TEXT NULL");
            }
            if (!$this->db->field_exists('CommunicationLang', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN CommunicationLang TEXT NULL");
            }
            if (!$this->db->field_exists('JobLocation', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN JobLocation TEXT NULL");
            }
            if (!$this->db->field_exists('Salary', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN Salary VARCHAR(255) NULL");
            }

            if ($this->db->field_exists('ExpMin', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests MODIFY COLUMN ExpMin DECIMAL(10,2) NULL DEFAULT 0.00");
            }
            if ($this->db->field_exists('ExpMax', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests MODIFY COLUMN ExpMax DECIMAL(10,2) NULL DEFAULT 0.00");
            }
            if ($this->db->field_exists('ExpMin', 'ihrjobslist')) {
                @$this->db->query("ALTER TABLE ihrjobslist MODIFY COLUMN ExpMin DECIMAL(10,2) NULL DEFAULT 0.00");
            }
            if ($this->db->field_exists('ExpMax', 'ihrjobslist')) {
                @$this->db->query("ALTER TABLE ihrjobslist MODIFY COLUMN ExpMax DECIMAL(10,2) NULL DEFAULT 0.00");
            }

            if ($requestId > 0) {
                $existing = $this->admin_model->getResourceRequestById($requestId);
                if (empty($existing)) {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Resource Request not found."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("error", "Resource Request not found.");
                    redirect($this->config->item("base_url") . "admin/RequestedResources");
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }

              
                if ($sessionRoleId === 9 && (int)$existing["RequestedBy"] !== $sessionUserId) {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Access denied. You can only update your own resource requests."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("error", "Access denied. You can only update your own resource requests.");
                    redirect($this->config->item("base_url") . "admin/RequestedResources");
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }

                $salaryStr = isset($inps["Salary"]) ? trim($inps["Salary"]) : ((!empty($inps["SalMin"]) || !empty($inps["SalMax"])) ? ($inps["SalMin"] . " - " . $inps["SalMax"] . " LPA") : "");

                $updateData = [
                    "JobTitle"             => trim($inps["JobTitle"]),
                    "FunctionalRole"       => isset($inps["FunctionalRole"]) ? trim($inps["FunctionalRole"]) : "",
                    "Did"                  => isset($inps["Did"]) ? (int)$inps["Did"] : null,
                    "JobLocation"          => isset($inps["JobLocation"]) ? trim($inps["JobLocation"]) : "",
                    "EducationRequired"     => isset($inps["EducationRequired"]) ? trim($inps["EducationRequired"]) : "",
                    "NoofOpenings"         => isset($inps["NoofOpenings"]) ? (int)$inps["NoofOpenings"] : 1,
                    "PositionType"         => isset($inps["PositionType"]) ? $inps["PositionType"] : "New Position",
                    "ExpMin"               => isset($inps["ExpMin"]) ? (float)$inps["ExpMin"] : 0.0,
                    "ExpMax"               => isset($inps["ExpMax"]) ? (float)$inps["ExpMax"] : 0.0,
                    "Salary"               => $salaryStr,
                    "RecruitmentStartDate" => !empty($inps["RecruitmentStartDate"]) ? $inps["RecruitmentStartDate"] : null,
                    "TargetOnboardingDate" => !empty($inps["TargetOnboardingDate"]) ? $inps["TargetOnboardingDate"] : null,
                    "ReasonForRequirement" => isset($inps["ReasonForRequirement"]) ? trim($inps["ReasonForRequirement"]) : "",
                    "MustHaveSkills"       => isset($inps["MustHaveSkills"]) ? trim($inps["MustHaveSkills"]) : "",
                    "NiceToHaveSkills"     => isset($inps["NiceToHaveSkills"]) ? trim($inps["NiceToHaveSkills"]) : "",
                    "CommunicationLang"    => isset($inps["CommunicationLang"]) ? trim($inps["CommunicationLang"]) : "",
                    "JobDescription"       => isset($inps["JobDescription"]) ? trim($inps["JobDescription"]) : "",
                    "Responsibilities"     => isset($inps["Responsibilities"]) ? trim($inps["Responsibilities"]) : "",
                    "ApproverId"           => (int)$inps["ApproverId"],
                    "CtcApproverId"        => !empty($inps["CtcApproverId"]) ? (int)$inps["CtcApproverId"] : null,
                    "UpdatedAt"            => date("Y-m-d H:i:s")
                ];

                $res = $this->admin_model->updateResourceRequest($requestId, $updateData);
                if ($res) {
                    if (!empty($existing["ConvertedJid"])) {
                        $mustHave = isset($inps["MustHaveSkills"]) ? trim($inps["MustHaveSkills"]) : "";
                        $niceHave = isset($inps["NiceToHaveSkills"]) ? trim($inps["NiceToHaveSkills"]) : "";
                        $allSkills = array_unique(array_filter(array_map('trim', explode(',', $mustHave . ', ' . $niceHave))));
                        $vacancyUpdate = [
                            "JobTitle"             => trim($inps["JobTitle"]),
                            "RoleSummary"          => isset($inps["FunctionalRole"]) ? trim($inps["FunctionalRole"]) : "",
                            "Did"                  => isset($inps["Did"]) ? (int)$inps["Did"] : null,
                            "JobLocation"          => isset($inps["JobLocation"]) ? trim($inps["JobLocation"]) : "",
                            "EducationRequired"     => isset($inps["EducationRequired"]) ? trim($inps["EducationRequired"]) : "",
                            "NoofOpenings"         => isset($inps["NoofOpenings"]) ? (int)$inps["NoofOpenings"] : 1,
                            "ExpMin"               => isset($inps["ExpMin"]) ? (float)$inps["ExpMin"] : 0.0,
                            "ExpMax"               => isset($inps["ExpMax"]) ? (float)$inps["ExpMax"] : 0.0,
                            "TargetOnboardingDate" => !empty($inps["TargetOnboardingDate"]) ? $inps["TargetOnboardingDate"] : null,
                            "MustHaveSkills"       => $mustHave,
                            "NiceToHaveSkills"     => $niceHave,
                            "CommunicationLang"    => isset($inps["CommunicationLang"]) ? trim($inps["CommunicationLang"]) : "",
                            "Skills"               => implode(', ', $allSkills),
                            "JobDescription"       => isset($inps["JobDescription"]) ? trim($inps["JobDescription"]) : "",
                            "Responsibilities"     => isset($inps["Responsibilities"]) ? trim($inps["Responsibilities"]) : "",
                            "CtcApproverId"        => !empty($inps["CtcApproverId"]) ? (int)$inps["CtcApproverId"] : null,
                        ];
                        $this->db->where("Jid", (int)$existing["ConvertedJid"])->update("ihrjobslist", $vacancyUpdate);
                    }
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "success", "message" => "Resource Request [" . $existing["RequestCode"] . "] updated successfully."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("true", "Resource Request [" . $existing["RequestCode"] . "] updated successfully.");
                } else {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Failed to update Resource Request."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("error", "Failed to update Resource Request.");
                }
            } else {
             
                $count = $this->db->count_all("resource_requests") + 1;
                $requestCode = "RR-" . date("Y") . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);

                $salaryStr = isset($inps["Salary"]) ? trim($inps["Salary"]) : ((!empty($inps["SalMin"]) || !empty($inps["SalMax"])) ? ($inps["SalMin"] . " - " . $inps["SalMax"] . " LPA") : "");

                $data = [
                    "RequestCode"          => $requestCode,
                    "JobTitle"             => trim($inps["JobTitle"]),
                    "FunctionalRole"       => isset($inps["FunctionalRole"]) ? trim($inps["FunctionalRole"]) : "",
                    "Did"                  => isset($inps["Did"]) ? (int)$inps["Did"] : null,
                    "JobLocation"          => isset($inps["JobLocation"]) ? trim($inps["JobLocation"]) : "",
                    "EducationRequired"     => isset($inps["EducationRequired"]) ? trim($inps["EducationRequired"]) : "",
                    "NoofOpenings"         => isset($inps["NoofOpenings"]) ? (int)$inps["NoofOpenings"] : 1,
                    "PositionType"         => isset($inps["PositionType"]) ? $inps["PositionType"] : "New Position",
                    "ExpMin"               => isset($inps["ExpMin"]) ? (float)$inps["ExpMin"] : 0.0,
                    "ExpMax"               => isset($inps["ExpMax"]) ? (float)$inps["ExpMax"] : 0.0,
                    
                    "Salary"               => $salaryStr,
                    "RecruitmentStartDate" => !empty($inps["RecruitmentStartDate"]) ? $inps["RecruitmentStartDate"] : date("Y-m-d"),
                    "TargetOnboardingDate" => !empty($inps["TargetOnboardingDate"]) ? $inps["TargetOnboardingDate"] : null,
                    "ReasonForRequirement" => isset($inps["ReasonForRequirement"]) ? trim($inps["ReasonForRequirement"]) : "",
                    "MustHaveSkills"       => isset($inps["MustHaveSkills"]) ? trim($inps["MustHaveSkills"]) : "",
                    "NiceToHaveSkills"     => isset($inps["NiceToHaveSkills"]) ? trim($inps["NiceToHaveSkills"]) : "",
                    "CommunicationLang"    => isset($inps["CommunicationLang"]) ? trim($inps["CommunicationLang"]) : "",
                    "JobDescription"       => isset($inps["JobDescription"]) ? trim($inps["JobDescription"]) : "",
                    "Responsibilities"     => isset($inps["Responsibilities"]) ? trim($inps["Responsibilities"]) : "",
                    "RequestedBy"          => $check_session["IUid"],
                    "ApproverId"           => (int)$inps["ApproverId"],
                    "CtcApproverId"        => !empty($inps["CtcApproverId"]) ? (int)$inps["CtcApproverId"] : null,
                    "Status"               => "PENDING APPROVAL",
                    "CreatedAt"            => date("Y-m-d H:i:s")
                ];

                $newId = $this->admin_model->insertResourceRequest($data);
                if ($newId) {
     
                    try {
                        @$this->_sendResourceRequestEmailToApprover($newId);
                    } catch (\Throwable $t) {
                        log_message('error', 'Resource Request Approver Email Error: ' . $t->getMessage());
                    }

                   
                    try {
                        $this->load->model("Notification_model");
                        $this->Notification_model->addNotification(
                            "New Resource Request Pending Approval",
                            "Resource Request [" . $requestCode . "] for \"" . trim($inps["JobTitle"]) . "\" requested by " . $check_session["EmpName"] . " requires your approval.",
                            "warning",
                            (int)$inps["ApproverId"],
                            12
                        );
                    } catch (\Throwable $t) {
                        log_message('error', 'Resource Request Notification Error: ' . $t->getMessage());
                    }

                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "success", "message" => "Resource Request submitted successfully and sent for approval."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("true", "Resource Request submitted successfully and sent for approval.");
                } else {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Failed to submit Resource Request."]);
                        $this->db->db_debug = $db_debug_orig;
                        return;
                    }
                    $this->session->set_flashdata("error", "Failed to submit Resource Request.");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'saveResourceRequest Exception: ' . $e->getMessage());
            if ($this->input->is_ajax_request()) {
                echo json_encode(["status" => "error", "message" => "An error occurred while saving: " . $e->getMessage()]);
                $this->db->db_debug = $db_debug_orig;
                return;
            }
            $this->session->set_flashdata("error", "An error occurred: " . $e->getMessage());
        }

        $this->db->db_debug = $db_debug_orig;
        redirect($this->config->item("base_url") . "admin/RequestedResources");
    }

    public function updateResourceRequestStatus()
    {
        @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - ENTRY_updateResourceRequestStatus\n", FILE_APPEND);
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        $db_debug_orig = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $check_session = $this->session->userdata("logged_in");
            if (empty($check_session)) {
                echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            $inps = $this->input->post();
            if (empty($inps)) {
                $inps = $_POST;
            }
            if (empty($inps)) {
                $raw = file_get_contents('php://input');
                if (!empty($raw)) {
                    $jsonInps = json_decode($raw, true);
                    if (is_array($jsonInps)) {
                        $inps = $jsonInps;
                    }
                }
            }

            $rawReqId = null;
            foreach (['RequestId', 'requestId', 'request_id', 'id', 'RequestCode'] as $key) {
                if (isset($inps[$key]) && $inps[$key] !== '') {
                    $rawReqId = $inps[$key];
                    break;
                }
            }

            $targetReq = null;
            if (!empty($rawReqId)) {
                if (is_numeric($rawReqId)) {
                    $targetReq = $this->admin_model->getResourceRequestById((int)$rawReqId);
                }
                if (empty($targetReq)) {
                    $reqs = $this->admin_model->getResourceRequests(['RequestId' => trim($rawReqId)]);
                    if (!empty($reqs)) {
                        $targetReq = $reqs[0];
                    }
                }
            }

            $requestId = !empty($targetReq['RequestId']) ? (int)$targetReq['RequestId'] : 0;
            if (!$requestId && !empty($rawReqId) && is_numeric($rawReqId)) {
                $requestId = (int)$rawReqId;
            }

            $status = "";
            foreach (['Status', 'status'] as $key) {
                if (isset($inps[$key]) && $inps[$key] !== '') {
                    $status = strtoupper(trim($inps[$key]));
                    break;
                }
            }

            $comment = "";
            foreach (['ApprovalComment', 'approvalComment', 'comment', 'ApprovalComments'] as $key) {
                if (isset($inps[$key])) {
                    $comment = trim($inps[$key]);
                    break;
                }
            }

            @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - UPDATE_RES_REQ: " . json_encode(['post' => $this->input->post(), '_POST' => $_POST, 'rawReqId' => $rawReqId, 'requestId' => $requestId, 'status' => $status, 'comment' => $comment, 'session' => $check_session]) . "\n", FILE_APPEND);

            if ((!$requestId && empty($targetReq)) || !in_array($status, ["ACCEPTED", "REJECTED"])) {
                echo json_encode(["status" => "error", "message" => "Invalid request parameters."]);
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            if (empty($comment)) {
                echo json_encode(["status" => "error", "message" => "Approval Comments are mandatory."]);
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            if (!$this->db->field_exists('ApprovalComment', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN ApprovalComment TEXT NULL");
            }
            if (!$this->db->field_exists('ActionedAt', 'resource_requests')) {
                @$this->db->query("ALTER TABLE resource_requests ADD COLUMN ActionedAt DATETIME NULL");
            }

            $sessionRoleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
            $sessionUserId = isset($check_session["IUid"]) ? (int)$check_session["IUid"] : 0;

            $roleRow = $this->db->select("RoleName")->from("emproles")->where("Erid", $sessionRoleId)->get()->row_array();
            $roleName = !empty($roleRow) ? strtolower(trim($roleRow["RoleName"])) : "";
            $isHiringManagerRole = ($sessionRoleId == 9 || $roleName === 'hiring manager');
            $adminRolesForApproval = [1, 3, 9, 10, 12];

            if (!in_array($sessionRoleId, $adminRolesForApproval) && !$isHiringManagerRole) {
                echo json_encode(["status" => "error", "message" => "Access denied. You do not have permission to perform this action."]);
                $this->db->db_debug = $db_debug_orig;
                return;
            }

            if ($sessionRoleId == 12) {
                $reqCheck = $this->admin_model->getResourceRequestById($requestId);
                if (empty($reqCheck) || (int)$reqCheck["ApproverId"] !== $sessionUserId) {
                    echo json_encode(["status" => "error", "message" => "Access denied. This request is not assigned to you for approval."]);
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }
            }

            if ($isHiringManagerRole) {
                $reqCheck = $this->admin_model->getResourceRequestById($requestId);
                if (empty($reqCheck)) {
                    echo json_encode(["status" => "error", "message" => "Resource request record not found."]);
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }
                if ($status !== "ACCEPTED") {
                    echo json_encode(["status" => "error", "message" => "Hiring Managers can only submit approved requests to the vacancy list."]);
                    $this->db->db_debug = $db_debug_orig;
                    return;
                }
            }

            $updateData = [
                "Status" => $status
            ];

            if ($this->db->field_exists('ApprovalComment', 'resource_requests')) {
                $updateData['ApprovalComment'] = $comment;
            }
            if ($this->db->field_exists('ActionedAt', 'resource_requests')) {
                $updateData['ActionedAt'] = date("Y-m-d H:i:s");
            }
            if ($this->db->field_exists('UpdatedAt', 'resource_requests')) {
                $updateData['UpdatedAt'] = date("Y-m-d H:i:s");
            }

            $res = $this->admin_model->updateResourceRequest($requestId, $updateData);
            if ($res) {
                if ($status === "ACCEPTED") {
                    $req = $this->admin_model->getResourceRequestById($requestId);

                    try {
                        $this->_sendResourceRequestAcceptEmail($requestId);
                    } catch (\Throwable $ex) {}

                    try {
                        $this->load->model("Notification_model");
                        $this->Notification_model->addNotification(
                            "Resource Request Approved",
                            "Resource Request [" . ($req["RequestCode"] ?? '') . "] (\"" . ($req["JobTitle"] ?? '') . "\") was approved by " . ($check_session["EmpName"] ?? 'Approver') . " and moved to Approved Resources for recruiter assignment.",
                            "success",
                            null,
                            10
                        );

                        if (!empty($req["RequestedBy"])) {
                            $this->Notification_model->addNotification(
                                "Resource Request Approved",
                                "Your Resource Request [" . ($req["RequestCode"] ?? '') . "] (\"" . ($req["JobTitle"] ?? '') . "\") has been approved!",
                                "success",
                                $req["RequestedBy"],
                                null
                            );
                        }
                    } catch (\Throwable $ne) {}

                    echo json_encode(["status" => "success", "message" => "Resource Request has been approved successfully and moved to Approved Resources."]);
                    return;
                } else if ($status === "REJECTED") {
                    try {
                        $this->_sendResourceRequestRejectEmail($requestId);
                    } catch (\Throwable $rex) {}

                    echo json_encode(["status" => "success", "message" => "Resource Request has been rejected successfully."]);
                    return;
                }
                echo json_encode(["status" => "success", "message" => "Resource Request updated successfully."]);
            } else {
                $dbErr = $this->db->error();
                echo json_encode(["status" => "error", "message" => "Failed to update Resource Request. " . ($dbErr['message'] ?? '')]);
            }
        } catch (\Throwable $t) {
            echo json_encode(["status" => "error", "message" => "Server error: " . $t->getMessage()]);
        }

        $this->db->db_debug = $db_debug_orig;
    }

    public function convertRequestToVacancy($requestId)
    {
        $check_session = $this->session->userdata("logged_in");
        if (empty($check_session)) {
            redirect($this->config->item("base_url") . "admin/index");
            return;
        }

        $req = $this->admin_model->getResourceRequestById((int)$requestId);
        if (empty($req) || $req["Status"] !== "ACCEPTED") {
            $this->session->set_flashdata("error", "Only ACCEPTED resource requests can be converted to Vacancies.");
            redirect($this->config->item("base_url") . "admin/RequestedResources");
            return;
        }

        $count = $this->db->count_all("ihrjobslist") + 1;
        $jobCode = "JOB-" . date("Y") . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);

        $vacancyData = [
            "JobCode"               => $jobCode,
            "JobTitle"              => $req["JobTitle"],
            "RoleSummary"           => $req["FunctionalRole"],
            "Did"                   => $req["Did"],
            "EmploymentType"        => "Full-Time",
            "WorkMode"              => "Onsite",
            "EducationRequired"     => !empty($req["EducationRequired"]) ? $req["EducationRequired"] : "Bachelor Degree",
            "ExpMin"                => $req["ExpMin"],
            "ExpMax"                => $req["ExpMax"],
            "SalMin"                => $req["SalMin"],
            "SalMax"                => $req["SalMax"],
                        "TargetOnboardingDate"  => !empty($req["TargetOnboardingDate"]) ? $req["TargetOnboardingDate"] : null,
                        "Salary"                => (!empty($req["SalMin"]) || !empty($req["SalMax"])) ? ($req["SalMin"] . " - " . $req["SalMax"] . " LPA") : "",
            "NoofOpenings"          => $req["NoofOpenings"],
            "JobStatus"             => "Open",
            "JobDescription"        => $req["JobDescription"],
            "Responsibilities"      => $req["Responsibilities"],
            "PostedBy"              => $check_session["IUid"],
            "PostedOn"              => date("Y-m-d H:i:s")
        ];

        $this->db->insert("ihrjobslist", $vacancyData);
        $jid = $this->db->insert_id();

        if ($jid) {
            $this->admin_model->updateResourceRequest((int)$requestId, ["ConvertedJid" => $jid]);
            $this->session->set_flashdata("true", "Vacancy created successfully from approved Resource Request (" . $req["RequestCode"] . ").");
            redirect($this->config->item("base_url") . "admin/VaccancyList");
        } else {
            $this->session->set_flashdata("error", "Failed to convert Resource Request to Vacancy.");
            redirect($this->config->item("base_url") . "admin/RequestedResources");
        }
    }


    private function _sendResourceRequestEmailToApprover($requestId)
    {
        if (empty($requestId)) return false;

        // Fetch full request details including approver gender
        $req = $this->db
            ->select('rr.*, d.Departmentname,
                      req.EmpName AS RequesterName, req.EmpEmail AS RequesterEmail,
                      app.EmpName AS ApproverName, app.EmpEmail AS ApproverEmail, app.EmpGender AS ApproverGender')
            ->from('resource_requests rr')
            ->join('Departments d', 'd.Did = rr.Did', 'left')
            ->join('IHUsers req', 'req.IUid = rr.RequestedBy', 'left')
            ->join('IHUsers app', 'app.IUid = rr.ApproverId', 'left')
            ->where('rr.RequestId', (int)$requestId)
            ->get()
            ->row_array();

        if (empty($req) || empty($req['ApproverEmail'])) {
            return false;
        }

        // Fetch CC recipients: Requester, Recruitment Managers (10), Hiring Managers (9), Recruiters
        $ccMap = [];

        // 1. Add Requester to CC if different from Approver
        if (!empty($req['RequesterEmail']) && strtolower(trim($req['RequesterEmail'])) !== strtolower(trim($req['ApproverEmail']))) {
            $ccMap[strtolower(trim($req['RequesterEmail']))] = !empty($req['RequesterName']) ? $req['RequesterName'] : 'Requester';
        }

        // 2. Fetch users by Role ID or Role Name
        $roleUsers = $this->db
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

        if (!empty($roleUsers)) {
            foreach ($roleUsers as $cu) {
                $e = strtolower(trim($cu['EmpEmail'] ?? ''));
                if (!empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    if ($e !== strtolower(trim($req['ApproverEmail']))) {
                        $ccMap[$e] = !empty($cu['EmpName']) ? $cu['EmpName'] : '';
                    }
                }
            }
        }

        // Gender-aware salutation for approver
        $approverGender = strtolower(trim($req['ApproverGender'] ?? ''));
        if ($approverGender === 'female') {
            $salutation = 'Dear Madam,';
        } elseif ($approverGender === 'male') {
            $salutation = 'Dear Sir,';
        } else {
            $salutation = 'Dear Sir / Madam,';
        }

        $approverName  = !empty($req['ApproverName'])  ? $req['ApproverName']  : 'Approver';
        $requesterName = !empty($req['RequesterName']) ? $req['RequesterName'] : 'A team member';
        $jobTitle      = !empty($req['JobTitle'])      ? $req['JobTitle']      : 'Resource';
        $requestCode   = !empty($req['RequestCode'])   ? $req['RequestCode']   : 'REQ';
        $department    = !empty($req['Departmentname']) ? $req['Departmentname'] : 'N/A';
        $openings      = !empty($req['NoofOpenings'])  ? (int)$req['NoofOpenings'] : 1;
        $positionType  = !empty($req['PositionType'])  ? $req['PositionType']  : 'New Position';
        $targetDate    = !empty($req['TargetOnboardingDate']) ? date('d M Y', strtotime($req['TargetOnboardingDate'])) : 'N/A';
        $expRange      = '';
        if (!empty($req['ExpMin']) || !empty($req['ExpMax'])) {
            $expRange = $req['ExpMin'] . ' - ' . $req['ExpMax'] . ' years';
        } else {
            $expRange = 'N/A';
        }

        $subject = '[Action Required] Resource Request ' . $requestCode . ' - ' . $jobTitle . ' | Approval Needed';

        $posTypeLower = strtolower(trim($positionType));
        $posTypePhrase = ($posTypeLower === 'replacement') ? 'replacement' : 'new position';

        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #ffffff; margin: 0; padding: 10px; }
        .req-table { width: 100%; max-width: 650px; border-collapse: collapse; margin: 20px 0; }
        .req-table th, .req-table td { border: 1px solid #dddddd; padding: 8px 12px; text-align: left; font-size: 13px; }
        .req-table th { background-color: #f8f9fa; width: 35%; color: #495057; font-weight: bold; }
        .req-table td { color: #212529; }
    </style>
</head>
<body>
    <p>' . $salutation . '</p>
    <p>A new Resource Request (<strong>' . htmlspecialchars($requestCode) . '</strong>) has been submitted by <strong>' . htmlspecialchars($requesterName) . '</strong> for the ' . $posTypePhrase . ' of <strong>' . htmlspecialchars($jobTitle) . '</strong> and is pending your approval.</p>

    <table class="req-table">
        <tr>
            <th>Request Code</th>
            <td><strong>' . htmlspecialchars($requestCode) . '</strong></td>
        </tr>
        <tr>
            <th>Job Title</th>
            <td>' . htmlspecialchars($jobTitle) . '</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>' . htmlspecialchars($department) . '</td>
        </tr>
        <tr>
            <th>No. of Positions</th>
            <td>' . $openings . '</td>
        </tr>
        <tr>
            <th>Position Type</th>
            <td>' . htmlspecialchars($positionType) . '</td>
        </tr>
        <tr>
            <th>Experience Required</th>
            <td>' . htmlspecialchars($expRange) . '</td>
        </tr>
        <tr>
            <th>Target Onboarding Date</th>
            <td>' . htmlspecialchars($targetDate) . '</td>
        </tr>
        <tr>
            <th>Requested By</th>
            <td>' . htmlspecialchars($requesterName) . ' (' . htmlspecialchars($req['RequesterEmail'] ?? '-') . ')</td>
        </tr>
    </table>

    <p>Thanks &amp; Regards,<br>
    <strong>Recruiter Team</strong></p>
    <br>
    <p style="font-size: 12px; color: #666666; font-style: italic; margin-top: 20px; border-top: 1px dashed #cccccc; padding-top: 8px;">Note: This is an auto-generated email.</p>
</body>
</html>';

        try {
            require_once(APPPATH . 'libraries/InetMailer.php');
            $objs = new InetMailer();
            $mail = $objs->load();
            if ($mail) {
                $mail->CharSet = 'UTF-8';
                $mail->Timeout = 10;
                $mail->setFrom('info@inetcsc.com', 'I-NET Recruitment Portal');
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;

                // To: Approver
                $mail->addAddress(trim($req['ApproverEmail']), $approverName);

                // CC: Recruitment Managers, Hiring Managers, Recruiters, and Requester
                foreach ($ccMap as $ccEmail => $ccName) {
                    try {
                        $mail->addCC($ccEmail, $ccName);
                    } catch (\Throwable $ct) {
                        log_message('error', 'addCC failed for email: ' . $ccEmail . ' - ' . $ct->getMessage());
                    }
                }

                $sent = $mail->send();
                @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - Resource Request Email Sent: " . ($sent ? "SUCCESS" : "FAILED") . " | To: " . $req['ApproverEmail'] . " | CC: " . implode(', ', array_keys($ccMap)) . "\n", FILE_APPEND);
                return $sent;
            }
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'Resource Request Approver Email Error: ' . $e->getMessage());
            @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - Resource Request Email ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    private function _sendResourceRequestAcceptEmail($requestId)
    {
        try {
            $req = $this->admin_model->getResourceRequestById($requestId);
            if (empty($req)) return false;

            $actionedAt = !empty($req["UpdatedAt"]) ? $req["UpdatedAt"] : date("Y-m-d H:i:s");
            $comment = !empty($req["ApprovalComment"]) ? $req["ApprovalComment"] : "-";

            $subject = "Resource Request ACCEPTED [" . $req["RequestCode"] . "] - " . $req["JobTitle"];

            $baseUrl = $this->config->item('base_url');
            $actionLink = $baseUrl . 'admin/ApprovedResources';

            $htmlBody = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: "Segoe UI", Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
                    .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e9ecef; }
                    .email-header { background: linear-gradient(135deg, #28a745, #218838); padding: 24px; text-align: center; color: #ffffff; }
                    .email-header h2 { margin: 0; font-size: 22px; font-weight: 600; }
                    .email-body { padding: 30px; }
                    .info-box { background: #e8f5e9; border-left: 4px solid #28a745; padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 15px; }
                    .info-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    .info-table td { padding: 10px; border-bottom: 1px solid #e9ecef; font-size: 14px; }
                    .info-table td.label { font-weight: 600; color: #495057; width: 40%; }
                    .btn-action { display: inline-block; background: #007bff; color: #ffffff !important; padding: 12px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; text-align: center; }
                    .email-footer { background: #f1f3f5; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
                </style>
            </head>
            <body>
                <div class="email-card">
                    <div class="email-header">
                        <h2>Resource Request Approved!</h2>
                    </div>
                    <div class="email-body">
                        <p>Dear <strong>' . htmlspecialchars($req['RequestedByName'] ?? 'Requester') . '</strong>,</p>
                        <div class="info-box">
                            Your Resource Request for <strong>' . htmlspecialchars($req['JobTitle'] ?? 'Position') . '</strong> has been <strong>APPROVED / ACCEPTED</strong> by ' . htmlspecialchars($req['ApproverName'] ?? 'Approver') . '.
                        </div>
                        <table class="info-table">
                            <tr>
                                <td class="label">Request Code:</td>
                                <td><strong style="color:#28a745;">' . htmlspecialchars($req['RequestCode'] ?? '-') . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Job Title:</td>
                                <td>' . htmlspecialchars($req['JobTitle'] ?? '-') . '</td>
                            </tr>
                            <tr>
                                <td class="label">Approval Date:</td>
                                <td>' . htmlspecialchars($actionedAt) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Approver Comments:</td>
                                <td>' . htmlspecialchars($comment) . '</td>
                            </tr>
                        </table>
                        <div style="text-align: center; margin-top: 25px;">
                            <a href="' . $actionLink . '" class="btn-action">View Approved Resources</a>
                        </div>
                    </div>
                    <div class="email-footer">
                        <p>This is an automated notification from I-NET Recruitment Portal.</p>
                    </div>
                </div>
            </body>
            </html>';

            require_once(APPPATH . 'libraries/InetMailer.php');
            $objs = new InetMailer();
            $mail = $objs->load();
            if ($mail) {
                $mail->Timeout = 3;
                $mail->setFrom('info@inetcsc.com', 'I-NET Recruitment Portal');
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;

                if (!empty($req["RequestedByEmail"])) {
                    $mail->addAddress(trim($req["RequestedByEmail"]));
                    @$mail->send();
                }
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Accept Email Error: ' . $e->getMessage());
            return false;
        }
    }

    private function _sendResourceRequestRejectEmail($requestId)
    {
        try {
            $req = $this->admin_model->getResourceRequestById($requestId);
            if (empty($req) || empty($req["RequestedByEmail"])) return false;

            $actionedAt = !empty($req["UpdatedAt"]) ? $req["UpdatedAt"] : date("Y-m-d H:i:s");
            $comment = !empty($req["ApprovalComment"]) ? $req["ApprovalComment"] : "-";

            $subject = "Resource Request REJECTED [" . $req["RequestCode"] . "] - " . $req["JobTitle"];

            $htmlBody = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: "Segoe UI", Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
                    .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e9ecef; }
                    .email-header { background: linear-gradient(135deg, #dc3545, #c82333); padding: 24px; text-align: center; color: #ffffff; }
                    .email-header h2 { margin: 0; font-size: 22px; font-weight: 600; }
                    .email-body { padding: 30px; }
                    .info-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 15px; color: #721c24; }
                    .info-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    .info-table td { padding: 10px; border-bottom: 1px solid #e9ecef; font-size: 14px; }
                    .info-table td.label { font-weight: 600; color: #495057; width: 40%; }
                    .email-footer { background: #f1f3f5; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
                </style>
            </head>
            <body>
                <div class="email-card">
                    <div class="email-header">
                        <h2>Resource Request Update</h2>
                    </div>
                    <div class="email-body">
                        <p>Dear <strong>' . htmlspecialchars($req['RequestedByName'] ?? 'Requester') . '</strong>,</p>
                        <div class="info-box">
                            Your Resource Request for <strong>' . htmlspecialchars($req['JobTitle'] ?? 'Position') . '</strong> has been <strong>REJECTED</strong>.
                        </div>
                        <table class="info-table">
                            <tr>
                                <td class="label">Request Code:</td>
                                <td><strong style="color:#dc3545;">' . htmlspecialchars($req['RequestCode'] ?? '-') . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Job Title:</td>
                                <td>' . htmlspecialchars($req['JobTitle'] ?? '-') . '</td>
                            </tr>
                            <tr>
                                <td class="label">Rejection Date:</td>
                                <td>' . htmlspecialchars($actionedAt) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Approver Comments:</td>
                                <td>' . htmlspecialchars($comment) . '</td>
                            </tr>
                        </table>
                    </div>
                    <div class="email-footer">
                        <p>This is an automated notification from I-NET Recruitment Portal.</p>
                    </div>
                </div>
            </body>
            </html>';

            require_once(APPPATH . 'libraries/InetMailer.php');
            $objs = new InetMailer();
            $mail = $objs->load();
            if ($mail) {
                $mail->Timeout = 3;
                $mail->setFrom('info@inetcsc.com', 'I-NET Recruitment Portal');
                $mail->addAddress(trim($req['RequestedByEmail']));
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;

                return @$mail->send();
            }
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'Reject Email Error: ' . $e->getMessage());
            return false;
        }
    }

    private function _sendVacancyOnHoldEmailToRecruiter($jid)
    {
        if (empty($jid)) return false;

        $job = $this->db->select('jl.*, u.EmpName AS RecruiterName, u.EmpEmail AS RecruiterEmail, pb.EmpName AS PostedByName, pb.EmpEmail AS PostedByEmail, d.Departmentname')
                        ->from('IHRJobsList jl')
                        ->join('IHUsers u', 'u.IUid = jl.AssignedRecruiterManagerId', 'left')
                        ->join('IHUsers pb', 'pb.IUid = jl.PostedBy', 'left')
                        ->join('Departments d', 'd.Did = jl.Did', 'left')
                        ->where('jl.Jid', $jid)
                        ->get()
                        ->row_array();

        if (empty($job)) return false;

        $holdDate = !empty($job['HoldUntilDate']) ? $job['HoldUntilDate'] : 'Not specified';

        $subject = "Job Placed On-Hold: " . $job['JobTitle'] . " (" . $job['JobCode'] . ")";

        $message  = "Dear %s,\n\n";
        $message .= "This is to inform you that the following vacancy has been placed ON-HOLD in the Recruitment system:\n\n";
        $message .= "Job Code    : " . $job['JobCode'] . "\n";
        $message .= "Job Title   : " . $job['JobTitle'] . "\n";
        $message .= "Department  : " . (!empty($job['Departmentname']) ? $job['Departmentname'] : 'N/A') . "\n";
        $message .= "Openings    : " . (!empty($job['NoofOpenings']) ? $job['NoofOpenings'] : 1) . "\n";
        $message .= "Status      : On-Hold\n";
        $message .= "Hold Until  : " . $holdDate . "\n";
        $message .= "Updated On  : " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Please pause all recruitment activities for this position until the hold date.\n";
        $message .= "A reminder will be sent 3 days before the hold date expires.\n\n";
        $message .= "Best regards,\nHR Recruitment System";

        // Collect recipients: Recruiter Manager + Posted By (Assigned Recruiter)
        $recipients = [];

        $recruiterManagerEmail = !empty($job['RecruiterEmail']) ? trim($job['RecruiterEmail']) : '';
        $recruiterManagerName  = !empty($job['RecruiterName']) ? trim($job['RecruiterName']) : 'Recruiter Manager';
        if (!empty($recruiterManagerEmail)) {
            $recipients[$recruiterManagerEmail] = $recruiterManagerName;
        }

        $assignedRecruiterEmail = !empty($job['PostedByEmail']) ? trim($job['PostedByEmail']) : '';
        $assignedRecruiterName  = !empty($job['PostedByName'])  ? trim($job['PostedByName'])  : 'Recruiter';
        if (!empty($assignedRecruiterEmail) && $assignedRecruiterEmail !== $recruiterManagerEmail) {
            $recipients[$assignedRecruiterEmail] = $assignedRecruiterName;
        }

        if (empty($recipients)) return false;

        $anySent = false;
        foreach ($recipients as $email => $name) {
            $personalMessage = sprintf($message, htmlspecialchars($name));
            $sent = false;
            try {
                if (file_exists(APPPATH . 'libraries/InetMailer.php')) {
                    require_once(APPPATH . 'libraries/InetMailer.php');
                    $objs = new InetMailer();
                    $mail = $objs->load();
                    if ($mail) {
                        $mail->setFrom('info@inetcsc.com', 'I-NET CSC Recruitment');
                        $mail->addAddress($email);
                        $mail->isHTML(false);
                        $mail->Subject = $subject;
                        $mail->Body    = $personalMessage;
                        $sent = $mail->send();
                    }
                }
            } catch (Exception $e) {}

            if (!$sent) {
                $headers  = "From: info@inetcsc.com\r\n";
                $headers .= "Reply-To: info@inetcsc.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                $sent = @mail($email, $subject, $personalMessage, $headers);
            }
            if ($sent) $anySent = true;
        }

        return $anySent;
    }

    private function _sendHoldReminderEmail($job)
    {
        if (empty($job)) return false;

        $holdDate = !empty($job['HoldUntilDate']) ? $job['HoldUntilDate'] : 'Not specified';
        $subject = "Reminder: Job Hold Expires in 3 Days – " . $job['JobTitle'] . " (" . $job['JobCode'] . ")";

        $message  = "Dear %s,\n\n";
        $message .= "This is an automated 3-day reminder that the hold period for the following vacancy is ending soon:\n\n";
        $message .= "Job Code    : " . $job['JobCode'] . "\n";
        $message .= "Job Title   : " . $job['JobTitle'] . "\n";
        $message .= "Department  : " . (!empty($job['Departmentname']) ? $job['Departmentname'] : 'N/A') . "\n";
        $message .= "Hold Ends On: " . $holdDate . "\n";
        $message .= "Status      : On-Hold\n\n";
        $message .= "Please prepare to resume recruitment activities for this position once the hold period ends.\n\n";
        $message .= "Best regards,\nHR Recruitment System";

        $recipients = [];

        $recruiterManagerEmail = !empty($job['RecruiterEmail']) ? trim($job['RecruiterEmail']) : '';
        $recruiterManagerName  = !empty($job['RecruiterName']) ? trim($job['RecruiterName']) : 'Recruiter Manager';
        if (!empty($recruiterManagerEmail)) {
            $recipients[$recruiterManagerEmail] = $recruiterManagerName;
        }

        $assignedRecruiterEmail = !empty($job['PostedByEmail']) ? trim($job['PostedByEmail']) : '';
        $assignedRecruiterName  = !empty($job['PostedByName'])  ? trim($job['PostedByName'])  : 'Recruiter';
        if (!empty($assignedRecruiterEmail) && $assignedRecruiterEmail !== $recruiterManagerEmail) {
            $recipients[$assignedRecruiterEmail] = $assignedRecruiterName;
        }

        if (empty($recipients) && !empty($job['PostedBy'])) {
            $postedUser = $this->db->select('EmpName, EmpEmail')->where('IUid', $job['PostedBy'])->get('IHUsers')->row();
            if ($postedUser && !empty($postedUser->EmpEmail)) {
                $recipients[trim($postedUser->EmpEmail)] = trim($postedUser->EmpName);
            }
        }

        if (empty($recipients)) return false;

        $anySent = false;
        foreach ($recipients as $email => $name) {
            $personalMessage = sprintf($message, htmlspecialchars($name));
            $sent = false;
            try {
                if (file_exists(APPPATH . 'libraries/InetMailer.php')) {
                    require_once(APPPATH . 'libraries/InetMailer.php');
                    $objs = new InetMailer();
                    $mail = $objs->load();
                    if ($mail) {
                        $mail->setFrom('info@inetcsc.com', 'I-NET CSC Recruitment');
                        $mail->addAddress($email);
                        $mail->isHTML(false);
                        $mail->Subject = $subject;
                        $mail->Body    = $personalMessage;
                        $sent = $mail->send();
                    }
                }
            } catch (Exception $e) {}

            if (!$sent) {
                $headers  = "From: info@inetcsc.com\r\n";
                $headers .= "Reply-To: info@inetcsc.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                $sent = @mail($email, $subject, $personalMessage, $headers);
            }
            if ($sent) $anySent = true;
        }

        return $anySent;
    }




    public function ApprovedResources()
    {
        $check_session = $this->session->userdata("logged_in");
        if (empty($check_session)) {
            redirect($this->config->item("base_url") . "admin/index");
            return;
        }

        $roleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
        
        // Strict role validation: Only Recruitment Manager (10) or Management/Admin (1)
        if ($roleId !== 10 && $roleId !== 1) {
            $this->session->set_flashdata("error", "Access Denied: You do not have permission to access Approved Resources.");
            redirect($this->config->item("base_url") . "admin/dashboard");
            return;
        }

        $currentUrl = strtolower(uri_string());
        $data["currentUrlArray"] = $this->admin_model->getBreadcrumb($currentUrl);

        $data["approved_resources"]   = $this->admin_model->getApprovedResourceRequests();
        $data["pending_resources"]    = $this->admin_model->getResourceRequests(['Status' => 'PENDING APPROVAL']);
        $data["recruitment_managers"] = $this->admin_model->getRecruitmentManagers();
        $data["department"]           = $this->admin_model->getUserDepartments();
        $data["ctc_approvers"]        = $this->admin_model->getAllUsers();
        $data["employee_det"]         = $check_session;

        $this->template->write_view("content", "admin/ApprovedResources", $data);
        $this->template->render();
    }

    public function assignResourceToRecruiter()
    {
        $check_session = $this->session->userdata("logged_in");
        if (empty($check_session)) {
            echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
            return;
        }

        $roleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
        if ($roleId !== 10 && $roleId !== 1) {
            echo json_encode(["status" => "error", "message" => "Access Denied: Only Recruitment Managers can perform assignments."]);
            return;
        }

        $requestId         = (int)$this->input->post("requestId");
        $assignedManagerId = (int)$this->input->post("assignedManagerId");

        if (empty($requestId) || empty($assignedManagerId)) {
            echo json_encode(["status" => "error", "message" => "Invalid parameters."]);
            return;
        }

        $req = $this->admin_model->getResourceRequestById($requestId);
        if (empty($req) || ($req["Status"] !== "ACCEPTED" && $req["Status"] !== "ASSIGNED")) {
            echo json_encode(["status" => "error", "message" => "Only ACCEPTED or ASSIGNED resource requests can be assigned."]);
            return;
        }

        $targetUser = $this->db->select("u.IUid, u.EmpName, u.EmpEmail")
            ->from("IHUsers u")
            ->where("u.IUid", $assignedManagerId)
            ->get()->row_array();

        if (empty($targetUser)) {
            echo json_encode(["status" => "error", "message" => "Selected Recruitment Manager not found."]);
            return;
        }

        $jid = !empty($req["ConvertedJid"]) ? (int)$req["ConvertedJid"] : null;
        if (empty($jid) && !empty($req["RequestCode"])) {
            $existingJob = $this->db->select("Jid")->from("ihrjobslist")->where("JobCode", $req["RequestCode"])->get()->row_array();
            if (!empty($existingJob["Jid"])) {
                $jid = (int)$existingJob["Jid"];
                $this->admin_model->updateResourceRequest($requestId, ["ConvertedJid" => $jid]);
            }
        }

        if (empty($jid)) {
            $year = date("Y");
            $count = $this->db->count_all("ihrjobslist") + 1;
            do {
                $jobCode = "JOB-" . $year . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
                $exists  = $this->db->where("JobCode", $jobCode)->count_all_results("ihrjobslist");
                if ($exists) $count++;
            } while ($exists > 0);

            $vacancyData = [
                "JobCode"                    => $jobCode,
                "JobTitle"                   => $req["JobTitle"],
                "RoleSummary"                => $req["FunctionalRole"],
                "Did"                        => $req["Did"],
                "EmploymentType"             => "Full-Time",
                "WorkMode"                   => "Onsite",
                "EducationRequired"          => !empty($req["EducationRequired"]) ? $req["EducationRequired"] : "Bachelor Degree",
                "ExpMin"                     => $req["ExpMin"],
                "ExpMax"                     => $req["ExpMax"],
                "SalMin"                     => $req["SalMin"],
                "SalMax"                     => $req["SalMax"],
                "TargetOnboardingDate"       => !empty($req["TargetOnboardingDate"]) ? $req["TargetOnboardingDate"] : null,
                "Salary"                     => (!empty($req["SalMin"]) || !empty($req["SalMax"])) ? ($req["SalMin"] . " - " . $req["SalMax"] . " LPA") : "",
                "NoofOpenings"               => $req["NoofOpenings"],
                "JobStatus"                  => "Open",
                "JobDescription"             => $req["JobDescription"],
                "Responsibilities"           => $req["Responsibilities"],
                "PostedBy"                   => $check_session["IUid"],
                "CtcApproverId"              => !empty($req["CtcApproverId"]) ? (int)$req["CtcApproverId"] : null,
                "AssignedRecruiterManagerId" => $assignedManagerId,
                "PostedOn"                   => date("Y-m-d H:i:s")
            ];

            $this->db->insert("ihrjobslist", $vacancyData);
            $jid = $this->db->insert_id();

            if ($jid) {
                $this->admin_model->updateResourceRequest($requestId, [
                    "ConvertedJid"               => $jid,
                    "AssignedRecruiterManagerId" => $assignedManagerId,
                    "Status"                     => "ASSIGNED"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to create vacancy record."]);
                return;
            }
        } else {
            
            $this->db->where("Jid", $jid)->update("ihrjobslist", [
                "AssignedRecruiterManagerId" => $assignedManagerId
            ]);

            
            $this->admin_model->updateResourceRequest($requestId, [
                "AssignedRecruiterManagerId" => $assignedManagerId,
                "Status"                     => "ASSIGNED"
            ]);
        }

      
        if ($assignedManagerId !== (int)$check_session["IUid"]) {
            $this->load->model("Notification_model");
            $this->Notification_model->addNotification(
                "New Vacancy Assigned",
                "Resource Request [" . $req["RequestCode"] . "] (\"" . $req["JobTitle"] . "\") has been assigned to you by " . $check_session["EmpName"] . ".",
                "info",
                $assignedManagerId,
                null
            );
        }

        echo json_encode([
            "status"  => "success",
            "message" => "Resource Request successfully assigned to " . $targetUser["EmpName"] . "."
        ]);
    }

    public function getJobHistoryDetails()
    {
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        $Hrms_Session = $this->session->userdata('logged_in');
        if (empty($Hrms_Session)) {
            echo json_encode(['status' => 'error', 'msg' => 'Session expired. Please log in again.']);
            return;
        }

        $jid = (int)$this->input->post('jid');
        if (empty($jid)) {
            echo json_encode(['status' => 'error', 'msg' => 'Job ID missing']);
            return;
        }

       
        $job = $this->db
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

        if (empty($job)) {
            echo json_encode(['status' => 'error', 'msg' => 'Job not found']);
            return;
        }

        
        $resourceRequest = $this->db
            ->select('
                rr.*,
                d.Departmentname,
                u_req.EmpName AS RequestedByName,
                u_req.EmpEmail AS RequestedByEmail,
                u_arm.EmpName AS AssignedManagerName,
                u_ctc.EmpName AS CtcApproverName
            ')
            ->from('resource_requests rr')
            ->join('Departments d', 'd.Did = rr.Did', 'left')
            ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
            ->join('IHUsers u_arm', 'u_arm.IUid = rr.AssignedRecruiterManagerId', 'left')
            ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
            ->where('rr.ConvertedJid', $jid)
            ->get()
            ->row_array();

        if (empty($resourceRequest) && !empty($job['JobTitle'])) {
            $resourceRequest = $this->db
                ->select('
                    rr.*,
                    d.Departmentname,
                    u_req.EmpName AS RequestedByName,
                    u_ctc.EmpName AS CtcApproverName
                ')
                ->from('resource_requests rr')
                ->join('Departments d', 'd.Did = rr.Did', 'left')
                ->join('IHUsers u_req', 'u_req.IUid = rr.RequestedBy', 'left')
                ->join('IHUsers u_ctc', 'u_ctc.IUid = rr.CtcApproverId', 'left')
                ->where('rr.JobTitle', $job['JobTitle'])
                ->order_by('rr.RequestId', 'DESC')
                ->get()
                ->row_array();
        }

        // Sync CtcApproverId and CtcApproverName if available on either job or resourceRequest
        if (empty($job['CtcApproverId']) && !empty($resourceRequest['CtcApproverId'])) {
            $job['CtcApproverId']   = $resourceRequest['CtcApproverId'];
            $job['CtcApproverName'] = $resourceRequest['CtcApproverName'];
        } elseif (!empty($resourceRequest) && empty($resourceRequest['CtcApproverId']) && !empty($job['CtcApproverId'])) {
            $resourceRequest['CtcApproverId']   = $job['CtcApproverId'];
            $resourceRequest['CtcApproverName'] = $job['CtcApproverName'];
        }

        // 3. Candidate Summary
        $applications = $this->db
            ->select('ja.ApplicationId, ja.CurrentStatus, ja.AppliedOn, c.CandidateId, c.Fullname, c.CandidateCode')
            ->from('JobApplications ja')
            ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
            ->where('ja.Jid', $jid)
            ->get()
            ->result_array();

        $candidateCount = count($applications);

        // 4. Milestone Summary Extraction
        $ctcDisplay = !empty($job['CtcApproverName']) ? $job['CtcApproverName'] : (!empty($resourceRequest['CtcApproverName']) ? $resourceRequest['CtcApproverName'] : 'Not Assigned');

        $milestones = [
            'requested_by'        => !empty($resourceRequest['RequestedByName']) ? $resourceRequest['RequestedByName'] . ' (' . ($resourceRequest['RequestedOn'] ?? '-') . ')' : ($job['PostedByName'] ?? 'Hiring Manager'),
            'approved_by'         => !empty($resourceRequest['AssignedManagerName']) ? $resourceRequest['AssignedManagerName'] : ($job['PostedByName'] ?? 'System Admin'),
            'assigned_to'         => !empty($job['AssignedManagerName']) ? $job['AssignedManagerName'] : (!empty($resourceRequest['AssignedManagerName']) ? $resourceRequest['AssignedManagerName'] : 'Unassigned'),
            'ctc_approver'        => $ctcDisplay,
            'hold_at'             => null,
            'hold_until'          => $job['HoldUntilDate'] ?? null,
            'unhold_at'           => null,
            'dropped_at'          => null,
            'closed_at'           => null,
            'position_filled'     => null
        ];

        // 5. Fetch Audit History from JobTracking Table
        if (!$this->db->table_exists('JobTracking')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS JobTracking (
                TrackId INT AUTO_INCREMENT PRIMARY KEY,
                Jid INT NULL,
                RequestId INT NULL,
                EventType VARCHAR(50) NOT NULL,
                EventTitle VARCHAR(255) NOT NULL,
                EventDescription TEXT NULL,
                HoldUntilDate DATE NULL,
                ActionBy INT NULL,
                ActionAt DATETIME NOT NULL,
                CreatedOn DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        }

        // Fetch tracking records for this Jid
        $trackingRows = $this->db
            ->select('jt.*, u.EmpName AS ActionByName')
            ->from('JobTracking jt')
            ->join('IHUsers u', 'u.IUid = jt.ActionBy', 'left')
            ->where('jt.Jid', $jid)
            ->order_by('jt.ActionAt', 'ASC')
            ->get()
            ->result_array();

        // If no records in JobTracking yet, perform retroactive backfill
        if (empty($trackingRows)) {
            if (!empty($resourceRequest)) {
                $this->_addJobTrackingLog(
                    $jid,
                    'RESOURCE_REQUESTED',
                    'Resource Request Raised (' . ($resourceRequest['RequestCode'] ?? 'REQ') . ')',
                    'Requested ' . ($resourceRequest['NoofOpenings'] ?? 1) . ' opening(s) for "' . ($resourceRequest['JobTitle'] ?? '-') . '". Target Onboarding: ' . ($resourceRequest['TargetOnboardingDate'] ?? 'N/A') . '. CTC Approver: ' . ($resourceRequest['CtcApproverName'] ?? 'Not Assigned'),
                    null,
                    $resourceRequest['RequestedBy'] ?? null,
                    $resourceRequest['RequestId'] ?? null
                );

                if (!empty($resourceRequest['Status']) && $resourceRequest['Status'] === 'ACCEPTED') {
                    $this->_addJobTrackingLog(
                        $jid,
                        'REQUEST_APPROVED',
                        'Resource Request Approved & Assigned',
                        'Resource request approved. Assigned Recruiter Manager: ' . ($resourceRequest['AssignedManagerName'] ?? 'Assigned Manager') . ' | CTC Approver: ' . ($resourceRequest['CtcApproverName'] ?? 'Not Assigned'),
                        null,
                        $resourceRequest['AssignedRecruiterManagerId'] ?? null,
                        $resourceRequest['RequestId'] ?? null
                    );
                }
            }

            $ctcName = !empty($job['CtcApproverName']) ? $job['CtcApproverName'] : (!empty($resourceRequest['CtcApproverName']) ? $resourceRequest['CtcApproverName'] : 'Not Assigned');
            $this->_addJobTrackingLog(
                $jid,
                'VACANCY_CREATED',
                'Vacancy Created (' . $job['JobCode'] . ')',
                'Job Vacancy "' . $job['JobTitle'] . '" created with status: ' . $job['JobStatus'] . '. Posted/Approved By: ' . ($job['PostedByName'] ?? 'System') . ' | Assigned Manager: ' . ($job['AssignedManagerName'] ?? 'Unassigned') . ' | CTC Approver: ' . $ctcName,
                null,
                $job['PostedBy'] ?? null
            );

            // If current status is On-Hold and still no tracking entry logged, log it
            $currentStatusLower = strtolower(trim($job['JobStatus'] ?? ''));
            if ($currentStatusLower === 'on-hold' || $currentStatusLower === 'on hold') {
                $this->_addJobTrackingLog(
                    $jid,
                    'JOB_ON_HOLD',
                    'Job Placed On-Hold',
                    'Job status updated to On-Hold until: ' . ($job['HoldUntilDate'] ?? 'Not specified'),
                    $job['HoldUntilDate'] ?? null,
                    $job['AssignedRecruiterManagerId'] ?? null
                );
            }

            // Re-query JobTracking after backfill
            $trackingRows = $this->db
                ->select('jt.*, u.EmpName AS ActionByName')
                ->from('JobTracking jt')
                ->join('IHUsers u', 'u.IUid = jt.ActionBy', 'left')
                ->where('jt.Jid', $jid)
                ->order_by('jt.ActionAt', 'ASC')
                ->get()
                ->result_array();
        }

        // Build timeline & extract milestone dates from JobTracking
        $holdCount   = 0;
        $unholdCount = 0;
        $timeline    = [];

        foreach ($trackingRows as $tr) {
            $evtType = strtoupper(trim($tr['EventType']));
            $icon    = 'fas fa-info-circle bg-info';
            $badge   = 'badge-info';
            $title   = $tr['EventTitle'];

            if ($evtType === 'RESOURCE_REQUESTED') {
                $icon  = 'fas fa-file-signature bg-info';
                $badge = 'badge-info';
            } elseif ($evtType === 'REQUEST_APPROVED') {
                $icon  = 'fas fa-user-check bg-success';
                $badge = 'badge-success';
            } elseif ($evtType === 'VACANCY_CREATED') {
                $icon  = 'fas fa-briefcase bg-primary';
                $badge = 'badge-primary';
            } elseif ($evtType === 'JOB_ON_HOLD') {
                $holdCount++;
                $icon  = 'fas fa-pause-circle bg-warning';
                $badge = 'badge-warning';
                $title = 'Job Placed On-Hold' . ($holdCount > 1 ? ' (Cycle #' . $holdCount . ')' : '');
                $milestones['hold_at']    = $tr['ActionAt'];
                $milestones['hold_until'] = !empty($tr['HoldUntilDate']) ? $tr['HoldUntilDate'] : ($job['HoldUntilDate'] ?? null);
            } elseif ($evtType === 'JOB_UNHELD' || $evtType === 'JOB_REOPENED') {
                $unholdCount++;
                $icon  = 'fas fa-play-circle bg-success';
                $badge = 'badge-success';
                $title = 'Job Reopened / Unheld' . ($unholdCount > 1 ? ' (Cycle #' . $unholdCount . ')' : '');
                $milestones['unhold_at']  = $tr['ActionAt'];
            } elseif ($evtType === 'JOB_DROPPED' || $evtType === 'JOB_CLOSED') {
                $icon  = 'fas fa-times-circle bg-danger';
                $badge = 'badge-danger';
                $title = 'Job Dropped';
                $milestones['dropped_at'] = $tr['ActionAt'];
                $milestones['closed_at']  = $tr['ActionAt'];
            }

            $timeline[] = [
                'type'        => strtolower($evtType),
                'title'       => $title,
                'timestamp'   => $tr['ActionAt'] ?? '-',
                'user'        => $tr['ActionByName'] ?? 'System',
                'description' => $tr['EventDescription'] ?? '',
                'badge_color' => $badge,
                'icon'        => $icon
            ];
        }

        // Stage 4: Position Filled / Selected Candidates Check
        if (!empty($applications)) {
            $appIds = array_column($applications, 'ApplicationId');
            if (!empty($appIds)) {
                $filledCandidates = $this->db
                    ->select('ja.*, c.Fullname AS CandidateName, c.CandidateCode, cst.ActionAt AS FilledAt, u.EmpName AS FilledByName')
                    ->from('JobApplications ja')
                    ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
                    ->join('CandidateStageTracking cst', 'cst.ApplicationId = ja.ApplicationId AND (LOWER(cst.Action) LIKE "%selected%" OR LOWER(cst.Action) LIKE "%offer%")', 'left')
                    ->join('IHUsers u', 'u.IUid = cst.ActionBy', 'left')
                    ->where_in('ja.ApplicationId', $appIds)
                    ->group_by('ja.ApplicationId')
                    ->get()
                    ->result_array();

                foreach ($filledCandidates as $fc) {
                    $currSt = strtolower(trim($fc['CurrentStatus'] ?? ''));
                    if (!empty($fc['FilledAt']) || $currSt === 'selected' || strpos($currSt, 'offer') !== false) {
                        $filledTime = !empty($fc['FilledAt']) ? $fc['FilledAt'] : ($fc['AppliedOn'] ?? '-');
                        $milestones['position_filled'] = [
                            'candidate_name' => $fc['CandidateName'],
                            'candidate_code' => $fc['CandidateCode'],
                            'filled_at'       => $filledTime,
                            'filled_by'       => $fc['FilledByName'] ?? 'HR Evaluator'
                        ];

                        $timeline[] = [
                            'type'        => 'position_filled',
                            'title'       => '🎉 Position Filled: Candidate Selected (' . ($fc['CandidateName'] ?? 'Candidate') . ')',
                            'timestamp'   => $filledTime,
                            'user'        => $fc['FilledByName'] ?? 'HR Evaluator',
                            'description' => 'Candidate ' . ($fc['CandidateName'] ?? 'Candidate') . ' (' . ($fc['CandidateCode'] ?? '-') . ') was selected and hired for this job vacancy.',
                            'badge_color' => 'badge-success',
                            'icon'        => 'fas fa-trophy bg-success'
                        ];
                    }
                }
            }
        }

        // Sort timeline by timestamp ascending
        if (is_array($timeline) && count($timeline) > 0) {
            usort($timeline, function ($a, $b) {
                $tsA = (!empty($a['timestamp']) && $a['timestamp'] !== '-') ? strtotime($a['timestamp']) : 0;
                $tsB = (!empty($b['timestamp']) && $b['timestamp'] !== '-') ? strtotime($b['timestamp']) : 0;
                return $tsA <=> $tsB;
            });
        } else {
            $timeline = [];
        }

        echo json_encode([
            'status'           => 'success',
            'job'              => $job,
            'resource_request' => $resourceRequest,
            'candidate_count'  => $candidateCount,
            'milestones'       => $milestones,
            'timeline'         => $timeline
        ]);
    }

    public function generateAiInterviewQuestions()
    {
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        try {
            $Hrms_Session = $this->session->userdata('logged_in');
            if (empty($Hrms_Session)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
                return;
            }

            $interviewId    = (int)$this->input->post('interviewId');
            $isRegeneration = (bool)$this->input->post('isRegeneration');

            if (empty($interviewId)) {
                echo json_encode(['status' => 'error', 'message' => 'Interview ID is required.']);
                return;
            }

            $this->load->library('AiInterviewQuestionGenerator');
            $result = $this->aiinterviewquestiongenerator->generateForInterview($interviewId, $Hrms_Session['IUid'], $isRegeneration);

            echo json_encode($result);
        } catch (\Throwable $e) {
            log_message('error', '[generateAiInterviewQuestions Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'AI question generation is temporarily unavailable. Please try again.']);
        }
    }

    public function getAiInterviewQuestions()
    {
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        try {
            $Hrms_Session = $this->session->userdata('logged_in');
            if (empty($Hrms_Session)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
                return;
            }

            $interviewId = (int)$this->input->post('interviewId');
            $version     = $this->input->post('version') !== null ? (int)$this->input->post('version') : null;

            if (empty($interviewId)) {
                echo json_encode(['status' => 'error', 'message' => 'Interview ID is required.']);
                return;
            }

            $this->load->library('AiInterviewQuestionGenerator');
            $questions = $this->aiinterviewquestiongenerator->getQuestionsForInterview($interviewId, $version);

            // Also fetch candidate name, job title, ATS score, MustHaveSkills, and all available versions
            $interviewDetails = $this->db
                ->select('c.Fullname as CandidateName, c.ProfileMatchPer, j.JobTitle, j.JobCode, j.MustHaveSkills')
                ->from('CandidateInterviews ci')
                ->join('JobApplications ja', 'ja.ApplicationId = ci.ApplicationId', 'left')
                ->join('IHrCandidates c', 'c.CandidateId = ja.CandidateId', 'left')
                ->join('IHRJobsList j', 'j.Jid = ja.Jid', 'left')
                ->where('ci.InterviewId', $interviewId)
                ->get()
                ->row_array();

            $versionsRes = $this->db
                ->distinct()
                ->select('generation_version')
                ->where('interview_id', $interviewId)
                ->order_by('generation_version', 'DESC')
                ->get('ai_interview_questions')
                ->result_array();

            $versions = !empty($versionsRes) ? array_column($versionsRes, 'generation_version') : [];

            // Determine source
            $source = 'ai';
            $latestGen = $this->db->select('reason')
                ->where('interview_id', $interviewId)
                ->where('is_active', 1)
                ->limit(1)
                ->get('ai_interview_questions')
                ->row_array();
            if (!empty($latestGen) && isset($latestGen['reason']) && stripos($latestGen['reason'], 'fallback') !== false) {
                $source = 'fallback';
            }

            // Extract must-have skills from job
            $mustHaveList = [];
            if (!empty($interviewDetails['MustHaveSkills'])) {
                $mustHaveList = array_values(array_unique(array_filter(array_map('trim', explode(',', $interviewDetails['MustHaveSkills'])))));
            }

            // Extract covered and uncovered skills from questions
            $coveredLower = [];
            if (!empty($questions)) {
                foreach ($questions as $q) {
                    if (!empty($q['skill'])) {
                        foreach (explode(',', $q['skill']) as $s) {
                            $c = strtolower(trim($s));
                            if (!empty($c)) $coveredLower[] = $c;
                        }
                    }
                    foreach ($mustHaveList as $ms) {
                        $msClean = strtolower(trim($ms));
                        if (!empty($msClean) && stripos($q['question'], $msClean) !== false) {
                            $coveredLower[] = $msClean;
                        }
                    }
                }
            }

            $coveredMustHave   = [];
            $uncoveredMustHave = [];
            foreach ($mustHaveList as $ms) {
                $msClean = strtolower(trim($ms));
                if (in_array($msClean, $coveredLower)) {
                    $coveredMustHave[] = $ms;
                } else {
                    $uncoveredMustHave[] = $ms;
                }
            }

            echo json_encode([
                'status'                   => 'success',
                'interview_id'             => $interviewId,
                'candidate_name'           => is_array($interviewDetails) ? ($interviewDetails['CandidateName'] ?? 'Candidate') : 'Candidate',
                'job_title'                => is_array($interviewDetails) ? ($interviewDetails['JobTitle'] ?? '') : '',
                'ats_score'                => is_array($interviewDetails) ? ($interviewDetails['ProfileMatchPer'] ?? 'N/A') : 'N/A',
                'source'                   => $source,
                'covered_must_have_skills' => array_values(array_unique($coveredMustHave)),
                'uncovered_must_have_skills' => array_values(array_unique($uncoveredMustHave)),
                'available_versions'       => $versions,
                'questions'                => $questions
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[getAiInterviewQuestions Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'Unable to load AI questions. Please try again.']);
        }
    }

    public function updateQuestionStatus()
    {
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        try {
            $Hrms_Session = $this->session->userdata('logged_in');
            if (empty($Hrms_Session)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
                return;
            }

            $questionId = (int)$this->input->post('questionId');
            $status     = strtolower(trim($this->input->post('status') ?? 'unasked'));
            $notes      = trim($this->input->post('notes') ?? '');

            if (empty($questionId)) {
                echo json_encode(['status' => 'error', 'message' => 'Question ID is required.']);
                return;
            }

            $updateData = ['status_notes' => $status];
            if (!empty($notes)) {
                $updateData['interviewer_notes'] = $notes;
            }

            $this->db->where('id', $questionId)->update('ai_interview_questions', $updateData);

            echo json_encode(['status' => 'success', 'message' => 'Question status updated successfully.']);
        } catch (\Throwable $e) {
            log_message('error', '[updateQuestionStatus Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'Unable to update status. Please try again.']);
        }
    }

    public function compareCandidates()
    {
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        try {
            $Hrms_Session = $this->session->userdata('logged_in');
            if (empty($Hrms_Session)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
                return;
            }

            $candidateIds = $this->input->post('candidate_ids');
            $vacancyId    = (int)$this->input->post('vacancy_id');

            if (empty($candidateIds) || !is_array($candidateIds) || count($candidateIds) < 2) {
                echo json_encode(['status' => 'error', 'message' => 'Please select at least 2 candidates to compare.']);
                return;
            }

            $candidateIds = array_values(array_unique(array_map('intval', $candidateIds)));

            $vacancy = $this->db->where('Jid', $vacancyId)->get('IHRJobsList')->row_array();
            if (empty($vacancy)) {
                echo json_encode(['status' => 'error', 'message' => 'Job vacancy record not found.']);
                return;
            }

            $candidates = $this->db
                ->select('CandidateId, CandidateCode, Fullname, Email, PhoneNo, ProfileMatchPer, ScoreBreakdown, MatchedSkills, ExpYrs, ExperienceMatch, EducationMatch, ResumePath, ExperienceDetails')
                ->where_in('CandidateId', $candidateIds)
                ->get('IHrCandidates')
                ->result_array();

            if (empty($candidates)) {
                echo json_encode(['status' => 'error', 'message' => 'No candidate records found for comparison.']);
                return;
            }

            $mustHaveSkills = array_values(array_unique(array_filter(array_map('trim', explode(',', $vacancy['MustHaveSkills'] ?? '')))));
            $niceHaveSkills = array_values(array_unique(array_filter(array_map('trim', explode(',', $vacancy['NiceToHaveSkills'] ?? '')))));

            $comparisonList = [];

            foreach ($candidates as $cand) {
                $scoreBreakdown = [];
                if (!empty($cand['ScoreBreakdown'])) {
                    $decoded = json_decode($cand['ScoreBreakdown'], true);
                    if (is_array($decoded)) $scoreBreakdown = $decoded;
                }

                $expDetails = [];
                if (!empty($cand['ExperienceDetails'])) {
                    $decoded = json_decode($cand['ExperienceDetails'], true);
                    if (is_array($decoded)) $expDetails = $decoded;
                }

                $candSkills = [];
                if (!empty($scoreBreakdown['relevant_evidence'])) {
                    foreach ($scoreBreakdown['relevant_evidence'] as $ev) {
                        if (preg_match('/Extracted Resume Skills:\s*(.+)/i', $ev, $m)) {
                            $candSkills = array_merge($candSkills, array_map('trim', explode(',', $m[1])));
                        }
                    }
                }
                if (!empty($cand['MatchedSkills'])) {
                    $candSkills = array_merge($candSkills, array_map('trim', explode(',', $cand['MatchedSkills'])));
                }

                // Extract directly from Resume PDF text
                $resumeText = '';
                if (!empty($cand['ResumePath'])) {
                    $pdfFile = FCPATH . $cand['ResumePath'];
                    if (file_exists($pdfFile)) {
                        try {
                            require_once FCPATH . 'vendor/autoload.php';
                            $parser = new \Smalot\PdfParser\Parser();
                            $pdfObj = $parser->parseFile($pdfFile);
                            $resumeText = $pdfObj->getText();
                        } catch (\Throwable $ex) {}
                    }
                }

                if (!empty($resumeText)) {
                    $allCheckSkills = array_merge($mustHaveSkills, $niceHaveSkills, [
                        'React', 'React.js', 'Node.js', 'Node', 'MongoDB', 'REST API', 'Express',
                        'PHP', 'MERN', 'JavaScript', 'HTML', 'CSS', 'Docker', 'AWS', 'Git', 'SQL',
                        'Recruitment', 'Payroll', 'Onboarding', 'Attendance', 'Exit Process', 'Audit', 'Excel', 'PowerBI'
                    ]);
                    foreach ($allCheckSkills as $kw) {
                        if (!empty($kw) && stripos($resumeText, $kw) !== false) {
                            $candSkills[] = $kw;
                        }
                    }
                }
                $candSkills = array_values(array_unique(array_filter($candSkills)));

                $matchedMustHave = [];
                $missingMustHave = [];
                $candSkillsLower = array_map('strtolower', $candSkills);

                foreach ($mustHaveSkills as $ms) {
                    $msLower = strtolower(trim($ms));
                    if (empty($msLower)) continue;
                    $found = false;
                    foreach ($candSkillsLower as $cs) {
                        if (strpos($cs, $msLower) !== false || strpos($msLower, $cs) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        $matchedMustHave[] = $ms;
                    } else {
                        $missingMustHave[] = $ms;
                    }
                }

                $comparisonList[] = [
                    'candidate_id'       => $cand['CandidateId'],
                    'candidate_code'     => $cand['CandidateCode'],
                    'fullname'           => (!empty($cand['Fullname']) && trim($cand['Fullname']) !== '' && $cand['Fullname'] !== 'N/A') ? $cand['Fullname'] : (!empty($cand['CandidateCode']) ? $cand['CandidateCode'] : 'Candidate #' . $cand['CandidateId']),
                    'email'              => $cand['Email'],
                    'phone'              => $cand['PhoneNo'],
                    'ats_score'          => $cand['ProfileMatchPer'] ?? 'N/A',
                    'experience_years'   => (float)($cand['ExpYrs'] ?? 0),
                    'experience_match'   => $cand['ExperienceMatch'] ?? 'N/A',
                    'education_match'    => $cand['EducationMatch'] ?? 'N/A',
                    'matched_must_have'  => $matchedMustHave,
                    'missing_must_have'  => $missingMustHave,
                    'all_candidate_skills'=> $candSkills,
                    'experience_details' => $expDetails,
                    'score_breakdown'    => $scoreBreakdown,
                    'resume_path'        => $cand['ResumePath'] ?? '',
                ];
            }

            $aiSummary = $this->generateAiComparisonSummary($vacancy, $comparisonList);

            echo json_encode([
                'status'           => 'success',
                'vacancy_id'       => $vacancyId,
                'job_title'        => $vacancy['JobTitle'] ?? '',
                'job_code'         => $vacancy['JobCode'] ?? '',
                'must_have_skills' => $mustHaveSkills,
                'nice_have_skills' => $niceHaveSkills,
                'candidates'       => $comparisonList,
                'ai_summary'       => $aiSummary,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[compareCandidates Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'Failed to generate candidate comparison: ' . $e->getMessage()]);
        }
    }

    private function generateAiComparisonSummary($vacancy, $comparisonList)
    {
        $topChoice = '';
        $topCandObj = null;
        $highestCompositeScore = -999;
        $differentiators = [];
        $mustHaveList = array_values(array_unique(array_filter(array_map('trim', explode(',', $vacancy['MustHaveSkills'] ?? '')))));
        $totalMustHave = count($mustHaveList);

        $suitableCount = 0;

        foreach ($comparisonList as $c) {
            $matchedCount = count($c['matched_must_have']);
            $missingCount = count($c['missing_must_have']);
            $atsScoreText = trim($c['ats_score'] ?? '');
            $atsScoreLower = strtolower($atsScoreText);

            $scoreBreakdown = $c['score_breakdown'] ?? [];
            $domainStatus = strtoupper($scoreBreakdown['domain_status'] ?? '');
            $candidateDomain = $scoreBreakdown['candidate_domain'] ?? ($scoreBreakdown['domain'] ?? '');

            // Identify if candidate is Not Suitable / Wrong Domain / Domain Mismatch
            $isNotSuitable = (
                strpos($atsScoreLower, 'not suitable') !== false ||
                strpos($atsScoreLower, 'wrong domain') !== false ||
                strpos($atsScoreLower, 'mismatch') !== false ||
                strpos($atsScoreLower, 'not recommended') !== false ||
                $domainStatus === 'MISMATCH' ||
                $domainStatus === 'WRONG_DOMAIN'
            );

            // ATS Fit Score (20% weight)
            $atsVal = 50;
            if (strpos($atsScoreLower, 'strong') !== false || $atsScoreText === 'Recommended') {
                $atsVal = 95;
            } elseif (strpos($atsScoreLower, 'potential') !== false) {
                $atsVal = 70;
            } elseif (strpos($atsScoreLower, 'review') !== false) {
                $atsVal = 40;
            } elseif (strpos($atsScoreLower, 'low') !== false) {
                $atsVal = 20;
            } elseif ($isNotSuitable) {
                $atsVal = 0;
            } else {
                $num = (float)preg_replace('/[^0-9\.]/', '', $atsScoreText);
                if ($num > 0) $atsVal = $num;
            }

            // Skill Score (60% weight)
            $skillScore = ($totalMustHave > 0) ? (($matchedCount / $totalMustHave) * 100) : 50;

            // Experience Score (20% weight)
            $expVal = ($c['experience_match'] === 'Yes') ? 100 : min(100, $c['experience_years'] * 20);

            if ($isNotSuitable) {
                // Not suitable / wrong domain candidates cannot compete for leading fit
                $composite = -1000 + $skillScore;
            } else {
                $suitableCount++;
                $composite = ($skillScore * 0.60) + ($atsVal * 0.20) + ($expVal * 0.20);
            }

            if ($composite > $highestCompositeScore) {
                $highestCompositeScore = $composite;
                $topChoice = $c['fullname'];
                $topCandObj = $c;
            }

            // Build differentiator string for each candidate
            if ($isNotSuitable) {
                $domainDetail = !empty($candidateDomain) ? " ({$candidateDomain} Background)" : "";
                $diffStr = "<strong>{$c['fullname']}</strong>: <span class='text-danger font-weight-bold'>Not Suitable / Domain Mismatch{$domainDetail}</span>. Background does not align with {$vacancy['JobTitle']} role requirements.";
            } else {
                if ($totalMustHave > 0 && $matchedCount === 0) {
                    $missingStr = implode(', ', $c['missing_must_have']);
                    $diffStr = "<strong>{$c['fullname']}</strong>: <span class='text-warning font-weight-bold'>Matches 0/{$totalMustHave} Must-Have skill(s)</span> (Missing: <em>{$missingStr}</em>) with {$c['experience_years']} yrs experience.";
                } elseif ($missingCount > 0) {
                    $missingStr = implode(', ', $c['missing_must_have']);
                    $diffStr = "<strong>{$c['fullname']}</strong>: Matches {$matchedCount}/{$totalMustHave} Must-Have skill(s) (Missing: <em>{$missingStr}</em>) with {$c['experience_years']} yrs experience.";
                } else {
                    $diffStr = "<strong>{$c['fullname']}</strong>: <span class='text-success font-weight-bold'>Matches {$matchedCount}/{$totalMustHave} Must-Have skill(s)</span> with {$c['experience_years']} yrs experience.";
                }

                if (!empty($c['all_candidate_skills'])) {
                    $diffStr .= " Core Skills: " . implode(', ', array_slice($c['all_candidate_skills'], 0, 5)) . ".";
                }
            }
            $differentiators[] = $diffStr;
        }

        if ($suitableCount === 0) {
            $topChoice = "No Suitable Candidate Found";
            $recommendation = "<strong>Domain & Competency Warning:</strong> None of the selected candidates are suitable for <strong>{$vacancy['JobTitle']}</strong> due to domain mismatch or lack of core role competency alignment. Sourcing candidates with relevant experience is strongly recommended.";
        } else {
            $topMatchedCount = count($topCandObj['matched_must_have'] ?? []);
            $topMissingCount = count($topCandObj['missing_must_have'] ?? []);
            $topMissingList  = $topCandObj['missing_must_have'] ?? [];

            if ($totalMustHave > 0 && $topMatchedCount === 0) {
                $missingSkillsStr = implode(', ', $topMissingList);
                $recommendation = "<strong>Competency Alert:</strong> <strong>{$topChoice}</strong> is selected as the top candidate among applicants for <strong>{$vacancy['JobTitle']}</strong> based on general fit and experience, but <span class='text-danger font-weight-bold'>matches 0 of {$totalMustHave} Must-Have skills</span> (Missing: <em>{$missingSkillsStr}</em>). Detailed technical screening or skill verification is strongly recommended.";
            } elseif ($totalMustHave > 0 && $topMissingCount > 0) {
                $missingSkillsStr = implode(', ', $topMissingList);
                $matchedSkillsStr = implode(', ', $topCandObj['matched_must_have']);
                $recommendation = "Based on ATS fit match and experience, <strong>{$topChoice}</strong> is identified as the leading candidate for <strong>{$vacancy['JobTitle']}</strong> (Matches: <em>{$matchedSkillsStr}</em>), but <span class='text-warning font-weight-bold'>lacks {$topMissingCount} Must-Have skill(s)</span> (Missing: <em>{$missingSkillsStr}</em>).";
            } else {
                $recommendation = "Based on complete Must-Have skill alignment ({$totalMustHave}/{$totalMustHave}), ATS fit match, and experience, <strong>{$topChoice}</strong> is identified as the leading candidate for <strong>{$vacancy['JobTitle']}</strong>.";
            }
        }

        return [
            'top_choice'      => $topChoice,
            'recommendation'  => $recommendation,
            'differentiators' => $differentiators,
        ];
    }

    public function analyzeResumeModal()
    {
        require_once APPPATH . 'modules/admin/controllers/Ats.php';
        $ats = new Ats();
        $ats->analyzeResumeModal();
    }
}