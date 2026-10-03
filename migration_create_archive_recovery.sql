-- Migration: Create archived_records table for Archive & Recovery
-- Safely preserves deleted records (module, original data, deleter, deletion time)
-- so they can be restored or permanently deleted later.

CREATE TABLE IF NOT EXISTS `archived_records` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module` VARCHAR(50) NOT NULL,
    `record_id` INT UNSIGNED NOT NULL,
    `data` LONGTEXT NOT NULL,
    `deleted_by` INT UNSIGNED DEFAULT NULL,
    `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_archive_module` (`module`),
    KEY `idx_archive_record` (`record_id`),
    KEY `idx_archive_deleted_by` (`deleted_by`),
    KEY `idx_archive_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_archive_user` FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
