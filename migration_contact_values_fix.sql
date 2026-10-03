-- ============================================================
-- MIGRATION: Contact Page Values (pinned to live dormitory details)
-- Ensures the System Settings -> Contact Page section holds exactly
-- these values. Safe to re-run (INSERT IGNORE + UPDATE, no
-- INFORMATION_SCHEMA queries, existing rows never duplicated).
-- ============================================================

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`) VALUES
('site_address',       'Pili Madredijos, Cebu',        'textarea', 'general', 5,  'Physical address'),
('site_phone',         '0945-495-5140',                'text',     'general', 4,  'Contact phone'),
('site_email',         'warlitovelliganio@gmail.com',  'email',    'general', 3,  'Contact email'),
('home_hours_weekday', '8:00 AM - 8:00 PM',            'text',     'home',    53, 'Working hours shown in the footer (weekdays).'),
('home_hours_saturday','8:00 AM - 6:00 PM',            'text',     'home',    54, 'Working hours shown in the footer (Saturday).'),
('home_hours_sunday',  'Closed',                       'text',     'home',    55, 'Working hours shown in the footer (Sunday).');

UPDATE `system_settings` SET `setting_value` = 'Pili Madredijos, Cebu'       WHERE `setting_key` = 'site_address';
UPDATE `system_settings` SET `setting_value` = '0945-495-5140'              WHERE `setting_key` = 'site_phone';
UPDATE `system_settings` SET `setting_value` = 'warlitovelliganio@gmail.com' WHERE `setting_key` = 'site_email';
UPDATE `system_settings` SET `setting_value` = '8:00 AM - 8:00 PM'          WHERE `setting_key` = 'home_hours_weekday';
UPDATE `system_settings` SET `setting_value` = '8:00 AM - 6:00 PM'          WHERE `setting_key` = 'home_hours_saturday';
UPDATE `system_settings` SET `setting_value` = 'Closed'                     WHERE `setting_key` = 'home_hours_sunday';

SELECT 'Contact Page values pinned!' AS result;