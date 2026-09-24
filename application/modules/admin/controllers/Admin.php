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

		$this->admin_model->syncDefaultMenuIcons();

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
			
			
			$user = $this->admin_model->getActiveUserByEmail($email);

			if (empty($user)) {
				$this->session->set_flashdata('error', 'Email address not found.');
				redirect($this->config->item('base_url').'admin/ForgotPassword');
			} else {
				
				$token = bin2hex(random_bytes(32));

				
				$this->admin_model->updateUserResetToken($user->IUid, $token, date('Y-m-d H:i:s'));

				
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
					$mail->Body = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body {
        margin: 0;
        padding: 20px 0;
        background-color: #f6f8fa;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: #333333;
    }
    .email-wrapper {
        width: 100%;
        table-layout: fixed;
    }
    .email-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #ffffff;
        border: 1px solid #e2e5e9;
        padding: 30px;
    }
    .email-header {
        font-size: 18px;
        font-weight: 700;
        color: #172b4d;
        border-bottom: 2px solid #0052cc;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }
    .button {
        display: inline-block;
        background-color: #0052cc;
        color: #ffffff !important;
        text-decoration: none;
        padding: 10px 22px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
        margin: 15px 0;
    }
    .security-notice {
        background-color: #f8f9fa;
        border-left: 3px solid #0052cc;
        padding: 12px 16px;
        margin: 20px 0;
        font-size: 13px;
        color: #495057;
        line-height: 1.5;
    }
    .email-footer {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px solid #eaecef;
        font-size: 12px;
        color: #6a737d;
        line-height: 1.5;
    }
</style>
</head>
<body>
<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <div class="email-container">
                <div class="email-header">
                    Password Reset Request
                </div>

                <p>Hello <strong>'.htmlspecialchars($user->EmpName).'</strong>,</p>

                <p>We received a request to reset the password for your account.</p>

                <p>Click the button below to create a new password:</p>

                <p>
                    <a href="' . $resetLink . '" class="button" target="_blank">Reset Password</a>
                </p>

                <p style="font-size: 13px; color: #5e6c84;">
                    If the button above does not work, copy and paste the following link into your browser:
                </p>

                <p style="font-size: 13px; word-break: break-all;">
                    <a href="'.$resetLink.'" style="color: #0052cc;">'.$resetLink.'</a>
                </p>

                <div class="security-notice">
                    <strong>Security Notice:</strong><br>
                    &bull; This password reset link is valid for a limited time.<br>
                    &bull; If you did not raise this password reset request, please contact the Support Team immediately to secure your account.<br>
                    &bull; If you require any assistance, please reach out to the Support Team.
                </div>

                <p style="margin-top: 20px;">
                    Thank you,<br>
                    <strong>REC Support Team</strong>
                </p>

                <div class="email-footer">
                    &copy; '.date('Y').' REC. All Rights Reserved.<br>
                    This is an automated notification. Please do not reply directly to this email.
                </div>
            </div>
        </td>
    </tr>
</table>
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

	public function ResetPassword($token = '')
	{
		if (empty($token)) {
			$this->session->set_flashdata('error', 'Invalid or expired reset token.');
			redirect($this->config->item('base_url')."admin/index");
			return;
		}

		$user = $this->admin_model->getUserByResetToken($token);

		if (empty($user)) {
			$this->session->set_flashdata('error', 'Invalid or expired reset token.');
			redirect($this->config->item('base_url')."admin/index");
		}

		// Check token expiration (20 minutes validity)
		$expiry = strtotime($user->ResetTokenCreatedAt . ' +20 minutes');
		if (time() > $expiry) {
			// Clear expired token
			$this->admin_model->clearUserResetToken($user->IUid);
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

			
			$this->admin_model->updateUserPassword($user->IUid, md5($newPassword));

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

			$IUidquery = $this->admin_model->validateLogin($Username, md5($LogPassword));
			
				
				if(!empty($IUidquery)){
 
					// Step 1 Passed: Do NOT create final logged_in session yet.
					// Store pending OTP login state
					$pending_data = array(
						'user'         => $IUidquery[0],
						'initiated_at' => time()
					);
					$this->session->set_userdata('pending_otp_login', $pending_data);

					// Invalidate any existing pending OTPs for this user
					$this->admin_model->invalidatePendingOtps($IUidquery[0]['IUid']);

					// Generate a secure 6-digit OTP
					$otp = sprintf("%06d", random_int(100000, 999999));
					$otpHash = password_hash($otp, PASSWORD_DEFAULT);
					$expiresAt = date('Y-m-d H:i:s', time() + 60);

					// Store OTP record via model (Issue 1 architecture)
					$this->admin_model->createOtpRecord(
						$IUidquery[0]['IUid'],
						$otpHash,
						$expiresAt,
						$this->input->ip_address(),
						substr($this->input->user_agent(), 0, 255)
					);

					// Send OTP to user's registered email
					$this->_sendOtpEmail($IUidquery[0]['EmpEmail'], $IUidquery[0]['EmpName'], $otp);

					// Redirect to OTP verification screen
					redirect($this->config->item('base_url').'admin/VerifyOtp');
					return;
 
                 } else {

                 	$this->session->set_flashdata('error', 'Invalid Credentials or User Inactive.!');
	   			    redirect($this->config->item('base_url').'admin/index');
	   			    return;

                 }
				
			}
		  	$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
			$this->template->write_view('content', 'admin/index');
			$this->template->render();

	}

	public function VerifyOtp()
	{
		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['user'])) {
			redirect($this->config->item('base_url').'admin/index');
			return;
		}

		$user = $pending['user'];
		$activeOtp = $this->admin_model->getLatestActiveOtp($user['IUid']);

		$remaining_seconds = 0;
		if (!empty($activeOtp)) {
			$remaining_seconds = max(0, strtotime($activeOtp->expires_at) - time());
		}

		$data = array(
			'user_email'        => $user['EmpEmail'],
			'user_name'         => $user['EmpName'],
			'remaining_seconds' => $remaining_seconds
		);

		$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
		$this->template->write_view('content', 'admin/VerifyOtp', $data);
		$this->template->render();
	}

	public function VerifyOtpSubmit()
	{
		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['user'])) {
			redirect($this->config->item('base_url').'admin/index');
			return;
		}

		$user = $pending['user'];
		$otpInput = trim($this->input->post('otp'));

		if (empty($otpInput) || strlen($otpInput) !== 6 || !ctype_digit($otpInput)) {
			$this->session->set_flashdata('error', 'Invalid OTP.');
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		$activeOtp = $this->admin_model->getLatestActiveOtp($user['IUid']);

		if (empty($activeOtp)) {
			$this->session->set_flashdata('error', 'Invalid OTP.');
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		// Brute force protection: maximum 5 incorrect attempts
		if ($activeOtp->attempt_count >= 5) {
			$this->admin_model->markOtpExpired($activeOtp->id);
			$this->session->set_flashdata('error', 'Too many incorrect attempts. Please request a new OTP.');
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		// Server-side expiry check (60 seconds)
		if (time() > strtotime($activeOtp->expires_at)) {
			$this->admin_model->markOtpExpired($activeOtp->id);
			$this->session->set_flashdata('error', 'OTP has expired. Please request a new OTP.');
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		// Verify OTP hash
		if (!password_verify($otpInput, $activeOtp->otp_hash)) {
			$this->admin_model->incrementOtpAttempts($activeOtp->id);
			$updatedOtp = $this->admin_model->getLatestActiveOtp($user['IUid']);
			if (!empty($updatedOtp) && $updatedOtp->attempt_count >= 5) {
				$this->admin_model->markOtpExpired($activeOtp->id);
				$this->session->set_flashdata('error', 'Too many incorrect attempts. Please request a new OTP.');
			} else {
				$this->session->set_flashdata('error', 'Invalid OTP.');
			}
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		// OTP is valid! Mark as verified
		$this->admin_model->markOtpVerified($activeOtp->id);

		// Remove pending session
		$this->session->unset_userdata('pending_otp_login');

		// Create the EXACT existing logged_in session structure
		$sess_array = array(
			'IUid'      => $user['IUid'],
			'EmpCode'   => $user['EmpCode'],
			'EmpName'   => $user['EmpName'],
			'EmpEmail'  => $user['EmpEmail'],
			'EmpPhone'  => $user['EmpPhone'],
			'EmpDOB'    => $user['EmpDOB'],
			'EmpGender' => $user['EmpGender'],
			'EmpRoleId' => $user['Erid'],
			'DepDid'    => $user['Did'],
			'UStatus'   => $user['UStatus']
		);
		$this->session->set_userdata('logged_in', $sess_array);

		// Execute existing audit log logic
		$IHRMS_Data = $this->session->userdata('logged_in');
		$data['Logdescription'] = "User ".$IHRMS_Data['IUid']." Logged in Successfully on ".date("Y M d H i s");
		$ip_address = $this->input->ip_address();
		$data['IUid'] = $IHRMS_Data['IUid'];
		$data['EmpRole'] = $IHRMS_Data['EmpRoleId'];
		$data['Ipaddress'] = $ip_address;
		$curr_time = time();
		$login_time = date("Y-m-d H:i:s", $curr_time);
		$data['LogInTime'] = $login_time;
		$data['LogOutTime'] = '';
		$data['Status'] = '1';
		$this->admin_model->logUserLogin($data);

		redirect($this->config->item('base_url').'admin/dashboard');
	}

	public function ResendOtp()
	{
		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['user'])) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(array('status' => 'error', 'message' => 'Session expired. Please login again.'));
				return;
			}
			redirect($this->config->item('base_url').'admin/index');
			return;
		}

		$user = $pending['user'];

		// Clear any previous error flashdata from session
		$this->session->unset_userdata('error');

		// Resend rate limiting: prevent resending more than 1 OTP per 5 seconds
		$activeOtp = $this->admin_model->getLatestActiveOtp($user['IUid']);
		if (!empty($activeOtp) && (time() - strtotime($activeOtp->created_at)) < 5) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(array('status' => 'error', 'message' => 'Please wait a few seconds before requesting a new OTP.'));
				return;
			}
			$this->session->set_flashdata('error', 'Please wait a few seconds before requesting a new OTP.');
			redirect($this->config->item('base_url').'admin/VerifyOtp');
			return;
		}

		// Invalidate previous pending OTPs
		$this->admin_model->invalidatePendingOtps($user['IUid']);

		// Generate a new 6-digit OTP
		$newOtp = sprintf("%06d", random_int(100000, 999999));
		$otpHash = password_hash($newOtp, PASSWORD_DEFAULT);
		$expiresAt = date('Y-m-d H:i:s', time() + 60);

		$this->admin_model->createOtpRecord(
			$user['IUid'],
			$otpHash,
			$expiresAt,
			$this->input->ip_address(),
			substr($this->input->user_agent(), 0, 255)
		);

		// Send email
		$this->_sendOtpEmail($user['EmpEmail'], $user['EmpName'], $newOtp);

		if ($this->input->is_ajax_request()) {
			echo json_encode(array(
				'status'            => 'success',
				'message'           => 'A new OTP has been sent to your registered email address.',
				'remaining_seconds' => 60
			));
			return;
		}

		$this->session->set_flashdata('success', 'A new OTP has been sent to your registered email address.');
		redirect($this->config->item('base_url').'admin/VerifyOtp');
	}

	private function _sendOtpEmail($toEmail, $toName, $otp)
	{
		require_once(APPPATH . 'libraries/InetMailer.php');
		$objs = new InetMailer();
		$mail = $objs->load();

		try {
			$mail->setFrom('inet@inetcsc.com', 'I-NET Secure Labs');
			$mail->addAddress(trim($toEmail));
			$mail->isHTML(true);
			$mail->Subject = 'Your Login OTP for I-NET Secure Labs';

			$userName = !empty($toName) ? htmlspecialchars($toName) : 'User';
			$safeOtp  = htmlspecialchars($otp);

			$body = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { margin: 0; padding: 20px 0; background-color: #f6f8fa; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .email-container { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e1e4e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .header { background: #1a56e8; padding: 24px; text-align: center; color: #ffffff; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: -0.2px; }
    .content { padding: 32px 28px; color: #24292e; line-height: 1.6; font-size: 14px; }
    .otp-card { background: #f2f5fd; border: 1.5px dashed #1a56e8; border-radius: 8px; text-align: center; padding: 18px; margin: 24px 0; }
    .otp-code { font-size: 32px; font-weight: 700; color: #1a56e8; letter-spacing: 6px; font-family: Consolas, Monaco, monospace; }
    .footer { padding: 16px 28px 24px; border-top: 1px solid #f0f2f5; font-size: 12px; color: #6a737d; text-align: center; }
</style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>I-NET Secure Labs</h1>
    </div>
    <div class="content">
        <p>Dear ' . $userName . ',</p>
        <p>Your one-time password (OTP) for login is:</p>
        <div class="otp-card">
            <div class="otp-code">' . $safeOtp . '</div>
        </div>
        <p>This OTP is valid for 1 minute (60 seconds).</p>
        <p>If you did not attempt to log in, please ignore this email.</p>
        <p style="margin-top: 24px;">Thanks &amp; Regards,<br>Recruitment Team</p>
    </div>
    <div class="footer">
        &copy; ' . date('Y') . ' I-NET Secure Labs Pvt Ltd. All rights reserved.
    </div>
</div>
</body>
</html>';

			$mail->Body = $body;
			$mail->AltBody = "Dear " . $userName . ",\n\nYour one-time password (OTP) for login is:\n\n" . $safeOtp . "\n\nThis OTP is valid for 1 minute (60 seconds).\n\nIf you did not attempt to log in, please ignore this email.\n\nThanks & Regards,\nRecruitment Team";

			return $mail->send();
		} catch (\Exception $e) {
			log_message('error', 'OTP Email Send Error: ' . $e->getMessage());
			return false;
		}
	}

	private function _sendPasswordSetupEmail($toEmail, $toName, $token)
	{
		require_once(APPPATH . 'libraries/InetMailer.php');
		$objs = new InetMailer();
		$mail = $objs->load();

		try {
			$mail->setFrom('inet@inetcsc.com', 'Recruitment Portal');
			$mail->addAddress(trim($toEmail));
			$mail->isHTML(true);
			$mail->Subject = 'Welcome to Recruitment Portal';

			$userName    = !empty($toName) ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'User';
			$setupLink   = $this->config->item('base_url') . 'admin/ResetPassword/' . $token;
			$escapedLink = htmlspecialchars($setupLink, ENT_QUOTES, 'UTF-8');

			$body = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { margin: 0; padding: 20px 0; background-color: #f6f8fa; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .email-container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e1e4e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .header { background: #1a56e8; padding: 24px; text-align: center; color: #ffffff; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: -0.2px; }
    .content { padding: 32px 28px; color: #24292e; line-height: 1.6; font-size: 14px; }
    .content p { margin: 0 0 16px 0; }
    .btn-container { text-align: center; margin: 28px 0; }
    .btn-setup { display: inline-block; background-color: #1a56e8; color: #ffffff !important; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 15px; }
    .notice { font-size: 13px; color: #586069; margin: 16px 0; }
    .footer { padding: 16px 28px 24px; border-top: 1px solid #f0f2f5; font-size: 12px; color: #6a737d; text-align: center; }
</style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>Recruitment Portal</h1>
    </div>
    <div class="content">
        <p>Hello ' . $userName . ',</p>
        <p>Your Account has been Registered Successfully.</p>
        <p>Please Take a second to Activate and Create Password for your Account.</p>
        <div class="btn-container">
            <a href="' . $escapedLink . '" class="btn-setup" style="display: inline-block; background-color: #1a56e8; color: #ffffff !important; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 15px;" target="_blank">Create Password</a>
        </div>
        <p class="notice">This password setup link is valid for 20 minutes.</p>
        <p class="notice">For more details Contact us on info@inetcsc.com, +91 44 - 4400666.</p>
        <p style="margin-top: 24px; margin-bottom: 0;">Thank You,<br>Recruitment Team</p>
    </div>
    <div class="footer">
        &copy; ' . date('Y') . ' Recruitment Portal. All rights reserved.
    </div>
</div>
</body>
</html>';

			$mail->Body = $body;
			$mail->AltBody = "Hello " . $userName . ",\n\nYour Account has been Registered Successfully.\n\nPlease Take a second to Activate and Create Password for your Account.\n\nThis password setup link is valid for 20 minutes.\n\nFor more details Contact us on info@inetcsc.com, +91 44 - 4400666.\n\nThank You,\nRecruitment Team";

			return $mail->send();
		} catch (\Throwable $e) {
			log_message('error', 'Password Setup Email Send Error: ' . $e->getMessage());
			return false;
		}
	}


 
    private function _getAccessibleJobIds($Hrms_Session)
    {
        return $this->admin_model->getAccessibleJobIds($Hrms_Session);
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

    $accessibleJobIds = $this->_getAccessibleJobIds($Hrms_Session);

    $roleName = strtolower(trim($this->admin_model->getRoleName($roleId)));
    $data["isHiringManager"] = ($roleName === 'hiring manager');
    $data["isManagement"]    = in_array($roleName, ['management', 'admin', 'super admin'], true);

    $data["recruitment_stages"] = $this->admin_model->getDashboardStagesWithCounts($roleId, $uid, $accessibleJobIds);

    $this->_checkAutoUnholdExpiredJobs();

    $vacCounts = $this->admin_model->getDashboardVacancyCountsExact($accessibleJobIds);
    $data["total_vacancies"]  = $vacCounts['total'];
    $data["onhold_vacancies"] = $vacCounts['onhold'];
    $data["open_vacancies"]   = $vacCounts['open'];

    $today = date('Y-m-d');
    $reminderDate = date('Y-m-d', strtotime('+3 days')); 
    $onHoldReminders = $this->admin_model->getOnHoldRemindersExact($reminderDate, $accessibleJobIds);
    $data["onhold_reminders"] = $onHoldReminders;

    foreach ($onHoldReminders as $remJob) {
        if (empty($remJob['HoldReminderSentDate']) || $remJob['HoldReminderSentDate'] !== $today) {
            $sent = $this->_sendHoldReminderEmail($remJob);
            if ($sent && !empty($remJob['Jid'])) {
                $this->admin_model->updateHoldReminderSentDate($remJob['Jid'], $today);
            }
        }
    }

    $this->admin_model->normalizeLegacyHrStatus();

    $rrCounts = $this->admin_model->getResourceRequestCounts();
    $data["total_resource_requests"]   = $rrCounts['total'];
    $data["pending_resource_requests"] = $rrCounts['pending'];
    $data["accepted_resource_requests"]= $rrCounts['accepted'];

    $rejected = $this->admin_model->getDashboardRejectedCount($accessibleJobIds);
    $screened = $this->admin_model->getDashboardScreenedCount($accessibleJobIds);

    $data["donut_labels"] = json_encode(["Total Vacancies", "On Hold", "Rejected", "Screened"]);
    $data["donut_values"] = json_encode([$data["total_vacancies"], $data["onhold_vacancies"], $rejected, $screened]);

    $monthlyStats = $this->admin_model->getDashboardMonthlyStatsExact($accessibleJobIds);
    $data["monthly_labels"] = json_encode(["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]);
    $data["monthly_values"] = json_encode(array_values($monthlyStats['apps']));
    $data["area_posted"]    = json_encode(array_values($monthlyStats['selected']));
    $data["area_rejected"]  = json_encode(array_values($monthlyStats['rejected']));

    $roleCounts = $this->admin_model->getDashboardRoleUserCounts();
    $data["user_labels"] = json_encode($roleCounts['labels']);
    $data["user_counts"] = json_encode($roleCounts['counts']);

    $data["all_jobs"] = $this->admin_model->getDashboardAllJobsExact($roleId, $uid, $accessibleJobIds);
    $data["all_candidates"] = $this->admin_model->getDashboardAllCandidatesExact($roleId, $uid, $accessibleJobIds);

    $data["candidate_total"] = count($data["all_candidates"]);
    $data["candidate_screened"] = $screened;
    $data["candidate_rejected"] = $rejected;

    $data["Resource_Requests_list"] = $this->admin_model->getDashboardResourceRequestsList();
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

    $roleId = isset($Hrms_Session["EmpRoleId"]) ? (int)$Hrms_Session["EmpRoleId"] : 0;
    if (!$this->admin_model->hasPagePermission($roleId, 'admin/Analytics')) {
        $this->session->set_flashdata("error", "Access Denied: You do not have permission to access Analytics.");
        redirect($this->config->item("base_url") . "admin/dashboard");
        return;
    }

    $currentUrl = strtolower(uri_string());
    $data["currentUrlArray"] = $this->admin_model->getBreadcrumb($currentUrl);

    $accessibleJobIds = $this->_getAccessibleJobIds($Hrms_Session);

    $summary = $this->admin_model->getAnalyticsSummaryCountsExact($accessibleJobIds);
    $data["total_jobs"]          = $summary["total_jobs"];
    $data["total_candidates"]    = $summary["total_candidates"];
    $data["total_applications"]  = $summary["total_applications"];
    $data["total_requests"]      = $summary["total_requests"];
    $data["open_jobs"]           = $summary["open_jobs"];
    $data["closed_jobs"]         = $summary["closed_jobs"];
    $data["hold_jobs"]           = $summary["hold_jobs"];
    $data["hired_candidates"]    = $summary["hired_candidates"];
    $data["rejected_candidates"] = $summary["rejected_candidates"];

    $data["all_jobs_history"]       = $this->admin_model->getAnalyticsJobsHistoryExact($accessibleJobIds);
    $data["all_candidates_history"] = $this->admin_model->getAnalyticsCandidatesHistoryExact($accessibleJobIds);
    $data["all_requests_history"]   = $this->admin_model->getAnalyticsRequestsHistory();
    $data["dept_analytics"]         = $this->admin_model->getAnalyticsDeptAnalyticsExact();
    $data["recruiter_analytics"]    = $this->admin_model->getAnalyticsRecruiterAnalyticsExact();
    $data["interviewer_summary"]    = $this->admin_model->getAnalyticsInterviewerSummaryExact();
    $data["interviewer_details"]    = $this->admin_model->getAnalyticsInterviewerDetailsExact();
    $data["departments"]            = $this->admin_model->getDepartments();

    $this->template->set_master_template("../../themes/" . $this->config->item("active_template") . "/bo_template.php");
    $this->template->write_view("content", "admin/Analytics", $data);
    $this->template->render();
}

public function ManageUsers(){

		$Hrms_Session=$this->session->userdata('logged_in');  
 		if(isset($Hrms_Session) && !empty($Hrms_Session))
		{	
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/ManageUsers')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Manage Users.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

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
                $data['jobdetails'] = $this->admin_model->getJobById($Jid);
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

        $candidate = $this->admin_model->getCandidateDetailsBasic($candidate_id);

        if (!$candidate) {
            echo json_encode(['status' => 'error', 'message' => 'Candidate not found']);
            return;
        }

        if (!empty($candidate['ExperienceDetails'])) {
            $candidate['experience_details'] = json_decode($candidate['ExperienceDetails'], true);
        }
        $applicationId = $candidate['ApplicationId'];

    	$stages = $this->admin_model->getCandidateTrackingStages($applicationId);

        $interviews = $this->admin_model->getCandidateInterviewsSimple($applicationId);

        foreach ($interviews as $idx => &$iv) {
            $iv['InterviewRound'] = $idx + 1;
        }
        unset($iv);

        $offers = $this->admin_model->getCandidateOffers($applicationId);

        $followups = $this->admin_model->getCandidateFollowUps($applicationId);

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

        $candidate = $this->admin_model->getCandidate360Info($candidate_id);

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

       
        $stages = $this->admin_model->getCandidateTrackingStages($applicationId);

       
        $interviews = $this->admin_model->getCandidateInterviewsWithInterviewers($applicationId);

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

        
        $aiQuestions = $this->admin_model->getCandidateAiQuestions($candidate_id);

        
        $offers = $this->admin_model->getCandidateOffers($applicationId);

        
        $hiring = $this->admin_model->getCandidateHiringRow($applicationId);

       
        $followups = $this->admin_model->getCandidateFollowUps($applicationId);

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

        $app = $this->admin_model->getApplicationById($applicationId);
        if (!$app) {
            echo json_encode(['status' => 'error', 'msg' => 'Application record not found']);
            return;
        }

        $uid = !empty($Hrms_Session['IUid']) ? $Hrms_Session['IUid'] : 17;
        $statusText = 'HR Final Decision: ' . ucwords(strtolower($decision));

        
        $this->admin_model->updateJobApplication($applicationId, [
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
        $this->admin_model->insertCandidateStageTracking($trackData);

       
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
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/ManageDepartments')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Manage Departments.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

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

                
                $existsCode = $this->admin_model->checkUserExistsByCode($EmpCode);
                if ($existsCode > 0) {
                    $this->session->set_flashdata('error', 'Employee ID already exists. Please use a different Employee Code.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                    return;
                }

               
                $existsEmail = $this->admin_model->checkUserExistsByEmail($EmpEmail);
                if ($existsEmail > 0) {
                    $this->session->set_flashdata('error', 'Email Address already exists. Please use a different email.');
                    $this->session->set_flashdata('form_values', $formValues);
                    redirect($this->config->item('base_url').'admin/ManageUsers');
                    return;
                }

              
                $existsPhone = $this->admin_model->checkUserExistsByPhone($EmpPhone);
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
                    $UsrId = $this->admin_model->insertUser($insertData);

                    if ($UsrId > 0) {
                        $setupToken = bin2hex(random_bytes(32));
                        $tokenCreatedAt = date('Y-m-d H:i:s');
                        $this->admin_model->updateUserResetToken($UsrId, $setupToken, $tokenCreatedAt);

                        $emailSent = $this->_sendPasswordSetupEmail($EmpEmail, $EmpName, $setupToken);

                        if ($emailSent) {
                            $this->session->set_flashdata('success', 'User added successfully and password setup email has been sent.');
                        } else {
                            $this->session->set_flashdata('warning', 'User added successfully, but failed to send password setup email. Please check mail settings.');
                        }
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

        if (!$this->admin_model->hasPagePermission($roleId, 'admin/VaccancyList')) {
            $this->session->set_flashdata('error', 'You do not have permission to access the Vacancy List page.');
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

			 	 $result = $this->admin_model->searchJobLocations($q);

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

			 	 $result = $this->admin_model->searchJobEducations($q);

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

			 	 $result = $this->admin_model->searchJobLanguages($q);

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

			 	 $result = $this->admin_model->searchSkills($q);

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
        $roleRow = $this->admin_model->getRoleById($roleId);
        $roleName = !empty($roleRow) ? strtolower($roleRow['RoleName']) : '';
        $approverId = $this->input->post('approverId');

         if ($roleName === 'hiring manager' || !empty($approverId)) {
            $requestCode = $this->admin_model->getNextResourceRequestCode();

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

       
        $exists = $this->admin_model->checkJobExists($jobTitle, $department);

        if($exists){
            $this->session->set_flashdata('job_exists', 'This job title already exists in this department');
            redirect($this->config->item('base_url')."admin/VaccancyList");
            return;
        }

        $jobCode = $this->generateJobCode();

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

        $jobId = $this->admin_model->insertVacancy($jobData);

        
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

    $last = $this->admin_model->getLastJobByCodePrefix($prefix);

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

        $exists = $this->admin_model->checkSkillExists($skill);

        if (!$exists) {
            $this->admin_model->insertSkill($skill);
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

            
            $skill = $this->admin_model->checkSkillExists($skillName);

            if ($skill) {
                $skillId = $skill->SkillId;
            } else {
               
                $skillId = $this->admin_model->insertSkill($skillName);
            }

            
            $exists = $this->admin_model->checkJobSkillExists($jobId, $skillId);

            if ($exists == 0) {
                $this->admin_model->insertJobSkill($jobId, $skillId);
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

        $this->admin_model->updateVacancy($jid, $updateData);

      
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
    $Hrms_Session = $this->session->userdata('logged_in');
    if (empty($actionBy) && !empty($Hrms_Session['IUid'])) {
        $actionBy = $Hrms_Session['IUid'];
    }

    $this->admin_model->insertJobTracking([
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
    $today = date('Y-m-d');
    $expiredJobs = $this->admin_model->getExpiredOnHoldJobs($today);

    foreach ($expiredJobs as $job) {
        $jid = (int)$job['Jid'];

     
        $this->admin_model->updateVacancy($jid, [
            'JobStatus'            => 'Open',
            'HoldUntilDate'        => null,
            'HoldReminderSentDate' => null,
            'UpdatedOn'            => date('Y-m-d H:i:s')
        ]);

     
        $latestTracking = $this->admin_model->getLatestTrackingByEvent($jid, 'JOB_UNHELD');

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
    $tableName = 'JobTracking';

    $existingTracking = $this->admin_model->getJobStatusHistoryRows($tableName);
    $totalExisting    = count($existingTracking);

    $jobs          = $this->db->get('IHRJobsList')->result_array();
    $migratedCount = 0;
    $skippedCount  = 0;

    foreach ($jobs as $job) {
        $jid      = (int)$job['Jid'];
        $tracking = $this->admin_model->getJobTrackingRows($jid);
        if (empty($tracking)) {
            $eventType  = 'VACANCY_CREATED';
            $eventTitle = 'Vacancy Created (' . ($job['JobCode'] ?? 'JOB') . ')';
            $eventDesc  = 'Job Vacancy "' . ($job['JobTitle'] ?? '') . '" created with status: ' . ($job['JobStatus'] ?? 'Open');
            $holdDate   = !empty($job['HoldUntilDate']) ? $job['HoldUntilDate'] : null;

            $this->admin_model->insertJobTracking([
                'Jid'              => $jid,
                'RequestId'        => null,
                'EventType'        => $eventType,
                'EventTitle'       => $eventTitle,
                'EventDescription' => $eventDesc,
                'HoldUntilDate'    => $holdDate,
                'ActionBy'         => !empty($job['PostedBy']) ? (int)$job['PostedBy'] : null,
                'ActionAt'         => !empty($job['PostedOn']) ? $job['PostedOn'] : date('Y-m-d H:i:s'),
                'CreatedOn'        => !empty($job['PostedOn']) ? $job['PostedOn'] : date('Y-m-d H:i:s')
            ]);
            $migratedCount++;
        } else {
            $skippedCount++;
        }
    }

    echo json_encode([
        'status'                    => 'success',
        'message'                   => 'Job status tracking is active using JobTracking.',
        'table'                     => $tableName,
        'migrated_rows'             => $migratedCount,
        'skipped_rows'              => $skippedCount,
        'total_analyzed'            => count($jobs),
        'existing_tracking_records' => $totalExisting
    ]);
}

public function UpdateUser()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $inps = $this->input->post();

        $userId   = isset($inps['IUid']) ? (int)$inps['IUid'] : 0;
        $EmpCode  = isset($inps['val-empid']) ? trim($inps['val-empid']) : (isset($inps['EmpCode']) ? trim($inps['EmpCode']) : '');
        $EmpEmail = isset($inps['val-email']) ? trim($inps['val-email']) : (isset($inps['EmpEmail']) ? trim($inps['EmpEmail']) : '');
        $EmpPhone = isset($inps['val-phoneus']) ? trim($inps['val-phoneus']) : (isset($inps['EmpPhone']) ? trim($inps['EmpPhone']) : '');

        if ($userId <= 0) {
            $this->session->set_flashdata('error', 'Invalid user selection.');
            redirect($this->config->item('base_url').'admin/ManageUsers');
            return;
        }

        if (!empty($EmpCode)) {
            $existsCode = $this->admin_model->checkUserExistsByCode($EmpCode, $userId);
            if ($existsCode > 0) {
                $this->session->set_flashdata('error', 'Employee Code already exists. Please use a different Employee Code.');
                redirect($this->config->item('base_url').'admin/ManageUsers');
                return;
            }
        }

        if (!empty($EmpEmail)) {
            $existsEmail = $this->admin_model->checkUserExistsByEmail($EmpEmail, $userId);
            if ($existsEmail > 0) {
                $this->session->set_flashdata('error', 'Email Address already exists. Please use a different email.');
                redirect($this->config->item('base_url').'admin/ManageUsers');
                return;
            }
        }

        if (!empty($EmpPhone)) {
            $existsPhone = $this->admin_model->checkUserExistsByPhone($EmpPhone, $userId);
            if ($existsPhone > 0) {
                $this->session->set_flashdata('error', 'Mobile Number already exists. Please use a different mobile number.');
                redirect($this->config->item('base_url').'admin/ManageUsers');
                return;
            }
        }

        $updateData = [
            'EmpName'        => isset($inps['val-username']) ? trim($inps['val-username']) : '',
            'EmpEmail'       => $EmpEmail,
            'EmpPhone'       => $EmpPhone,
            'EmpDOB'         => isset($inps['val-dob']) ? $inps['val-dob'] : null,
            'EmpGender'      => isset($inps['val-gender']) ? $inps['val-gender'] : null,
            'EmpDesignation' => isset($inps['val-designation']) ? trim($inps['val-designation']) : '',
            'Erid'           => isset($inps['val-Role']) ? $inps['val-Role'] : null,
            'Did'            => isset($inps['val-department']) ? $inps['val-department'] : null,
        ];

        if (!empty($EmpCode)) {
            $updateData['EmpCode'] = $EmpCode;
        }

        $this->admin_model->updateUser($userId, $updateData);

        $this->session->set_flashdata('success', 'User updated successfully');
        redirect($this->config->item('base_url').'admin/ManageUsers');

    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function SaveDepartment()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {

        $deptName = trim($this->input->post('val-depname'));

       
        $exists = $this->admin_model->checkDepartmentExists($deptName);

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

        $this->admin_model->insertDepartment($data);

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

        
        

        $this->admin_model->updateDepartment($inps['Did'], [
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

	    	$this->admin_model->setDepartmentStatus($id, 1);

		   
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

	    	$this->admin_model->setUserStatus($id, 1);

		    
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

    		
   			$this->admin_model->setUserStatus($id, 0);

            
             
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

    		
   			$this->admin_model->setDepartmentStatus($id, 0);

            
           
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

     $row = $this->admin_model->getJobDetailsRow($jid);

     if ($row && !empty($row['Jid'])) {
         $rr = $this->admin_model->getResourceRequestByConvertedJid($row['Jid']);

         if (empty($rr) && !empty($row['JobTitle'])) {
             $rr = $this->admin_model->getResourceRequestByJobTitle($row['JobTitle']);
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
                 $this->admin_model->updateVacancy($row['Jid'], ['CtcApproverId' => $rr['CtcApproverId']]);
             }
         }

         $rawPanels = $this->admin_model->getJobInterviewPanels($row['Jid']);
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

    $app = $this->admin_model->getLatestApplicationByCandidateId($candidateId);

    $jid = $app ? $app->Jid : 0;
    if (!$jid) {
        $cand = $this->admin_model->getCandidateById($candidateId);
        $jid = $cand ? (is_object($cand) ? $cand->Jid : ($cand['Jid'] ?? 0)) : 0;
    }

    $panels = [];
    if ($jid) {
        $panels = $this->admin_model->getInterviewPanelsWithInterviewers($jid);
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
                $codeRow = $this->admin_model->getResourceRequestByCode(trim($codeToSearch));
                if (!empty($codeRow)) {
                    $requestId = (int)$codeRow['RequestId'];
                    if (!empty($codeRow['ConvertedJid']) && (int)$codeRow['ConvertedJid'] > 0) {
                        $jid = (int)$codeRow['ConvertedJid'];
                    }
                }
            }
        }

        if ($jid <= 0 && $requestId > 0) {
            $req = $this->admin_model->getResourceRequestById($requestId);
            if (!empty($req)) {
                if (!empty($req['ConvertedJid']) && (int)$req['ConvertedJid'] > 0) {
                    $jid = (int)$req['ConvertedJid'];
                } else {
                    $year = date("Y");
                    $count = $this->admin_model->countAllJobs() + 1;
                    do {
                        $jobCode = "JOB-" . $year . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
                        $exists  = $this->admin_model->countJobsWithCode($jobCode);
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

                    $jid = $this->admin_model->insertVacancy($vacancyData);

                    if ($jid) {
                        $this->admin_model->updateResourceRequest($requestId, ['ConvertedJid' => $jid]);
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

        $updateRes = $this->admin_model->updateVacancyWithResult($jid, $data);
        if (!$updateRes['status']) {
            echo json_encode(['status' => 'error', 'msg' => 'DB update failed: ' . ($updateRes['message'] ?? 'Unknown error'), 'sql' => $updateRes['sql'] ?? '']);
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
            $this->admin_model->updateResourceRequestSync($requestId, $jid, $rrSync);
        }

        $interviewPanel = $this->input->post('interviewPanel');
        if ($interviewPanel !== null) {
            $this->admin_model->syncJobInterviewPanels($jid, $interviewPanel);
        }

        $skills = $this->input->post('skills'); 
        $this->admin_model->syncJobSkillsList($jid, $skills);

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
        $currentStage = $this->admin_model->getCurrentStageByOrder($currentOrder);
        $currentGroup = $currentStage ? $currentStage->StageGroup : 'Application';

        $nextStages = $this->admin_model->getNextRecruitmentStages($currentGroup, $currentOrder);
        echo json_encode($nextStages);
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
        // Resolve Screened stage when stageId is not provided
        //added for screened issue 
if ($actionLower === 'screened' && empty($stageId)) {

    $screenedStage = $this->admin_model
        ->getStageByGroupAndName('Application', 'Screened');

    if ($screenedStage) {
        $stageId = $screenedStage->StageId;
    } else {
        echo json_encode([
            'status' => 'error',
            'msg' => 'Screened stage is not configured.'
        ]);
        return;
    }
}
//added for screened issue 

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
        $debugLog .= "  teamsMeetingLink = " . $this->input->post('teamsMeetingLink') . "\n";
        $debugLog .= "---\n";
        file_put_contents(FCPATH . 'interview_debug.log', $debugLog, FILE_APPEND);
       

        $actionLower = strtolower(trim($action));

        if(($actionLower == 'shortlisted' || $actionLower == 'reschedule') && !empty($level)){
            $stageId = $level;
        }

        if($actionLower == 'rejected' && empty($stageId)){
           
            $fallback = $this->admin_model->getStageByGroupAndStatus('Rejection', 1);
            if($fallback){
                $stageId = $fallback->StageId;
            }
        }

     
        $app = $this->admin_model->getApplicationByCandidateId($candidateId);

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = $app->ApplicationId;

      
        $this->admin_model->insertCandidateStageTracking([
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
    elseif($actLower == 'shortlisted'){
        if (!empty($level)) {
            $currentStatus = (is_numeric($level) || stripos($level, 'level') === false) ? ('Level ' . trim($level)) : trim($level);
        } else {
            $currentStatus = 'Level 1';
        }
    }
    elseif(!empty($stageId)){
        $stageRow = $this->admin_model->getStageById($stageId);
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
    $this->admin_model->updateJobApplication($applicationId, $updateJobApp);
    $this->admin_model->updateCandidateStatus($candidateId, $currentStatus);

                


        
        $isFollowupStage = false;
        if(!empty($stageId)){
            $stageRow = $this->admin_model->getStageById($stageId);
            if($stageRow){
                $stageNameLower = strtolower(trim($stageRow->StageName));
                if($stageNameLower === 'switch off' || $stageNameLower === 'rnr'){
                    $isFollowupStage = true;
                }
            }
        }

        if($isFollowupStage && !empty($followupType)){
            $this->admin_model->insertCandidateFollowUp([
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

        $this->admin_model->updateJobApplication($applicationId, ['CurrentStatus' => 'Rejected']);

        $data['candidatelist'] = $this->admin_model->getCandidateByIdObj($candidateId);

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
            
            $existingCount = $this->admin_model->checkCandidateInterviewCount($applicationId);
            $isReschedule = (strtolower($action) == 'reschedule');

            if ($isReschedule) {
                $this->admin_model->rescheduleAssignedInterviews($applicationId);

                $targetRound = max(1, $existingCount + 1);
            } else {
                $targetRound = $existingCount + 1;
            }

            $meetLink = '';
            if (!empty($interviewType) && strtolower($interviewType) === 'online') {
                $postedMeetLink = trim($this->input->post('teamsMeetingLink') ?: ($this->input->post('meetLink') ?: ''));
                if (!empty($postedMeetLink)) {
                    $meetLink = $postedMeetLink;
                } else {
                    $candMeta = $this->admin_model->getCandidateWithJob($candidateId);

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

            $interviewId = $this->admin_model->insertCandidateInterview($interviewDataToSave);
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
         

           
            $this->admin_model->updateJobApplication($applicationId, ['CurrentStage' => $level]);
            


                $data['candidatelist'] = $this->admin_model->getCandidateByIdObj($candidateId);
                $data['action'] = strtolower($action);

                $jobRow = $this->admin_model->getCandidateJobInfo($candidateId);

                $data['jobTitle']      = $jobRow ? $jobRow->JobTitle : 'Job Position';
                $data['interviewDate'] = $interviewDate;
                $data['interviewTime'] = $this->input->post('interviewTime');
                $data['interviewMode'] = $interviewType;
                $data['meetLink']      = $meetLink;

                $interviewer = $this->admin_model->getUserContact($interviewerId);
                $data['interviewerName']  = $interviewer ? $interviewer->EmpName  : 'Interviewer';
                $data['interviewerEmail'] = $interviewer ? trim($interviewer->EmpEmail) : '';

                $data['interviewLevelName'] = !empty($level)
                    ? ((is_numeric($level) || stripos($level, 'level') === false) ? ('Level ' . trim($level)) : trim($level))
                    : 'Level 1';

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
    $stages = $this->admin_model->getInterviewStages();
    echo json_encode($stages);
}

public function MyInterviews()
{



    $Hrms_Session = $this->session->userdata('logged_in');
    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/MyInterviews')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access My Interviews.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

        $uid = $Hrms_Session['IUid'];

        $this->admin_model->cleanupDuplicateAssignedInterviews();

        $data['Candidatelist'] = $this->admin_model->getMyInterviewsList($uid);

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
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/interviewCalendar')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Interview Calendar.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

        $uid = $Hrms_Session['IUid'];

        $this->admin_model->cleanupDuplicateAssignedInterviews();

        $interviews = $this->admin_model->getInterviewCalendarDataExact($uid);

        
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

        $interview = $this->admin_model->getInterviewDetailsById($interviewId);

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

        $interview = $this->admin_model->getInterviewRecordById($interviewId);

        if (!$interview) {
            echo json_encode(['status' => 'error', 'msg' => 'Interview record not found']);
            return;
        }

        $applicationId = is_object($interview) ? ($interview->ApplicationId ?? null) : ($interview['ApplicationId'] ?? null);

        if (empty($applicationId)) {
            echo json_encode(['status' => 'error', 'msg' => 'Application ID missing']);
            return;
        }

        $this->admin_model->updateInterviewResultData($interviewId, [
            'Result'               => $result,
            'Feedback'             => $feedback,
            'SkillScore'           => $skill,
            'CommunicationScore'   => $communication,
            'ProblemSolvingScore'  => $problemSolving,
            'CultureFitScore'      => $cultureFit,
            'LeadershipScore'      => $leadership,
            'OverallScore'         => $overallScore
        ]);

        $app = $this->admin_model->getApplicationById($applicationId);

        $stageIdToUse = '';
        if ($app) {
            $currStage = is_object($app) ? ($app->CurrentStage ?? '') : ($app['CurrentStage'] ?? '');
            $sId = is_object($app) ? ($app->StageId ?? '') : ($app['StageId'] ?? '');
            if (!empty($currStage)) {
                $stageIdToUse = $currStage;
            } elseif (!empty($sId)) {
                $stageIdToUse = $sId;
            }
        }

        $stageName = '';
        if (!empty($stageIdToUse)) {
            $stageRow = $this->admin_model->getStageById($stageIdToUse);

            if ($stageRow) {
                $stageName = is_object($stageRow) ? ($stageRow->StageName ?? '') : ($stageRow['StageName'] ?? '');
            }
        }

        if (empty($stageName)) {
            $statusToSave = ucwords(strtolower($result));
        } else {
            $statusToSave = $stageName . ' Round Completed - ' . ucwords(strtolower($result));
        }

        $this->admin_model->updateJobApplication($applicationId, [   
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
        $roleId        = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        $currentUserId = isset($Hrms_Session['IUid']) ? (int)$Hrms_Session['IUid'] : 0;
        $department    = $this->input->post('department');
        $status        = $this->input->post('status');
        $dateRange     = $this->input->post('daterange');

        if (!$this->admin_model->hasPagePermission($roleId, 'admin/VaccancyList')) {
            $this->session->set_flashdata('error', 'You do not have permission to access the Vacancy List page.');
            redirect($this->config->item('base_url') . 'admin/RequestedResources');
            return;
        }

        $data['vaclist'] = $this->admin_model->getFilteredVacancies($roleId, $currentUserId, $department, $status, $dateRange);
        $data['department'] = $this->admin_model->getUserDepartments();
        $data['ctc_approvers'] = $this->admin_model->getAllUsers();

        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->admin_model->getBreadcrumb($currentUrl);

        $this->template->write_view('content', 'admin/VaccancyList', $data);
        $this->template->render();
    } else {
        $this->session->set_flashdata('error','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."admin/index");
    }
}

public function filterAssignedInterviews()
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if (isset($Hrms_Session) && !empty($Hrms_Session)) {
        $status = trim($this->input->post('status') ?? '');

        $session = $this->session->userdata('logged_in');
        $data = $this->admin_model->getAssignedInterviewsFiltered($session['IUid'], $status);

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

        $data = $this->admin_model->getFilteredCandidatesStatus($jid, $status);

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

        $app = $this->admin_model->getApplicationByCandidateId($candidateId);

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = is_object($app) ? ($app->ApplicationId ?? null) : ($app['ApplicationId'] ?? null);

        $onboardingStage = $this->admin_model->getStageByGroupAndName('Hiring', 'On Boarding');
        $onboardingStageId = $onboardingStage ? (is_object($onboardingStage) ? $onboardingStage->StageId : $onboardingStage['StageId']) : 11;

        $this->admin_model->insertCandidateStageTracking([
            'ApplicationId' => $applicationId,
            'StageId'       => $onboardingStageId,
            'Action'        => 'On Boarding',
            'ActionBy'      => $Hrms_Session['IUid'],
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => "Documents Submitted: " . $documents . " | " . $remarks
        ]);

        $this->admin_model->updateJobApplication($applicationId, [
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
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/RecruitmentStages')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Recruitment Stages.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

        $data['stages'] = $this->admin_model->getAllRecruitmentStages();

      
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

      
        $existing = $this->admin_model->checkStageOrderExists($stageGroup, $stageOrder);
        if ($existing > 0) {
            $this->admin_model->shiftStageOrdersUp($stageGroup, $stageOrder);
        }

        $data = [
            'StageGroup'  => $stageGroup,
            'StageName'   => $stageName,
            'StageOrder'  => $stageOrder,
            'StageStatus' => 1,
            'IsFinal'     => 0,
            'CreatedAT'   => date('Y-m-d H:i:s')
        ];

        $this->admin_model->insertStage($data);

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

        
        $existing = $this->admin_model->checkStageOrderExists($stageGroup, $stageOrder, $stageId);
        if ($existing > 0) {
            $this->admin_model->shiftStageOrdersUp($stageGroup, $stageOrder, $stageId);
        }

        $data = [
            'StageGroup' => $stageGroup,
            'StageName'  => $stageName,
            'StageOrder' => $stageOrder
        ];

        $this->admin_model->updateStage($stageId, $data);

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
        $maxOrder = $this->admin_model->getMaxStageOrder($group);
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
        $this->admin_model->setStageStatus($id, $status);

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
        $menus = $this->admin_model->getRolePermissionMenuIds($roleId);
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
        $roleId = isset($Hrms_Session['EmpRoleId']) ? (int)$Hrms_Session['EmpRoleId'] : 0;
        if (!$this->admin_model->hasPagePermission($roleId, 'admin/RolePermissions')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Role Permissions.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

        $data['roles'] = $this->admin_model->getActiveRoles();
        $data['menus'] = $this->admin_model->getActiveMenus();

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

        $allMenus = $this->admin_model->getAllMenus();

        foreach($allMenus as $menu){
            $menuId     = $menu['IHMid'];
            $isSelected = (!empty($menus) && in_array($menuId, $menus)) ? 1 : 0;

            $exists = $this->admin_model->checkRolePermissionExists($roleId, $menuId);

            if($exists){
                $this->admin_model->updateRolePermission($roleId, $menuId, $isSelected);
            } else {
                $this->admin_model->insertRolePermission($roleId, $menuId, $isSelected);
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

        $app = $this->admin_model->getApplicationByCandidateId($candidateId);

        if(!$app){
            echo json_encode(['status'=>'error','msg'=>'Application not found']);
            return;
        }

        $applicationId = is_object($app) ? ($app->ApplicationId ?? null) : ($app['ApplicationId'] ?? null);

        $expectedJoining = date('Y-m-d', strtotime($offerDate.' +'.$noticeDays.' days'));

        $this->admin_model->insertCandidateOffer([
            'ApplicationId'       => $applicationId,
            'OfferDate'           => $offerDate,
            'NoticePeriodDays'    => $noticeDays,
            'ExpectedJoiningDate' => $expectedJoining,
            'OfferStatus'         => $offerStatus,
            'OfferActionAt'       => date('Y-m-d H:i:s')
        ]);

        $offerStage = $this->admin_model->getStageByGroup('Offer');
        $offerStageId = $offerStage ? (is_object($offerStage) ? $offerStage->StageId : $offerStage['StageId']) : 12;

        $this->admin_model->insertCandidateStageTracking([
            'ApplicationId' => $applicationId,
            'StageId'       => $offerStageId,
            'Action'        => 'Offer',
            'ActionBy'      => $Hrms_Session['IUid'],
            'ActionAt'      => date('Y-m-d H:i:s'),
            'Remarks'       => $remarks
        ]);

        $this->admin_model->updateJobApplication($applicationId, [
            'CurrentStage'  => $offerStageId,
            'CurrentStatus' => 'Offer ' . $offerStatus
        ]);

        $candidate = $this->admin_model->getCandidateByIdObj($candidateId);

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

        $app = $this->admin_model->getApplicationByCandidateId($candidateId);

        if(!$app){
            echo "Application not found";
            return;
        }

        $applicationId = is_object($app) ? ($app->ApplicationId ?? null) : ($app['ApplicationId'] ?? null);

        $hiredStage = $this->admin_model->getStageByGroupAndName('Hiring', 'Hired');
        $hiredStageId = $hiredStage ? (is_object($hiredStage) ? $hiredStage->StageId : $hiredStage['StageId']) : 13;

        $this->admin_model->insertCandidateStageTracking([
            'ApplicationId' => $applicationId,
            'StageId'       => $hiredStageId,
            'Action'        => 'Hired',
            'ActionBy'      => $userId,
            'Remarks'       => 'Candidate successfully hired',
            'ActionAt'      => date('Y-m-d H:i:s')
        ]);

        $this->admin_model->insertCandidateHiring([
            'ApplicationId' => $applicationId,
            'CandidateId'   => $candidateId,
            'HiringDate'    => date('Y-m-d'),
            'JoiningDate'   => $joiningDate,
            'SalaryOffered' => $salary,
            'Remarks'       => $remarks,
            'CreatedBy'     => $userId,
            'CreatedAt'     => date('Y-m-d H:i:s')
        ]);

        $this->admin_model->updateJobApplication($applicationId, [
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
    return $this->admin_model->getApplicationIdByCandidateId($candidateId);
}

public function viewResume($candidateId)
{
    $Hrms_Session = $this->session->userdata('logged_in');

    if(isset($Hrms_Session) && !empty($Hrms_Session))
    {

        $candidate = $this->admin_model->getCandidateById($candidateId);

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

        $roleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
        $userId = isset($check_session["IUid"]) ? $check_session["IUid"] : null;

        if (!$this->admin_model->hasPagePermission($roleId, 'admin/RequestedResources')) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access Requested Resources.');
            redirect($this->config->item('base_url') . 'admin/dashboard');
            return;
        }

        $roleRow = !empty($roleId) ? $this->admin_model->getUserRoleById($roleId) : null;
        $roleName = !empty($roleRow) ? strtolower($roleRow["RoleName"]) : "";

        $hasApprovedResPermission = $this->admin_model->hasPagePermission($roleId, 'admin/ApprovedResources');
        $isApproverRole = ($roleName === 'approver' || strpos($roleName, 'approver') !== false);
        // $isAdminRole = ($hasApprovedResPermission || $roleName === 'management' || $roleName === 'admin' || $roleName === 'super admin');
$isAdminRole = ($roleName === 'management');
        $filters = [];
        if ($isAdminRole) {
            // Admins / Management / Recruitment Managers: view all requests
        } elseif ($isApproverRole) {
            // Approvers: view requests assigned to them for approval
            $filters["ApproverId"] = $userId;
        } else {
            // Requesters (e.g. Hiring Managers, Recruiters, staff): view requests they submitted
            $filters["RequestedBy"] = $userId;
        }

        $data["employee_det"] = $check_session;
        $data["requests"] = $this->admin_model->getResourceRequests($filters);
        $data["approvers"] = $this->admin_model->getApproverUsers();
        $data["ctc_approvers"] = $this->admin_model->getAllUsers();
        $data["department"] = $this->admin_model->getActiveDepartments();
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

        try {
            $check_session = $this->session->userdata("logged_in");
            if (empty($check_session)) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
                    return;
                }
                redirect($this->config->item("base_url") . "admin/index");
                return;
            }

            $inps = $this->input->post();
            if (empty($inps["JobTitle"]) || empty($inps["ApproverId"])) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Job Title and Approver Name are required."]);
                    return;
                }
                $this->session->set_flashdata("error", "Job Title and Approver Name are required.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            $requestId = isset($inps["RequestId"]) ? (int)$inps["RequestId"] : 0;
            $sessionRoleId = isset($check_session["EmpRoleId"]) ? (int)$check_session["EmpRoleId"] : 0;
            $sessionUserId = isset($check_session["IUid"]) ? (int)$check_session["IUid"] : 0;

            // Parse and sanitize ExtraCcUsers
            $extraCcUsers = [];
            $rawExtraCc = isset($inps["ExtraCcUsers"]) ? $inps["ExtraCcUsers"] : null;
            if (!empty($rawExtraCc)) {
                if (is_array($rawExtraCc)) {
                    foreach ($rawExtraCc as $uid) {
                        $uidInt = (int)$uid;
                        if ($uidInt > 0) {
                            $extraCcUsers[] = $uidInt;
                        }
                    }
                } elseif (is_string($rawExtraCc)) {
                    $decoded = json_decode($rawExtraCc, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $uid) {
                            $uidInt = (int)$uid;
                            if ($uidInt > 0) {
                                $extraCcUsers[] = $uidInt;
                            }
                        }
                    } else {
                        $parts = explode(',', $rawExtraCc);
                        foreach ($parts as $uid) {
                            $uidInt = (int)trim($uid);
                            if ($uidInt > 0) {
                                $extraCcUsers[] = $uidInt;
                            }
                        }
                    }
                }
                $extraCcUsers = array_values(array_unique($extraCcUsers));
            }
            $extraCcUsersJson = !empty($extraCcUsers) ? json_encode($extraCcUsers) : null;

            $expSalaryMinRaw = isset($inps["ExpectedSalaryMin"]) && trim($inps["ExpectedSalaryMin"]) !== '' ? trim($inps["ExpectedSalaryMin"]) : null;
            $expSalaryMaxRaw = isset($inps["ExpectedSalaryMax"]) && trim($inps["ExpectedSalaryMax"]) !== '' ? trim($inps["ExpectedSalaryMax"]) : null;

            if ($expSalaryMinRaw !== null && !is_numeric($expSalaryMinRaw)) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Minimum expected salary must be a valid number."]);
                    return;
                }
                $this->session->set_flashdata("error", "Minimum expected salary must be a valid number.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            if ($expSalaryMaxRaw !== null && !is_numeric($expSalaryMaxRaw)) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Maximum expected salary must be a valid number."]);
                    return;
                }
                $this->session->set_flashdata("error", "Maximum expected salary must be a valid number.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            if ($expSalaryMinRaw !== null && (float)$expSalaryMinRaw < 0) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Minimum expected salary cannot be negative."]);
                    return;
                }
                $this->session->set_flashdata("error", "Minimum expected salary cannot be negative.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            if ($expSalaryMaxRaw !== null && (float)$expSalaryMaxRaw < 0) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Maximum expected salary cannot be negative."]);
                    return;
                }
                $this->session->set_flashdata("error", "Maximum expected salary cannot be negative.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            if ($expSalaryMinRaw !== null && $expSalaryMaxRaw !== null && (float)$expSalaryMinRaw > (float)$expSalaryMaxRaw) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(["status" => "error", "message" => "Minimum expected salary cannot be greater than maximum expected salary."]);
                    return;
                }
                $this->session->set_flashdata("error", "Minimum expected salary cannot be greater than maximum expected salary.");
                redirect($this->config->item("base_url") . "admin/RequestedResources");
                return;
            }

            $expSalMinVal = ($expSalaryMinRaw !== null) ? round((float)$expSalaryMinRaw, 2) : null;
            $expSalMaxVal = ($expSalaryMaxRaw !== null) ? round((float)$expSalaryMaxRaw, 2) : null;

            if ($requestId > 0) {
                $existing = $this->admin_model->getResourceRequestById($requestId);
                if (empty($existing)) {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Resource Request not found."]);
                        return;
                    }
                    $this->session->set_flashdata("error", "Resource Request not found.");
                    redirect($this->config->item("base_url") . "admin/RequestedResources");
                    return;
                }

              
                // if ($sessionRoleId === 9 && (int)$existing["RequestedBy"] !== $sessionUserId) {
                //     if ($this->input->is_ajax_request()) {
                //         echo json_encode(["status" => "error", "message" => "Access denied. You can only update your own resource requests."]);
                //         return;
                //     }
                //     $this->session->set_flashdata("error", "Access denied. You can only update your own resource requests.");
                //     redirect($this->config->item("base_url") . "admin/RequestedResources");
                //     return;
                // }

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
                    "ExpectedSalaryMin"    => $expSalMinVal,
                    "ExpectedSalaryMax"    => $expSalMaxVal,
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
                    "ExtraCcUsers"         => isset($inps["ExtraCcUsers"]) ? $extraCcUsersJson : (isset($existing["ExtraCcUsers"]) ? $existing["ExtraCcUsers"] : null),
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
                        $this->admin_model->updateVacancy((int)$existing["ConvertedJid"], $vacancyUpdate);
                    }
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "success", "message" => "Resource Request [" . $existing["RequestCode"] . "] updated successfully."]);
                        return;
                    }
                    $this->session->set_flashdata("true", "Resource Request [" . $existing["RequestCode"] . "] updated successfully.");
                } else {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Failed to update Resource Request."]);
                        return;
                    }
                    $this->session->set_flashdata("error", "Failed to update Resource Request.");
                }
            } else {
                $sessionUserId = (int)$check_session["IUid"];
                $this->admin_model->acquireResourceRequestLock($sessionUserId);

                try {
                    $did = isset($inps["Did"]) && $inps["Did"] !== '' ? (int)$inps["Did"] : null;
                    $approverId = isset($inps["ApproverId"]) && $inps["ApproverId"] !== '' ? (int)$inps["ApproverId"] : null;
                    $positionType = isset($inps["PositionType"]) ? $inps["PositionType"] : "New Position";

                    $duplicate = $this->admin_model->checkDuplicateResourceRequest(
                        $sessionUserId,
                        $inps["JobTitle"],
                        $did,
                        $approverId,
                        $positionType
                    );

                    if (!empty($duplicate)) {
                        if ($this->input->is_ajax_request()) {
                            echo json_encode(["status" => "error", "message" => "An identical resource request already exists."]);
                            return;
                        }
                        $this->session->set_flashdata("error", "An identical resource request already exists.");
                        redirect($this->config->item("base_url") . "admin/RequestedResources");
                        return;
                    }

                    $count = $this->admin_model->countResourceRequests() + 1;
                    $requestCode = "RR-" . date("Y") . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);

                    $salaryStr = isset($inps["Salary"]) ? trim($inps["Salary"]) : ((!empty($inps["SalMin"]) || !empty($inps["SalMax"])) ? ($inps["SalMin"] . " - " . $inps["SalMax"] . " LPA") : "");

                    $data = [
                        "RequestCode"          => $requestCode,
                        "JobTitle"             => trim($inps["JobTitle"]),
                        "FunctionalRole"       => isset($inps["FunctionalRole"]) ? trim($inps["FunctionalRole"]) : "",
                        "Did"                  => $did,
                        "JobLocation"          => isset($inps["JobLocation"]) ? trim($inps["JobLocation"]) : "",
                        "EducationRequired"     => isset($inps["EducationRequired"]) ? trim($inps["EducationRequired"]) : "",
                        "NoofOpenings"         => isset($inps["NoofOpenings"]) ? (int)$inps["NoofOpenings"] : 1,
                        "PositionType"         => $positionType,
                        "ExpMin"               => isset($inps["ExpMin"]) ? (float)$inps["ExpMin"] : 0.0,
                        "ExpMax"               => isset($inps["ExpMax"]) ? (float)$inps["ExpMax"] : 0.0,
                        
                        "Salary"               => $salaryStr,
                        "ExpectedSalaryMin"    => $expSalMinVal,
                        "ExpectedSalaryMax"    => $expSalMaxVal,
                        "RecruitmentStartDate" => !empty($inps["RecruitmentStartDate"]) ? $inps["RecruitmentStartDate"] : date("Y-m-d"),
                        "TargetOnboardingDate" => !empty($inps["TargetOnboardingDate"]) ? $inps["TargetOnboardingDate"] : null,
                        "ReasonForRequirement" => isset($inps["ReasonForRequirement"]) ? trim($inps["ReasonForRequirement"]) : "",
                        "MustHaveSkills"       => isset($inps["MustHaveSkills"]) ? trim($inps["MustHaveSkills"]) : "",
                        "NiceToHaveSkills"     => isset($inps["NiceToHaveSkills"]) ? trim($inps["NiceToHaveSkills"]) : "",
                        "CommunicationLang"    => isset($inps["CommunicationLang"]) ? trim($inps["CommunicationLang"]) : "",
                        "JobDescription"       => isset($inps["JobDescription"]) ? trim($inps["JobDescription"]) : "",
                        "Responsibilities"     => isset($inps["Responsibilities"]) ? trim($inps["Responsibilities"]) : "",
                        "RequestedBy"          => $sessionUserId,
                        "ApproverId"           => (int)$inps["ApproverId"],
                        "CtcApproverId"        => !empty($inps["CtcApproverId"]) ? (int)$inps["CtcApproverId"] : null,
                        "ExtraCcUsers"         => $extraCcUsersJson,
                        "Status"               => "PENDING APPROVAL",
                        "CreatedAt"            => date("Y-m-d H:i:s")
                    ];

                    $newId = $this->admin_model->insertResourceRequest($data);
                } finally {
                    $this->admin_model->releaseResourceRequestLock($sessionUserId);
                }
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

                    if (!empty($extraCcUsers)) {
                        try {
                            $this->load->model("Notification_model");
                            foreach ($extraCcUsers as $ccUid) {
                                if ((int)$ccUid !== (int)$inps["ApproverId"]) {
                                    $this->Notification_model->addNotification(
                                        "Resource Request Notification (CC)",
                                        "You have been CC'd on Resource Request [" . $requestCode . "] for \"" . trim($inps["JobTitle"]) . "\" requested by " . $check_session["EmpName"] . ".",
                                        "info",
                                        (int)$ccUid,
                                        null
                                    );
                                }
                            }
                        } catch (\Throwable $t) {
                            log_message('error', 'Resource Request CC Notification Error: ' . $t->getMessage());
                        }
                    }

                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "success", "message" => "Resource Request submitted successfully and sent for approval."]);
                        return;
                    }
                    $this->session->set_flashdata("true", "Resource Request submitted successfully and sent for approval.");
                } else {
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(["status" => "error", "message" => "Failed to submit Resource Request."]);
                        return;
                    }
                    $this->session->set_flashdata("error", "Failed to submit Resource Request.");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'saveResourceRequest Exception: ' . $e->getMessage());
            if ($this->input->is_ajax_request()) {
                echo json_encode(["status" => "error", "message" => "An error occurred while saving: " . $e->getMessage()]);
                return;
            }
            $this->session->set_flashdata("error", "An error occurred: " . $e->getMessage());
        }

        redirect($this->config->item("base_url") . "admin/RequestedResources");
    }

    public function updateResourceRequestStatus()
    {
        @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - ENTRY_updateResourceRequestStatus\n", FILE_APPEND);
        if (ob_get_length()) { @ob_clean(); }
        header('Content-Type: application/json');

        try {
            $check_session = $this->session->userdata("logged_in");
            if (empty($check_session)) {
                echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
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
                return;
            }

            if (empty($comment)) {
                echo json_encode(["status" => "error", "message" => "Approval Comments are mandatory."]);
                return;
            }

            $roleId = isset($check_session['EmpRoleId'])
                ? (int)$check_session['EmpRoleId']
                : 0;
            $sessionUserId = isset($check_session['IUid'])
                ? (int)$check_session['IUid']
                : 0;

            $hasRequestedResPermission = $this->admin_model->hasPagePermission($roleId, 'admin/RequestedResources');
            if (!$hasRequestedResPermission) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Access denied. You do not have permission to perform this action.'
                ]);
                return;
            }

            $reqCheck = $this->admin_model->getResourceRequestById($requestId);
            if (empty($reqCheck)) {
                echo json_encode(['status' => 'error', 'message' => 'Resource request record not found.']);
                return;
            }

            $roleRow = $this->admin_model->getRoleById($roleId);
            $roleName = !empty($roleRow)
                ? strtolower(trim($roleRow['RoleName']))
                : '';

            $isRecruitmentManagerRole = ($roleName === 'recruitment manager');
            $isApproverRole           = ($roleName === 'approver');
            $isHiringManagerRole      = ($roleName === 'hiring manager');
            $isAssignedApprover       = (!empty($reqCheck['ApproverId']) && (int)$reqCheck['ApproverId'] === $sessionUserId);

            // Action authorization: must be Recruitment Manager, Approver (or assigned approver), or Hiring Manager
            if (!$isRecruitmentManagerRole && !$isApproverRole && !$isHiringManagerRole && !$isAssignedApprover) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Access denied. You do not have permission to perform this action.'
                ]);
                return;
            }

            // Record-level data-scope validation: approvers may only action requests assigned to them
            if ($isApproverRole) {
                if ((int)$reqCheck['ApproverId'] !== $sessionUserId) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Access denied. This request is not assigned to you for approval.'
                    ]);
                    return;
                }
            }

            // Hiring Managers can only submit approved requests to the vacancy list
            if ($isHiringManagerRole) {
                if ($status !== 'ACCEPTED') {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Hiring Managers can only submit approved requests to the vacancy list.'
                    ]);
                    return;
                }
            }

            $updateData = [
                "Status"          => $status,
                "ApprovalComment" => $comment,
                "ActionedAt"      => date("Y-m-d H:i:s"),
                "UpdatedAt"       => date("Y-m-d H:i:s")
            ];

            $res = $this->admin_model->updateResourceRequest($requestId, $updateData);
            if ($res) {
                if ($status === "ACCEPTED") {
                    $this->admin_model->ensureVacancyForResourceRequest($requestId, $sessionUserId);
                    $req = $this->admin_model->getResourceRequestById($requestId);

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
                    echo json_encode(["status" => "success", "message" => "Resource Request has been rejected successfully."]);
                    return;
                }
                echo json_encode(["status" => "success", "message" => "Resource Request updated successfully."]);
            } else {
                $dbErr = $this->admin_model->getDbError();
                echo json_encode(["status" => "error", "message" => "Failed to update Resource Request. " . ($dbErr['message'] ?? '')]);
            }
        } catch (\Throwable $t) {
            echo json_encode(["status" => "error", "message" => "Server error: " . $t->getMessage()]);
        }
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

        $count = $this->admin_model->countAllJobs() + 1;
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

        $jid = $this->admin_model->insertVacancy($vacancyData);

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
        $req = $this->admin_model->getResourceRequestForApproverEmail($requestId);

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
        $roleUsers = $this->admin_model->getRecruiterAndManagerUsersForEmail();

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

        // 3. Add ExtraCcUsers configured on the Resource Request
        if (!empty($req['ExtraCcUsers'])) {
            $extraUids = [];
            if (is_array($req['ExtraCcUsers'])) {
                $extraUids = $req['ExtraCcUsers'];
            } elseif (is_string($req['ExtraCcUsers'])) {
                $decoded = json_decode($req['ExtraCcUsers'], true);
                if (is_array($decoded)) {
                    $extraUids = $decoded;
                } else {
                    $extraUids = explode(',', $req['ExtraCcUsers']);
                }
            }
            foreach ($extraUids as $uId) {
                $uIdInt = (int)trim($uId);
                if ($uIdInt > 0) {
                    $uInfo = $this->admin_model->getUserNameAndEmail($uIdInt);
                    if ($uInfo && !empty($uInfo->EmpEmail)) {
                        $e = strtolower(trim($uInfo->EmpEmail));
                        if (filter_var($e, FILTER_VALIDATE_EMAIL) && $e !== strtolower(trim($req['ApproverEmail']))) {
                            $ccMap[$e] = !empty($uInfo->EmpName) ? $uInfo->EmpName : '';
                        }
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
        $requestedBy   = !empty($req['RequesterName']) ? $req['RequesterName'] : (!empty($req['RequestedByName']) ? $req['RequestedByName'] : 'A team member');
        $jobTitle      = !empty($req['JobTitle'])      ? $req['JobTitle']      : 'Resource';
        $requestCode   = !empty($req['RequestCode'])   ? $req['RequestCode']   : 'REQ';
        $department    = !empty($req['Departmentname']) ? $req['Departmentname'] : 'N/A';
        $openings      = !empty($req['NoofOpenings'])  ? (int)$req['NoofOpenings'] : 1;
        $positionType  = !empty($req['PositionType'])  ? $req['PositionType']  : 'New Position';
        $targetDate    = !empty($req['TargetOnboardingDate']) ? date('d M Y', strtotime($req['TargetOnboardingDate'])) : 'N/A';

        $minExp = isset($req['ExpMin']) && $req['ExpMin'] !== '' ? $req['ExpMin'] : (isset($req['MinExperience']) ? $req['MinExperience'] : 0);
        $maxExp = isset($req['ExpMax']) && $req['ExpMax'] !== '' ? $req['ExpMax'] : (isset($req['MaxExperience']) ? $req['MaxExperience'] : 0);
        $expRange = $minExp . ' – ' . $maxExp . ' Years';

        $baseUrl    = $this->config->item('base_url');
        $approveUrl = $baseUrl . 'admin/RequestedResources?action=approve&id=' . urlencode($requestId);
        $rejectUrl  = $baseUrl . 'admin/RequestedResources?action=reject&id=' . urlencode($requestId);
        $viewUrl    = $baseUrl . 'admin/RequestedResources?action=view&id=' . urlencode($requestId);

        $subject = 'Approval Required – Resource Request ' . $requestCode . ' – ' . $jobTitle;

        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #f8fafc; margin: 0; padding: 20px;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #0f766e; padding: 18px 24px;">
            <h2 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: 0.3px;">I-NET Recruitment Portal</h2>
        </div>
        <div style="padding: 24px 28px;">
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #333333;">Dear Sir / Madam,</p>
            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">This is to inform you that a new resource request has been raised and is currently awaiting your approval.</p>

            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;">Request Summary</strong>
                </div>
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; width: 40%; font-weight: bold; color: #475569; font-size: 13px;">Request Code:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #0f766e; font-weight: bold; font-size: 13px;">' . htmlspecialchars($requestCode) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Position:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($jobTitle) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Department:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($department) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Position Type:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($positionType) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">No. of Positions:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . (int)$openings . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Experience Required:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($expRange) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Target Onboarding Date:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($targetDate) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Requested By:</td>
                        <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">' . htmlspecialchars($requestedBy) . '</td>
                    </tr>
                </table>
            </div>

            <p style="margin: 22px 0 6px 0; font-size: 14px; font-weight: bold; color: #1e293b;">Action Required:</p>
            <p style="margin: 0 0 18px 0; font-size: 14px; color: #333333; line-height: 1.6;">Kindly review the request details and provide your approval to proceed with the recruitment process.</p>

            <table cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0 24px 0;">
                <tr>
                    <td style="padding-right: 12px;">
                        <a href="' . $approveUrl . '" target="_blank" style="display: inline-block; background-color: #28a745; color: #ffffff !important; padding: 10px 22px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">Approve</a>
                    </td>
                    <td style="padding-right: 12px;">
                        <a href="' . $rejectUrl . '" target="_blank" style="display: inline-block; background-color: #dc3545; color: #ffffff !important; padding: 10px 22px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">Reject</a>
                    </td>
                    <td>
                        <a href="' . $viewUrl . '" target="_blank" style="display: inline-block; background-color: #007bff; color: #ffffff !important; padding: 10px 22px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">View Request</a>
                    </td>
                </tr>
            </table>

            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">Your timely action would be appreciated.</p>

            <p style="margin: 0; font-size: 14px; color: #333333; line-height: 1.6;">
                Thanks &amp; Regards,<br>
                <strong>Recruiter Team</strong>
            </p>
        </div>
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 24px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-style: italic;">Note: This is an auto-generated notification from I-NET Recruitment Portal.</p>
        </div>
    </div>
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

            $requestCode  = !empty($req['RequestCode']) ? $req['RequestCode'] : 'REQ';
            $jobTitle     = !empty($req['JobTitle']) ? $req['JobTitle'] : 'Resource';
            $requestedBy  = !empty($req['RequestedByName']) ? $req['RequestedByName'] : (!empty($req['RequesterName']) ? $req['RequesterName'] : 'Requester');
            $approvedBy   = !empty($req['ApproverName']) ? $req['ApproverName'] : 'Approver';
            $status       = !empty($req['Status']) ? $req['Status'] : 'APPROVED';
            $rawDate      = !empty($req['ActionedAt']) ? $req['ActionedAt'] : (!empty($req['UpdatedAt']) ? $req['UpdatedAt'] : date('Y-m-d H:i:s'));
            $approvalDate = date('d M Y, H:i', strtotime($rawDate));
            $comment      = !empty($req['ApprovalComment']) ? $req['ApprovalComment'] : '-';

            $subject = 'Resource Request Approved – ' . $requestCode . ' | ' . $jobTitle;

            $baseUrl = $this->config->item('base_url');
            $actionLink = $baseUrl . 'admin/ApprovedResources';

            $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #f8fafc; margin: 0; padding: 20px;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #0f766e; padding: 18px 24px;">
            <h2 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: 0.3px;">I-NET Recruitment Portal</h2>
        </div>
        <div style="padding: 24px 28px;">
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #333333;">Dear ' . htmlspecialchars($requestedBy) . ',</p>
            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">Your Resource Request (<strong>' . htmlspecialchars($requestCode) . '</strong>) for the position of <strong>' . htmlspecialchars($jobTitle) . '</strong> has been <strong style="color: #16a34a;">APPROVED</strong> by <strong>' . htmlspecialchars($approvedBy) . '</strong>.</p>

            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;">Approval Details</strong>
                </div>
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 9px 16px; text-align: left; font-size: 13px; font-weight: bold; color: #475569; width: 38%;">Field</th>
                            <th style="padding: 9px 16px; text-align: left; font-size: 13px; font-weight: bold; color: #475569;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Request Code:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #0f766e; font-weight: bold; font-size: 13px;">' . htmlspecialchars($requestCode) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Job Title:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($jobTitle) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Approval Status:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                                <span style="display: inline-block; background-color: #dcfce7; color: #15803d; font-weight: bold; padding: 3px 10px; border-radius: 4px; border: 1px solid #bbf7d0;">' . htmlspecialchars($status) . '</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Approved By:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($approvedBy) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Approval Date:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($approvalDate) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Approver Comments:</td>
                            <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">' . htmlspecialchars($comment) . '</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin: 22px 0 6px 0;">
                <strong style="font-size: 14px; color: #1e293b;">Next Step</strong>
            </div>
            <p style="margin: 0 0 18px 0; font-size: 14px; color: #333333; line-height: 1.6;">The approved resource request is now available for further recruitment processing.</p>

            <table cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0 24px 0;">
                <tr>
                    <td>
                        <a href="' . $actionLink . '" target="_blank" style="display: inline-block; background-color: #007bff; color: #ffffff !important; padding: 11px 26px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; font-family: Arial, Helvetica, sans-serif; text-align: center;">View Approved Resources</a>
                    </td>
                </tr>
            </table>

            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">Please proceed with the next steps as applicable.</p>

            <p style="margin: 0; font-size: 14px; color: #333333; line-height: 1.6;">
                Thanks &amp; Regards,<br>
                <strong>Recruitment Team</strong>
            </p>
        </div>
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 24px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-style: italic;">Note: This is an auto-generated notification from I-NET Recruitment Portal.</p>
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

            $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #ffffff; margin: 0; padding: 10px; }
        .req-table { width: 100%; max-width: 600px; border-collapse: collapse; margin: 20px 0; }
        .req-table th, .req-table td { border: 1px solid #dddddd; padding: 8px 12px; text-align: left; font-size: 13px; }
        .req-table th { background-color: #f8f9fa; width: 35%; color: #495057; font-weight: bold; }
        .req-table td { color: #212529; }
    </style>
</head>
<body>
    <p>Dear <strong>' . htmlspecialchars($req['RequestedByName'] ?? 'Requester') . '</strong>,</p>
    <p>Your Resource Request for <strong>' . htmlspecialchars($req['JobTitle'] ?? 'Position') . '</strong> has been <strong>REJECTED</strong>.</p>

    <table class="req-table">
        <tr>
            <th>Request Code</th>
            <td><strong style="color: #dc3545;">' . htmlspecialchars($req['RequestCode'] ?? '-') . '</strong></td>
        </tr>
        <tr>
            <th>Job Title</th>
            <td>' . htmlspecialchars($req['JobTitle'] ?? '-') . '</td>
        </tr>
        <tr>
            <th>Rejection Date</th>
            <td>' . htmlspecialchars($actionedAt) . '</td>
        </tr>
        <tr>
            <th>Approver Comments</th>
            <td>' . htmlspecialchars($comment) . '</td>
        </tr>
    </table>

    <p style="margin-top: 25px;">Thanks &amp; Regards,<br>
    <strong>Recruiter Team</strong></p>
    <br>
    <p style="font-size: 12px; color: #666666; font-style: italic; margin-top: 20px; border-top: 1px dashed #cccccc; padding-top: 8px;">Note: This is an auto-generated notification from I-NET Recruitment Portal.</p>
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

    private function _sendVacancyAssignedEmail($req, $targetUser, $check_session, $linkedJob = null)
    {
        if (empty($targetUser) || empty($targetUser['EmpEmail']) || !filter_var(trim($targetUser['EmpEmail']), FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $assignedUserName  = !empty($targetUser['EmpName']) ? $targetUser['EmpName'] : 'Team Member';
        $assignedUserEmail = trim($targetUser['EmpEmail']);
        $assignedByName    = !empty($check_session['EmpName']) ? $check_session['EmpName'] : 'Management';

        $requestCode  = !empty($req['RequestCode']) ? $req['RequestCode'] : (!empty($linkedJob['JobCode']) ? $linkedJob['JobCode'] : 'REQ');
        $jobTitle     = !empty($req['JobTitle']) ? $req['JobTitle'] : (!empty($linkedJob['JobTitle']) ? $linkedJob['JobTitle'] : 'Resource');
        $role         = !empty($req['FunctionalRole']) ? $req['FunctionalRole'] : (!empty($linkedJob['RoleSummary']) ? $linkedJob['RoleSummary'] : 'N/A');
        $department   = !empty($req['Departmentname']) ? $req['Departmentname'] : 'N/A';
        $empType      = !empty($req['EmploymentType']) ? $req['EmploymentType'] : (!empty($linkedJob['EmploymentType']) ? $linkedJob['EmploymentType'] : 'Full-Time');
        $workMode     = !empty($req['WorkMode']) ? $req['WorkMode'] : (!empty($linkedJob['WorkMode']) ? $linkedJob['WorkMode'] : 'Onsite');

        $expMin = isset($req['ExpMin']) && $req['ExpMin'] !== '' ? $req['ExpMin'] : (isset($linkedJob['ExpMin']) ? $linkedJob['ExpMin'] : 0);
        $expMax = isset($req['ExpMax']) && $req['ExpMax'] !== '' ? $req['ExpMax'] : (isset($linkedJob['ExpMax']) ? $linkedJob['ExpMax'] : 0);
        $expStr = ($expMin || $expMax) ? ($expMin . ' – ' . $expMax . ' Years') : 'N/A';

        $salMin = isset($req['ExpectedSalaryMin']) && $req['ExpectedSalaryMin'] !== '' ? $req['ExpectedSalaryMin'] : (isset($req['SalMin']) && $req['SalMin'] !== '' ? $req['SalMin'] : (isset($linkedJob['SalMin']) ? $linkedJob['SalMin'] : ''));
        $salMax = isset($req['ExpectedSalaryMax']) && $req['ExpectedSalaryMax'] !== '' ? $req['ExpectedSalaryMax'] : (isset($req['SalMax']) && $req['SalMax'] !== '' ? $req['SalMax'] : (isset($linkedJob['SalMax']) ? $linkedJob['SalMax'] : ''));
        if ($salMin !== '' && $salMax !== '') {
            $salaryStr = $salMin . ' – ' . $salMax . ' LPA';
        } elseif (!empty($req['Salary'])) {
            $salaryStr = $req['Salary'];
        } elseif (!empty($linkedJob['Salary'])) {
            $salaryStr = $linkedJob['Salary'];
        } else {
            $salaryStr = 'N/A';
        }

        $openings = !empty($req['NoofOpenings']) ? (int)$req['NoofOpenings'] : (!empty($linkedJob['NoofOpenings']) ? (int)$linkedJob['NoofOpenings'] : 1);
        $targetDate = !empty($req['TargetOnboardingDate']) && $req['TargetOnboardingDate'] !== '0000-00-00' ? date('d M Y', strtotime($req['TargetOnboardingDate'])) : (!empty($linkedJob['TargetOnboardingDate']) && $linkedJob['TargetOnboardingDate'] !== '0000-00-00' ? date('d M Y', strtotime($linkedJob['TargetOnboardingDate'])) : 'N/A');

        $jobDesc = !empty($req['JobDescription']) ? $req['JobDescription'] : (!empty($linkedJob['JobDescription']) ? $linkedJob['JobDescription'] : '');
        $responsibilities = !empty($req['Responsibilities']) ? $req['Responsibilities'] : (!empty($linkedJob['Responsibilities']) ? $linkedJob['Responsibilities'] : '');

        $baseUrl = $this->config->item('base_url');
        $viewUrl = $baseUrl . 'admin/ApprovedResources';

        $subject = "Vacancy Assigned to You – " . $requestCode . " | " . $jobTitle;

        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #f8fafc; margin: 0; padding: 20px;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #0f766e; padding: 18px 24px;">
            <h2 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: 0.3px;">I-NET Recruitment Portal</h2>
        </div>
        <div style="padding: 24px 28px;">
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #333333;">Hi ' . htmlspecialchars($assignedUserName) . ',</p>
            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">A vacancy has been assigned to you by ' . htmlspecialchars($assignedByName) . '.</p>

            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;">Vacancy Details</strong>
                </div>
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; width: 40%; font-weight: bold; color: #475569; font-size: 13px;">Request Code:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #0f766e; font-weight: bold; font-size: 13px;">' . htmlspecialchars($requestCode) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Job Title:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($jobTitle) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Functional Role / Role:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($role) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Department:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($department) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Employment Type:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($empType) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Work Mode:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($workMode) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Experience (Min – Max):</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($expStr) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Salary (Min – Max):</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($salaryStr) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Number of Openings:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . (int)$openings . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Target Onboarding Date:</td>
                        <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">' . htmlspecialchars($targetDate) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Assigned By:</td>
                        <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">' . htmlspecialchars($assignedByName) . '</td>
                    </tr>
                </table>
            </div>';

        if (!empty($jobDesc)) {
            $htmlBody .= '
            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;">Job Description</strong>
                </div>
                <div style="padding: 14px 16px; font-size: 13px; color: #333333; line-height: 1.6; white-space: pre-wrap; background-color: #ffffff;">' . nl2br(htmlspecialchars($jobDesc)) . '</div>
            </div>';
        }

        if (!empty($responsibilities)) {
            $htmlBody .= '
            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;">Responsibilities</strong>
                </div>
                <div style="padding: 14px 16px; font-size: 13px; color: #333333; line-height: 1.6; white-space: pre-wrap; background-color: #ffffff;">' . nl2br(htmlspecialchars($responsibilities)) . '</div>
            </div>';
        }

        $htmlBody .= '
            <table cellpadding="0" cellspacing="0" border="0" style="margin: 22px 0 24px 0;">
                <tr>
                    <td>
                        <a href="' . $viewUrl . '" target="_blank" style="display: inline-block; background-color: #0f766e; color: #ffffff !important; padding: 11px 26px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">View Approved Vacancy</a>
                    </td>
                </tr>
            </table>

            <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">Please proceed with sourcing and processing candidates for this vacancy.</p>

            <p style="margin: 0; font-size: 14px; color: #333333; line-height: 1.6;">
                Thanks &amp; Regards,<br>
                <strong>Recruitment Team</strong>
            </p>
        </div>
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 24px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-style: italic;">Note: This is an auto-generated notification from I-NET Recruitment Portal.</p>
        </div>
    </div>
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
                $mail->addAddress($assignedUserEmail, $assignedUserName);

                $sent = $mail->send();
                @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - Vacancy Assigned Email Sent: " . ($sent ? "SUCCESS" : "FAILED") . " | To: " . $assignedUserEmail . " | RequestCode: " . $requestCode . "\n", FILE_APPEND);
                return $sent;
            }
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'Vacancy Assigned Email Error: ' . $e->getMessage());
            @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - Vacancy Assigned Email ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    private function _sendVacancyOnHoldEmailToRecruiter($jid)
    {
        if (empty($jid)) return false;

        $job = $this->admin_model->getJobWithRecruiterDetails($jid);

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
            $postedUser = $this->admin_model->getUserNameAndEmail($job['PostedBy']);
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

        if (!$this->admin_model->hasPagePermission($roleId, 'admin/ApprovedResources')) {
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

        $targetUser = $this->admin_model->getUserByIdRowArray($assignedManagerId);

        if (empty($targetUser)) {
            echo json_encode(["status" => "error", "message" => "Selected Recruitment Manager not found."]);
            return;
        }

        $jid = !empty($req["ConvertedJid"]) ? (int)$req["ConvertedJid"] : null;
        if (empty($jid)) {
            $jid = $this->admin_model->ensureVacancyForResourceRequest($requestId, $check_session["IUid"]);
        }

        $linkedJob = !empty($jid) ? $this->admin_model->getJobById($jid) : null;
        if (empty($linkedJob)) {
            echo json_encode(["status" => "error", "message" => "This approved resource request is not linked to a vacancy yet."]);
            return;
        }

        $this->db->trans_start();
        $this->admin_model->updateVacancy($jid, [
            "AssignedRecruiterManagerId" => $assignedManagerId
        ]);

        $this->admin_model->updateResourceRequest($requestId, [
            "AssignedRecruiterManagerId" => $assignedManagerId,
            "Status"                     => "ASSIGNED"
        ]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(["status" => "error", "message" => "Failed to update assignment."]);
            return;
        }

        // Send vacancy assignment email to the assigned recruiter/recruitment manager
        try {
            $this->_sendVacancyAssignedEmail($req, $targetUser, $check_session, $linkedJob);
        } catch (\Throwable $et) {
            log_message('error', 'Vacancy Assignment Email Error: ' . $et->getMessage());
            @file_put_contents(APPPATH . 'logs/ats_debug.log', date('Y-m-d H:i:s') . " - Vacancy Assignment Email ERROR: " . $et->getMessage() . "\n", FILE_APPEND);
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

       
        $job = $this->admin_model->getJobHistoryDetailsJob($jid);

        if (empty($job)) {
            echo json_encode(['status' => 'error', 'msg' => 'Job not found']);
            return;
        }

        
        $resourceRequest = $this->admin_model->getJobHistoryResourceRequest($jid, $job['JobTitle'] ?? null);

        // Sync CtcApproverId and CtcApproverName if available on either job or resourceRequest
        if (empty($job['CtcApproverId']) && !empty($resourceRequest['CtcApproverId'])) {
            $job['CtcApproverId']   = $resourceRequest['CtcApproverId'];
            $job['CtcApproverName'] = $resourceRequest['CtcApproverName'];
        } elseif (!empty($resourceRequest) && empty($resourceRequest['CtcApproverId']) && !empty($job['CtcApproverId'])) {
            $resourceRequest['CtcApproverId']   = $job['CtcApproverId'];
            $resourceRequest['CtcApproverName'] = $job['CtcApproverName'];
        }

        // 3. Candidate Summary
        $applications = $this->admin_model->getJobHistoryApplications($jid);

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
        $trackingRows = $this->admin_model->getJobTrackingRows($jid);

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
            $trackingRows = $this->admin_model->getJobTrackingRows($jid);
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
                $filledCandidates = $this->admin_model->getJobFilledCandidates($appIds);

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
            $interviewDetails = $this->admin_model->getAiInterviewDetails($interviewId);

            $versions = $this->admin_model->getAiQuestionVersions($interviewId);

            // Determine source
            $source = 'ai';
            $latestGen = $this->admin_model->getAiQuestionLatestReason($interviewId);
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

            $this->admin_model->updateAiQuestion($questionId, $updateData);

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

            $vacancy = $this->admin_model->getJobById($vacancyId);
            if (empty($vacancy)) {
                echo json_encode(['status' => 'error', 'message' => 'Job vacancy record not found.']);
                return;
            }

            $candidates = $this->admin_model->getCandidatesForComparison($candidateIds);

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
