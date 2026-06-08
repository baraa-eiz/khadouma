-- ============================================================
-- KHADOMEH MIGRATION: UPDATE CITIES & AREAS SCHEMAS
-- ============================================================

-- 1. Update Cities table
ALTER TABLE `cities`
  CHANGE COLUMN `key_name` `key` VARCHAR(100) NOT NULL UNIQUE,
  ADD COLUMN `public_id` VARCHAR(36) NULL AFTER `id`,
  ADD COLUMN `display_name_en` VARCHAR(150) NULL AFTER `display_name_ar`,
  ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
  ADD COLUMN `meta_title_ar` VARCHAR(255) NULL AFTER `sort_order`,
  ADD COLUMN `meta_description_ar` TEXT NULL AFTER `meta_title_ar`;

-- Populate public_id with deterministic UUIDs for existing seeded cities
UPDATE `cities` SET `public_id` = '660e8400-e29b-41d4-a716-446655440001' WHERE `key` = 'damascus';
UPDATE `cities` SET `public_id` = '660e8400-e29b-41d4-a716-446655440002' WHERE `key` = 'aleppo';
UPDATE `cities` SET `public_id` = '660e8400-e29b-41d4-a716-446655440003' WHERE `key` = 'homs';
UPDATE `cities` SET `public_id` = '660e8400-e29b-41d4-a716-446655440004' WHERE `key` = 'latakia';
UPDATE `cities` SET `public_id` = '660e8400-e29b-41d4-a716-446655440005' WHERE `key` = 'hama';

-- Fallback for any other custom rows
UPDATE `cities` SET `public_id` = UUID() WHERE `public_id` IS NULL;

-- Make cities public_id NOT NULL and UNIQUE
ALTER TABLE `cities` MODIFY COLUMN `public_id` VARCHAR(36) NOT NULL UNIQUE;


-- 2. Update Areas table
-- Drop global slug unique index
ALTER TABLE `areas` DROP INDEX `slug`;

ALTER TABLE `areas`
  CHANGE COLUMN `key_name` `key` VARCHAR(100) NOT NULL,
  ADD COLUMN `public_id` VARCHAR(36) NULL AFTER `id`,
  ADD COLUMN `display_name_en` VARCHAR(150) NULL AFTER `display_name_ar`,
  ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
  ADD COLUMN `meta_title_ar` VARCHAR(255) NULL AFTER `sort_order`,
  ADD COLUMN `meta_description_ar` TEXT NULL AFTER `meta_title_ar`;

-- Add composite unique index for city-scoped uniqueness
ALTER TABLE `areas` ADD UNIQUE KEY `uq_area_city_slug` (`city_id`, `slug`);

-- Populate public_id with deterministic UUIDs for seeded areas
UPDATE `areas` SET `public_id` = '770e8400-e29b-41d4-a716-446655440001' WHERE `key` = 'mezzeh';
UPDATE `areas` SET `public_id` = '770e8400-e29b-41d4-a716-446655440002' WHERE `key` = 'malki';
UPDATE `areas` SET `public_id` = '770e8400-e29b-41d4-a716-446655440003' WHERE `key` = 'abou_roumaneh';
UPDATE `areas` SET `public_id` = '770e8400-e29b-41d4-a716-446655440004' WHERE `key` = 'midan';

-- Fallback for other rows
UPDATE `areas` SET `public_id` = UUID() WHERE `public_id` IS NULL;

-- Make areas public_id NOT NULL and UNIQUE
ALTER TABLE `areas` MODIFY COLUMN `public_id` VARCHAR(36) NOT NULL UNIQUE;


-- 3. Record migration execution
INSERT INTO `schema_versions` (`migration_name`) VALUES ('0003_update_locations_tables');
