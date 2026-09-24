-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2026 at 08:39 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rec`
--

-- --------------------------------------------------------

--
-- Table structure for table `AI_Interview_Questions`
--

CREATE TABLE `AI_Interview_Questions` (
  `id` int(11) NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `vacancy_id` int(11) NOT NULL,
  `interview_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_type` varchar(50) NOT NULL DEFAULT 'technical',
  `difficulty` varchar(30) NOT NULL DEFAULT 'medium',
  `skill` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `personalized` tinyint(1) DEFAULT 1,
  `generation_version` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `question_hash` varchar(64) DEFAULT NULL,
  `status_notes` varchar(50) DEFAULT 'unasked',
  `interviewer_notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `CandidateFollowUps`
--

CREATE TABLE `CandidateFollowUps` (
  `FollowUpId` int(11) NOT NULL,
  `ApplicationId` int(11) NOT NULL,
  `FollowUpType` enum('Call','Email','WhatsApp','Meeting') DEFAULT NULL,
  `FollowUpNotes` text DEFAULT NULL,
  `NextFollowUpDate` date DEFAULT NULL,
  `CreatedBy` int(10) UNSIGNED DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `CandidateHiring`
--

CREATE TABLE `CandidateHiring` (
  `HiringId` int(10) UNSIGNED NOT NULL,
  `ApplicationId` int(10) UNSIGNED NOT NULL,
  `CandidateId` int(10) UNSIGNED NOT NULL,
  `HiringDate` date NOT NULL,
  `JoiningDate` date DEFAULT NULL,
  `SalaryOffered` decimal(12,2) DEFAULT NULL,
  `Remarks` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CreatedBy` int(10) UNSIGNED DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `CandidateInterviews`
--

CREATE TABLE `CandidateInterviews` (
  `InterviewId` int(11) NOT NULL,
  `ApplicationId` int(11) NOT NULL,
  `InterviewRound` int(11) DEFAULT NULL,
  `InterviewType` varchar(100) DEFAULT NULL,
  `InterviewerId` int(11) NOT NULL,
  `ScheduledAt` datetime DEFAULT NULL,
  `CompletedAt` datetime DEFAULT NULL,
  `Feedback` text DEFAULT NULL,
  `SkillScore` int(11) DEFAULT NULL,
  `CommunicationScore` int(11) DEFAULT NULL,
  `ProblemSolvingScore` int(11) DEFAULT NULL,
  `CultureFitScore` int(11) DEFAULT NULL,
  `LeadershipScore` int(11) DEFAULT NULL,
  `OverallScore` decimal(5,2) DEFAULT NULL,
  `Result` varchar(100) DEFAULT NULL,
  `MeetLink` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `CandidateOffers`
--

CREATE TABLE `CandidateOffers` (
  `OfferId` int(11) NOT NULL,
  `ApplicationId` int(11) NOT NULL,
  `OfferDate` date DEFAULT NULL,
  `NoticePeriodDays` int(11) DEFAULT NULL,
  `ExpectedJoiningDate` date DEFAULT NULL,
  `OfferStatus` varchar(100) DEFAULT NULL,
  `OfferActionAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `CandidateStageTracking`
--

CREATE TABLE `CandidateStageTracking` (
  `TrackId` int(11) NOT NULL,
  `ApplicationId` int(11) NOT NULL,
  `StageId` int(11) NOT NULL,
  `Action` varchar(100) DEFAULT NULL,
  `ActionBy` int(10) UNSIGNED DEFAULT NULL,
  `ActionAt` datetime DEFAULT current_timestamp(),
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_feedback`
--

CREATE TABLE `candidate_feedback` (
  `FeedbackId` int(11) NOT NULL,
  `ApplicationId` int(11) NOT NULL,
  `CandidateId` int(10) UNSIGNED NOT NULL,
  `OverallExperience` tinyint(4) NOT NULL DEFAULT 5,
  `ApplicationProcess` tinyint(4) NOT NULL DEFAULT 5,
  `EaseOfApplying` tinyint(4) NOT NULL DEFAULT 5,
  `Communication` tinyint(4) NOT NULL DEFAULT 5,
  `Comments` text DEFAULT NULL,
  `SubmittedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `Departments`
--

CREATE TABLE `Departments` (
  `Did` int(11) NOT NULL,
  `Departmentname` varchar(50) NOT NULL,
  `Status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `UpdatedDate` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `EmpRoles`
--

CREATE TABLE `EmpRoles` (
  `Erid` int(10) NOT NULL,
  `RoleName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` tinyint(1) NOT NULL COMMENT '1-Active,0-Inactive',
  `LastChangeTs` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `CreationTs` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `IHMenus`
--

CREATE TABLE `IHMenus` (
  `IHMid` int(11) NOT NULL,
  `MenuName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Menuurl` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ParentId` int(11) DEFAULT NULL COMMENT 'NULL = Parent menu',
  `MenuIcon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MenuStatus` tinyint(1) DEFAULT 1,
  `CreatedAT` datetime DEFAULT current_timestamp(),
  `UpdatedAT` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `IHrCandidates`
--

CREATE TABLE `IHrCandidates` (
  `CandidateId` int(10) UNSIGNED NOT NULL,
  `CandidateCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Jid` int(11) NOT NULL,
  `JobCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PhoneNo` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ExpYrs` tinyint(4) DEFAULT NULL,
  `ResumePath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ProfileMatchPer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Review Required',
  `ATS_Status` enum('Shortlisted','Rejected','Hold','Pending') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `ATS_Stage` tinyint(3) UNSIGNED DEFAULT 1 COMMENT 'ATS Level / Round',
  `MatchedSkills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EducationMatch` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ExperienceMatch` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ScoreBreakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `IHRJobsList`
--

CREATE TABLE `IHRJobsList` (
  `Jid` int(11) NOT NULL,
  `JobCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `JobTitle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `RoleSummary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Did` int(10) DEFAULT NULL,
  `EmploymentType` enum('Full-Time','Part-Time','Contract','Internship') COLLATE utf8mb4_unicode_ci NOT NULL,
  `WorkMode` enum('Onsite','Remote','Hybrid') COLLATE utf8mb4_unicode_ci NOT NULL,
  `EducationRequired` varchar(175) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ExpMin` decimal(10,2) DEFAULT 0.00,
  `ExpMax` decimal(10,2) DEFAULT 0.00,
  `SalMin` int(10) UNSIGNED DEFAULT 0,
  `SalMax` int(10) UNSIGNED DEFAULT 0,
  `Currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'INR',
  `NoofOpenings` int(10) UNSIGNED DEFAULT 1,
  `JobStatus` enum('Draft','Open','On-Hold','Closed','Re-Open','Not Required') COLLATE utf8mb4_unicode_ci DEFAULT 'Open',
  `JobDescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Responsibilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Qualifications` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `JobLocation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CommunicationLang` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PostedOn` datetime DEFAULT current_timestamp(),
  `UpdatedOn` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ExpiryDate` date DEFAULT NULL,
  `TargetOnboardingDate` date DEFAULT NULL,
  `Salary` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PostedBy` int(10) UNSIGNED DEFAULT NULL,
  `SkillScore` int(11) NOT NULL,
  `EducationScore` int(11) NOT NULL,
  `ExperienceScore` int(11) NOT NULL,
  `ProjectScore` int(11) NOT NULL,
  `CertificationScore` int(11) NOT NULL,
  `ResumeQualityScore` int(11) NOT NULL,
  `DomainKnowledgeScore` int(11) NOT NULL,
  `CtcApproverId` int(10) UNSIGNED DEFAULT NULL,
  `AssignedRecruiterManagerId` int(10) UNSIGNED DEFAULT NULL,
  `HoldUntilDate` date DEFAULT NULL,
  `HoldReminderSentDate` date DEFAULT NULL,
  `MustHaveSkills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NiceToHaveSkills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `IHrmsLogin_Log`
--

CREATE TABLE `IHrmsLogin_Log` (
  `Ilid` int(11) NOT NULL,
  `IUid` int(10) UNSIGNED DEFAULT NULL,
  `EmpRole` int(11) DEFAULT NULL,
  `Logdescription` text NOT NULL,
  `Ipaddress` varchar(45) NOT NULL,
  `Status` tinyint(4) NOT NULL COMMENT '1=Login, 0=Logout',
  `LogInTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LogOutTime` timestamp NULL DEFAULT NULL,
  `CreationTs` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `IHrNotifications`
--

CREATE TABLE `IHrNotifications` (
  `NotificationId` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` enum('Unread','Read') COLLATE utf8mb4_unicode_ci DEFAULT 'Unread',
  `TargetRoleId` int(11) DEFAULT NULL,
  `TargetUserId` int(11) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `IHRolePermissions`
--

CREATE TABLE `IHRolePermissions` (
  `IHRid` int(11) NOT NULL,
  `Erid` int(10) DEFAULT NULL,
  `IHMid` int(11) DEFAULT NULL,
  `Status` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedAT` datetime DEFAULT current_timestamp(),
  `UpdatedAT` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `IHSkills`
--

CREATE TABLE `IHSkills` (
  `SkillId` int(11) NOT NULL,
  `SkillName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `IHUsers`
--

CREATE TABLE `IHUsers` (
  `IUid` int(10) UNSIGNED NOT NULL,
  `EmpCode` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpName` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpEmail` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpPass` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpFPtoken` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpFPtokenExpiry` datetime NOT NULL,
  `ResetToken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ResetTokenCreatedAt` datetime DEFAULT NULL,
  `LastPasswordResetAt` datetime DEFAULT NULL,
  `LastResetTokenUsedAt` datetime DEFAULT NULL,
  `EmpPhone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmpDOB` date DEFAULT NULL,
  `EmpGender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmpDesignation` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Erid` int(10) DEFAULT NULL,
  `Did` int(10) DEFAULT NULL,
  `UStatus` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `CreatedAT` datetime DEFAULT current_timestamp(),
  `UpdatedAT` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Ih_User_Otps`
--

CREATE TABLE `Ih_User_Otps` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: pending, 1: verified, 2: expired/invalidated',
  `attempt_count` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `JobApplications`
--

CREATE TABLE `JobApplications` (
  `ApplicationId` int(11) NOT NULL,
  `Jid` int(11) NOT NULL,
  `CandidateId` int(10) UNSIGNED NOT NULL,
  `ApplicationToken` varchar(64) DEFAULT NULL,
  `StageId` int(11) DEFAULT NULL,
  `CurrentStage` varchar(50) DEFAULT 'Application Created',
  `CurrentStatus` varchar(255) DEFAULT NULL,
  `AppliedOn` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `JobInterviewPanels`
--

CREATE TABLE `JobInterviewPanels` (
  `PanelId` int(11) NOT NULL,
  `Jid` int(11) NOT NULL,
  `LevelOrder` int(11) NOT NULL,
  `InterviewerId` int(11) NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `jobskills`
--

CREATE TABLE `jobskills` (
  `Jid` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `JobTracking`
--

CREATE TABLE `JobTracking` (
  `TrackId` int(11) NOT NULL,
  `Jid` int(11) DEFAULT NULL,
  `RequestId` int(11) DEFAULT NULL,
  `EventType` varchar(50) NOT NULL,
  `EventTitle` varchar(255) NOT NULL,
  `EventDescription` text DEFAULT NULL,
  `HoldUntilDate` date DEFAULT NULL,
  `ActionBy` int(11) DEFAULT NULL,
  `ActionAt` datetime NOT NULL,
  `CreatedOn` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `RecruitmentStages`
--

CREATE TABLE `RecruitmentStages` (
  `StageId` int(11) NOT NULL,
  `StageGroup` varchar(50) DEFAULT NULL,
  `StageName` varchar(50) DEFAULT NULL,
  `StageOrder` int(11) DEFAULT NULL,
  `IsFinal` tinyint(1) DEFAULT 0,
  `CreatedAT` datetime DEFAULT current_timestamp(),
  `StageStatus` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `Resource_Requests`
--

CREATE TABLE `Resource_Requests` (
  `RequestId` int(11) NOT NULL,
  `RequestCode` varchar(50) NOT NULL,
  `JobTitle` varchar(100) NOT NULL,
  `FunctionalRole` varchar(100) DEFAULT NULL,
  `Did` int(10) DEFAULT NULL,
  `NoofOpenings` int(10) UNSIGNED DEFAULT 1,
  `PositionType` enum('New Position','Replacement') NOT NULL DEFAULT 'New Position',
  `ExpMin` decimal(10,2) DEFAULT 0.00,
  `ExpMax` decimal(10,2) DEFAULT 0.00,
  `RecruitmentStartDate` date DEFAULT NULL,
  `TargetOnboardingDate` date DEFAULT NULL,
  `ReasonForRequirement` text DEFAULT NULL,
  `JobDescription` text NOT NULL,
  `Responsibilities` text DEFAULT NULL,
  `RequestedBy` int(10) UNSIGNED NOT NULL,
  `ApproverId` int(10) UNSIGNED NOT NULL,
  `CtcApproverId` int(10) UNSIGNED DEFAULT NULL,
  `AssignedRecruiterManagerId` int(10) UNSIGNED DEFAULT NULL,
  `Status` enum('DRAFT','PENDING APPROVAL','ACCEPTED','REJECTED','ASSIGNED','ON-HOLD') DEFAULT 'PENDING APPROVAL',
  `ApprovalComment` text DEFAULT NULL,
  `ActionedAt` datetime DEFAULT NULL,
  `ConvertedJid` int(11) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `MustHaveSkills` text DEFAULT NULL,
  `NiceToHaveSkills` text DEFAULT NULL,
  `CommunicationLang` text DEFAULT NULL,
  `JobLocation` text DEFAULT NULL,
  `EducationRequired` text DEFAULT NULL,
  `Salary` varchar(255) DEFAULT NULL,
  `ExpectedSalaryMin` decimal(10,2) DEFAULT NULL,
  `ExpectedSalaryMax` decimal(10,2) DEFAULT NULL,
  `ExtraCcUsers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AI_Interview_Questions`
--
ALTER TABLE `AI_Interview_Questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_candidate_vacancy` (`candidate_id`,`vacancy_id`),
  ADD KEY `idx_interview` (`interview_id`),
  ADD KEY `idx_vacancy` (`vacancy_id`);

--
-- Indexes for table `CandidateFollowUps`
--
ALTER TABLE `CandidateFollowUps`
  ADD PRIMARY KEY (`FollowUpId`),
  ADD KEY `FK_CF_App` (`ApplicationId`),
  ADD KEY `FK_CF_User` (`CreatedBy`);

--
-- Indexes for table `CandidateHiring`
--
ALTER TABLE `CandidateHiring`
  ADD PRIMARY KEY (`HiringId`),
  ADD KEY `fk_hiring_candidate` (`CandidateId`);

--
-- Indexes for table `CandidateInterviews`
--
ALTER TABLE `CandidateInterviews`
  ADD PRIMARY KEY (`InterviewId`),
  ADD KEY `FK_CI_App` (`ApplicationId`);

--
-- Indexes for table `CandidateOffers`
--
ALTER TABLE `CandidateOffers`
  ADD PRIMARY KEY (`OfferId`),
  ADD KEY `FK_CO_App` (`ApplicationId`);

--
-- Indexes for table `CandidateStageTracking`
--
ALTER TABLE `CandidateStageTracking`
  ADD PRIMARY KEY (`TrackId`),
  ADD KEY `IDX_CST_App` (`ApplicationId`),
  ADD KEY `IDX_CST_Stage` (`StageId`),
  ADD KEY `IDX_CST_ActionBy` (`ActionBy`);

--
-- Indexes for table `candidate_feedback`
--
ALTER TABLE `candidate_feedback`
  ADD PRIMARY KEY (`FeedbackId`),
  ADD KEY `ApplicationId` (`ApplicationId`),
  ADD KEY `CandidateId` (`CandidateId`);

--
-- Indexes for table `Departments`
--
ALTER TABLE `Departments`
  ADD PRIMARY KEY (`Did`);

--
-- Indexes for table `EmpRoles`
--
ALTER TABLE `EmpRoles`
  ADD PRIMARY KEY (`Erid`);

--
-- Indexes for table `IHMenus`
--
ALTER TABLE `IHMenus`
  ADD PRIMARY KEY (`IHMid`),
  ADD KEY `IDX_BidMenus_ParentId` (`ParentId`);

--
-- Indexes for table `IHRJobsList`
--
ALTER TABLE `IHRJobsList`
  ADD PRIMARY KEY (`Jid`),
  ADD UNIQUE KEY `JobCode` (`JobCode`),
  ADD KEY `IDX_JL_Did` (`Did`),
  ADD KEY `IDX_JL_PostedBy` (`PostedBy`);

--
-- Indexes for table `IHrmsLogin_Log`
--
ALTER TABLE `IHrmsLogin_Log`
  ADD PRIMARY KEY (`Ilid`),
  ADD KEY `IDX_IHRMSLoginLog_IUid` (`IUid`);

--
-- Indexes for table `IHrNotifications`
--
ALTER TABLE `IHrNotifications`
  ADD PRIMARY KEY (`NotificationId`);

--
-- Indexes for table `IHRolePermissions`
--
ALTER TABLE `IHRolePermissions`
  ADD PRIMARY KEY (`IHRid`),
  ADD KEY `IDX_RP_EmpRolesId` (`Erid`),
  ADD KEY `IDX_RP_IHMenusId` (`IHMid`);

--
-- Indexes for table `IHSkills`
--
ALTER TABLE `IHSkills`
  ADD PRIMARY KEY (`SkillId`),
  ADD UNIQUE KEY `SkillName` (`SkillName`);

--
-- Indexes for table `IHUsers`
--
ALTER TABLE `IHUsers`
  ADD PRIMARY KEY (`IUid`),
  ADD UNIQUE KEY `EmpEmail` (`EmpEmail`),
  ADD UNIQUE KEY `EmpCode` (`EmpCode`),
  ADD KEY `IDX_Users_Erid` (`Erid`),
  ADD KEY `IDX_Users_Department` (`Did`);

--
-- Indexes for table `Ih_User_Otps`
--
ALTER TABLE `Ih_User_Otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `JobApplications`
--
ALTER TABLE `JobApplications`
  ADD PRIMARY KEY (`ApplicationId`),
  ADD UNIQUE KEY `UK_Job_Candidate` (`Jid`,`CandidateId`),
  ADD UNIQUE KEY `idx_app_token` (`ApplicationToken`),
  ADD KEY `IDX_JA_Jid` (`Jid`),
  ADD KEY `IDX_JA_Candidate` (`CandidateId`);

--
-- Indexes for table `JobInterviewPanels`
--
ALTER TABLE `JobInterviewPanels`
  ADD PRIMARY KEY (`PanelId`),
  ADD KEY `Jid` (`Jid`);

--
-- Indexes for table `jobskills`
--
ALTER TABLE `jobskills`
  ADD PRIMARY KEY (`Jid`,`SkillId`),
  ADD KEY `SkillId` (`SkillId`);

--
-- Indexes for table `JobTracking`
--
ALTER TABLE `JobTracking`
  ADD PRIMARY KEY (`TrackId`);

--
-- Indexes for table `RecruitmentStages`
--
ALTER TABLE `RecruitmentStages`
  ADD PRIMARY KEY (`StageId`);

--
-- Indexes for table `Resource_Requests`
--
ALTER TABLE `Resource_Requests`
  ADD PRIMARY KEY (`RequestId`),
  ADD UNIQUE KEY `RequestCode` (`RequestCode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AI_Interview_Questions`
--
ALTER TABLE `AI_Interview_Questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CandidateFollowUps`
--
ALTER TABLE `CandidateFollowUps`
  MODIFY `FollowUpId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CandidateHiring`
--
ALTER TABLE `CandidateHiring`
  MODIFY `HiringId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CandidateInterviews`
--
ALTER TABLE `CandidateInterviews`
  MODIFY `InterviewId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CandidateOffers`
--
ALTER TABLE `CandidateOffers`
  MODIFY `OfferId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CandidateStageTracking`
--
ALTER TABLE `CandidateStageTracking`
  MODIFY `TrackId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_feedback`
--
ALTER TABLE `candidate_feedback`
  MODIFY `FeedbackId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Departments`
--
ALTER TABLE `Departments`
  MODIFY `Did` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `EmpRoles`
--
ALTER TABLE `EmpRoles`
  MODIFY `Erid` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHMenus`
--
ALTER TABLE `IHMenus`
  MODIFY `IHMid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHrCandidates`
--
ALTER TABLE `IHrCandidates`
  MODIFY `CandidateId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHRJobsList`
--
ALTER TABLE `IHRJobsList`
  MODIFY `Jid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHrmsLogin_Log`
--
ALTER TABLE `IHrmsLogin_Log`
  MODIFY `Ilid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHrNotifications`
--
ALTER TABLE `IHrNotifications`
  MODIFY `NotificationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHRolePermissions`
--
ALTER TABLE `IHRolePermissions`
  MODIFY `IHRid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHSkills`
--
ALTER TABLE `IHSkills`
  MODIFY `SkillId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `IHUsers`
--
ALTER TABLE `IHUsers`
  MODIFY `IUid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Ih_User_Otps`
--
ALTER TABLE `Ih_User_Otps`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `JobApplications`
--
ALTER TABLE `JobApplications`
  MODIFY `ApplicationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `JobInterviewPanels`
--
ALTER TABLE `JobInterviewPanels`
  MODIFY `PanelId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `JobTracking`
--
ALTER TABLE `JobTracking`
  MODIFY `TrackId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `RecruitmentStages`
--
ALTER TABLE `RecruitmentStages`
  MODIFY `StageId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Resource_Requests`
--
ALTER TABLE `Resource_Requests`
  MODIFY `RequestId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CandidateFollowUps`
--
ALTER TABLE `CandidateFollowUps`
  ADD CONSTRAINT `FK_CF_App` FOREIGN KEY (`ApplicationId`) REFERENCES `JobApplications` (`ApplicationId`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_CF_User` FOREIGN KEY (`CreatedBy`) REFERENCES `IHUsers` (`IUid`);

--
-- Constraints for table `CandidateInterviews`
--
ALTER TABLE `CandidateInterviews`
  ADD CONSTRAINT `FK_CI_App` FOREIGN KEY (`ApplicationId`) REFERENCES `JobApplications` (`ApplicationId`) ON DELETE CASCADE;

--
-- Constraints for table `CandidateOffers`
--
ALTER TABLE `CandidateOffers`
  ADD CONSTRAINT `FK_CO_App` FOREIGN KEY (`ApplicationId`) REFERENCES `JobApplications` (`ApplicationId`) ON DELETE CASCADE;

--
-- Constraints for table `CandidateStageTracking`
--
ALTER TABLE `CandidateStageTracking`
  ADD CONSTRAINT `FK_CST_App` FOREIGN KEY (`ApplicationId`) REFERENCES `JobApplications` (`ApplicationId`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_CST_Stage` FOREIGN KEY (`StageId`) REFERENCES `RecruitmentStages` (`StageId`),
  ADD CONSTRAINT `FK_CST_User` FOREIGN KEY (`ActionBy`) REFERENCES `IHUsers` (`IUid`);

--
-- Constraints for table `candidate_feedback`
--
ALTER TABLE `candidate_feedback`
  ADD CONSTRAINT `fk_feedback_application` FOREIGN KEY (`ApplicationId`) REFERENCES `JobApplications` (`ApplicationId`) ON DELETE CASCADE;

--
-- Constraints for table `IHMenus`
--
ALTER TABLE `IHMenus`
  ADD CONSTRAINT `FK_IHMenus_parent` FOREIGN KEY (`ParentId`) REFERENCES `IHMenus` (`IHMid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `IHRJobsList`
--
ALTER TABLE `IHRJobsList`
  ADD CONSTRAINT `FK_JL_Department` FOREIGN KEY (`Did`) REFERENCES `Departments` (`Did`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_JL_PostedBy` FOREIGN KEY (`PostedBy`) REFERENCES `IHUsers` (`IUid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `IHrmsLogin_Log`
--
ALTER TABLE `IHrmsLogin_Log`
  ADD CONSTRAINT `FK_HRMSLoginLog_IUid` FOREIGN KEY (`IUid`) REFERENCES `IHUsers` (`IUid`);

--
-- Constraints for table `IHRolePermissions`
--
ALTER TABLE `IHRolePermissions`
  ADD CONSTRAINT `FK_RMP_Menu` FOREIGN KEY (`IHMid`) REFERENCES `IHMenus` (`IHMid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_RMP_Role` FOREIGN KEY (`Erid`) REFERENCES `EmpRoles` (`Erid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `IHUsers`
--
ALTER TABLE `IHUsers`
  ADD CONSTRAINT `FK_Users_Did` FOREIGN KEY (`Did`) REFERENCES `Departments` (`Did`),
  ADD CONSTRAINT `FK_Users_Erid` FOREIGN KEY (`Erid`) REFERENCES `EmpRoles` (`Erid`);

--
-- Constraints for table `Ih_User_Otps`
--
ALTER TABLE `Ih_User_Otps`
  ADD CONSTRAINT `fk_Ih_User_Otps_user` FOREIGN KEY (`user_id`) REFERENCES `IHUsers` (`IUid`) ON DELETE CASCADE;

--
-- Constraints for table `JobApplications`
--
ALTER TABLE `JobApplications`
  ADD CONSTRAINT `FK_JA_Candidate` FOREIGN KEY (`CandidateId`) REFERENCES `IHrCandidates` (`CandidateId`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_JA_Job` FOREIGN KEY (`Jid`) REFERENCES `IHRJobsList` (`Jid`) ON DELETE CASCADE;

--
-- Constraints for table `jobskills`
--
ALTER TABLE `jobskills`
  ADD CONSTRAINT `JobSkills_ibfk_1` FOREIGN KEY (`Jid`) REFERENCES `IHRJobsList` (`Jid`) ON DELETE CASCADE,
  ADD CONSTRAINT `JobSkills_ibfk_2` FOREIGN KEY (`SkillId`) REFERENCES `IHSkills` (`SkillId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
