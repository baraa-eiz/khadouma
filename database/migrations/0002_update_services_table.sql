-- ============================================================
-- KHADOMEH MIGRATION: UPDATE SERVICES TABLE SCHEMA
-- ============================================================

ALTER TABLE `services` 
  CHANGE COLUMN `key_name` `key` VARCHAR(100) NOT NULL UNIQUE,
  CHANGE COLUMN `icon_key` `icon` VARCHAR(100) NULL,
  ADD COLUMN `public_id` VARCHAR(36) NULL AFTER `id`,
  ADD COLUMN `meta_title_ar` VARCHAR(255) NULL AFTER `sort_order`,
  ADD COLUMN `meta_description_ar` TEXT NULL AFTER `meta_title_ar`,
  ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- Populate public_id with deterministic UUIDs for seeded services
UPDATE `services` SET `public_id` = '550e8400-e29b-41d4-a716-446655440001' WHERE `key` = 'cleaning';
UPDATE `services` SET `public_id` = '550e8400-e29b-41d4-a716-446655440002' WHERE `key` = 'plumbing';
UPDATE `services` SET `public_id` = '550e8400-e29b-41d4-a716-446655440003' WHERE `key` = 'electricity';
UPDATE `services` SET `public_id` = '550e8400-e29b-41d4-a716-446655440004' WHERE `key` = 'painting';
UPDATE `services` SET `public_id` = '550e8400-e29b-41d4-a716-446655440005' WHERE `key` = 'moving';

-- Fallback for any other rows
UPDATE `services` SET `public_id` = UUID() WHERE `public_id` IS NULL;

-- Now make public_id NOT NULL and UNIQUE
ALTER TABLE `services` MODIFY COLUMN `public_id` VARCHAR(36) NOT NULL UNIQUE;

-- Record migration execution
INSERT INTO `schema_versions` (`migration_name`) VALUES ('0002_update_services_table');
