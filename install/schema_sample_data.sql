-- ============================================================
-- Planford v0.1 — Full Sample Database (with Demo Data)
-- Published by Lubhata - Software & Innovations
-- License: AGPL-3.0
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- -------------------------------------------------------
-- Table structure for `audit_logs` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `org_id` int unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int unsigned DEFAULT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ua` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `org_action` (`org_id`,`action`),
  KEY `user_id` (`user_id`),
  KEY `entity` (`entity`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `audit_logs` 
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('1','3','1','login','users','3','Login from ::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-27 17:04:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('2','3','1','logout','users','3','User signed out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-27 17:08:57');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('3','2','1','login','users','2','Login from ::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-27 17:09:00');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('4','1','1','login','users','1','Executive login from 127.0.0.1','127.0.0.1','Mozilla/5.0','2026-09-27 17:39:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('5','2','1','sprint_start','sprints','2','Sprint 2 - Microservices & OAuth Integration started','127.0.0.1','Mozilla/5.0','2026-09-27 17:39:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('6','1','1','gate_signoff','phase_gates','1','Signed off Architecture & iQMS Gate 1','127.0.0.1','Mozilla/5.0','2026-09-27 17:39:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('7','2','1','gate_signoff','phase_gates','5','Quality Gate status set to passed','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-27 17:42:49');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('8','1','1','login','users','1','Executive login from 127.0.0.1','127.0.0.1','Mozilla/5.0','2026-09-27 17:46:48');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('9','2','1','sprint_start','sprints','2','Sprint 2 - Microservices & OAuth Integration started','127.0.0.1','Mozilla/5.0','2026-09-27 17:46:48');
INSERT INTO `audit_logs` (`id`,`user_id`,`org_id`,`action`,`entity`,`entity_id`,`detail`,`ip`,`ua`,`created_at`) VALUES ('10','1','1','gate_signoff','phase_gates','1','Signed off Architecture & iQMS Gate 1','127.0.0.1','Mozilla/5.0','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `automation_rules` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `automation_rules`;
CREATE TABLE `automation_rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` enum('task_blocked','task_overdue','gate_failed','budget_exceeded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` enum('notify_pm','escalate_priority','change_status','audit_log') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_trigger` (`program_id`,`trigger_event`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `automation_rules` 
INSERT INTO `automation_rules` (`id`,`program_id`,`name`,`trigger_event`,`action_type`,`is_active`,`created_at`) VALUES ('1','1','Auto-Escalate Blocked Task to Critical Priority','task_blocked','escalate_priority','1','2026-09-27 19:15:19');
INSERT INTO `automation_rules` (`id`,`program_id`,`name`,`trigger_event`,`action_type`,`is_active`,`created_at`) VALUES ('2','1','Notify PM on Overdue Task','task_overdue','notify_pm','1','2026-09-27 19:15:19');
INSERT INTO `automation_rules` (`id`,`program_id`,`name`,`trigger_event`,`action_type`,`is_active`,`created_at`) VALUES ('3','1','Audit Log & Escalation on Quality Gate Failure','gate_failed','audit_log','1','2026-09-27 19:15:19');

-- -------------------------------------------------------
-- Table structure for `capex_opex_logs` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `capex_opex_logs`;
CREATE TABLE `capex_opex_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `category` enum('capex','opex') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'capex',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fiscal_quarter` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'Q3-2026',
  `capitalization_status` enum('eligible','expensed','under_review') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'eligible',
  `logged_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_cat` (`program_id`,`category`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `capex_opex_logs` 
INSERT INTO `capex_opex_logs` (`id`,`program_id`,`category`,`item_name`,`amount`,`fiscal_quarter`,`capitalization_status`,`logged_by`,`created_at`) VALUES ('1','1','capex','AWS EKS Multi-Region Infrastructure Licensing & Custom IP','8500000.00','Q3-2026','eligible','1','2026-09-27 19:19:22');
INSERT INTO `capex_opex_logs` (`id`,`program_id`,`category`,`item_name`,`amount`,`fiscal_quarter`,`capitalization_status`,`logged_by`,`created_at`) VALUES ('2','1','capex','Custom OAuth2 Gateway Intellectual Property Development','6200000.00','Q3-2026','eligible','2','2026-09-27 19:19:22');
INSERT INTO `capex_opex_logs` (`id`,`program_id`,`category`,`item_name`,`amount`,`fiscal_quarter`,`capitalization_status`,`logged_by`,`created_at`) VALUES ('3','1','opex','Cloud Operations & 24/7 Monitoring Subscriptions','2400000.00','Q3-2026','expensed','5','2026-09-27 19:19:22');
INSERT INTO `capex_opex_logs` (`id`,`program_id`,`category`,`item_name`,`amount`,`fiscal_quarter`,`capitalization_status`,`logged_by`,`created_at`) VALUES ('4','1','opex','External Security Auditor & Penetration Test Consulting','1800000.00','Q3-2026','expensed','1','2026-09-27 19:19:22');

-- -------------------------------------------------------
-- Table structure for `change_requests` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `change_requests`;
CREATE TABLE `change_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `impact` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','submitted','approved','rejected','implemented') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `priority` enum('critical','high','medium','low') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `requested_by` int unsigned DEFAULT NULL,
  `reviewed_by` int unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `change_requests` 
INSERT INTO `change_requests` (`id`,`program_id`,`title`,`description`,`impact`,`status`,`priority`,`requested_by`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('3','1','CR-101: Add Multi-Currency Support for Global Invoicing','Client requested support for EUR, GBP, and JPY currency conversions in financial reporting.','Adds 12 story points to Sprint 3; minimal architectural risk.','approved','high','4','2','2026-09-22 11:00:00','2026-09-27 17:46:48');
INSERT INTO `change_requests` (`id`,`program_id`,`title`,`description`,`impact`,`status`,`priority`,`requested_by`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('4','1','CR-102: Extend Sprint 2 by 3 days for Network Failover Test','Additional time required to conduct AWS DirectConnect latency load testing with AWS Support.','Defers Sprint 3 start by 3 days.','submitted','medium','5',NULL,NULL,'2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `custom_field_values` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `custom_field_values`;
CREATE TABLE `custom_field_values` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int unsigned NOT NULL,
  `entity_id` int unsigned NOT NULL,
  `value_text` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_entity` (`field_id`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `custom_field_values` 
INSERT INTO `custom_field_values` (`id`,`field_id`,`entity_id`,`value_text`,`updated_at`) VALUES ('1','1','4','Confidential','2026-09-27 19:15:19');
INSERT INTO `custom_field_values` (`id`,`field_id`,`entity_id`,`value_text`,`updated_at`) VALUES ('2','2','4','PAY-8921','2026-09-27 19:15:19');
INSERT INTO `custom_field_values` (`id`,`field_id`,`entity_id`,`value_text`,`updated_at`) VALUES ('3','3','1','WBS-PRG101-CLOUD-001','2026-09-27 19:15:19');

-- -------------------------------------------------------
-- Table structure for `custom_fields` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `custom_fields`;
CREATE TABLE `custom_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` enum('program','task') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'task',
  `field_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` enum('text','number','select','date') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `options_json` json DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_field` (`entity_type`,`field_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `custom_fields` 
INSERT INTO `custom_fields` (`id`,`entity_type`,`field_name`,`field_label`,`field_type`,`options_json`,`is_required`,`created_at`) VALUES ('1','task','security_classification','Security Classification','select','[\"Public\", \"Confidential\", \"Restricted\", \"Secret\"]','0','2026-09-27 19:15:19');
INSERT INTO `custom_fields` (`id`,`entity_type`,`field_name`,`field_label`,`field_type`,`options_json`,`is_required`,`created_at`) VALUES ('2','task','jira_issue_key','Jira Issue Key','text',NULL,'0','2026-09-27 19:15:19');
INSERT INTO `custom_fields` (`id`,`entity_type`,`field_name`,`field_label`,`field_type`,`options_json`,`is_required`,`created_at`) VALUES ('3','program','sap_wbs_code','SAP WBS Element Code','text',NULL,'0','2026-09-27 19:15:19');

-- -------------------------------------------------------
-- Table structure for `documents` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int unsigned NOT NULL,
  `program_id` int unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `filename` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origname` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int unsigned DEFAULT '0',
  `mime` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `status` enum('draft','review','approved','superseded') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `is_client_visible` tinyint(1) DEFAULT '0',
  `uploaded_by` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table structure for `epics` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `epics`;
CREATE TABLE `epics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#6366f1',
  `owner_id` int unsigned DEFAULT NULL,
  `status` enum('backlog','in_progress','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'backlog',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `epics` 
INSERT INTO `epics` (`id`,`program_id`,`title`,`summary`,`color`,`owner_id`,`status`,`created_at`) VALUES ('1','1','EP-1: Cloud Infrastructure & Kubernetes Automation','Terraform IaC blueprints, EKS multi-region deployment, and Istio Service Mesh.','#6366f1','5','in_progress','2026-09-27 17:46:48');
INSERT INTO `epics` (`id`,`program_id`,`title`,`summary`,`color`,`owner_id`,`status`,`created_at`) VALUES ('2','1','EP-2: Core API & Security Hardening','OAuth2/OIDC Auth Gateway, JWT token verification, and Rate Limiting.','#06b6d4','3','in_progress','2026-09-27 17:46:48');
INSERT INTO `epics` (`id`,`program_id`,`title`,`summary`,`color`,`owner_id`,`status`,`created_at`) VALUES ('3','1','EP-3: Executive Control Tower & EVM Analytics','Financial Earned Value Management, CPI/SPI computation, and RAG status.','#22c55e','2','in_progress','2026-09-27 17:46:48');
INSERT INTO `epics` (`id`,`program_id`,`title`,`summary`,`color`,`owner_id`,`status`,`created_at`) VALUES ('4','1','EP-4: Automated CI/CD & SonarQube Quality Gates','Zero-downtime deployment pipelines and vulnerability security gates.','#f59e0b','7','backlog','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `milestones` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `milestones`;
CREATE TABLE `milestones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `phase_id` int unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date NOT NULL,
  `achieved_date` date DEFAULT NULL,
  `status` enum('pending','achieved','delayed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_client_visible` tinyint(1) DEFAULT '1',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `milestones` 
INSERT INTO `milestones` (`id`,`program_id`,`phase_id`,`title`,`description`,`due_date`,`achieved_date`,`status`,`is_client_visible`,`created_by`,`created_at`) VALUES ('5','1','1','Architecture Freeze & iQMS Sign-off','High Level Architecture and Security Blueprint approved by VP','2026-09-20','2026-09-19','achieved','1','1','2026-09-27 17:46:48');
INSERT INTO `milestones` (`id`,`program_id`,`phase_id`,`title`,`description`,`due_date`,`achieved_date`,`status`,`is_client_visible`,`created_by`,`created_at`) VALUES ('6','1','2','Sprint 2 Feature Cut & SIT Sign-off','Core Auth Gateway, API Routing, and RACI Engine integration','2026-09-30',NULL,'pending','1','2','2026-09-27 17:46:48');
INSERT INTO `milestones` (`id`,`program_id`,`phase_id`,`title`,`description`,`due_date`,`achieved_date`,`status`,`is_client_visible`,`created_by`,`created_at`) VALUES ('7','1','3','Security Penetration Test Sign-off','Zero critical/high vulnerabilities verified by external audit firm','2026-11-25',NULL,'pending','1','1','2026-09-27 17:46:48');
INSERT INTO `milestones` (`id`,`program_id`,`phase_id`,`title`,`description`,`due_date`,`achieved_date`,`status`,`is_client_visible`,`created_by`,`created_at`) VALUES ('8','1','4','Production Go-Live Launch','Full deployment to AWS EKS Production multi-region cluster','2026-12-15',NULL,'pending','1','1','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `notifications` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `type` enum('info','success','warning','danger') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `link` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id_read` (`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table structure for `organizations` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan` enum('community','cloud','enterprise') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'community',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `settings` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `organizations` 
INSERT INTO `organizations` (`id`,`name`,`slug`,`logo`,`industry`,`plan`,`is_active`,`settings`,`created_at`,`updated_at`) VALUES ('1','Lubhata Demo','lubhata-demo',NULL,'Technology','enterprise','1',NULL,'2026-09-27 11:17:20','2026-09-27 11:17:20');

-- -------------------------------------------------------
-- Table structure for `phase_gates` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `phase_gates`;
CREATE TABLE `phase_gates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `phase_id` int unsigned NOT NULL,
  `gate_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria_json` json DEFAULT NULL,
  `status` enum('pending','passed','failed','waived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `signed_off_by` int unsigned DEFAULT NULL,
  `signed_off_at` datetime DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_phase` (`program_id`,`phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `phase_gates` 
INSERT INTO `phase_gates` (`id`,`program_id`,`phase_id`,`gate_name`,`criteria_json`,`status`,`signed_off_by`,`signed_off_at`,`comments`,`created_at`) VALUES ('8','1','1','Architecture & iQMS Gate 1','{\"sast_passed\": true, \"architecture_approved\": true}','passed','1','2026-09-19 14:30:00','Passed iQMS Architecture Review with Zero Objections.','2026-09-27 17:46:48');
INSERT INTO `phase_gates` (`id`,`program_id`,`phase_id`,`gate_name`,`criteria_json`,`status`,`signed_off_by`,`signed_off_at`,`comments`,`created_at`) VALUES ('9','1','2','SIT & Code Quality Gate 2','{\"coverage_above_80\": true, \"zero_critical_bugs\": true}','pending',NULL,NULL,'Scheduled for Sprint 2 completion review.','2026-09-27 17:46:48');
INSERT INTO `phase_gates` (`id`,`program_id`,`phase_id`,`gate_name`,`criteria_json`,`status`,`signed_off_by`,`signed_off_at`,`comments`,`created_at`) VALUES ('10','1','3','Security Audit & UAT Gate 3','{\"pentest_passed\": true, \"client_uat_approved\": true}','pending',NULL,NULL,'Pending Client VP Signoff.','2026-09-27 17:46:48');
INSERT INTO `phase_gates` (`id`,`program_id`,`phase_id`,`gate_name`,`criteria_json`,`status`,`signed_off_by`,`signed_off_at`,`comments`,`created_at`) VALUES ('11','1','4','Production Go-Live Gate 4','{\"monitoring_active\": true, \"rollback_plan_verified\": true}','pending',NULL,NULL,'Final deployment signoff gate.','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `phases` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `phases`;
CREATE TABLE `phases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order_index` int DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planning','active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planning',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `phases` 
INSERT INTO `phases` (`id`,`program_id`,`name`,`description`,`order_index`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('1','1','Phase 1: Inception & Architecture Freeze','Blueprint, HLD/LLD docs, and CI/CD Pipeline Setup','1','2026-09-01','2026-09-20','completed','2026-09-27 17:46:48');
INSERT INTO `phases` (`id`,`program_id`,`name`,`description`,`order_index`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('2','1','Phase 2: Core Microservices Build & SIT','Sprint 1 to Sprint 4 development and integration testing','2','2026-09-21','2026-11-15','active','2026-09-27 17:46:48');
INSERT INTO `phases` (`id`,`program_id`,`name`,`description`,`order_index`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('3','1','Phase 3: UAT & Security Vulnerability Gate','Penetration testing and client UAT sign-off','3','2026-11-16','2026-12-10','planning','2026-09-27 17:46:48');
INSERT INTO `phases` (`id`,`program_id`,`name`,`description`,`order_index`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('4','1','Phase 4: Production Go-Live & Hypercare','Blue/Green deployment and 30-day hypercare support','4','2026-12-11','2026-12-31','planning','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `portfolio_okrs` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `portfolio_okrs`;
CREATE TABLE `portfolio_okrs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('Strategic','Operational','Quality','Financial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Strategic',
  `target_value` decimal(10,2) NOT NULL DEFAULT '100.00',
  `current_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '%',
  `due_date` date DEFAULT NULL,
  `status` enum('on_track','at_risk','behind','achieved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'on_track',
  `owner_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `portfolio_okrs` 
INSERT INTO `portfolio_okrs` (`id`,`program_id`,`title`,`category`,`target_value`,`current_value`,`unit`,`due_date`,`status`,`owner_id`,`created_at`) VALUES ('1','1','Reduce Legacy Server Footprint by 60%','Strategic','60.00','48.00','%','2026-12-31','on_track','1','2026-09-27 19:19:22');
INSERT INTO `portfolio_okrs` (`id`,`program_id`,`title`,`category`,`target_value`,`current_value`,`unit`,`due_date`,`status`,`owner_id`,`created_at`) VALUES ('2','1','Achieve 99.99% API Gateway Uptime SLAs','Operational','99.99','99.95','%','2026-11-30','on_track','3','2026-09-27 19:19:22');
INSERT INTO `portfolio_okrs` (`id`,`program_id`,`title`,`category`,`target_value`,`current_value`,`unit`,`due_date`,`status`,`owner_id`,`created_at`) VALUES ('3','1','Pass Zero High Vulnerability Security Audit','Quality','0.00','0.00','Issues','2026-11-15','on_track','5','2026-09-27 19:19:22');
INSERT INTO `portfolio_okrs` (`id`,`program_id`,`title`,`category`,`target_value`,`current_value`,`unit`,`due_date`,`status`,`owner_id`,`created_at`) VALUES ('4','2','Migrate 100% Core ISO20022 Banking Messages','Strategic','100.00','42.00','%','2026-11-30','at_risk','2','2026-09-27 19:19:22');

-- -------------------------------------------------------
-- Table structure for `program_members` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `program_members`;
CREATE TABLE `program_members` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `role` enum('pm','member','viewer','stakeholder') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prog_user` (`program_id`,`user_id`),
  KEY `program_id` (`program_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `program_members` 
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('14','1','1','pm','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('15','1','2','pm','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('16','1','3','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('17','1','4','stakeholder','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('18','1','5','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('19','1','6','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('20','1','7','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('21','2','2','pm','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('22','2','3','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('23','2','6','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('24','3','1','pm','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('25','3','5','member','2026-09-27 17:46:48');
INSERT INTO `program_members` (`id`,`program_id`,`user_id`,`role`,`joined_at`) VALUES ('26','3','7','member','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `program_raci` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `program_raci`;
CREATE TABLE `program_raci` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `raci_role` enum('R','A','C','I') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_task` (`program_id`,`task_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `program_raci` 
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('18','1','4','3','A','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('19','1','4','6','R','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('20','1','4','5','C','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('21','1','4','1','I','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('22','1','5','5','R','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('23','1','5','3','A','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('24','1','5','2','I','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('25','1','7','3','R','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('26','1','7','2','A','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('27','1','7','4','I','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('28','1','9','5','R','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('29','1','9','1','A','2026-09-27 17:46:48');
INSERT INTO `program_raci` (`id`,`program_id`,`task_id`,`user_id`,`raci_role`,`created_at`) VALUES ('30','1','9','2','I','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `programs` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `methodology` enum('scrum','kanban','waterfall','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scrum',
  `rag_status` enum('green','amber','red') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'green',
  `status` enum('planning','active','on_hold','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planning',
  `priority` enum('critical','high','medium','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `currency` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'INR',
  `owner_id` int unsigned DEFAULT NULL,
  `client_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_logo` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#6366f1',
  `is_public` tinyint(1) DEFAULT '0',
  `stakeholder_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stakeholder_token` (`stakeholder_token`),
  KEY `org_id` (`org_id`),
  KEY `owner_id` (`owner_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `programs` 
INSERT INTO `programs` (`id`,`org_id`,`code`,`name`,`description`,`methodology`,`rag_status`,`status`,`priority`,`start_date`,`end_date`,`budget`,`currency`,`owner_id`,`client_name`,`client_logo`,`cover_color`,`is_public`,`stakeholder_token`,`settings`,`created_by`,`created_at`,`updated_at`) VALUES ('1','1','PRG-101','Enterprise Cloud Transformation','Multi-cloud Kubernetes migration and microservices architecture modernization for global banking platform.','scrum','green','active','critical','2026-09-01','2026-12-31','25000000.00','INR','2','Global Financial Services Corp',NULL,'#6366f1','0',NULL,NULL,'1','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `programs` (`id`,`org_id`,`code`,`name`,`description`,`methodology`,`rag_status`,`status`,`priority`,`start_date`,`end_date`,`budget`,`currency`,`owner_id`,`client_name`,`client_logo`,`cover_color`,`is_public`,`stakeholder_token`,`settings`,`created_by`,`created_at`,`updated_at`) VALUES ('2','1','PRG-102','Core Banking API Modernization','RESTful & gRPC API gateway integration with real-time ISO20022 message processing.','hybrid','amber','active','high','2026-08-15','2026-11-30','18000000.00','INR','2','Enterprise Banking Group',NULL,'#06b6d4','0',NULL,NULL,'1','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `programs` (`id`,`org_id`,`code`,`name`,`description`,`methodology`,`rag_status`,`status`,`priority`,`start_date`,`end_date`,`budget`,`currency`,`owner_id`,`client_name`,`client_logo`,`cover_color`,`is_public`,`stakeholder_token`,`settings`,`created_by`,`created_at`,`updated_at`) VALUES ('3','1','PRG-103','AI Supply Chain & Logistics Analytics','Predictive demand forecasting engine and IoT telemetry ingestion pipeline.','kanban','green','active','medium','2026-09-10','2027-01-31','32000000.00','INR','1','Global Logistics & Retail Corp',NULL,'#22c55e','0',NULL,NULL,'1','2026-09-27 17:46:48','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `resource_allocations` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `resource_allocations`;
CREATE TABLE `resource_allocations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `allocation_pct` tinyint unsigned NOT NULL DEFAULT '100',
  `role_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Developer',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_user` (`program_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `resource_allocations` 
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('11','1','1','100','Vice President - Executive Sponsor','2026-09-01','2026-12-31','2026-09-27 17:46:48');
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('12','1','2','100','Principal Delivery Manager','2026-09-01','2026-12-31','2026-09-27 17:46:48');
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('13','1','3','100','Lead Full-Stack Architect','2026-09-01','2026-12-31','2026-09-27 17:46:48');
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('14','1','5','80','Senior DevOps Lead','2026-09-01','2026-12-31','2026-09-27 17:46:48');
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('15','1','6','100','Senior Backend Engineer','2026-09-01','2026-12-31','2026-09-27 17:46:48');
INSERT INTO `resource_allocations` (`id`,`program_id`,`user_id`,`allocation_pct`,`role_name`,`start_date`,`end_date`,`created_at`) VALUES ('16','1','7','75','Lead QA Automation Engineer','2026-09-01','2026-12-31','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `risks` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `risks`;
CREATE TABLE `risks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('risk','issue','dependency','assumption') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'risk',
  `status` enum('open','monitoring','mitigated','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `likelihood` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `impact` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `owner_id` int unsigned DEFAULT NULL,
  `mitigation` text COLLATE utf8mb4_unicode_ci,
  `due_date` date DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `risks` 
INSERT INTO `risks` (`id`,`program_id`,`title`,`description`,`type`,`status`,`likelihood`,`impact`,`owner_id`,`mitigation`,`due_date`,`created_by`,`created_at`,`updated_at`) VALUES ('4','1','AWS DirectConnect BGP Failover Latency Variance','Secondary network link experiences 450ms latency spike during route failover test.','issue','open','high','critical','5','Engage AWS Enterprise Support to reconfigure BGP timers and add IPsec backup tunnel.','2026-09-29','2','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `risks` (`id`,`program_id`,`title`,`description`,`type`,`status`,`likelihood`,`impact`,`owner_id`,`mitigation`,`due_date`,`created_by`,`created_at`,`updated_at`) VALUES ('5','1','Third-party Payment Gateway Schema Breaking Change','Upcoming API v3 upgrade deprecates legacy authentication header format.','risk','monitoring','medium','high','6','Implement adapter pattern in API gateway to support dual token formats during migration.','2026-10-15','3','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `risks` (`id`,`program_id`,`title`,`description`,`type`,`status`,`likelihood`,`impact`,`owner_id`,`mitigation`,`due_date`,`created_by`,`created_at`,`updated_at`) VALUES ('6','1','Client Security Audit Team Sign-Off Delay','ISO27001 compliance verification cycle requires 10 business days lead time.','dependency','open','high','medium','4','Submit preliminary architecture blueprint 2 weeks ahead of Phase 3 start date.','2026-10-20','2','2026-09-27 17:46:48','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `sprints` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `sprints`;
CREATE TABLE `sprints` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planning','active','completed','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planning',
  `capacity_hours` decimal(8,1) DEFAULT '80.0',
  `velocity_points` int unsigned DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `sprints` 
INSERT INTO `sprints` (`id`,`program_id`,`name`,`goal`,`start_date`,`end_date`,`status`,`capacity_hours`,`velocity_points`,`created_at`) VALUES ('1','1','Sprint 1 - Foundation & Terraform Infra','Provision AWS EKS clusters, VPC networks, and GitHub Actions workflow.','2026-09-01','2026-09-15','completed','120.0','34','2026-09-27 17:46:48');
INSERT INTO `sprints` (`id`,`program_id`,`name`,`goal`,`start_date`,`end_date`,`status`,`capacity_hours`,`velocity_points`,`created_at`) VALUES ('2','1','Sprint 2 - Microservices & OAuth Integration','Deliver Auth Service, API Gateway routing, and RACI Governance matrix.','2026-09-16','2026-09-30','active','140.0','42','2026-09-27 17:46:48');
INSERT INTO `sprints` (`id`,`program_id`,`name`,`goal`,`start_date`,`end_date`,`status`,`capacity_hours`,`velocity_points`,`created_at`) VALUES ('3','1','Sprint 3 - EVM Engine & Quality Gates','Implement Earned Value Analytics, Phase Gates, and Executive Tower UI.','2026-10-01','2026-10-15','planning','130.0','38','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `stakeholder_sessions` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `stakeholder_sessions`;
CREATE TABLE `stakeholder_sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table structure for `task_comments` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `task_comments`;
CREATE TABLE `task_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table structure for `task_dependencies` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `task_dependencies`;
CREATE TABLE `task_dependencies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int unsigned NOT NULL,
  `depends_on_task_id` int unsigned NOT NULL,
  `type` enum('FS','SS','FF','SF') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FS',
  `lag_days` int DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dep_unique` (`task_id`,`depends_on_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `task_dependencies` 
INSERT INTO `task_dependencies` (`id`,`task_id`,`depends_on_task_id`,`type`,`lag_days`,`created_at`) VALUES ('1','2','1','FS','0','2026-09-27 19:15:19');
INSERT INTO `task_dependencies` (`id`,`task_id`,`depends_on_task_id`,`type`,`lag_days`,`created_at`) VALUES ('2','4','1','FS','0','2026-09-27 19:15:19');
INSERT INTO `task_dependencies` (`id`,`task_id`,`depends_on_task_id`,`type`,`lag_days`,`created_at`) VALUES ('3','5','4','FS','0','2026-09-27 19:15:19');
INSERT INTO `task_dependencies` (`id`,`task_id`,`depends_on_task_id`,`type`,`lag_days`,`created_at`) VALUES ('4','6','4','FS','0','2026-09-27 19:15:19');
INSERT INTO `task_dependencies` (`id`,`task_id`,`depends_on_task_id`,`type`,`lag_days`,`created_at`) VALUES ('5','10','4','FS','0','2026-09-27 19:15:19');

-- -------------------------------------------------------
-- Table structure for `tasks` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `track_id` int unsigned DEFAULT NULL,
  `phase_id` int unsigned DEFAULT NULL,
  `epic_id` int unsigned DEFAULT NULL,
  `sprint_id` int unsigned DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `type` enum('epic','story','task','bug','spike') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'story',
  `code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `acceptance_criteria` text COLLATE utf8mb4_unicode_ci,
  `definition_of_done` tinyint(1) DEFAULT '0',
  `story_points` tinyint unsigned DEFAULT '3',
  `status` enum('not_started','in_progress','review','completed','blocked','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `priority` enum('critical','high','medium','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `progress` tinyint unsigned DEFAULT '0',
  `assigned_to` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `effort_est` decimal(6,1) DEFAULT NULL COMMENT 'hours',
  `effort_act` decimal(6,1) DEFAULT NULL COMMENT 'hours',
  `tags` json DEFAULT NULL,
  `order_index` int DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `track_id` (`track_id`),
  KEY `phase_id` (`phase_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`),
  KEY `sprint_id` (`sprint_id`),
  KEY `epic_id` (`epic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `tasks` 
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('1','1','1','1','1','1',NULL,'story','STORY-101','Provision EKS Cluster with Terraform IaC','Set up 3-node AWS EKS cluster with VPC peering and NAT gateways.','Must pass terraform plan without security warnings','1','8','completed','high','100','5','1','2026-09-01','2026-09-05','2026-09-05 00:00:00','16.0','14.5',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('2','1','2','1','2','1',NULL,'task','TSK-102','Configure Istio Service Mesh & mTLS','Enable Mutual TLS encryption across all pods in the default namespace.','All inter-service traffic encrypted','1','5','completed','medium','100','3','1','2026-09-06','2026-09-10','2026-09-09 00:00:00','12.0','11.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('3','1','3','1','4','1',NULL,'task','TSK-103','Setup SonarQube & SAST Pipeline Scan','Integrate static application security testing into GitHub Actions.','Code coverage > 80% enforced','1','3','completed','medium','100','7','1','2026-09-11','2026-09-14','2026-09-14 00:00:00','8.0','7.5',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('4','1','2','2','2','2',NULL,'story','STORY-201','Implement OAuth2 / JWT Authentication Gateway','Build high-performance OAuth token validation middleware with redis caching.','Token validation latency < 5ms','1','8','in_progress','critical','75','6','2','2026-09-16','2026-09-24',NULL,'20.0','15.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('5','1','1','2','1','2',NULL,'story','STORY-202','Deploy Kong API Gateway Routing Rules','Configure ingress routes, rate limiting, and CORS headers for external client APIs.','Supports 10k requests/sec','1','5','in_progress','high','60','3','2','2026-09-18','2026-09-26',NULL,'16.0','10.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('6','1','3','2','4','2',NULL,'bug','BUG-203','Fix Redis Cache Invalidation on Session Revocation','Ensure revoked stakeholder tokens are invalidated instantly across nodes.','Token revocation propagates in < 1s','1','3','review','critical','90','6','2','2026-09-20','2026-09-25',NULL,'8.0','7.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('7','1','4','2','3','2',NULL,'story','STORY-204','Build RACI Matrix & Capacity Heatmap UI','Create interactive governance matrix and developer workload capacity bars.','Supports R, A, C, I role assignments','1','5','completed','high','100','3','2','2026-09-21','2026-09-27','2026-09-27 00:00:00','14.0','13.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('8','1','2','2','2','2',NULL,'spike','SPK-205','Spike: Benchmark gRPC vs REST for Financial Transactions','Conduct latency load testing under 5,000 concurrent virtual users.','Provide latency distribution report','1','2','review','medium','85','3','2','2026-09-22','2026-09-28',NULL,'6.0','5.5',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('9','1','1','2','1','2',NULL,'bug','BUG-206','Resolve AWS DirectConnect Failover Latency Spike','Investigate BGP route flap during primary line failover test.','Failover downtime < 3 seconds','1','5','blocked','critical','30','5','2','2026-09-23','2026-09-29',NULL,'12.0','8.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('10','1','4','3','3','3',NULL,'story','STORY-301','Build Financial EVM Engine (CPI/SPI/RAG)','Calculate Earned Value, Planned Value, Actual Cost, and Schedule Variances.','RAG status dynamically rendered','1','8','not_started','high','0','3','2','2026-10-01','2026-10-08',NULL,'18.0','0.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('11','1','2','3','4','3',NULL,'story','STORY-302','Implement Phase Quality Gate Sign-off Workflow','Create iQMS audit trail for Phase Gate approvals and PDF export.','PM and VP approval controls enforced','1','5','not_started','high','0','2','2','2026-10-03','2026-10-11',NULL,'12.0','0.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');
INSERT INTO `tasks` (`id`,`program_id`,`track_id`,`phase_id`,`epic_id`,`sprint_id`,`parent_id`,`type`,`code`,`title`,`description`,`acceptance_criteria`,`definition_of_done`,`story_points`,`status`,`priority`,`progress`,`assigned_to`,`created_by`,`start_date`,`due_date`,`completed_at`,`effort_est`,`effort_act`,`tags`,`order_index`,`created_at`,`updated_at`) VALUES ('12','1','3','3','4','3',NULL,'task','TSK-303','Automate Playwright E2E Regression Test Suite','Script automated end-to-end tests for login, task creation, and sprint board.','Clean execution on Chromium & WebKit','1','5','not_started','medium','0','7','2','2026-10-05','2026-10-14',NULL,'14.0','0.0',NULL,'0','2026-09-27 17:46:48','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `timesheets` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `timesheets`;
CREATE TABLE `timesheets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `program_id` int unsigned NOT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `work_date` date NOT NULL,
  `hours` decimal(4,2) NOT NULL DEFAULT '0.00',
  `billable` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approved_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_date` (`user_id`,`work_date`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `timesheets` 
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('1','3','1','4','2026-09-21','8.00','1','OAuth2 JWT Auth Gateway development and testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('2','5','1','5','2026-09-21','7.50','1','Terraform EKS cluster deployment & mesh config','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('3','6','1','6','2026-09-21','8.00','1','Redis Cache Invalidation bug fix & unit testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('4','3','1','4','2026-09-22','8.00','1','OAuth2 JWT Auth Gateway development and testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('5','5','1','5','2026-09-22','7.50','1','Terraform EKS cluster deployment & mesh config','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('6','6','1','6','2026-09-22','8.00','1','Redis Cache Invalidation bug fix & unit testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('7','3','1','4','2026-09-23','8.00','1','OAuth2 JWT Auth Gateway development and testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('8','5','1','5','2026-09-23','7.50','1','Terraform EKS cluster deployment & mesh config','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('9','6','1','6','2026-09-23','8.00','1','Redis Cache Invalidation bug fix & unit testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('10','3','1','4','2026-09-24','8.00','1','OAuth2 JWT Auth Gateway development and testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('11','5','1','5','2026-09-24','7.50','1','Terraform EKS cluster deployment & mesh config','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('12','6','1','6','2026-09-24','8.00','1','Redis Cache Invalidation bug fix & unit testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('13','3','1','4','2026-09-25','8.00','1','OAuth2 JWT Auth Gateway development and testing','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('14','5','1','5','2026-09-25','7.50','1','Terraform EKS cluster deployment & mesh config','approved','2','2026-09-27 19:15:19');
INSERT INTO `timesheets` (`id`,`user_id`,`program_id`,`task_id`,`work_date`,`hours`,`billable`,`description`,`status`,`approved_by`,`created_at`) VALUES ('15','6','1','6','2026-09-25','8.00','1','Redis Cache Invalidation bug fix & unit testing','approved','2','2026-09-27 19:15:19');

-- -------------------------------------------------------
-- Table structure for `tracks` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#6366f1',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'bi-layers',
  `order_index` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `tracks` 
INSERT INTO `tracks` (`id`,`program_id`,`name`,`description`,`color`,`icon`,`order_index`,`is_active`,`created_at`) VALUES ('1','1','Engineering & Cloud DevOps','Kubernetes, Terraform, Microservices','#6366f1','bi-cpu','1','1','2026-09-27 17:46:48');
INSERT INTO `tracks` (`id`,`program_id`,`name`,`description`,`color`,`icon`,`order_index`,`is_active`,`created_at`) VALUES ('2','1','Security & Compliance (iQMS)','OWASP Top 10, ISO27001, SonarQube','#ef4444','bi-shield-check','2','1','2026-09-27 17:46:48');
INSERT INTO `tracks` (`id`,`program_id`,`name`,`description`,`color`,`icon`,`order_index`,`is_active`,`created_at`) VALUES ('3','1','QA & Test Automation','Playwright, JUnit, Performance Testing','#06b6d4','bi-check-all','3','1','2026-09-27 17:46:48');
INSERT INTO `tracks` (`id`,`program_id`,`name`,`description`,`color`,`icon`,`order_index`,`is_active`,`created_at`) VALUES ('4','1','Business Architecture','Domain Driven Design & Event Schemas','#22c55e','bi-diagram-3','4','1','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `updates` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `updates`;
CREATE TABLE `updates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int unsigned NOT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress` tinyint unsigned DEFAULT NULL,
  `status_from` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_to` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `updates` 
INSERT INTO `updates` (`id`,`program_id`,`task_id`,`user_id`,`body`,`progress`,`status_from`,`status_to`,`is_pinned`,`created_at`) VALUES ('4','1','4','6','Completed OAuth2 JWT token verification middleware with Redis cache. Benchmarked token verification latency at 2.8ms under 2,000 req/sec.','75','in_progress','in_progress','1','2026-09-27 17:46:48');
INSERT INTO `updates` (`id`,`program_id`,`task_id`,`user_id`,`body`,`progress`,`status_from`,`status_to`,`is_pinned`,`created_at`) VALUES ('5','1','7','3','Deployed RACI Matrix UI and Developer Capacity Heatmap in Planford. Passed internal code review.','100','review','completed','1','2026-09-27 17:46:48');
INSERT INTO `updates` (`id`,`program_id`,`task_id`,`user_id`,`body`,`progress`,`status_from`,`status_to`,`is_pinned`,`created_at`) VALUES ('6','1','9','5','Investigating AWS DirectConnect route flap during failover test with AWS Enterprise Support. Issue logged in RIDA.','30','in_progress','blocked','1','2026-09-27 17:46:48');

-- -------------------------------------------------------
-- Table structure for `users` 
-- -------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','org_admin','pm','member','stakeholder','viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `avatar` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `timezone` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT 'Asia/Kolkata',
  `settings` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_org` (`email`,`org_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `users` 
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('1','1','Super Admin','admin@planford.io','$2y$10$m0TQmogrby5vj0J356y1Ve4KemFBUPQAp56zQwDkMlHQJVj4NTY5O','super_admin',NULL,NULL,NULL,'1',NULL,'Asia/Kolkata',NULL,'2026-09-27 11:17:20');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('2','1','Program Manager','pm@planford.io','$2y$10$m0TQmogrby5vj0J356y1Ve4KemFBUPQAp56zQwDkMlHQJVj4NTY5O','pm',NULL,NULL,NULL,'1','2026-09-27 17:09:00','Asia/Kolkata',NULL,'2026-09-27 11:17:20');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('3','1','Team Member','member@planford.io','$2y$10$m0TQmogrby5vj0J356y1Ve4KemFBUPQAp56zQwDkMlHQJVj4NTY5O','member',NULL,NULL,NULL,'1','2026-09-27 17:04:25','Asia/Kolkata',NULL,'2026-09-27 11:17:20');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('4','1','Stakeholder','stakeholder@planford.io','$2y$10$m0TQmogrby5vj0J356y1Ve4KemFBUPQAp56zQwDkMlHQJVj4NTY5O','stakeholder',NULL,NULL,NULL,'1',NULL,'Asia/Kolkata',NULL,'2026-09-27 11:17:20');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('5','1','Rajesh Sharma','dev1@planford.io','$2y$10$3E6zxFWLtjq3I6D/xTlDIO5saWt0ocX4XMdegkH6pK0cEAy8bDfq6','member',NULL,'Senior DevOps Lead','Infrastructure','1',NULL,'Asia/Kolkata',NULL,'2026-09-27 17:39:34');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('6','1','Priya Nair','dev2@planford.io','$2y$10$3E6zxFWLtjq3I6D/xTlDIO5saWt0ocX4XMdegkH6pK0cEAy8bDfq6','member',NULL,'Senior Backend Engineer','Engineering','1',NULL,'Asia/Kolkata',NULL,'2026-09-27 17:39:34');
INSERT INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`,`avatar`,`title`,`department`,`is_active`,`last_login`,`timezone`,`settings`,`created_at`) VALUES ('7','1','Amit Verma','qa1@planford.io','$2y$10$3E6zxFWLtjq3I6D/xTlDIO5saWt0ocX4XMdegkH6pK0cEAy8bDfq6','member',NULL,'Lead QA Automation Engineer','Quality Assurance','1',NULL,'Asia/Kolkata',NULL,'2026-09-27 17:39:34');

SET foreign_key_checks = 1;
