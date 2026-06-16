-- Migration 0004: Reviews and Verification Workflow Schema Additions

-- 1. Update reviews table
-- Note: Using CHANGE is safe and backward-compatible on MySQL/MariaDB.
ALTER TABLE `reviews` CHANGE `review_text` `comment` TEXT NOT NULL;
ALTER TABLE `reviews` ADD COLUMN `ip_hash` VARCHAR(64) NULL AFTER `is_approved`;
ALTER TABLE `reviews` ADD COLUMN `user_agent_hash` VARCHAR(64) NULL AFTER `ip_hash`;

-- 2. Update providers table
ALTER TABLE `providers` ADD COLUMN `verification_status` VARCHAR(50) NOT NULL DEFAULT 'unverified' AFTER `verified`;
ALTER TABLE `providers` ADD COLUMN `verification_document_path` VARCHAR(255) NULL AFTER `verification_status`;
ALTER TABLE `providers` ADD COLUMN `verification_rejection_reason` TEXT NULL AFTER `verification_document_path`;
ALTER TABLE `providers` ADD COLUMN `platform_score` INT NOT NULL DEFAULT 0 AFTER `sort_weight`;
