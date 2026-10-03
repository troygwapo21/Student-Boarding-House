-- Run this in your database (phpMyAdmin, MySQL Workbench, etc.)
-- Updates lockout duration from 3 minutes to 5 minutes (300 seconds)

UPDATE `system_settings` 
SET `setting_value` = '5' 
WHERE `setting_key` = 'lockout_duration';

-- Verify the change
SELECT `setting_key`, `setting_value`, `description` 
FROM `system_settings` 
WHERE `setting_key` IN ('max_login_attempts', 'lockout_duration');