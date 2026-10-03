-- Update lockout duration from 3 minutes (180s) to 5 minutes (300s)
UPDATE `system_settings` 
SET `setting_value` = '300' 
WHERE `setting_key` = 'lockout_duration';

-- Verify the change
SELECT * FROM `system_settings` WHERE `setting_key` = 'lockout_duration';