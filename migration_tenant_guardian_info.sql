-- ============================================================
-- Migration: Add Tenant Information & Guardian Information
-- ============================================================

-- 1. Add username to users table (guarded: already added in the base schema)
SET @dbname = DATABASE();
SELECT COUNT(*) INTO @user_col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username';
SET @sql = IF(@user_col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) DEFAULT NULL AFTER `email`, ADD UNIQUE KEY `uk_users_username` (`username`)',
    'SELECT "username column already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Extend students table with new tenant fields
ALTER TABLE `students`
    ADD COLUMN `middle_name` VARCHAR(100) DEFAULT NULL AFTER `first_name`,
    ADD COLUMN `suffix` VARCHAR(20) DEFAULT NULL AFTER `last_name`,
    ADD COLUMN `civil_status` ENUM('single','married','widowed','separated','divorced') DEFAULT NULL AFTER `gender`,
    ADD COLUMN `nationality` VARCHAR(100) DEFAULT 'Filipino' AFTER `civil_status`,
    ADD COLUMN `school_id_path` VARCHAR(255) DEFAULT NULL AFTER `valid_id_path`,
    ADD COLUMN `house_unit` VARCHAR(50) DEFAULT NULL AFTER `address`,
    ADD COLUMN `street` VARCHAR(255) DEFAULT NULL AFTER `house_unit`,
    ADD COLUMN `barangay` VARCHAR(255) DEFAULT NULL AFTER `street`,
    ADD COLUMN `municipality_city` VARCHAR(255) DEFAULT NULL AFTER `barangay`,
    ADD COLUMN `province` VARCHAR(255) DEFAULT NULL AFTER `municipality_city`,
    ADD COLUMN `zip_code` VARCHAR(10) DEFAULT NULL AFTER `province`;

-- 3. Create guardians table
CREATE TABLE IF NOT EXISTS `guardians` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`            INT UNSIGNED NOT NULL,
    `first_name`            VARCHAR(100) NOT NULL,
    `middle_name`           VARCHAR(100) DEFAULT NULL,
    `last_name`             VARCHAR(100) NOT NULL,
    `relationship`          VARCHAR(100) NOT NULL,
    `mobile_number`         VARCHAR(20) NOT NULL,
    `alternative_contact`   VARCHAR(20) DEFAULT NULL,
    `email`                 VARCHAR(255) DEFAULT NULL,
    `house_unit`            VARCHAR(50) DEFAULT NULL,
    `street`                VARCHAR(255) DEFAULT NULL,
    `barangay`              VARCHAR(255) DEFAULT NULL,
    `municipality_city`     VARCHAR(255) DEFAULT NULL,
    `province`              VARCHAR(255) DEFAULT NULL,
    `zip_code`              VARCHAR(10) DEFAULT NULL,
    `created_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_guardians_student` (`student_id`),
    CONSTRAINT `fk_guardians_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
