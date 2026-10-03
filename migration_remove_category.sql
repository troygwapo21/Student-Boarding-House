-- Migration: Remove room_categories table and category_id column
-- Run this SQL on your existing database

-- 1. Drop foreign key and index on category_id
ALTER TABLE `rooms` DROP FOREIGN KEY IF EXISTS `fk_rooms_category`;
ALTER TABLE `rooms` DROP INDEX IF EXISTS `idx_rooms_category`;

-- 2. Drop category_id column from rooms
ALTER TABLE `rooms` DROP COLUMN IF EXISTS `category_id`;

-- 3. Drop the room_categories table
DROP TABLE IF EXISTS `room_categories`;
