-- ============================================================
-- MIGRATION: Enforce unique email + username on users
-- Prevents duplicate accounts even under concurrent/duplicate
-- submissions or direct DB inserts.
-- Safe to run multiple times (idempotent).
-- ============================================================

-- 1. Ensure the username column exists on installs that predate it.
SET @add_username_col = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) DEFAULT NULL AFTER `email`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'username'
);
PREPARE stmt FROM @add_username_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Safety net: resolve any existing duplicates before adding constraints.
--    Keeps the OLDEST account (lowest id) for each duplicated email/username.
--    NOTE: On a healthy database these delete 0 rows.

DELETE u1 FROM `users` u1
INNER JOIN `users` u2
    ON LOWER(u1.email) = LOWER(u2.email)
   AND u1.id > u2.id;

DELETE u1 FROM `users` u1
INNER JOIN `users` u2
    ON LOWER(u1.username) = LOWER(u2.username)
   AND u1.username IS NOT NULL
   AND u2.username IS NOT NULL
   AND u1.id > u2.id;

-- 3. Normalize credentials to lowercase so comparisons are consistent.
UPDATE `users` SET `email` = LOWER(TRIM(`email`)) WHERE `email` != LOWER(TRIM(`email`));
UPDATE `users` SET `username` = LOWER(TRIM(`username`)) WHERE `username` IS NOT NULL AND `username` != LOWER(TRIM(`username`));

-- 4. Add the UNIQUE constraints if they do not already exist.
SET @add_email_unique = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD UNIQUE KEY `uk_users_email` (`email`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'uk_users_email'
);
PREPARE stmt FROM @add_email_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_username_unique = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD UNIQUE KEY `uk_users_username` (`username`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'uk_users_username'
);
PREPARE stmt FROM @add_username_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
