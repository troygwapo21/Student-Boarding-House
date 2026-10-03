-- Migration: Create refund_requests table
-- Tenants (students with an approved reservation) can request a refund;
-- admins/managers review and approve or reject the request.

CREATE TABLE IF NOT EXISTS `refund_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `refund_code` VARCHAR(20) NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `reservation_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `reason` TEXT NOT NULL,
    `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_refund_code` (`refund_code`),
    KEY `idx_refunds_student` (`student_id`),
    KEY `idx_refunds_reservation` (`reservation_id`),
    KEY `idx_refunds_status` (`status`),
    CONSTRAINT `fk_refunds_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_refunds_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_refunds_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
