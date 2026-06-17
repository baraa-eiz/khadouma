-- ============================================================
-- KHADOMEH DATABASE MIGRATION (Stage 3.8 User Portal Foundation)
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `public_id` VARCHAR(36) NOT NULL UNIQUE,
  `display_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NULL UNIQUE,
  `phone` VARCHAR(30) NULL UNIQUE,
  `avatar` VARCHAR(255) NULL,
  `city_id` INT NULL,
  `area_id` INT NULL,
  `default_address` TEXT NULL,
  `preferred_contact_method` VARCHAR(50) NOT NULL DEFAULT 'phone',
  `preferred_language` VARCHAR(10) NULL DEFAULT 'ar',
  `timezone` VARCHAR(100) NULL DEFAULT 'Asia/Damascus',
  `marketing_opt_in` TINYINT(1) NULL DEFAULT 0,
  `notification_preferences` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  INDEX `idx_user_public_id` (`public_id`),
  INDEX `idx_user_email` (`email`),
  INDEX `idx_user_phone` (`phone`),
  INDEX `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL, -- 'provider', 'service', 'area'
  `entity_public_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_user_favorite_entity` (`user_id`, `entity_type`, `entity_public_id`),
  INDEX `idx_favorite_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
