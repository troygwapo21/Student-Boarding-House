-- ============================================================
-- COMPREHENSIVE MIGRATION: Fix payments table + system settings
-- Run this ONCE on existing databases to add all missing columns
-- ============================================================

-- 1. Add missing columns to payments table (skip if already exists)
SET @dbname = DATABASE();

-- Add late_fee column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'late_fee';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `payments` ADD COLUMN `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `amount`',
    'SELECT "late_fee column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add amount_paid column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'amount_paid';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `payments` ADD COLUMN `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `late_fee`',
    'SELECT "amount_paid column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add billing_period column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'billing_period';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `payments` ADD COLUMN `billing_period` VARCHAR(7) DEFAULT NULL AFTER `amount_paid`',
    'SELECT "billing_period column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add penalty_applied column
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'penalty_applied';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `payments` ADD COLUMN `penalty_applied` TINYINT(1) NOT NULL DEFAULT 0 AFTER `billing_period`',
    'SELECT "penalty_applied column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add billing_period index
SELECT COUNT(*) INTO @idx_exists FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_billing_period';
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `payments` ADD KEY `idx_payments_billing_period` (`billing_period`)',
    'SELECT "billing_period index already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Fix payments status ENUM (add missing values: upcoming, due_today, partially_paid)
ALTER TABLE `payments`
    MODIFY COLUMN `status` ENUM('pending','upcoming','due_today','partially_paid','paid','overdue','cancelled','refunded') NOT NULL DEFAULT 'pending';

-- 3. Update late_fee setting to 100
UPDATE `system_settings` SET `setting_value` = '100' WHERE `setting_key` = 'late_fee' AND `setting_value` != '100';

-- 4. Remove obsolete billing settings
DELETE FROM `system_settings` WHERE `setting_key` IN ('default_electric_bill', 'default_water_bill');

-- 5. Update rooms to 900/900
UPDATE `rooms` SET `monthly_rent` = 900.00, `advance_payment` = 900.00 WHERE `monthly_rent` != 900.00 OR `advance_payment` != 900.00;

SELECT 'Migration complete! All missing columns and settings have been added.' AS result;
