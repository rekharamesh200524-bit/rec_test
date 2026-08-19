<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-08-19 10:32:30 --> Array
ERROR - 2026-08-19 10:32:54 --> Array
ERROR - 2026-08-19 10:33:09 --> Array
ERROR - 2026-08-19 10:33:20 --> Array
ERROR - 2026-08-19 10:39:56 --> Array
ERROR - 2026-08-19 10:41:51 --> Array
ERROR - 2026-08-19 10:46:20 --> Array
ERROR - 2026-08-19 10:46:46 --> Array
ERROR - 2026-08-19 10:46:59 --> Array
ERROR - 2026-08-19 10:56:49 --> Array
ERROR - 2026-08-19 10:58:38 --> Array
ERROR - 2026-08-19 10:59:08 --> Array
ERROR - 2026-08-19 11:28:33 --> [getAiInterviewQuestions Exception] syntax error, unexpected '$config' (T_VARIABLE) in D:\xampp\htdocs\REC\application\config\ai_config.php:28
ERROR - 2026-08-19 11:29:43 --> [Gemini Model 404] Retrying with model 'gemini-flash-latest'
ERROR - 2026-08-19 11:29:55 --> [Gemini] HTTP 0: 
ERROR - 2026-08-19 11:29:55 --> [Gemini Model 404] Retrying with model 'gemini-flash-latest'
ERROR - 2026-08-19 11:30:01 --> [Gemini] HTTP 503: {
  "error": {
    "code": 503,
    "message": "This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.",
    "status": "UNAVAILABLE"
  }
}

ERROR - 2026-08-19 11:30:01 --> [AiInterviewQuestionGenerator] AI failed: Gemini API returned HTTP 503. — using fallback.
ERROR - 2026-08-19 11:30:50 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 12009 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 11:31:12 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 12010 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 11:31:41 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 12005 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 11:31:54 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 12015 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 12:05:43 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 45002 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 08:35:43 --> Severity: Error --> Maximum execution time of 30 seconds exceeded D:\xampp\htdocs\REC\system\libraries\Session\drivers\Session_files_driver.php 180
ERROR - 2026-08-19 08:35:43 --> Severity: Warning --> Unknown: Cannot call session save handler in a recursive manner Unknown 0
ERROR - 2026-08-19 08:35:43 --> Severity: Warning --> Unknown: Failed to write session data using user defined save handler. (session.save_path: C:\Users\reka\AppData\Local\Temp) Unknown 0
ERROR - 2026-08-19 12:14:05 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 45012 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-19 08:44:06 --> Severity: Error --> Maximum execution time of 30 seconds exceeded D:\xampp\htdocs\REC\system\libraries\Session\drivers\Session_files_driver.php 180
ERROR - 2026-08-19 08:44:06 --> Severity: Warning --> Unknown: Cannot call session save handler in a recursive manner Unknown 0
ERROR - 2026-08-19 08:44:06 --> Severity: Warning --> Unknown: Failed to write session data using user defined save handler. (session.save_path: C:\Users\reka\AppData\Local\Temp) Unknown 0
ERROR - 2026-08-19 12:51:01 --> Array
ERROR - 2026-08-19 12:51:47 --> Array
ERROR - 2026-08-19 12:56:39 --> Array
ERROR - 2026-08-19 13:03:57 --> Array
ERROR - 2026-08-19 13:05:03 --> Array
ERROR - 2026-08-19 13:05:52 --> Array
ERROR - 2026-08-19 14:05:41 --> Array
ERROR - 2026-08-19 10:45:11 --> 404 Page Not Found: ../modules/admin/controllers/Admin/analyzeResumeModal
ERROR - 2026-08-19 10:48:23 --> 404 Page Not Found: ../modules/admin/controllers/Admin/analyzeResumeModal
ERROR - 2026-08-19 10:49:30 --> 404 Page Not Found: ../modules/admin/controllers/Admin/analyzeResumeModal
ERROR - 2026-08-19 10:51:32 --> 404 Page Not Found: ../modules/admin/controllers/Admin/analyzeResumeModal
ERROR - 2026-08-19 10:51:42 --> 404 Page Not Found: ../modules/admin/controllers/Admin/analyzeResumeModal
ERROR - 2026-08-19 15:06:10 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:06:12 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:06:13 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:06:33 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:06 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN('29', '35')
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:07 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN('29', '35')
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:16 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN('29', '30', '31', '32', '34', '35')
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:17 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN('29', '30', '31', '32', '34', '35')
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:40 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:41 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:07:43 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:08:47 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:08:52 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:08:53 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:08:55 --> Query error: Unknown column 'c.ATS_Score' in 'field list' - Invalid query: SELECT `ja`.`ApplicationId`, `ja`.`Jid`, `ja`.`CurrentStage`, `ja`.`CurrentStatus`, `ja`.`AppliedOn`, `c`.`CandidateId`, `c`.`Fullname`, `c`.`Email`, `c`.`PhoneNo`, `c`.`ExpYrs`, `c`.`ATS_Score`, `c`.`ATS_Status`, `jl`.`JobCode`, `jl`.`JobTitle`, `jl`.`Did`, `d`.`Departmentname`
FROM `JobApplications` `ja`
INNER JOIN `IHrCandidates` `c` ON `c`.`CandidateId` = `ja`.`CandidateId`
INNER JOIN `IHRJobsList` `jl` ON `jl`.`Jid` = `ja`.`Jid`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
WHERE `ja`.`Jid` IN(0)
ORDER BY `ja`.`AppliedOn` DESC
ERROR - 2026-08-19 15:20:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`' at line 1 - Invalid query: SELECT `DISTINCT` `ja`.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`.`ApplicationId` = `ci`.`ApplicationId`
WHERE `ci`.`InterviewerId` = 16
ERROR - 2026-08-19 15:20:54 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`' at line 1 - Invalid query: SELECT `DISTINCT` `ja`.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`.`ApplicationId` = `ci`.`ApplicationId`
WHERE `ci`.`InterviewerId` = 16
ERROR - 2026-08-19 15:20:55 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`' at line 1 - Invalid query: SELECT `DISTINCT` `ja`.`Jid`
FROM `candidateinterviews` `ci`
INNER JOIN `JobApplications` `ja` ON `ja`.`ApplicationId` = `ci`.`ApplicationId`
WHERE `ci`.`InterviewerId` = 16
ERROR - 2026-08-19 15:40:23 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
ERROR - 2026-08-19 15:40:44 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
ERROR - 2026-08-19 15:40:45 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
ERROR - 2026-08-19 15:40:46 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
ERROR - 2026-08-19 15:40:48 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
ERROR - 2026-08-19 15:40:49 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON ' at line 1 - Invalid query: SELECT `DISTINCT` `jl`.*, `d`.`Departmentname`
FROM `IHRJobsList` `jl`
LEFT JOIN `Departments` `d` ON `d`.`Did` = `jl`.`Did`
INNER JOIN `JobApplications` `ja` ON `ja`.`Jid` = `jl`.`Jid`
INNER JOIN `candidateinterviews` `ci` ON `ci`.`ApplicationId` = `ja`.`ApplicationId`
WHERE `ci`.`InterviewerId` = '16'
ORDER BY `jl`.`PostedOn` DESC
