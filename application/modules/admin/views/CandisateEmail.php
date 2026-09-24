<?php 
$action = strtolower(isset($action) ? $action : '');
$mode   = strtolower(isset($interviewMode) ? $interviewMode : '');
$isReschedule = ($action === 'reschedule');

if (empty($candidatelist)) {
    redirect($this->config->item('base_url') . 'admin/index');
    return;
}

$interviewDateFormatted = !empty($interviewDate) ? date('d-m-Y', strtotime($interviewDate)) : 'TBD';
$interviewTimeFormatted = !empty($interviewDate) ? date('h:i A', strtotime($interviewDate)) : '';
$candidateName = !empty($candidatelist->Fullname) ? $candidatelist->Fullname : 'Candidate';
$jobTitleText = !empty($jobTitle) ? $jobTitle : 'Position';

$pageTitle = ($action == 'rejected') 
    ? 'Application Update' 
    : ($isReschedule 
        ? 'Interview Rescheduled' 
        : (($mode == 'offline') ? 'Interview Call Letter' : 'Interview Scheduled'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
</head>
<body style="margin: 0; padding: 20px; background-color: #f8fafc; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        
        <!-- Header -->
        <div style="background-color: #0f766e; padding: 18px 24px;">
            <h2 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: 0.3px;">I-NET Recruitment Portal</h2>
        </div>

        <!-- Content Area -->
        <div style="padding: 24px 28px;">
            <!-- Greeting -->
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #333333; line-height: 1.6;">Dear <strong><?php echo htmlspecialchars($candidateName); ?></strong>,</p>

            <?php if ($action == 'rejected'): ?>
                <!-- Application Update / Rejected -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">Thank you for your interest in the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position at I-NET Secure Labs.</p>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">After careful consideration, we regret to inform you that your profile has not been selected for further rounds at this time. We appreciate your time and encourage you to apply again in the future.</p>

            <?php elseif ($isReschedule): ?>
                <!-- PAIR 1: RESCHEDULED INTERVIEW (Offline / Online) -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    Please note that your interview for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position has been <strong style="color: #d97706;">RESCHEDULED</strong>.
                </p>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    <?php echo ($mode == 'offline') ? 'Below are your updated in-person interview details:' : 'Below are your updated online interview details:'; ?>
                </p>

                <!-- Interview Details Table -->
                <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                        <strong style="font-size: 14px; color: #1e293b;"><?php echo ($mode == 'offline') ? 'Updated Interview Call Letter' : 'Updated Interview Details'; ?></strong>
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
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Candidate:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($candidateName); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Position:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($jobTitleText); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Updated Date:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewDateFormatted); ?></td>
                            </tr>
                            <?php if (!empty($interviewTimeFormatted)): ?>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Updated Time:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewTimeFormatted); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding: 10px 16px; <?php echo ($mode == 'offline') ? 'border-bottom: 1px solid #f1f5f9;' : ''; ?> font-weight: bold; color: #475569; font-size: 13px;">Mode:</td>
                                <td style="padding: 10px 16px; <?php echo ($mode == 'offline') ? 'border-bottom: 1px solid #f1f5f9;' : ''; ?> color: #1e293b; font-size: 13px;">
                                    <?php echo ($mode == 'offline') ? 'In-Person (Offline)' : 'Online (Video Call)'; ?>
                                </td>
                            </tr>
                            <?php if ($mode == 'offline'): ?>
                            <tr>
                                <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Venue:</td>
                                <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">I-NET Secure Labs Pvt. Ltd., Chennai, Tamil Nadu</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($mode == 'offline'): ?>
                    <!-- SCENARIO 3: Offline Instructions -->
                    <div style="margin: 20px 0; padding: 14px 18px; background-color: #f8fafc; border-left: 4px solid #0f766e; border-radius: 4px;">
                        <strong style="color: #1e293b; font-size: 13px;">Please bring the following documents:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 18px; font-size: 13px; color: #475569; line-height: 1.6;">
                            <li>Updated resume (2 copies)</li>
                            <li>Government-issued photo ID</li>
                            <li>Educational certificates</li>
                            <li>Experience / relieving letters (if applicable)</li>
                        </ul>
                        <p style="margin: 10px 0 0 0; font-size: 13px; color: #475569;">Please arrive 10–15 minutes before the updated time. If you have any questions, contact us at <strong>info@inetcsc.com</strong>.</p>
                    </div>
                <?php else: ?>
                    <!-- SCENARIO 4: Online Teams Meeting & Instructions -->
                    <?php if (!empty($meetLink)): ?>
                    <p style="margin: 20px 0 10px 0; font-size: 14px; color: #333333;">Click the button below to join your rescheduled interview at the updated time:</p>
                    <table cellpadding="0" cellspacing="0" border="0" style="margin: 14px 0 16px 0;">
                        <tr>
                            <td>
                                <a href="<?php echo htmlspecialchars($meetLink); ?>" target="_blank" style="display: inline-block; background-color: #007bff; color: #ffffff !important; padding: 11px 26px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; font-family: Arial, Helvetica, sans-serif; text-align: center;">Join Teams Meeting</a>
                            </td>
                        </tr>
                    </table>
                    <p style="font-size: 12px; color: #64748b; margin: 0 0 16px 0; word-break: break-all;">Direct meeting link: <a href="<?php echo htmlspecialchars($meetLink); ?>" style="color: #007bff; text-decoration: underline;"><?php echo htmlspecialchars($meetLink); ?></a></p>
                    <?php endif; ?>

                    <div style="margin: 20px 0; padding: 14px 18px; background-color: #f8fafc; border-left: 4px solid #007bff; border-radius: 4px;">
                        <strong style="color: #1e293b; font-size: 13px;">Important Instructions:</strong>
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">Please join the meeting 2 minutes early. If you face any technical issues, contact <strong>info@inetcsc.com</strong>.</p>
                    </div>
                <?php endif; ?>

            <?php elseif ($mode == 'offline'): ?>
                <!-- PAIR 2: MAIL SCENARIO 5 — OFFLINE INTERVIEW CALL LETTER -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    We are pleased to inform you that you have been shortlisted for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position and are invited for an in-person interview.
                </p>

                <!-- Interview Call Letter Details Table -->
                <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                        <strong style="font-size: 14px; color: #1e293b;">Interview Call Letter Details</strong>
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
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Candidate:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($candidateName); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Position:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($jobTitleText); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Date:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewDateFormatted); ?></td>
                            </tr>
                            <?php if (!empty($interviewTimeFormatted)): ?>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Time:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewTimeFormatted); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Mode:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;">In-Person (Offline)</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Venue:</td>
                                <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">I-NET Secure Labs Pvt. Ltd., Chennai, Tamil Nadu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Required Documents & Instructions -->
                <div style="margin: 20px 0; padding: 14px 18px; background-color: #f8fafc; border-left: 4px solid #0f766e; border-radius: 4px;">
                    <strong style="color: #1e293b; font-size: 13px;">Please bring the following documents:</strong>
                    <ul style="margin: 8px 0 0 0; padding-left: 18px; font-size: 13px; color: #475569; line-height: 1.6;">
                        <li>Updated resume (2 copies)</li>
                        <li>Government-issued photo ID</li>
                        <li>Educational certificates</li>
                        <li>Experience / relieving letters (if applicable)</li>
                    </ul>
                    <p style="margin: 10px 0 0 0; font-size: 13px; color: #475569;">Please arrive 15 minutes before the scheduled time. If you need to reschedule, contact us at <strong>info@inetcsc.com</strong>.</p>
                </div>

            <?php elseif ($mode == 'online'): ?>
                <!-- PAIR 2: MAIL SCENARIO 6 — ONLINE INTERVIEW SCHEDULED EMAIL -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    Congratulations! Your profile has been shortlisted for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position. An online interview has been scheduled for you.
                </p>

                <!-- Interview Details Table -->
                <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                        <strong style="font-size: 14px; color: #1e293b;">Interview Details</strong>
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
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Candidate:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($candidateName); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Position:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($jobTitleText); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Date:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewDateFormatted); ?></td>
                            </tr>
                            <?php if (!empty($interviewTimeFormatted)): ?>
                            <tr>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Time:</td>
                                <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewTimeFormatted); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Mode:</td>
                                <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">Online (Video Call)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Action Button -->
                <?php if (!empty($meetLink)): ?>
                <p style="margin: 20px 0 10px 0; font-size: 14px; color: #333333;">Click the button below to join your interview at the scheduled time:</p>
                <table cellpadding="0" cellspacing="0" border="0" style="margin: 14px 0 16px 0;">
                    <tr>
                        <td>
                            <a href="<?php echo htmlspecialchars($meetLink); ?>" target="_blank" style="display: inline-block; background-color: #007bff; color: #ffffff !important; padding: 11px 26px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; font-family: Arial, Helvetica, sans-serif; text-align: center;">Join Teams Meeting</a>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 16px 0; word-break: break-all;">Direct meeting link: <a href="<?php echo htmlspecialchars($meetLink); ?>" style="color: #007bff; text-decoration: underline;"><?php echo htmlspecialchars($meetLink); ?></a></p>
                <?php endif; ?>

                <!-- Online Instructions -->
                <div style="margin: 20px 0; padding: 14px 18px; background-color: #f8fafc; border-left: 4px solid #007bff; border-radius: 4px;">
                    <strong style="color: #1e293b; font-size: 13px;">Important Instructions:</strong>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">Please join the meeting 2–3 minutes early and ensure your camera and microphone are working properly. If you face any issues, contact <strong>info@inetcsc.com</strong>.</p>
                </div>

            <?php elseif ($action == 'offer'): ?>
                <!-- Offer Letter Notification -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">Congratulations! We are pleased to extend an offer for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position. Our HR team will contact you with further details.</p>

            <?php else: ?>
                <!-- General Shortlisted Notification -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">We are pleased to inform you that your profile has been shortlisted for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position. Our team will contact you shortly with the next steps.</p>
            <?php endif; ?>

            <!-- Consistent Sign-off / Footer -->
            <p style="margin: 24px 0 0 0; font-size: 14px; color: #333333; line-height: 1.6;">
                Thanks &amp; Regards,<br>
                <strong>Recruitment Team</strong>
            </p>
        </div>

        <!-- Card Footer -->
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 24px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-style: italic;">Note: This is an auto-generated notification from I-NET Recruitment Portal.</p>
        </div>

    </div>
</body>
</html>
