-- ============================================================
-- MIGRATION: Improve system_settings table
-- Adds setting_group, setting_order, setting_options, fixes ENUM
-- ============================================================

SET @dbname = DATABASE();

-- Add setting_group column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'system_settings' AND COLUMN_NAME = 'setting_group';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `system_settings` ADD COLUMN `setting_group` VARCHAR(50) NOT NULL DEFAULT \'general\' AFTER `setting_type`',
    'SELECT "setting_group column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add setting_order column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'system_settings' AND COLUMN_NAME = 'setting_order';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `system_settings` ADD COLUMN `setting_order` INT NOT NULL DEFAULT 0 AFTER `setting_group`',
    'SELECT "setting_order column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add setting_options column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'system_settings' AND COLUMN_NAME = 'setting_options';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `system_settings` ADD COLUMN `setting_options` JSON DEFAULT NULL AFTER `setting_order`',
    'SELECT "setting_options column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add setting_group index
SELECT COUNT(*) INTO @idx_exists FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'system_settings' AND INDEX_NAME = 'idx_settings_group';
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `system_settings` ADD KEY `idx_settings_group` (`setting_group`)',
    'SELECT "setting_group index already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Fix setting_type ENUM to include email, textarea, select, integer
ALTER TABLE `system_settings`
    MODIFY COLUMN `setting_type` ENUM('text','email','number','integer','boolean','textarea','select','json','file') NOT NULL DEFAULT 'text';

-- ============================================================
-- Update existing settings with proper groups, orders, and types
-- ============================================================

-- General settings
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 1 WHERE `setting_key` = 'site_name';
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 2 WHERE `setting_key` = 'site_tagline';
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 3, `setting_type` = 'email' WHERE `setting_key` = 'site_email';
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 4 WHERE `setting_key` = 'site_phone';
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 5, `setting_type` = 'textarea' WHERE `setting_key` = 'site_address';
UPDATE `system_settings` SET `setting_group` = 'general', `setting_order` = 6 WHERE `setting_key` = 'site_logo';

-- Billing settings
UPDATE `system_settings` SET `setting_group` = 'billing', `setting_order` = 10 WHERE `setting_key` = 'currency';
UPDATE `system_settings` SET `setting_group` = 'billing', `setting_order` = 14 WHERE `setting_key` = 'late_fee';
UPDATE `system_settings` SET `setting_group` = 'billing', `setting_order` = 15, `setting_type` = 'number' WHERE `setting_key` = 'grace_period_days';
UPDATE `system_settings` SET `setting_group` = 'billing', `setting_order` = 17 WHERE `setting_key` = 'tax_rate';

-- Add monthly_rent and advance_payment if not exist
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES ('monthly_rent', '900', 'number', 'billing', 12, 'Default monthly rent amount');
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES ('advance_payment', '900', 'number', 'billing', 13, 'Advance payment amount');

-- Security settings
UPDATE `system_settings` SET `setting_group` = 'security', `setting_order` = 20 WHERE `setting_key` = 'max_login_attempts';
UPDATE `system_settings` SET `setting_group` = 'security', `setting_order` = 21 WHERE `setting_key` = 'lockout_duration';
UPDATE `system_settings` SET `setting_group` = 'security', `setting_order` = 22 WHERE `setting_key` = 'reservation_expiry_days';

-- Email/SMTP settings
UPDATE `system_settings` SET `setting_group` = 'email', `setting_order` = 30 WHERE `setting_key` = 'smtp_host';
UPDATE `system_settings` SET `setting_group` = 'email', `setting_order` = 31 WHERE `setting_key` = 'smtp_port';
UPDATE `system_settings` SET `setting_group` = 'email', `setting_order` = 32 WHERE `setting_key` = 'smtp_username';
UPDATE `system_settings` SET `setting_group` = 'email', `setting_order` = 33 WHERE `setting_key` = 'smtp_password';
UPDATE `system_settings` SET `setting_group` = 'email', `setting_order` = 34, `setting_type` = 'select', `setting_options` = '["tls","ssl","none"]' WHERE `setting_key` = 'smtp_encryption';

-- Social Media settings
UPDATE `system_settings` SET `setting_group` = 'social', `setting_order` = 40 WHERE `setting_key` = 'facebook_url';
UPDATE `system_settings` SET `setting_group` = 'social', `setting_order` = 41 WHERE `setting_key` = 'twitter_url';
UPDATE `system_settings` SET `setting_group` = 'social', `setting_order` = 42 WHERE `setting_key` = 'instagram_url';

-- About page settings
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 50 WHERE `setting_key` = 'about_title';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 51 WHERE `setting_key` = 'about_subtitle';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 52 WHERE `setting_key` = 'about_story_title';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 53, `setting_type` = 'textarea' WHERE `setting_key` = 'about_story_text';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 54 WHERE `setting_key` = 'about_years_label';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 55 WHERE `setting_key` = 'about_years_sublabel';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 56, `setting_type` = 'textarea' WHERE `setting_key` = 'about_mission';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 57, `setting_type` = 'textarea' WHERE `setting_key` = 'about_vision';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 58 WHERE `setting_key` = 'about_team_title';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 59 WHERE `setting_key` = 'about_team_subtitle';
UPDATE `system_settings` SET `setting_group` = 'about', `setting_order` = 60 WHERE `setting_key` = 'about_values_title';

SELECT 'System settings migration complete!' AS result;
