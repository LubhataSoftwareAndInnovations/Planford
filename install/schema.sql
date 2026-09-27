-- ============================================================
-- Planford v0.1 — by Lubhata
-- Enterprise Program Management Platform
-- Database Schema
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- -------------------------------------------------------
-- Organizations (multi-tenant root)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organizations` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150) NOT NULL,
  `slug`        VARCHAR(100) NOT NULL UNIQUE,
  `logo`        VARCHAR(300) DEFAULT NULL,
  `industry`    VARCHAR(100) DEFAULT NULL,
  `plan`        ENUM('community','cloud','enterprise') NOT NULL DEFAULT 'community',
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `settings`    JSON DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id`        INT UNSIGNED NOT NULL,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(150) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `role`          ENUM('super_admin','org_admin','pm','member','stakeholder','viewer') NOT NULL DEFAULT 'member',
  `avatar`        VARCHAR(300) DEFAULT NULL,
  `title`         VARCHAR(100) DEFAULT NULL,
  `department`    VARCHAR(100) DEFAULT NULL,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `last_login`    DATETIME DEFAULT NULL,
  `timezone`      VARCHAR(60) DEFAULT 'Asia/Kolkata',
  `settings`      JSON DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_org` (`email`, `org_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Programs (top-level container — replaces "project" in SAP)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `programs` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id`         INT UNSIGNED NOT NULL,
  `code`           VARCHAR(30) NOT NULL,
  `name`           VARCHAR(200) NOT NULL,
  `description`    TEXT DEFAULT NULL,
  `methodology`    ENUM('scrum','kanban','waterfall','hybrid') NOT NULL DEFAULT 'scrum',
  `rag_status`     ENUM('green','amber','red') NOT NULL DEFAULT 'green',
  `status`         ENUM('planning','active','on_hold','completed','cancelled') NOT NULL DEFAULT 'planning',
  `priority`       ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `start_date`     DATE DEFAULT NULL,
  `end_date`       DATE DEFAULT NULL,
  `budget`         DECIMAL(15,2) DEFAULT NULL,
  `currency`       VARCHAR(5) DEFAULT 'INR',
  `owner_id`       INT UNSIGNED DEFAULT NULL,
  `client_name`    VARCHAR(150) DEFAULT NULL,
  `client_logo`    VARCHAR(300) DEFAULT NULL,
  `cover_color`    VARCHAR(20) DEFAULT '#6366f1',
  `is_public`      TINYINT(1) DEFAULT 0,
  `stakeholder_token` VARCHAR(64) DEFAULT NULL UNIQUE,
  `settings`       JSON DEFAULT NULL,
  `created_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `org_id` (`org_id`),
  KEY `owner_id` (`owner_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Program Members (who's on this program + their role)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `program_members` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `role`       ENUM('pm','member','viewer','stakeholder') NOT NULL DEFAULT 'member',
  `joined_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prog_user` (`program_id`, `user_id`),
  KEY `program_id` (`program_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Epics (Feature level groupings for Scrum / Agile)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `epics` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`  INT UNSIGNED NOT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `summary`     TEXT DEFAULT NULL,
  `color`       VARCHAR(20) DEFAULT '#6366f1',
  `owner_id`    INT UNSIGNED DEFAULT NULL,
  `status`      ENUM('backlog','in_progress','done') NOT NULL DEFAULT 'backlog',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Sprints (Time-boxed iterations for Scrum)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sprints` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`       INT UNSIGNED NOT NULL,
  `name`             VARCHAR(100) NOT NULL,
  `goal`             TEXT DEFAULT NULL,
  `start_date`       DATE DEFAULT NULL,
  `end_date`         DATE DEFAULT NULL,
  `status`           ENUM('planning','active','completed','closed') NOT NULL DEFAULT 'planning',
  `capacity_hours`   DECIMAL(8,1) DEFAULT 80.0,
  `velocity_points`  INT UNSIGNED DEFAULT 0,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tracks (Engineering, IT, Business, Compliance, Custom)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tracks` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`  INT UNSIGNED NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `color`       VARCHAR(20) DEFAULT '#6366f1',
  `icon`        VARCHAR(50) DEFAULT 'bi-layers',
  `order_index` INT DEFAULT 0,
  `is_active`   TINYINT(1) DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Phases (time-bound stages within a program)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `phases` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`  INT UNSIGNED NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `order_index` INT DEFAULT 0,
  `start_date`  DATE DEFAULT NULL,
  `end_date`    DATE DEFAULT NULL,
  `status`      ENUM('planning','active','completed','cancelled') NOT NULL DEFAULT 'planning',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Phase Quality Gates (MNC Quality Sign-offs)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `phase_gates` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`    INT UNSIGNED NOT NULL,
  `phase_id`      INT UNSIGNED NOT NULL,
  `gate_name`     VARCHAR(150) NOT NULL,
  `criteria_json` JSON DEFAULT NULL,
  `status`        ENUM('pending','passed','failed','waived') NOT NULL DEFAULT 'pending',
  `signed_off_by` INT UNSIGNED DEFAULT NULL,
  `signed_off_at` DATETIME DEFAULT NULL,
  `comments`      TEXT DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_phase` (`program_id`, `phase_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tasks (core work unit)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`          INT UNSIGNED NOT NULL,
  `track_id`            INT UNSIGNED DEFAULT NULL,
  `phase_id`            INT UNSIGNED DEFAULT NULL,
  `epic_id`             INT UNSIGNED DEFAULT NULL,
  `sprint_id`           INT UNSIGNED DEFAULT NULL,
  `parent_id`           INT UNSIGNED DEFAULT NULL,
  `type`                ENUM('epic','story','task','bug','spike') NOT NULL DEFAULT 'story',
  `code`                VARCHAR(30) DEFAULT NULL,
  `title`               VARCHAR(300) NOT NULL,
  `description`         TEXT DEFAULT NULL,
  `acceptance_criteria` TEXT DEFAULT NULL,
  `definition_of_done`  TINYINT(1) DEFAULT 0,
  `story_points`        TINYINT UNSIGNED DEFAULT 3,
  `status`              ENUM('not_started','in_progress','review','completed','blocked','cancelled') NOT NULL DEFAULT 'not_started',
  `priority`            ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `progress`            TINYINT UNSIGNED DEFAULT 0,
  `assigned_to`         INT UNSIGNED DEFAULT NULL,
  `created_by`          INT UNSIGNED DEFAULT NULL,
  `start_date`          DATE DEFAULT NULL,
  `due_date`            DATE DEFAULT NULL,
  `completed_at`        DATETIME DEFAULT NULL,
  `effort_est`          DECIMAL(6,1) DEFAULT NULL COMMENT 'hours',
  `effort_act`          DECIMAL(6,1) DEFAULT NULL COMMENT 'hours',
  `tags`                JSON DEFAULT NULL,
  `order_index`         INT DEFAULT 0,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `sprint_id` (`sprint_id`),
  KEY `epic_id` (`epic_id`),
  KEY `track_id` (`track_id`),
  KEY `phase_id` (`phase_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Task Comments (threaded)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id`    INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `parent_id`  INT UNSIGNED DEFAULT NULL,
  `body`       TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Milestones
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `milestones` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`   INT UNSIGNED NOT NULL,
  `phase_id`     INT UNSIGNED DEFAULT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `due_date`     DATE NOT NULL,
  `achieved_date` DATE DEFAULT NULL,
  `status`       ENUM('pending','achieved','delayed','cancelled') NOT NULL DEFAULT 'pending',
  `is_client_visible` TINYINT(1) DEFAULT 1,
  `created_by`   INT UNSIGNED DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Risks & Issues
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `risks` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`   INT UNSIGNED NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `type`         ENUM('risk','issue','dependency','assumption') NOT NULL DEFAULT 'risk',
  `status`       ENUM('open','monitoring','mitigated','closed') NOT NULL DEFAULT 'open',
  `likelihood`   ENUM('low','medium','high') DEFAULT 'medium',
  `impact`       ENUM('low','medium','high','critical') DEFAULT 'medium',
  `owner_id`     INT UNSIGNED DEFAULT NULL,
  `mitigation`   TEXT DEFAULT NULL,
  `due_date`     DATE DEFAULT NULL,
  `created_by`   INT UNSIGNED DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Documents (version-controlled)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id`       INT UNSIGNED NOT NULL,
  `program_id`   INT UNSIGNED DEFAULT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `category`     VARCHAR(80) DEFAULT 'general',
  `filename`     VARCHAR(300) NOT NULL,
  `origname`     VARCHAR(300) NOT NULL,
  `size`         INT UNSIGNED DEFAULT 0,
  `mime`         VARCHAR(120) DEFAULT NULL,
  `version`      VARCHAR(20) DEFAULT '1.0',
  `status`       ENUM('draft','review','approved','superseded') DEFAULT 'draft',
  `is_client_visible` TINYINT(1) DEFAULT 0,
  `uploaded_by`  INT UNSIGNED NOT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Updates / Progress Log
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `updates` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`  INT UNSIGNED NOT NULL,
  `task_id`     INT UNSIGNED DEFAULT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `body`        TEXT NOT NULL,
  `progress`    TINYINT UNSIGNED DEFAULT NULL,
  `status_from` VARCHAR(30) DEFAULT NULL,
  `status_to`   VARCHAR(30) DEFAULT NULL,
  `is_pinned`   TINYINT(1) DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Notifications
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `body`       TEXT DEFAULT NULL,
  `type`       ENUM('info','success','warning','danger') DEFAULT 'info',
  `link`       VARCHAR(300) DEFAULT NULL,
  `is_read`    TINYINT(1) DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Audit Log (every action, immutable)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `org_id`     INT UNSIGNED DEFAULT NULL,
  `action`     VARCHAR(50) NOT NULL,
  `entity`     VARCHAR(50) NOT NULL,
  `entity_id`  INT UNSIGNED DEFAULT NULL,
  `detail`     TEXT DEFAULT NULL,
  `ip`         VARCHAR(45) DEFAULT NULL,
  `ua`         VARCHAR(200) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `org_action` (`org_id`, `action`),
  KEY `user_id` (`user_id`),
  KEY `entity` (`entity`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Stakeholder Tokens (magic-link portal access)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stakeholder_sessions` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` INT UNSIGNED NOT NULL,
  `token`      VARCHAR(128) NOT NULL UNIQUE,
  `email`      VARCHAR(150) DEFAULT NULL,
  `name`       VARCHAR(120) DEFAULT NULL,
  `last_seen`  DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Change Requests
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `change_requests` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`   INT UNSIGNED NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `impact`       TEXT DEFAULT NULL,
  `status`       ENUM('draft','submitted','approved','rejected','implemented') DEFAULT 'draft',
  `priority`     ENUM('critical','high','medium','low') DEFAULT 'medium',
  `requested_by` INT UNSIGNED DEFAULT NULL,
  `reviewed_by`  INT UNSIGNED DEFAULT NULL,
  `reviewed_at`  DATETIME DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
-- -------------------------------------------------------
-- RACI Governance Matrix (Responsible, Accountable, Consulted, Informed)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `program_raci` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` INT UNSIGNED NOT NULL,
  `task_id`    INT UNSIGNED DEFAULT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `raci_role`  ENUM('R','A','C','I') NOT NULL DEFAULT 'R',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_task` (`program_id`, `task_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Resource Allocations & Capacity Planner
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resource_allocations` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`     INT UNSIGNED NOT NULL,
  `user_id`        INT UNSIGNED NOT NULL,
  `allocation_pct` TINYINT UNSIGNED NOT NULL DEFAULT 100,
  `role_name`      VARCHAR(100) DEFAULT 'Developer',
  `start_date`     DATE DEFAULT NULL,
  `end_date`       DATE DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_user` (`program_id`, `user_id`)
-- -------------------------------------------------------
-- Task Dependencies (Gantt FS, SS, FF, SF links)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_dependencies` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id`            INT UNSIGNED NOT NULL,
  `depends_on_task_id` INT UNSIGNED NOT NULL,
  `type`               ENUM('FS','SS','FF','SF') NOT NULL DEFAULT 'FS',
  `lag_days`           INT DEFAULT 0,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dep_unique` (`task_id`, `depends_on_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Timesheets & Worklog Matrix
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `timesheets` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `program_id`  INT UNSIGNED NOT NULL,
  `task_id`     INT UNSIGNED DEFAULT NULL,
  `work_date`   DATE NOT NULL,
  `hours`       DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `billable`    TINYINT(1) NOT NULL DEFAULT 1,
  `description` VARCHAR(300) DEFAULT NULL,
  `status`      ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_date` (`user_id`, `work_date`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Dynamic Custom Field Engine
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `custom_fields` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type`  ENUM('program','task') NOT NULL DEFAULT 'task',
  `field_name`   VARCHAR(60) NOT NULL,
  `field_label`  VARCHAR(100) NOT NULL,
  `field_type`   ENUM('text','number','select','date') NOT NULL DEFAULT 'text',
  `options_json` JSON DEFAULT NULL,
  `is_required`  TINYINT(1) DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_field` (`entity_type`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `custom_field_values` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id`    INT UNSIGNED NOT NULL,
  `entity_id`   INT UNSIGNED NOT NULL,
  `value_text`  TEXT DEFAULT NULL,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_entity` (`field_id`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Automation & Escalation Rules Engine
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `automation_rules` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`    INT UNSIGNED NOT NULL,
  `name`          VARCHAR(150) NOT NULL,
  `trigger_event` ENUM('task_blocked','task_overdue','gate_failed','budget_exceeded') NOT NULL,
  `action_type`   ENUM('notify_pm','escalate_priority','change_status','audit_log') NOT NULL,
  `is_active`     TINYINT(1) DEFAULT 1,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_trigger` (`program_id`, `trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Strategic Portfolio Objectives & OKRs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio_okrs` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`    INT UNSIGNED NOT NULL,
  `title`         VARCHAR(200) NOT NULL,
  `category`      ENUM('Strategic','Operational','Quality','Financial') NOT NULL DEFAULT 'Strategic',
  `target_value`  DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  `current_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `unit`          VARCHAR(30) DEFAULT '%',
  `due_date`      DATE DEFAULT NULL,
  `status`        ENUM('on_track','at_risk','behind','achieved') NOT NULL DEFAULT 'on_track',
  `owner_id`      INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- CAPEX / OPEX Financial Accounting & Capitalization Logs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `capex_opex_logs` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id`            INT UNSIGNED NOT NULL,
  `category`              ENUM('capex','opex') NOT NULL DEFAULT 'capex',
  `item_name`             VARCHAR(200) NOT NULL,
  `amount`                DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fiscal_quarter`        VARCHAR(10) DEFAULT 'Q3-2026',
  `capitalization_status` ENUM('eligible','expensed','under_review') NOT NULL DEFAULT 'eligible',
  `logged_by`             INT UNSIGNED DEFAULT NULL,
  `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `program_cat` (`program_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- -------------------------------------------------------
-- Default Super Admin + Sample Org
-- -------------------------------------------------------
INSERT IGNORE INTO `organizations` (`id`,`name`,`slug`,`industry`,`plan`) VALUES
(1,'Lubhata Demo','lubhata-demo','Technology','enterprise');

-- password: Admin@123
INSERT IGNORE INTO `users` (`id`,`org_id`,`name`,`email`,`password`,`role`) VALUES
(1,1,'Super Admin','admin@planford.io','$2y$10$ZYYyEkpgQGfvhnEp1jPRXuhmVemFNrWq1uu3WhmxXcREkVgtg74N.','super_admin'),
(2,1,'Program Manager','pm@planford.io','$2y$10$ZYYyEkpgQGfvhnEp1jPRXuhmVemFNrWq1uu3WhmxXcREkVgtg74N.','pm'),
(3,1,'Team Member','member@planford.io','$2y$10$ZYYyEkpgQGfvhnEp1jPRXuhmVemFNrWq1uu3WhmxXcREkVgtg74N.','member'),
(4,1,'Stakeholder','stakeholder@planford.io','$2y$10$ZYYyEkpgQGfvhnEp1jPRXuhmVemFNrWq1uu3WhmxXcREkVgtg74N.','stakeholder');
