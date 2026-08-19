<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-08-18 10:37:12 --> Array
ERROR - 2026-08-18 10:38:27 --> Array
ERROR - 2026-08-18 11:08:40 --> Array
ERROR - 2026-08-18 11:09:05 --> Array
ERROR - 2026-08-18 11:09:23 --> Array
ERROR - 2026-08-18 11:09:37 --> Array
ERROR - 2026-08-18 11:13:09 --> Array
ERROR - 2026-08-18 11:13:21 --> Array
ERROR - 2026-08-18 12:23:59 --> [AiInterviewQuestionGenerator] AI failed: cURL error: Operation timed out after 12002 milliseconds with 0 bytes received — using fallback.
ERROR - 2026-08-18 12:54:41 --> Array
ERROR - 2026-08-18 09:41:32 --> 404 Page Not Found: ../modules/admin/controllers/Admin/my_interviews
ERROR - 2026-08-18 14:59:04 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`rec`.`candidatestagetracking`, CONSTRAINT `FK_CST_User` FOREIGN KEY (`ActionBy`) REFERENCES `ihusers` (`IUid`)) - Invalid query: INSERT INTO `CandidateStageTracking` (`ApplicationId`, `StageId`, `Action`, `ActionBy`, `ActionAt`, `Remarks`) VALUES ('28', 1, 'HR Final Decision - Keep in Consideration', 1, '2026-08-18 14:59:04', 'Automated Candidate 360 Verification Test Rationale')
ERROR - 2026-08-18 15:06:26 --> Query error: Unknown column 'd.department_name' in 'field list' - Invalid query: SELECT `ci`.*, `u`.`EmpCode` as `InterviewerEmpCode`, `u`.`EmpName` as `InterviewerName`, `u`.`EmpDesignation` as `InterviewerDesignation`, `u`.`EmpEmail` as `InterviewerEmail`, `d`.`department_name` as `InterviewerDepartment`
FROM `CandidateInterviews` `ci`
LEFT JOIN `IHUsers` `u` ON `u`.`IUid` = `ci`.`InterviewerId`
LEFT JOIN `departments` `d` ON `d`.`id` = `u`.`Did`
WHERE `ci`.`ApplicationId` = 28
ORDER BY `ci`.`InterviewId` ASC
