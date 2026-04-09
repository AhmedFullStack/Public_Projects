-- ============================================================
-- ElectroMech Portfolio - Database Schema
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Version: 1.0.0
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- ============================================================
-- TABLE: settings
-- Global site configuration, SEO, social links
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `key`                 VARCHAR(100)    NOT NULL,
  `value`               TEXT            NULL,
  `group`               VARCHAR(50)     NOT NULL DEFAULT 'general',
  `is_translatable`     TINYINT(1)      NOT NULL DEFAULT 0,
  `updated_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`),
  INDEX `idx_settings_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings_translations
-- Bilingual values for translatable settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings_translations` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `setting_id`  INT UNSIGNED    NOT NULL,
  `locale`      VARCHAR(5)      NOT NULL,
  `value`       TEXT            NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_locale` (`setting_id`, `locale`),
  CONSTRAINT `fk_st_setting` FOREIGN KEY (`setting_id`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admins
-- Admin users with secure credential storage
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(100)    NOT NULL,
  `email`             VARCHAR(191)    NOT NULL,
  `password_hash`     VARCHAR(255)    NOT NULL,
  `role`              ENUM('superadmin','editor') NOT NULL DEFAULT 'editor',
  `avatar`            VARCHAR(255)    NULL,
  `last_login_at`     TIMESTAMP       NULL,
  `last_login_ip`     VARCHAR(45)     NULL,
  `failed_attempts`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`      TIMESTAMP       NULL,
  `remember_token`    VARCHAR(100)    NULL,
  `token_expires_at`  TIMESTAMP       NULL,
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`),
  INDEX `idx_admins_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admin_sessions
-- Secure session tracking per admin
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id`    INT UNSIGNED    NOT NULL,
  `token_hash`  VARCHAR(255)    NOT NULL,
  `ip_address`  VARCHAR(45)     NOT NULL,
  `user_agent`  VARCHAR(255)    NULL,
  `expires_at`  TIMESTAMP       NOT NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sessions_token` (`token_hash`(32)),
  INDEX `idx_sessions_admin` (`admin_id`),
  CONSTRAINT `fk_sess_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories
-- Project categories (bilingual)
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `slug`      VARCHAR(100)    NOT NULL,
  `icon`      VARCHAR(50)     NULL,
  `color`     VARCHAR(7)      NULL DEFAULT '#4a9eff',
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  INDEX `idx_categories_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories_translations
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories_translations` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED  NOT NULL,
  `locale`      VARCHAR(5)    NOT NULL,
  `name`        VARCHAR(150)  NOT NULL,
  `description` TEXT          NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_locale` (`category_id`, `locale`),
  CONSTRAINT `fk_ct_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: projects
-- Portfolio projects
-- ============================================================
CREATE TABLE IF NOT EXISTS `projects` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `category_id`     INT UNSIGNED    NULL,
  `slug`            VARCHAR(200)    NOT NULL,
  `featured_image`  VARCHAR(255)    NULL,
  `client`          VARCHAR(150)    NULL,
  `project_url`     VARCHAR(255)    NULL,
  `year`            YEAR            NULL,
  `duration`        VARCHAR(50)     NULL,
  `is_featured`     TINYINT(1)      NOT NULL DEFAULT 0,
  `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
  `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `views_count`     INT UNSIGNED    NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug` (`slug`),
  INDEX `idx_projects_cat` (`category_id`),
  INDEX `idx_projects_active` (`is_active`, `sort_order`),
  INDEX `idx_projects_featured` (`is_featured`, `is_active`),
  CONSTRAINT `fk_proj_cat` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: projects_translations
-- ============================================================
CREATE TABLE IF NOT EXISTS `projects_translations` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `project_id`      INT UNSIGNED  NOT NULL,
  `locale`          VARCHAR(5)    NOT NULL,
  `title`           VARCHAR(255)  NOT NULL,
  `summary`         VARCHAR(500)  NULL,
  `description`     LONGTEXT      NULL,
  `technologies`    TEXT          NULL COMMENT 'JSON array of tech tags',
  `challenges`      TEXT          NULL,
  `results`         TEXT          NULL,
  `meta_title`      VARCHAR(70)   NULL,
  `meta_description` VARCHAR(165) NULL,
  `meta_keywords`   VARCHAR(255)  NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pt_locale` (`project_id`, `locale`),
  FULLTEXT KEY `ft_proj_search` (`title`, `summary`, `description`),
  CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: project_images
-- Gallery images per project
-- ============================================================
CREATE TABLE IF NOT EXISTS `project_images` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED    NOT NULL,
  `filename`    VARCHAR(255)    NOT NULL,
  `alt_ar`      VARCHAR(255)    NULL,
  `alt_en`      VARCHAR(255)    NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pi_project` (`project_id`, `sort_order`),
  CONSTRAINT `fk_pi_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: skill_groups
-- Skill categories (e.g. PLC, Mechanical, Software)
-- ============================================================
CREATE TABLE IF NOT EXISTS `skill_groups` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100)    NOT NULL,
  `icon`        VARCHAR(50)     NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sg_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skill_groups_translations` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `group_id`      INT UNSIGNED  NOT NULL,
  `locale`        VARCHAR(5)    NOT NULL,
  `name`          VARCHAR(100)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sgt_locale` (`group_id`, `locale`),
  CONSTRAINT `fk_sgt_group` FOREIGN KEY (`group_id`) REFERENCES `skill_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: skills
-- ============================================================
CREATE TABLE IF NOT EXISTS `skills` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `group_id`    INT UNSIGNED    NULL,
  `slug`        VARCHAR(100)    NOT NULL,
  `proficiency` TINYINT UNSIGNED NOT NULL DEFAULT 80 COMMENT '0-100',
  `icon`        VARCHAR(50)     NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_skills_slug` (`slug`),
  INDEX `idx_skills_group` (`group_id`, `sort_order`),
  CONSTRAINT `fk_sk_group` FOREIGN KEY (`group_id`) REFERENCES `skill_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skills_translations` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `skill_id`  INT UNSIGNED  NOT NULL,
  `locale`    VARCHAR(5)    NOT NULL,
  `name`      VARCHAR(100)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_skt_locale` (`skill_id`, `locale`),
  CONSTRAINT `fk_skt_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: experiences
-- Work experience / career timeline
-- ============================================================
CREATE TABLE IF NOT EXISTS `experiences` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `start_date`  DATE            NOT NULL,
  `end_date`    DATE            NULL COMMENT 'NULL = current',
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_exp_dates` (`start_date` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `experiences_translations` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `experience_id` INT UNSIGNED  NOT NULL,
  `locale`        VARCHAR(5)    NOT NULL,
  `job_title`     VARCHAR(150)  NOT NULL,
  `company`       VARCHAR(150)  NOT NULL,
  `location`      VARCHAR(150)  NULL,
  `description`   TEXT          NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ext_locale` (`experience_id`, `locale`),
  CONSTRAINT `fk_ext_exp` FOREIGN KEY (`experience_id`) REFERENCES `experiences`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: cv_files
-- Uploaded CV documents
-- ============================================================
CREATE TABLE IF NOT EXISTS `cv_files` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `locale`      VARCHAR(5)      NOT NULL DEFAULT 'ar',
  `filename`    VARCHAR(255)    NOT NULL,
  `original_name` VARCHAR(255)  NOT NULL,
  `file_size`   INT UNSIGNED    NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cv_active_locale` (`is_active`, `locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: messages
-- Contact form submissions
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `email`       VARCHAR(191)    NOT NULL,
  `phone`       VARCHAR(30)     NULL,
  `subject`     VARCHAR(255)    NOT NULL,
  `body`        TEXT            NOT NULL,
  `ip_address`  VARCHAR(45)     NOT NULL,
  `user_agent`  VARCHAR(255)    NULL,
  `locale`      VARCHAR(5)      NOT NULL DEFAULT 'ar',
  `status`      ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `is_spam`     TINYINT(1)      NOT NULL DEFAULT 0,
  `admin_notes` TEXT            NULL,
  `replied_at`  TIMESTAMP       NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_msg_status` (`status`, `is_spam`, `created_at` DESC),
  INDEX `idx_msg_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: rate_limits
-- OWASP rate limiting for forms/login
-- ============================================================
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier`  VARCHAR(100)    NOT NULL COMMENT 'IP or email hash',
  `action`      VARCHAR(50)     NOT NULL,
  `attempts`    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `window_start` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked_until` TIMESTAMP     NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rl_identifier_action` (`identifier`, `action`),
  INDEX `idx_rl_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- Admin action audit trail
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id`    INT UNSIGNED    NULL,
  `action`      VARCHAR(100)    NOT NULL,
  `entity_type` VARCHAR(50)     NULL,
  `entity_id`   INT UNSIGNED    NULL,
  `old_values`  JSON            NULL,
  `new_values`  JSON            NULL,
  `ip_address`  VARCHAR(45)     NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_al_admin` (`admin_id`),
  INDEX `idx_al_entity` (`entity_type`, `entity_id`),
  INDEX `idx_al_created` (`created_at` DESC),
  CONSTRAINT `fk_al_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: page_seo
-- Per-page SEO metadata (bilingual)
-- ============================================================
CREATE TABLE IF NOT EXISTS `page_seo` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `page_key`    VARCHAR(100)  NOT NULL,
  `locale`      VARCHAR(5)    NOT NULL,
  `title`       VARCHAR(70)   NULL,
  `description` VARCHAR(165)  NULL,
  `keywords`    VARCHAR(255)  NULL,
  `og_image`    VARCHAR(255)  NULL,
  `schema_json` JSON          NULL COMMENT 'Structured data JSON-LD',
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ps_page_locale` (`page_key`, `locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED: Default settings
-- ============================================================
INSERT INTO `settings` (`key`, `value`, `group`, `is_translatable`) VALUES
('site_name',           'Portfolio',        'general', 0),
('base_url',            '',                 'general', 0),
('default_locale',      'ar',               'general', 0),
('available_locales',   'ar,en',            'general', 0),
('owner_name',          '',                 'general', 1),
('owner_title',         '',                 'general', 1),
('owner_email',         '',                 'general', 0),
('owner_phone',         '',                 'general', 0),
('linkedin_url',        '',                 'social',  0),
('github_url',          '',                 'social',  0),
('whatsapp_number',     '',                 'social',  0),
('years_experience',    '8',                'stats',   0),
('projects_count',      '30',               'stats',   0),
('clients_count',       '15',               'stats',   0),
('google_analytics_id', '',                 'seo',     0),
('google_adsense_id',   '',                 'seo',     0),
('robots_txt',          "User-agent: *\nAllow: /", 'seo', 0),
('maintenance_mode',    '0',                'general', 0);

-- SEED: Default admin (password: Admin@2024 — CHANGE IMMEDIATELY)
INSERT INTO `admins` (`name`, `email`, `password_hash`, `role`) VALUES
('Admin', 'admin@example.com', '$2y$12$placeholder_change_on_install', 'superadmin');