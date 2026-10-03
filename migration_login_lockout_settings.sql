-- ============================================================
-- MIGRATION: Login Lockout Settings (3 attempts / 5 minutes)
-- After 3 failed login attempts the account is automatically
-- locked for 5 minutes (300 seconds). Safe to re-run.
-- ============================================================

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`) VALUES
('max_login_attempts', '3', 'number', 'security', 20, 'Max failed login attempts'),
('lockout_duration',    '300', 'number', 'security', 21, 'Account lockout duration in seconds');

UPDATE `system_settings` SET `setting_value` = '3' WHERE `setting_key` = 'max_login_attempts';
UPDATE `system_settings` SET `setting_value` = '300' WHERE `setting_key` = 'lockout_duration';

SELECT 'Login lockout set to 3 attempts / 5 minutes (300 seconds)!' AS result;