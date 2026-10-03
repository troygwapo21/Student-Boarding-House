-- Migration: Add default billing amounts to system_settings
-- Run this SQL on existing databases to add the new settings

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('default_electric_bill', '1500', 'number', 'Default electric bill amount (auto-created on reservation approval)'),
('default_water_bill', '500', 'number', 'Default water bill amount (auto-created on reservation approval)');
