-- ============================================================
-- MIGRATION: Real-Time Due Date & Monthly Billing (Asia/Manila)
-- Run this ONCE on existing databases.
-- ============================================================

-- 1. Add moved_in_at column to reservations (real-time move-in timestamp)
SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'reservations' AND COLUMN_NAME = 'moved_in_at';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `reservations` ADD COLUMN `moved_in_at` DATETIME DEFAULT NULL AFTER `move_in_date`',
    'SELECT "moved_in_at column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Backfill moved_in_at for approved reservations using approved_at
UPDATE `reservations`
SET `moved_in_at` = COALESCE(`approved_at`, `created_at`)
WHERE `status` = 'approved' AND `moved_in_at` IS NULL;

-- 3. Update billing setting descriptions to reflect the new move-in based logic
UPDATE `system_settings`
SET `description` = 'Late payment penalty applied immediately when a payment becomes overdue'
WHERE `setting_key` = 'late_fee';

UPDATE `system_settings`
SET `description` = 'Grace period (legacy; real-time billing applies the penalty immediately on the day after the due date)'
WHERE `setting_key` = 'grace_period_days';
