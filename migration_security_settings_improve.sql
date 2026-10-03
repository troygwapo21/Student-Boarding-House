-- ============================================================
-- MIGRATION: Improve Security Settings
-- Adds session_timeout, password_expiry_days, password_min_length,
-- password_changed_at column, and login attempts cleanup
-- ============================================================

SET @dbname = DATABASE();

-- ============================================================
-- 1. Add password_changed_at column to users table
-- ============================================================
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password_changed_at';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `password_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `locked_until`',
    'SELECT "password_changed_at column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill existing users: set password_changed_at to created_at
UPDATE `users` SET `password_changed_at` = `created_at` WHERE `password_changed_at` IS NULL;

-- ============================================================
-- 2. Add new security settings to system_settings
-- ============================================================

-- Session timeout (in minutes, 5-1440 = 5min to 24hrs)
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES ('session_timeout', '120', 'number', 'security', 23, 'Session idle timeout in minutes (5-1440). User is logged out after this period of inactivity.');

-- Password expiry days (0 = disabled, 30-365 = force change after N days)
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES ('password_expiry_days', '90', 'number', 'security', 24, 'Force password change after N days (0 = disabled, 30-365).');

-- Password minimum length (8-128)
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES ('password_min_length', '8', 'number', 'security', 25, 'Minimum password length (8-128 characters).');

-- Require password change on first login
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`, `setting_options`)
VALUES ('require_password_change', '0', 'boolean', 'security', 26, 'Force all users to change password on next login.', NULL);

-- ============================================================
-- 3. Clean up old login_attempts (older than 30 days)
-- ============================================================
DELETE FROM `login_attempts` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- ============================================================
-- 4. Clean up expired password_resets
-- ============================================================
DELETE FROM `password_resets` WHERE `expires_at` < NOW();

SELECT 'Security settings migration complete!' AS result;
