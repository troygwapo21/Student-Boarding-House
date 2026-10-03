-- Migration: Create user_module_reads table for automatic notification badge removal
-- Tracks when each user last viewed each sidebar module

CREATE TABLE IF NOT EXISTS `user_module_reads` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `last_read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_module` (`user_id`, `module`),
    KEY `idx_umr_user` (`user_id`),
    CONSTRAINT `fk_umr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
