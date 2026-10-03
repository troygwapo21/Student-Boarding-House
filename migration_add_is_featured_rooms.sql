-- Add is_featured column to rooms table
ALTER TABLE `rooms` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `room_type`;
ALTER TABLE `rooms` ADD INDEX `idx_rooms_featured` (`is_featured`);
