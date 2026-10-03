-- ============================================================
-- MIGRATION: Add room_type ENUM column to rooms table
-- Run this on existing databases to add room_type support
-- ============================================================

ALTER TABLE `rooms`
ADD COLUMN `room_type` ENUM('bedspacer','single','studio') NOT NULL DEFAULT 'bedspacer'
AFTER `furniture`;

ALTER TABLE `rooms`
ADD INDEX `idx_rooms_type` (`room_type`);

-- Update existing rooms based on their characteristics
-- Rooms with aircon on upper floors = single
UPDATE `rooms` SET `room_type` = 'single' WHERE `has_aircon` = 1 AND `max_capacity` = 1;
-- Room 601 with kitchenette/living area = studio
UPDATE `rooms` SET `room_type` = 'studio' WHERE `room_number` = '601';
-- All others remain bedspacer (default)
