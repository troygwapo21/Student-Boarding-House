-- ============================================================
-- Email Verification System Migration
-- 1. Adds users.email_verified (1 = verified, 0 = pending)
-- 2. Creates email_verification_codes for 6-digit OTPs (5-min TTL)
-- ============================================================

-- Add the verification flag to users (backfilled for existing rows so
-- previously registered accounts keep working).
ALTER TABLE `users`
    ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

-- Existing accounts that already carry an email_verified_at timestamp are
-- treated as verified. Only brand-new self-signups start unverified.
UPDATE `users` SET `email_verified` = 1 WHERE `email_verified_at` IS NOT NULL;

-- 6-digit single-use verification codes. created_at/expires_at are written
-- from PHP (Asia/Manila) so cooldown and expiry math is timezone-safe.
CREATE TABLE `email_verification_codes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `code` CHAR(6) NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_evc_user_unused` (`user_id`, `used`),
    KEY `idx_evc_code` (`code`),
    CONSTRAINT `fk_evc_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;