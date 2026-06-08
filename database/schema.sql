-- ============================================================
-- KHADOMEH DATABASE SCHEMA (Stage 1 Foundation)
-- Database Engine: MySQL / MariaDB
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `schema_versions`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `areas`;
DROP TABLE IF EXISTS `providers`;
DROP TABLE IF EXISTS `provider_service_map`;
DROP TABLE IF EXISTS `provider_area_map`;
DROP TABLE IF EXISTS `provider_images`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `contact_events`;
DROP TABLE IF EXISTS `static_pages`;
DROP TABLE IF EXISTS `faq_entries`;
DROP TABLE IF EXISTS `trust_badges`;
DROP TABLE IF EXISTS `provider_trust_badge_map`;
DROP TABLE IF EXISTS `seo_metadata`;
DROP TABLE IF EXISTS `audit_logs`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 1. SCHEMA VERSION TRACKING
-- ------------------------------------------------------------
CREATE TABLE `schema_versions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration_name` VARCHAR(255) NOT NULL UNIQUE,
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. ADMIN USERS
-- ------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin', -- 'superadmin', 'admin', 'moderator'
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admin_email` (`email`),
  INDEX `idx_admin_status` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. WEBSITE SETTINGS
-- ------------------------------------------------------------
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_type` VARCHAR(50) NOT NULL DEFAULT 'text', -- 'text', 'textarea', 'boolean', 'number'
  `label_ar` VARCHAR(255) NOT NULL,
  `description_ar` TEXT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. SERVICES (E.g. Cleaning, Plumbing, Electricity)
-- ------------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `public_id` VARCHAR(36) NOT NULL UNIQUE,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `display_name_ar` VARCHAR(150) NOT NULL,
  `short_name_ar` VARCHAR(50) NOT NULL,
  `description_ar` TEXT NULL,
  `icon` VARCHAR(100) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `meta_title_ar` VARCHAR(255) NULL,
  `meta_description_ar` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_service_slug` (`slug`),
  INDEX `idx_service_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. CITIES (E.g. Damascus, Aleppo, Homs)
-- ------------------------------------------------------------
CREATE TABLE `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE, -- E.g. 'damascus'
  `slug` VARCHAR(100) NOT NULL UNIQUE, -- E.g. 'damascus'
  `display_name_ar` VARCHAR(150) NOT NULL, -- E.g. 'دمشق'
  `governorate_ar` VARCHAR(100) NULL, -- E.g. 'دمشق'
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_city_slug` (`slug`),
  INDEX `idx_city_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. AREAS (Sub-divisions inside Cities, e.g. Mezzeh, Malki)
-- ------------------------------------------------------------
CREATE TABLE `areas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `city_id` INT NOT NULL,
  `key_name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `display_name_ar` VARCHAR(150) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  INDEX `idx_area_slug` (`slug`),
  INDEX `idx_area_city` (`city_id`),
  INDEX `idx_area_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. PROVIDERS (Local service providers, e.g. Abu Ahmad Plumber)
-- ------------------------------------------------------------
CREATE TABLE `providers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `display_name_ar` VARCHAR(200) NOT NULL,
  `normalized_name` VARCHAR(200) NOT NULL, -- Name normalized without diacritics for searching
  `business_type` VARCHAR(50) NOT NULL DEFAULT 'individual', -- 'individual', 'company'
  `phone` VARCHAR(30) NOT NULL,
  `whatsapp` VARCHAR(30) NULL,
  `email` VARCHAR(150) NULL,
  `city_id` INT NOT NULL,
  `primary_service_id` INT NOT NULL,
  `short_description_ar` VARCHAR(255) NULL,
  `description_ar` TEXT NULL,
  `years_experience` INT NOT NULL DEFAULT 0,
  `starting_price` DECIMAL(10,2) NULL,
  `price_unit` VARCHAR(50) DEFAULT 'hour', -- 'hour', 'job', 'day'
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `reviews_count` INT NOT NULL DEFAULT 0,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  `phone_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `identity_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `has_work_photos` TINYINT(1) NOT NULL DEFAULT 0,
  `available_today` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_weight` INT NOT NULL DEFAULT 0, -- Higher weight means shows up higher in lists
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected', 'suspended'
  `notes_internal` TEXT NULL, -- Internal notes visible to admins only
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  FOREIGN KEY (`primary_service_id`) REFERENCES `services` (`id`),
  INDEX `idx_provider_slug` (`slug`),
  INDEX `idx_provider_city` (`city_id`),
  INDEX `idx_provider_service` (`primary_service_id`),
  INDEX `idx_provider_search` (`is_active`, `status`, `sort_weight`),
  INDEX `idx_provider_normalized` (`normalized_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. PROVIDER SERVICE MAP (For providers serving multiple services)
-- ------------------------------------------------------------
CREATE TABLE `provider_service_map` (
  `provider_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  PRIMARY KEY (`provider_id`, `service_id`),
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. PROVIDER AREA MAP (Specific areas served by a provider)
-- ------------------------------------------------------------
CREATE TABLE `provider_area_map` (
  `provider_id` INT NOT NULL,
  `area_id` INT NOT NULL,
  PRIMARY KEY (`provider_id`, `area_id`),
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. PROVIDER IMAGES (Work photos, profile photos, verified badges)
-- ------------------------------------------------------------
CREATE TABLE `provider_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `provider_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL, -- Relative path in filesystem
  `thumbnail_path` VARCHAR(255) NULL,
  `alt_text_ar` VARCHAR(255) NULL,
  `caption_ar` VARCHAR(255) NULL,
  `image_type` VARCHAR(50) NOT NULL DEFAULT 'work_photo', -- 'profile', 'work_photo', 'verification'
  `source_type` VARCHAR(50) NOT NULL DEFAULT 'uploaded', -- 'uploaded', 'licenced_stock'
  `source_url` VARCHAR(255) NULL, -- For stock image attribution
  `attribution_text` VARCHAR(255) NULL,
  `license_note` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  INDEX `idx_prov_img_active` (`provider_id`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. REVIEWS
-- ------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `provider_id` INT NOT NULL,
  `reviewer_name` VARCHAR(100) NOT NULL,
  `reviewer_phone` VARCHAR(30) NULL,
  `rating` INT NOT NULL, -- 1 to 5 stars
  `service_id` INT NULL, -- The specific service reviewed
  `review_text` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
  `is_approved` TINYINT(1) NOT NULL DEFAULT 0, -- Duplicate representation for fast queries
  `admin_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  INDEX `idx_review_provider` (`provider_id`, `is_approved`),
  INDEX `idx_review_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. REPORTS (Complaints or issue reports against providers)
-- ------------------------------------------------------------
CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `provider_id` INT NOT NULL,
  `reporter_name` VARCHAR(100) NOT NULL,
  `reporter_phone` VARCHAR(30) NOT NULL,
  `reason` VARCHAR(255) NOT NULL, -- Short reason category
  `details` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'open', -- 'open', 'investigating', 'resolved', 'dismissed'
  `admin_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  INDEX `idx_report_provider` (`provider_id`),
  INDEX `idx_report_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. CONTACT EVENTS (Tracks user clicks on Phone/WhatsApp buttons)
-- ------------------------------------------------------------
CREATE TABLE `contact_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `provider_id` INT NOT NULL,
  `service_id` INT NULL,
  `city_id` INT NULL,
  `area_id` INT NULL,
  `method` VARCHAR(50) NOT NULL, -- 'phone_call', 'whatsapp_message'
  `source_page` VARCHAR(150) NULL, -- 'provider_profile', 'search_results'
  `user_ip_hash` CHAR(64) NULL, -- Privacy-safe IP hashing (SHA-256)
  `user_agent_hash` CHAR(64) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  INDEX `idx_contact_prov` (`provider_id`),
  INDEX `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. STATIC PAGES (E.g. About, Terms, Privacy)
-- ------------------------------------------------------------
CREATE TABLE `static_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title_ar` VARCHAR(255) NOT NULL,
  `content_ar` LONGTEXT NOT NULL,
  `meta_title_ar` VARCHAR(255) NULL,
  `meta_description_ar` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_page_slug` (`slug`),
  INDEX `idx_page_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 15. FAQ ENTRIES (Frequently Asked Questions)
-- ------------------------------------------------------------
CREATE TABLE `faq_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_id` INT NULL, -- Optional mapping to service pages
  `city_id` INT NULL, -- Optional mapping to city landing pages
  `area_id` INT NULL, -- Optional mapping to area landing pages
  `question_ar` TEXT NOT NULL,
  `answer_ar` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  INDEX `idx_faq_routing` (`service_id`, `city_id`, `area_id`),
  INDEX `idx_faq_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 16. TRUST BADGES (E.g. Background Checked, Certified, Experienced)
-- ------------------------------------------------------------
CREATE TABLE `trust_badges` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `label_ar` VARCHAR(150) NOT NULL,
  `description_ar` TEXT NULL,
  `icon_key` VARCHAR(100) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_badge_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 17. PROVIDER TRUST BADGE MAP
-- ------------------------------------------------------------
CREATE TABLE `provider_trust_badge_map` (
  `provider_id` INT NOT NULL,
  `trust_badge_id` INT NOT NULL,
  PRIMARY KEY (`provider_id`, `trust_badge_id`),
  FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trust_badge_id`) REFERENCES `trust_badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 18. SEO METADATA (Polymorphic SEO tags for dynamic landing pages)
-- ------------------------------------------------------------
CREATE TABLE `seo_metadata` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `entity_type` VARCHAR(50) NOT NULL, -- 'service', 'city', 'area', 'provider', 'static_page', 'general'
  `entity_id` INT NULL, -- NULL for general pages like Home
  `canonical_url` VARCHAR(255) NULL,
  `meta_title_ar` VARCHAR(255) NOT NULL,
  `meta_description_ar` TEXT NOT NULL,
  `og_title_ar` VARCHAR(255) NULL,
  `og_description_ar` TEXT NULL,
  `og_image_path` VARCHAR(255) NULL,
  `structured_data_json` TEXT NULL, -- JSON-LD schema
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_seo_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 19. AUDIT LOGS (Admin actions logs)
-- ------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_user_id` INT NULL, -- NULL if system action or login failure
  `action` VARCHAR(100) NOT NULL, -- 'login_success', 'login_failed', 'create_provider', etc.
  `entity_type` VARCHAR(50) NULL, -- 'providers', 'reviews', etc.
  `entity_id` INT NULL,
  `old_value_json` TEXT NULL,
  `new_value_json` TEXT NULL,
  `ip_hash` CHAR(64) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  INDEX `idx_audit_admin` (`admin_user_id`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
