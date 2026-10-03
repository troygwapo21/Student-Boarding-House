-- ============================================================
-- MIGRATION: Add missing reservations billing columns
-- Fixes: Fatal error 1054 Unknown column 'r.move_in_time'
--        (and the follow-up 'rent_amount')
-- Run this ONCE on the LIVE database via phpMyAdmin (Import).
-- Uses plain ALTER TABLE (no INFORMATION_SCHEMA), so it works
-- on hosts like InfinityFree where information_schema is read-only.
-- ============================================================

-- 1. move_in_time (approval move-in time, HH:MM:SS)
ALTER TABLE `reservations` ADD COLUMN IF NOT EXISTS `move_in_time` TIME DEFAULT NULL AFTER `move_in_date`;

-- 2. rent_amount (rent frozen on the reservation at approval)
ALTER TABLE `reservations` ADD COLUMN IF NOT EXISTS `rent_amount` DECIMAL(10,2) DEFAULT NULL AFTER `expected_duration`;

-- 3. Backfill rent_amount for approved reservations from their room's rent
UPDATE `reservations` r
JOIN `rooms` rm ON rm.id = r.room_id
SET r.rent_amount = COALESCE(r.rent_amount, rm.monthly_rent)
WHERE r.status = 'approved' AND r.rent_amount IS NULL;
