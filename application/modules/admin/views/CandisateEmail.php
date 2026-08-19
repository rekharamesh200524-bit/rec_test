<?php 
$action = strtolower(isset($action) ? $action : '');
$mode   = strtolower(isset($interviewMode) ? $interviewMode : '');
$isReschedule = ($action === 'reschedule');
?>
<?php 
if(!empty($candidatelist)) {
} else {
    redirect($this->config->item('base_url').'admin/index');
}
?>

<?php
$interviewDateFormatted = !empty($interviewDate) ? date('d M Y', strtotime($interviewDate)) : 'TBD';
$interviewTimeFormatted = !empty($interviewDate) ? date('h:i A', strtotime($interviewDate)) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo ($action == 'rejected') ? 'Application Update' : ($isReschedule ? 'Interview Rescheduled' : (($mode == 'offline') ? 'Interview Call Letter' : 'Interview Scheduled')); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin:0; padding:0; background-color:#f4f6f8; font-family:system-ui,sans-serif; }
        .container { max-width:620px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.07); }
        .header-online  { background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); padding:28px; text-align:center; color:#fff; }
        .header-offline { background:linear-gradient(135deg,#2e7d32 0%,#1b5e20 100%); padding:28px; text-align:center; color:#fff; }
        .header-reject  { background:#3F51B5; padding:25px; text-align:center; color:#fff; }
        .header h1 { margin:0; font-size:22px; font-weight:700; }
        .header p  { margin:6px 0 0; font-size:13px; opacity:0.85; }
        .badge { display:inline-block; background:rgba(255,255,255,0.2); color:#fff; padding:4px 14px; border-radius:20px; font-size:12px; margin-top:10px; }
        .content { padding:30px; color:#555; font-size:14px; line-height:24px; }
        .content p { margin:0 0 14px; }
        .info-box { border-radius:6px; padding:18px 20px; margin:20px 0; }
        .info-box.online  { background:#f0f6ff; border-left:4px solid #1a73e8; }
        .info-box.offline { background:#f0fff4; border-left:4px solid #2e7d32; }
        .info-box table { width:100%; border-collapse:collapse; }
        .info-box td { padding:7px 0; font-size:14px; }
        .info-box td:first-child { color:#666; width:130px; }
        .info-box td:last-child  { font-weight:600; color:#222; }
        .meet-btn { text-align:center; margin:24px 0; }
        .meet-btn a { background:linear-gradient(135deg,#1a73e8,#0d47a1); color:#fff; padding:13px 32px; text-decoration:none; border-radius:6px; font-size:15px; font-weight:700; display:inline-block; }
        .meet-link-text { text-align:center; font-size:12px; color:#999; margin-top:8px; word-break:break-all; }
        .call-letter-box { border:2px solid #2e7d32; border-radius:8px; padding:24px; margin:20px 0; background:#f9fff9; }
        .call-letter-box h3 { margin:0 0 14px; color:#2e7d32; font-size:16px; }
        .call-letter-box p  { margin:0 0 10px; font-size:14px; color:#444; }
        .footer { padding:20px 30px; background:#fafafa; color:#777; font-size:12px; line-height:20px; border-top:1px solid #eee; }
    </style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 10px;">
    <tr><td align="center">
        <div class="container">

            <?php if ($action == 'rejected'): ?>
            <div class="header header-reject"><h1>Application Update</h1></div>

            <?php elseif ($isReschedule): ?>
                <?php if ($mode == 'offline'): ?>
                <div class="header header-offline">
                    <h1>&#128222; Interview Rescheduled</h1>
                    <p>Your in-person interview call letter has been updated</p>
                    <span class="badge"><?php echo htmlspecialchars($jobTitle); ?></span>
                </div>
                <?php else: ?>
                <div class="header header-online">
                    <h1>&#127909; Interview Rescheduled</h1>
                    <p>Your online interview schedule has been updated</p>
                    <span class="badge"><?php echo htmlspecialchars($jobTitle); ?></span>
                </div>
                <?php endif; ?>

            <?php elseif ($mode == 'offline'): ?>
            <div class="header header-offline">
                <h1>&#128222; Interview Call Letter</h1>
                <p>You have been called for an in-person interview</p>
                <span class="badge"><?php echo htmlspecialchars($jobTitle); ?></span>
            </div>

            <?php elseif ($mode == 'online'): ?>
            <div class="header header-online">
                <h1>&#127909; Interview Scheduled</h1>
                <p>Your online interview has been confirmed</p>
                <span class="badge"><?php echo htmlspecialchars($jobTitle); ?></span>
            </div>

            <?php else: ?>
            <div class="header header-online">
                <h1><?php echo htmlspecialchars($jobTitle); ?> &ndash; Shortlisted</h1>
            </div>
            <?php endif; ?>

            <div class="content">
                <p>Dear <strong><?php echo htmlspecialchars($candidatelist->Fullname); ?></strong>,</p>

                <?php if ($action == 'rejected'): ?>
                    <p>Thank you for applying with us.</p>
                    <p>After careful consideration, we regret to inform you that your profile has not been selected for further rounds. We appreciate your time and encourage you to apply again in the future.</p>

                <?php elseif ($isReschedule): ?>
                    <p>Please note that your interview for the <strong><?php echo htmlspecialchars($jobTitle); ?></strong> position has been <strong>RESCHEDULED</strong>.</p>
                    
                    <?php if ($mode == 'offline'): ?>
                        <p>Below are your updated in-person interview details:</p>

                        <div class="call-letter-box">
                            <h3>&#128196; Updated Interview Call Letter</h3>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:6px 0;color:#666;width:130px;">Updated Date</td>
                                    <td style="padding:6px 0;font-weight:600;color:#222;"><?php echo $interviewDateFormatted; ?></td>
                                </tr>
                                <?php if (!empty($interviewTimeFormatted)): ?>
                                <tr>
                                    <td style="padding:6px 0;color:#666;">Updated Time</td>
                                    <td style="padding:6px 0;font-weight:600;color:#222;"><?php echo $interviewTimeFormatted; ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="padding:6px 0;color:#666;">Mode</td>
                                    <td style="padding:6px 0;font-weight:600;color:#222;">In-Person (Offline)</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;color:#666;">Venue</td>
                                    <td style="padding:6px 0;font-weight:600;color:#222;">I-NET Secure Labs Pvt. Ltd.,<br>Chennai, Tamil Nadu</td>
                                </tr>
                            </table>
                        </div>

                        <p><strong>Please bring the following documents:</strong></p>
                        <ul style="margin:0 0 14px;padding-left:20px;color:#555;">
                            <li>Updated resume (2 copies)</li>
                            <li>Government-issued photo ID</li>
                            <li>Educational certificates</li>
                            <li>Experience/relieving letters (if applicable)</li>
                        </ul>
                        <p>Please arrive 10–15 minutes before the updated time. If you have any questions, contact us at <strong>info@inetcsc.com</strong>.</p>

                    <?php else: ?>
                        <p>Below are your updated online interview details:</p>

                        <div class="info-box online">
                            <table>
                                <tr><td>Position</td><td><?php echo htmlspecialchars($jobTitle); ?></td></tr>
                                <tr><td>Updated Date</td><td><?php echo $interviewDateFormatted; ?></td></tr>
                                <?php if (!empty($interviewTimeFormatted)): ?>
                                <tr><td>Updated Time</td><td><?php echo $interviewTimeFormatted; ?></td></tr>
                                <?php endif; ?>
                                <tr><td>Mode</td><td>Online (Video Call)</td></tr>
                            </table>
                        </div>

                        <?php if (!empty($meetLink)): ?>
                        <p>Click the button below to join your rescheduled interview at the updated time:</p>
                        <div class="meet-btn">
                            <a href="<?php echo htmlspecialchars($meetLink); ?>" target="_blank">&#127909; Join Rescheduled Interview</a>
                        </div>
                        <p class="meet-link-text">
                            Or copy this link:<br>
                            <a href="<?php echo htmlspecialchars($meetLink); ?>" style="color:#1a73e8;"><?php echo htmlspecialchars($meetLink); ?></a>
                        </p>
                        <?php endif; ?>

                        <p style="margin-top:20px;">Please join the meeting 2–3 minutes early. If you face any issues, contact <strong>info@inetcsc.com</strong>.</p>
                    <?php endif; ?>

                <?php elseif ($mode == 'offline'): ?>
                    <p>We are pleased to inform you that you have been shortlisted for the <strong><?php echo htmlspecialchars($jobTitle); ?></strong> position and are invited for an in-person interview.</p>

                    <div class="call-letter-box">
                        <h3>&#128196; Interview Call Letter</h3>
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:6px 0;color:#666;width:130px;">Date</td>
                                <td style="padding:6px 0;font-weight:600;color:#222;"><?php echo $interviewDateFormatted; ?></td>
                            </tr>
                            <?php if (!empty($interviewTimeFormatted)): ?>
                            <tr>
                                <td style="padding:6px 0;color:#666;">Time</td>
                                <td style="padding:6px 0;font-weight:600;color:#222;"><?php echo $interviewTimeFormatted; ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:6px 0;color:#666;">Mode</td>
                                <td style="padding:6px 0;font-weight:600;color:#222;">In-Person (Offline)</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#666;">Venue</td>
                                <td style="padding:6px 0;font-weight:600;color:#222;">I-NET Secure Labs Pvt. Ltd.,<br>Chennai, Tamil Nadu</td>
                            </tr>
                        </table>
                    </div>

                    <p><strong>Please bring the following documents:</strong></p>
                    <ul style="margin:0 0 14px;padding-left:20px;color:#555;">
                        <li>Updated resume (2 copies)</li>
                        <li>Government-issued photo ID</li>
                        <li>Educational certificates</li>
                        <li>Experience/relieving letters (if applicable)</li>
                    </ul>
                    <p>Please arrive 10–15 minutes before the scheduled time. If you need to reschedule, contact us at <strong>info@inetcsc.com</strong>.</p>

                <?php elseif ($mode == 'online'): ?>
                    <p>Congratulations! Your profile has been shortlisted for the <strong><?php echo htmlspecialchars($jobTitle); ?></strong> position. An online interview has been scheduled for you.</p>

                    <div class="info-box online">
                        <table>
                            <tr><td>Position</td><td><?php echo htmlspecialchars($jobTitle); ?></td></tr>
                            <tr><td>Date</td><td><?php echo $interviewDateFormatted; ?></td></tr>
                            <?php if (!empty($interviewTimeFormatted)): ?>
                            <tr><td>Time</td><td><?php echo $interviewTimeFormatted; ?></td></tr>
                            <?php endif; ?>
                            <tr><td>Mode</td><td>Online (Video Call)</td></tr>
                        </table>
                    </div>

                    <?php if (!empty($meetLink)): ?>
                    <p>Click the button below to join your interview at the scheduled time:</p>
                    <div class="meet-btn">
                        <a href="<?php echo htmlspecialchars($meetLink); ?>" target="_blank">&#127909; Join Interview Now</a>
                    </div>
                    <p class="meet-link-text">
                        Or copy this link:<br>
                        <a href="<?php echo htmlspecialchars($meetLink); ?>" style="color:#1a73e8;"><?php echo htmlspecialchars($meetLink); ?></a>
                    </p>
                    <?php endif; ?>

                    <p style="margin-top:20px;">Please join the meeting 2–3 minutes early and ensure your camera and microphone are working. If you face any issues, contact <strong>info@inetcsc.com</strong>.</p>

                <?php elseif ($action == 'offer'): ?>
                    <p>Congratulations! We are pleased to extend an offer to you. Our HR team will contact you with further details.</p>

                <?php else: ?>
                    <p>We are pleased to inform you that your profile has been shortlisted. Our team will contact you shortly with the next steps.</p>
                <?php endif; ?>

