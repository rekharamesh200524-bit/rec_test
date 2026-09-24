<?php
if (empty($candidatelist)) { return; }
$actLower = strtolower(isset($action) ? $action : '');
$isReschedule = ($actLower === 'reschedule');
$interviewDateFormatted = !empty($interviewDate) ? date('d-m-Y', strtotime($interviewDate)) : 'TBD';
$interviewTimeFormatted = !empty($interviewDate) ? date('h:i A', strtotime($interviewDate)) : '';
$candidateName = !empty($candidatelist->Fullname) ? $candidatelist->Fullname : 'Candidate';
$jobTitleText = !empty($jobTitle) ? $jobTitle : 'Position';
$interviewerNameText = !empty($interviewerName) ? $interviewerName : 'Interviewer';
$mode = strtolower(isset($interviewMode) ? $interviewMode : '');
$pageTitle = $isReschedule ? 'Interview Rescheduled' : 'Interview Assignment';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #333333; line-height: 1.6;">Dear <strong><?php echo htmlspecialchars($interviewerNameText); ?></strong>,</p>

            <?php if ($isReschedule): ?>
                <!-- RESCHEDULED INTERVIEW (Offline / Online) -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    Please note that the interview assigned to you for candidate <strong><?php echo htmlspecialchars($candidateName); ?></strong> for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position has been <strong style="color: #d97706;">RESCHEDULED</strong>.
                </p>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    <?php echo ($mode === 'online') ? 'Below are the updated online interview details:' : 'Below are the updated in-person interview details:'; ?>
                </p>
            <?php else: ?>
                <!-- SCHEDULED INTERVIEW ASSIGNMENT (Offline / Online) -->
                <p style="margin: 0 0 14px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    You have been scheduled to conduct an interview for the <strong><?php echo htmlspecialchars($jobTitleText); ?></strong> position.
                </p>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #333333; line-height: 1.6;">
                    <?php echo ($mode === 'online') ? 'Please review the candidate details below and join the meeting at the scheduled time:' : 'Please review the candidate details below for this in-person interview:'; ?>
                </p>
            <?php endif; ?>

            <!-- Interview Details Table -->
            <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo $isReschedule ? 'Updated Interview Assignment Details' : 'Interview Assignment Details'; ?></strong>
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
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;">Interview Level:</td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars(isset($interviewLevelName) ? $interviewLevelName : 'Interview'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;"><?php echo $isReschedule ? 'Updated Date:' : 'Date:'; ?></td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewDateFormatted); ?></td>
                        </tr>
                        <?php if (!empty($interviewTimeFormatted)): ?>
                        <tr>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; font-size: 13px;"><?php echo $isReschedule ? 'Updated Time:' : 'Time:'; ?></td>
                            <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px;"><?php echo htmlspecialchars($interviewTimeFormatted); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="padding: 10px 16px; <?php echo ($mode !== 'online') ? 'border-bottom: 1px solid #f1f5f9;' : ''; ?> font-weight: bold; color: #475569; font-size: 13px;">Mode:</td>
                            <td style="padding: 10px 16px; <?php echo ($mode !== 'online') ? 'border-bottom: 1px solid #f1f5f9;' : ''; ?> color: #1e293b; font-size: 13px;">
                                <?php echo ($mode === 'online') ? 'Online (Video Call)' : 'In-Person (Offline)'; ?>
                            </td>
                        </tr>
                        <?php if ($mode !== 'online'): ?>
                        <tr>
                            <td style="padding: 10px 16px; font-weight: bold; color: #475569; font-size: 13px;">Venue:</td>
                            <td style="padding: 10px 16px; color: #1e293b; font-size: 13px;">I-NET Secure Labs Pvt. Ltd., Chennai, Tamil Nadu</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($mode === 'online'): ?>
                <!-- Online Teams Meeting & Instructions -->
                <?php if (!empty($meetLink)): ?>
                <p style="margin: 20px 0 10px 0; font-size: 14px; color: #333333;">Click the button below to join the interview meeting:</p>
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
                    <strong style="color: #1e293b; font-size: 13px;">Interviewer Instructions:</strong>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">Please join the meeting on time and record your feedback in the recruitment portal after the interview concludes.</p>
                </div>
            <?php else: ?>
                <!-- Offline Instructions -->
                <div style="margin: 20px 0; padding: 14px 18px; background-color: #f8fafc; border-left: 4px solid #0f766e; border-radius: 4px;">
                    <strong style="color: #1e293b; font-size: 13px;">Interviewer Instructions:</strong>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">Please be present at the interview venue on time. The candidate's resume and application details can be accessed via the recruitment portal.</p>
                </div>
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

