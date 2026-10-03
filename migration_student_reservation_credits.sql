-- ============================================================
-- Migration: Student Reservation Payment Credits
-- Stores payment credits when an approved reservation is deleted
-- so they can be applied to a new reservation
-- ============================================================

CREATE TABLE IF NOT EXISTS `student_reservation_credits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `original_reservation_id` INT NOT NULL,
    `original_reservation_code` VARCHAR(50) NOT NULL,
    `payment_type` VARCHAR(50) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student_credits` (`student_id`),
    INDEX `idx_original_reservation` (`original_reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;