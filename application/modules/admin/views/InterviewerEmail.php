<?php
if (empty($candidatelist)) { return; }
$actLower = strtolower(isset($action) ? $action : '');
$isReschedule = ($actLower === 'reschedule');
$interviewDateFormatted = !empty($interviewDate) ? date('d-m-Y', strtotime($interviewDate)) : 'TBD';
$interviewTimeFormatted = !empty($interviewDate) ? date('h:i A', strtotime($interviewDate)) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $isReschedule ? 'Interview Rescheduled' : 'Interview Assignment'; ?></title>
    <style>
        body{margin:0;padding:0;background-color:#f0f4f8;font-family:system-ui,sans-serif;}
        .wrap{max-width:620px;margin:30px auto;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);}
        .header{background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%);padding:32px 30px;text-align:center;}
        .header h1{margin:0;color:#fff;font-size:22px;font-weight:700;}
        .header p{margin:6px 0 0;color:#bbdefb;font-size:13px;}
        .badge{display:inline-block;background:rgba(255,255,255,0.2);color:#fff;padding:4px 14px;border-radius:20px;font-size:12px;margin-top:10px;}
        .body{padding:32px 30px;color:#444;font-size:14px;line-height:24px;}
        .body p{margin:0 0 14px;}
        .info-box{background:#f8faff;border-left:4px solid #1a73e8;border-radius:6px;padding:18px 20px;margin:20px 0;}
        .info-box table{width:100%;border-collapse:collapse;}
        .info-box td{padding:6px 0;font-size:14px;}
        .info-box td:first-child{color:#666;width:140px;}
        .info-box td:last-child{font-weight:600;color:#222;}
        .meet-btn{display:block;text-align:center;margin:24px 0;}
        .meet-btn a{background:linear-gradient(135deg,#1a73e8,#0d47a1);color:#fff;padding:14px 36px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:700;display:inline-block;}
        .meet-link-text{text-align:center;margin:10px 0 0;font-size:12px;color:#888;word-break:break-all;}
        .footer{padding:20px 30px;background:#f8faff;color:#999;font-size:12px;border-top:1px solid #eee;}
        .footer strong{color:#555;}
    </style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 10px;">
<tr><td align="center">
<div class="wrap">
    <div class="header">
        <?php if ($isReschedule): ?>
        <h1>&#127897; Interview Rescheduled</h1>
        <p>The interview assigned to you has been rescheduled</p>
        <?php else: ?>
        <h1>&#127897; Interview Assignment</h1>
        <p>You have been assigned to conduct an interview</p>
        <?php endif; ?>
        <span class="badge"><?php echo htmlspecialchars($jobTitle); ?></span>
    </div>
    <div class="body">
        <p>Dear <strong><?php echo htmlspecialchars($interviewerName); ?></strong>,</p>
        
        <?php if ($isReschedule): ?>
        <p>Please note that the interview assigned to you for candidate <strong><?php echo htmlspecialchars($candidatelist->Fullname); ?></strong> has been <strong>RESCHEDULED</strong>.</p>
        <p>Below are the updated interview details. 
        <?php if (strtolower($interviewMode ?? '') === 'online'): ?>
        Please review the details and join the meeting at the updated time.
        <?php else: ?>
        Please review the candidate details below for this in-person interview.
        <?php endif; ?>
        </p>
        <?php else: ?>
        <p>You have been scheduled to conduct an interview for the following candidate. 
        <?php if (strtolower($interviewMode ?? '') === 'online'): ?>
        Please review the details and join the meeting at the scheduled time.
        <?php else: ?>
        Please review the candidate details below for this in-person interview.
        <?php endif; ?>
        </p>
        <?php endif; ?>

        <div class="info-box">
            <table>
                <tr><td>Candidate</td><td><?php echo htmlspecialchars($candidatelist->Fullname); ?></td></tr>
                <tr><td>Job Position</td><td><?php echo htmlspecialchars($jobTitle); ?></td></tr>
                <tr><td>Interview Level</td><td><?php echo htmlspecialchars(isset($interviewLevelName) ? $interviewLevelName : 'Interview'); ?></td></tr>
                <tr><td><?php echo $isReschedule ? 'Updated Date' : 'Date'; ?></td><td><?php echo $interviewDateFormatted; ?></td></tr>
                <?php if (!empty($interviewTimeFormatted)): ?><tr><td><?php echo $isReschedule ? 'Updated Time' : 'Time'; ?></td><td><?php echo $interviewTimeFormatted; ?></td></tr><?php endif; ?>
                <tr><td>Mode</td><td><?php echo htmlspecialchars($interviewMode); ?></td></tr>
            </table>
        </div>
        <?php if (!empty($meetLink)): ?>
        <p>Click the button below to join the online interview:</p>
        <div class="meet-btn">
            <a href="<?php echo htmlspecialchars($meetLink); ?>" target="_blank">&#127909; Join <?php echo $isReschedule ? 'Rescheduled ' : ''; ?>Meeting</a>
        </div>
        <p class="meet-link-text">Or copy this link: <a href="<?php echo htmlspecialchars($meetLink); ?>" style="color:#1a73e8;"><?php echo htmlspecialchars($meetLink); ?></a></p>
        <?php endif; ?>
        <p style="margin-top:24px;">If you have any questions, please contact the HR team.</p>
    </div>
    <div class="footer">
        <p>For assistance, contact <strong>info@inetcsc.com</strong> or call <strong>+91 44 4400 6666</strong>.</p>
        <p>Best regards,<br><strong>HR Recruitment Team &ndash; I-NET CSC</strong></p>
    </div>
</div>
</td></tr>
</table>
</body>
</html>
