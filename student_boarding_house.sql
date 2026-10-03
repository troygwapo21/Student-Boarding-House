-- ============================================================
-- Student Boarding House Management System
-- Complete Database Schema & Seed Data
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- CREATE DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `student_boarding_house` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_boarding_house`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `username` VARCHAR(50) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin','manager','student') NOT NULL DEFAULT 'student',
    `status` ENUM('active','inactive','suspended','locked') NOT NULL DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `login_attempts` INT NOT NULL DEFAULT 0,
    `locked_until` TIMESTAMP NULL DEFAULT NULL,
    `password_changed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: managers
-- ============================================================
CREATE TABLE `managers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `link` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_managers_user` (`user_id`),
    CONSTRAINT `fk_managers_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: students
-- ============================================================
CREATE TABLE `students` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `student_id_number` VARCHAR(50) DEFAULT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `date_of_birth` DATE DEFAULT NULL,
    `gender` ENUM('male','female','other') DEFAULT NULL,
    `school_university` VARCHAR(255) DEFAULT NULL,
    `course_program` VARCHAR(255) DEFAULT NULL,
    `year_level` VARCHAR(50) DEFAULT NULL,
    `emergency_contact_name` VARCHAR(200) DEFAULT NULL,
    `emergency_contact_phone` VARCHAR(20) DEFAULT NULL,
    `valid_id_path` VARCHAR(255) DEFAULT NULL,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_students_user` (`user_id`),
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: rooms
-- ============================================================
CREATE TABLE `rooms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_number` VARCHAR(20) NOT NULL,
    `room_name` VARCHAR(100) NOT NULL,
    `monthly_rent` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `advance_payment` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `max_capacity` INT UNSIGNED NOT NULL DEFAULT 1,
    `current_occupancy` INT UNSIGNED NOT NULL DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('available','reserved','occupied','under_maintenance') NOT NULL DEFAULT 'available',
    `floor` INT DEFAULT NULL,
    `size_sqm` DECIMAL(6,2) DEFAULT NULL,
    `has_bathroom` TINYINT(1) NOT NULL DEFAULT 0,
    `has_balcony` TINYINT(1) NOT NULL DEFAULT 0,
    `has_aircon` TINYINT(1) NOT NULL DEFAULT 0,
    `house_rules` TEXT DEFAULT NULL,
    `furniture` TEXT DEFAULT NULL,
    `room_type` ENUM('bedspacer','single','studio') NOT NULL DEFAULT 'bedspacer',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_rooms_number` (`room_number`),
    KEY `idx_rooms_status` (`status`),
    KEY `idx_rooms_type` (`room_type`),
    KEY `idx_rooms_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: room_images
-- ============================================================
CREATE TABLE `room_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_room_images_room` (`room_id`),
    CONSTRAINT `fk_room_images_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: amenities
-- ============================================================
CREATE TABLE `amenities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: room_amenities
-- ============================================================
CREATE TABLE `room_amenities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `amenity_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_room_amenity` (`room_id`, `amenity_id`),
    KEY `idx_ra_amenity` (`amenity_id`),
    CONSTRAINT `fk_ra_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ra_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reservations
-- ============================================================
CREATE TABLE `reservations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reservation_code` VARCHAR(20) NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `room_id` INT UNSIGNED NOT NULL,
    `move_in_date` DATE NOT NULL,
    `moved_in_at` DATETIME DEFAULT NULL,
    `expected_duration` INT UNSIGNED DEFAULT NULL COMMENT 'months',
    `status` ENUM('pending','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT DEFAULT NULL,
    `admin_notes` TEXT DEFAULT NULL,
    `valid_id_path` VARCHAR(255) DEFAULT NULL,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `rejected_at` TIMESTAMP NULL DEFAULT NULL,
    `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_reservations_code` (`reservation_code`),
    KEY `idx_reservations_student` (`student_id`),
    KEY `idx_reservations_room` (`room_id`),
    KEY `idx_reservations_status` (`status`),
    CONSTRAINT `fk_reservations_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reservations_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE `payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_code` VARCHAR(20) NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `reservation_id` INT UNSIGNED DEFAULT NULL,
    `payment_type` ENUM('reservation_fee','advance_payment','monthly_rent','electric_bill','water_bill','other') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `refunded_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `refund_request_id` INT UNSIGNED DEFAULT NULL,
    `billing_period` VARCHAR(7) DEFAULT NULL,
    `penalty_applied` TINYINT(1) NOT NULL DEFAULT 0,
    `payment_method` ENUM('cash','gcash') DEFAULT 'cash',
    `status` ENUM('pending','upcoming','due_today','partially_paid','paid','overdue','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `reference_number` VARCHAR(100) DEFAULT NULL,
    `proof_of_payment` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `due_date` DATE DEFAULT NULL,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `verified_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payments_code` (`payment_code`),
    KEY `idx_payments_student` (`student_id`),
    KEY `idx_payments_reservation` (`reservation_id`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_type` (`payment_type`),
    KEY `idx_payments_billing_period` (`billing_period`),
    KEY `idx_payments_refund_request` (`refund_request_id`),
    CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payment_history
-- ============================================================
CREATE TABLE `payment_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_id` INT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `old_status` VARCHAR(20) DEFAULT NULL,
    `new_status` VARCHAR(20) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `performed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ph_payment` (`payment_id`),
    CONSTRAINT `fk_ph_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: receipts
-- ============================================================
CREATE TABLE `receipts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `receipt_number` VARCHAR(20) NOT NULL,
    `payment_id` INT UNSIGNED NOT NULL,
    `issued_date` DATE NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_receipts_number` (`receipt_number`),
    KEY `idx_receipts_payment` (`payment_id`),
    CONSTRAINT `fk_receipts_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE `announcements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` ENUM('general','important','urgent','maintenance','event') NOT NULL DEFAULT 'general',
    `priority` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_announcements_published` (`is_published`),
    KEY `idx_announcements_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: gallery
-- ============================================================
CREATE TABLE `gallery` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'general',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: testimonials
-- ============================================================
CREATE TABLE `testimonials` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_name` VARCHAR(200) NOT NULL,
    `student_course` VARCHAR(200) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: faqs
-- ============================================================
CREATE TABLE `faqs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` VARCHAR(500) NOT NULL,
    `answer` TEXT NOT NULL,
    `category` VARCHAR(100) DEFAULT 'general',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: maintenance_requests
-- ============================================================
CREATE TABLE `maintenance_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_code` VARCHAR(20) NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `room_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` ENUM('plumbing','electrical','furniture','appliance','structural','other') NOT NULL DEFAULT 'other',
    `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
    `admin_response` TEXT DEFAULT NULL,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_maintenance_code` (`request_code`),
    KEY `idx_maintenance_student` (`student_id`),
    KEY `idx_maintenance_room` (`room_id`),
    KEY `idx_maintenance_status` (`status`),
    CONSTRAINT `fk_maintenance_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_maintenance_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: complaints
-- ============================================================
CREATE TABLE `complaints` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `complaint_code` VARCHAR(20) NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` ENUM('noise','cleanliness','security','roommate','management','other') NOT NULL DEFAULT 'other',
    `severity` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `status` ENUM('open','under_review','resolved','closed') NOT NULL DEFAULT 'open',
    `admin_response` TEXT DEFAULT NULL,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_complaints_code` (`complaint_code`),
    KEY `idx_complaints_student` (`student_id`),
    KEY `idx_complaints_status` (`status`),
    CONSTRAINT `fk_complaints_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: feedback
-- ============================================================
CREATE TABLE `feedback` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(200) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `rating` TINYINT UNSIGNED DEFAULT NULL,
    `category` ENUM('suggestion','compliment','complaint','inquiry','other') NOT NULL DEFAULT 'other',
    `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    `admin_response` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_feedback_student` (`student_id`),
    KEY `idx_feedback_status` (`status`),
    CONSTRAINT `fk_feedback_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('reservation','payment','announcement','maintenance','complaint','system') NOT NULL DEFAULT 'system',
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_read` (`is_read`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: contact_messages
-- ============================================================
CREATE TABLE `contact_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    `admin_response` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: activity_logs
-- ============================================================
CREATE TABLE `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activity_user` (`user_id`),
    KEY `idx_activity_action` (`action`),
    KEY `idx_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `table_name` VARCHAR(100) NOT NULL,
    `record_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('create','update','delete') NOT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_table` (`table_name`),
    KEY `idx_audit_user` (`user_id`),
    KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: login_attempts
-- ============================================================
CREATE TABLE `login_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_email` (`email`),
    KEY `idx_login_ip` (`ip_address`),
    KEY `idx_login_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: password_resets
-- ============================================================
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pr_email` (`email`),
    KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_module_reads
-- ============================================================
CREATE TABLE `user_module_reads` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `last_read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_module` (`user_id`, `module`),
    KEY `idx_umr_user` (`user_id`),
    CONSTRAINT `fk_umr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: system_settings
-- ============================================================
CREATE TABLE `system_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('text','email','number','integer','boolean','textarea','select','json','file') NOT NULL DEFAULT 'text',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `setting_order` INT NOT NULL DEFAULT 0,
    `setting_options` JSON DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: team_members
-- ============================================================
CREATE TABLE `team_members` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `image_path` VARCHAR(255) DEFAULT NULL,
    `facebook_url` VARCHAR(255) DEFAULT NULL,
    `twitter_url` VARCHAR(255) DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_team_members_status` (`status`),
    KEY `idx_team_members_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: about_values
-- ============================================================
CREATE TABLE `about_values` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'fas fa-star',
    `title` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(20) DEFAULT '#2563eb',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_about_values_status` (`status`),
    KEY `idx_about_values_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: System Settings
-- ============================================================
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `setting_options`, `description`) VALUES
('site_name', 'Alondes Dorm', 'text', 'general', 1, NULL, 'Website name'),
('site_tagline', 'Your Home Away From Home', 'text', 'general', 2, NULL, 'Website tagline'),
('site_email', 'alondes@gmail.com', 'email', 'general', 3, NULL, 'Contact email'),
('site_phone', '+63 912 345 6789', 'text', 'general', 4, NULL, 'Contact phone'),
('site_address', '123 University Avenue, Manila, Philippines', 'textarea', 'general', 5, NULL, 'Physical address'),
('site_logo', 'logo.png', 'file', 'general', 6, NULL, 'Site logo'),
('monthly_rent', '900', 'number', 'billing', 12, NULL, 'Default monthly rent amount prefilled when creating rooms'),
('advance_payment', '900', 'number', 'billing', 13, NULL, 'Default advance payment amount prefilled when creating rooms'),
('late_fee', '100', 'number', 'billing', 14, NULL, 'One-time penalty added automatically when a payment becomes overdue'),
('grace_period_days', '0', 'number', 'billing', 16, NULL, 'Days after the due date before a payment is marked overdue and the late fee applies (0 = none)'),
('tax_rate', '0', 'number', 'billing', 17, NULL, 'Tax rate percentage added on top of billing amounts'),
('max_login_attempts', '3', 'number', 'security', 20, NULL, 'Max failed login attempts'),
('lockout_duration', '300', 'number', 'security', 21, NULL, 'Account lockout duration in seconds'),
('reservation_expiry_days', '5', 'number', 'security', 22, NULL, 'Reservation expiry in days'),
('facebook_url', 'https://facebook.com', 'text', 'social', 40, NULL, 'Facebook URL'),
('twitter_url', 'https://twitter.com', 'text', 'social', 41, NULL, 'Twitter URL'),
('instagram_url', 'https://instagram.com', 'text', 'social', 42, NULL, 'Instagram URL'),
('about_title', 'About Us', 'text', 'about', 50, NULL, 'About page title'),
('about_subtitle', 'Learn more about Alondes Dorm.', 'text', 'about', 51, NULL, 'About page subtitle'),
('about_story_title', 'Welcome to Alondes Dorm', 'text', 'about', 52, NULL, 'About story section title'),
('about_story_text', 'Founded with the vision of providing safe, comfortable, and affordable accommodation for students, Alondes Dorm has been a trusted home for hundreds of students pursuing their academic dreams.||We understand the challenges students face when looking for the right place to stay while studying. That''s why we''ve created a community-oriented living space that feels like home.', 'textarea', 'about', 53, NULL, 'About story text (use || for paragraphs)'),
('about_years_label', '5+ Years', 'text', 'about', 54, NULL, 'Years badge label'),
('about_years_sublabel', 'of Service', 'text', 'about', 55, NULL, 'Years badge sublabel'),
('about_mission', 'To provide students with a secure, convenient, and reliable platform for finding and reserving quality boarding houses. The website aims to simplify room management, reservations, payments, communication, and tenant services through an organized digital system.', 'textarea', 'about', 56, NULL, 'Mission statement'),
('about_vision', 'To become a trusted digital boarding house platform that improves the student housing experience through accessible technology, efficient management, transparent services, and better communication between students, tenants, and boarding house administrators.', 'textarea', 'about', 57, NULL, 'Vision statement'),
('about_team_title', 'Meet Our Team', 'text', 'about', 58, NULL, 'Team section title'),
('about_team_subtitle', 'The dedicated people behind Alondes Dorm.', 'text', 'about', 59, NULL, 'Team section subtitle'),
('about_values_title', 'What We Stand For', 'text', 'about', 60, NULL, 'Values section title'),
('about_image', '', 'file', 'about', 63, NULL, 'About page story image');

-- ============================================================
-- Sample DATA: Users (Password: password123 for all)
-- ============================================================
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `created_at`) VALUES
(1, 'alondes@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW(), NOW()),
(2, 'manager@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NOW(), NOW()),
(3, 'manager2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NOW(), NOW()),
(4, 'juan.delacruz@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW(), NOW()),
(5, 'maria.santos@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW(), NOW()),
(6, 'pedro.garcia@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW(), NOW()),
(7, 'ana.reyes@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW(), NOW()),
(8, 'carlos.mendoza@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW(), NOW());

-- ============================================================
-- SEED DATA: Managers
-- ============================================================
INSERT INTO `managers` (`user_id`, `first_name`, `last_name`, `phone`, `address`, `created_at`) VALUES
(2, 'Roberto', 'Santos', '+63 917 123 4567', '456 Admin Street, Quezon City', NOW()),
(3, 'Elena', 'Cruz', '+63 918 234 5678', '789 Manager Ave, Makati City', NOW());

-- ============================================================
-- SEED DATA: Students
-- ============================================================
INSERT INTO `students` (`user_id`, `student_id_number`, `first_name`, `last_name`, `phone`, `address`, `date_of_birth`, `gender`, `school_university`, `course_program`, `year_level`, `emergency_contact_name`, `emergency_contact_phone`, `created_at`) VALUES
(4, 'STU-2024-001', 'Juan', 'Dela Cruz', '+63 921 345 6789', '123 Sampaloc, Manila', '2002-05-15', 'male', 'University of Santo Tomas', 'BS Computer Science', '3rd Year', 'Jose Dela Cruz', '+63 912 111 2222', NOW()),
(5, 'STU-2024-002', 'Maria', 'Santos', '+63 922 456 7890', '456 Ermita, Manila', '2001-08-22', 'female', 'De La Salle University', 'BS Business Administration', '4th Year', 'Roberto Santos', '+63 913 222 3333', NOW()),
(6, 'STU-2024-003', 'Pedro', 'Garcia', '+63 923 567 8901', '789 Intramuros, Manila', '2003-01-10', 'male', 'Far Eastern University', 'BS Information Technology', '2nd Year', 'Miguel Garcia', '+63 914 333 4444', NOW()),
(7, 'STU-2024-004', 'Ana', 'Reyes', '+63 924 678 9012', '321 Tondo, Manila', '2002-11-05', 'female', 'University of the East', 'BS Nursing', '3rd Year', 'Pedro Reyes', '+63 915 444 5555', NOW()),
(8, 'STU-2024-005', 'Carlos', 'Mendoza', '+63 925 789 0123', '654 Binondo, Manila', '2001-06-18', 'male', 'Polytechnic University of the Philippines', 'BS Mechanical Engineering', '4th Year', 'Luis Mendoza', '+63 916 555 6666', NOW());

-- ============================================================
-- SEED DATA: Amenities
-- ============================================================
INSERT INTO `amenities` (`name`, `icon`, `description`, `status`) VALUES
('Free Wi-Fi', 'fas fa-wifi', 'High-speed wireless internet access', 'active'),
('Air Conditioning', 'fas fa-snowflake', 'Individual air conditioning unit', 'active'),
('Study Desk', 'fas fa-desktop', 'Dedicated study desk and chair', 'active'),
('Wardrobe', 'fas fa-door-closed', 'Built-in wardrobe for storage', 'active'),
('Private Bathroom', 'fas fa-bath', 'En-suite bathroom facilities', 'active'),
('Hot Water', 'fas fa-temperature-high', 'Hot and cold shower', 'active'),
('Laundry Service', 'fas fa-shirt', 'On-site laundry service available', 'active'),
('Kitchen Access', 'fas fa-utensils', 'Shared kitchen facilities', 'active'),
('Common Area', 'fas fa-couch', 'Shared living/recreation area', 'active'),
('24/7 Security', 'fas fa-shield-halved', 'Round-the-clock security personnel', 'active'),
('CCTV Surveillance', 'fas fa-video', 'Closed-circuit television monitoring', 'active'),
('Parking Space', 'fas fa-square-parking', 'Designated parking area', 'active'),
('Water Dispenser', 'fas fa-droplet', 'Free drinking water dispenser', 'active'),
('Generator', 'fas fa-bolt', 'Backup power generator', 'active'),
('Cleaning Service', 'fas fa-broom', 'Regular room cleaning service', 'active'),
('Bed Linens', 'fas fa-bed', 'Provided bed sheets and pillow', 'active'),
('Electrical Outlet', 'fas fa-plug', 'Ample power outlets', 'active'),
('Balcony', 'fas fa-person-shelter', 'Private balcony', 'active');

-- ============================================================
-- SEED DATA: Rooms
-- ============================================================
INSERT INTO `rooms` (`room_number`, `room_name`, `monthly_rent`, `advance_payment`, `max_capacity`, `current_occupancy`, `description`, `status`, `floor`, `size_sqm`, `has_bathroom`, `has_balcony`, `has_aircon`, `house_rules`, `furniture`, `room_type`) VALUES
('101', 'Sunrise Bedspace', 900.00, 900.00, 1, 0, 'Cozy bedspace on the first floor with great morning sunlight. Perfect for students who prefer a quiet space for studying.', 'available', 1, 12.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Study desk and chair, Wardrobe, Night stand', 'bedspacer'),
('102', 'Morning Light Bedspace', 900.00, 900.00, 1, 1, 'Well-lit bedspace with east-facing window. Ideal for early risers who love natural light.', 'occupied', 1, 12.50, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Study desk and chair, Wardrobe, Night stand', 'bedspacer'),
('103', 'Garden View Bedspace', 900.00, 900.00, 1, 0, 'Bedspace overlooking the garden area. Peaceful and serene environment for studying.', 'available', 1, 13.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Study desk and chair, Wardrobe, Night stand', 'bedspacer'),
('201', 'Twin Bedspace', 900.00, 900.00, 2, 1, 'Spacious bedspace ideal for friends or classmates. Separate study areas for productive studying.', 'occupied', 2, 18.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', '2 Bedspaces, 2 Study desks and chairs, 2 Wardrobes, Shared bookshelf', 'bedspacer'),
('202', 'Scholar\'s Bedspace', 900.00, 900.00, 2, 2, 'Premium bedspace with extra study space. Designed for serious students who need a conducive study environment.', 'occupied', 2, 20.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', '2 Bedspaces, 2 Large study desks, 2 Wardrobes, Bookshelf, Desk lamps', 'bedspacer'),
('203', 'Barkada Bedspace', 900.00, 900.00, 2, 0, 'Affordable bedspace with all basic amenities. Great value for money for budget-conscious students.', 'available', 2, 17.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', '2 Bedspaces, 2 Study desks and chairs, 2 Wardrobes', 'bedspacer'),
('301', 'Explorer Bedspace', 900.00, 900.00, 3, 2, 'Value-packed bedspace perfect for a group of friends. Shared expenses mean more savings.', 'occupied', 3, 24.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', '3 Bedspaces, 3 Study desks and chairs, 3 Wardrobes, Shared bookshelf', 'bedspacer'),
('302', 'Study Hub Bedspace', 900.00, 900.00, 3, 0, 'Bedspace with enhanced study features including individual desk lamps and power strips.', 'under_maintenance', 3, 25.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', '3 Bedspaces, 3 Study desks with lamps, 3 Wardrobes, Bookshelf', 'bedspacer'),
('401', 'Community Bedspace', 900.00, 900.00, 6, 4, 'Budget-friendly bedspace. Perfect for students who want affordable accommodation and enjoy community living.', 'available', 4, 36.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only. Maintain cleanliness.', '6 Bedspaces, 6 Personal lockers, 2 Shared study tables', 'bedspacer'),
('402', 'Budget Bedspace', 900.00, 900.00, 6, 5, 'Affordable bedspace with all essential amenities. Shared with fellow students for a vibrant community experience.', 'occupied', 4, 35.00, 0, 0, 0, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only. Maintain cleanliness.', '6 Bedspaces, 6 Personal lockers, 2 Shared study tables', 'bedspacer'),
('501', 'Premier Bedspace', 900.00, 900.00, 1, 0, 'Top-tier bedspace with premium furnishings. The best bedspace in the house.', 'available', 5, 22.00, 0, 0, 1, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Large study desk, Wardrobe, Desk lamp, Bookshelf', 'single'),
('502', 'Executive Bedspace', 900.00, 900.00, 1, 1, 'Executive-level bedspace with modern amenities. Ideal for graduate students or working students.', 'occupied', 5, 23.00, 0, 0, 1, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Large study desk, Wardrobe, Desk lamp, Mini fridge', 'single'),
('601', 'Studio Bedspace', 900.00, 900.00, 2, 0, 'Self-contained bedspace with separate living area. Maximum privacy and independence.', 'available', 6, 30.00, 0, 0, 1, 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7AM. Visitors allowed until 9PM only.', 'Bedspace, Full desk setup, Kitchenette, Living area', 'studio');

-- ============================================================
-- SEED DATA: Room Images
-- ============================================================
INSERT INTO `room_images` (`room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 'rooms/room_1.svg', 'Sunrise Bedspace', 1, 1, NOW()),
(1, 'rooms/room_3.svg', 'Sunrise Bedspace - Alternate View', 0, 2, NOW()),
(2, 'rooms/room_2.svg', 'Morning Light Bedspace', 1, 1, NOW()),
(2, 'rooms/room_1.svg', 'Morning Light Bedspace - Alternate View', 0, 2, NOW()),
(3, 'rooms/room_3.svg', 'Garden View Bedspace', 1, 1, NOW()),
(3, 'rooms/room_9.svg', 'Garden View Bedspace - Alternate View', 0, 2, NOW()),
(4, 'rooms/room_4.svg', 'Twin Bedspace', 1, 1, NOW()),
(4, 'rooms/room_6.svg', 'Twin Bedspace - Alternate View', 0, 2, NOW()),
(5, 'rooms/room_5.svg', 'Scholar''s Bedspace', 1, 1, NOW()),
(5, 'rooms/room_8.svg', 'Scholar''s Bedspace - Alternate View', 0, 2, NOW()),
(6, 'rooms/room_6.svg', 'Barkada Bedspace', 1, 1, NOW()),
(6, 'rooms/room_4.svg', 'Barkada Bedspace - Alternate View', 0, 2, NOW()),
(7, 'rooms/room_7.svg', 'Explorer Bedspace', 1, 1, NOW()),
(7, 'rooms/room_10.svg', 'Explorer Bedspace - Alternate View', 0, 2, NOW()),
(8, 'rooms/room_8.svg', 'Study Hub Bedspace', 1, 1, NOW()),
(8, 'rooms/room_5.svg', 'Study Hub Bedspace - Alternate View', 0, 2, NOW()),
(9, 'rooms/room_9.svg', 'Community Bedspace', 1, 1, NOW()),
(9, 'rooms/room_3.svg', 'Community Bedspace - Alternate View', 0, 2, NOW()),
(10, 'rooms/room_10.svg', 'Budget Bedspace', 1, 1, NOW()),
(10, 'rooms/room_7.svg', 'Budget Bedspace - Alternate View', 0, 2, NOW()),
(11, 'rooms/room_11.svg', 'Premier Bedspace', 1, 1, NOW()),
(11, 'rooms/room_12.svg', 'Premier Bedspace - Alternate View', 0, 2, NOW()),
(12, 'rooms/room_12.svg', 'Executive Bedspace', 1, 1, NOW()),
(12, 'rooms/room_11.svg', 'Executive Bedspace - Alternate View', 0, 2, NOW());

-- ============================================================
-- SEED DATA: Room Amenities
-- ============================================================
INSERT INTO `room_amenities` (`room_id`, `amenity_id`) VALUES
(1, 1), (1, 3), (1, 4), (1, 10), (1, 11), (1, 13), (1, 17),
(2, 1), (2, 3), (2, 4), (2, 10), (2, 11), (2, 13), (2, 17),
(3, 1), (3, 3), (3, 4), (3, 10), (3, 11), (3, 13), (3, 17),
(4, 1), (4, 2), (4, 3), (4, 4), (4, 10), (4, 11), (4, 13), (4, 16), (4, 17),
(5, 1), (5, 2), (5, 3), (5, 4), (5, 10), (5, 11), (5, 13), (5, 16), (5, 17),
(6, 1), (6, 2), (6, 3), (6, 4), (6, 10), (6, 11), (6, 13), (6, 17),
(7, 1), (7, 2), (7, 3), (7, 4), (7, 10), (7, 11), (7, 13), (7, 17),
(8, 1), (8, 2), (8, 3), (8, 4), (8, 10), (8, 11), (8, 13), (8, 17),
(9, 1), (9, 10), (9, 11), (9, 13), (9, 14), (9, 17),
(10, 1), (10, 10), (10, 11), (10, 13), (10, 14), (10, 17),
(11, 1), (11, 2), (11, 3), (11, 4), (11, 5), (11, 6), (11, 10), (11, 11), (11, 13), (11, 15), (11, 16), (11, 17), (11, 18),
(12, 1), (12, 2), (12, 3), (12, 4), (12, 5), (12, 6), (12, 10), (12, 11), (12, 13), (12, 15), (12, 16), (12, 17), (12, 18),
(13, 1), (13, 2), (13, 3), (13, 4), (13, 5), (13, 6), (13, 8), (13, 9), (13, 10), (13, 11), (13, 13), (13, 14), (13, 15), (13, 16), (13, 17), (13, 18);

-- ============================================================
-- SEED DATA: Reservations
-- ============================================================
INSERT INTO `reservations` (`reservation_code`, `student_id`, `room_id`, `move_in_date`, `expected_duration`, `status`, `created_at`) VALUES
('RES-2024-001', 1, 2, '2024-06-01', 12, 'approved', '2024-05-15 10:00:00'),
('RES-2024-002', 2, 4, '2024-06-01', 12, 'approved', '2024-05-16 11:30:00'),
('RES-2024-003', 3, 5, '2024-06-15', 6, 'approved', '2024-05-20 09:00:00'),
('RES-2024-004', 4, 7, '2024-07-01', 12, 'approved', '2024-06-01 14:00:00'),
('RES-2024-005', 5, 10, '2024-07-01', 12, 'approved', '2024-06-02 16:00:00'),
('RES-2024-006', 1, 12, '2024-08-01', 6, 'pending', '2024-07-10 08:00:00');

-- ============================================================
-- SEED DATA: Payments
-- ============================================================
INSERT INTO `payments` (`payment_code`, `student_id`, `reservation_id`, `payment_type`, `amount`, `late_fee`, `amount_paid`, `billing_period`, `penalty_applied`, `payment_method`, `status`, `due_date`, `paid_at`, `created_at`) VALUES
('PAY-2024-001', 1, 1, 'reservation_fee', 500.00, 0.00, 500.00, NULL, 0, 'gcash', 'paid', '2024-06-01', '2024-05-28 10:00:00', '2024-05-15 10:00:00'),
('PAY-2024-002', 1, 1, 'advance_payment', 900.00, 0.00, 900.00, NULL, 0, 'gcash', 'paid', '2024-06-01', '2024-05-28 10:05:00', '2024-05-15 10:00:00'),
('PAY-2024-004', 1, 1, 'monthly_rent', 900.00, 0.00, 900.00, '2024-07', 0, 'gcash', 'paid', '2024-07-01', '2024-06-28 15:00:00', '2024-06-01 00:00:00'),
('PAY-2024-005', 1, 1, 'monthly_rent', 900.00, 0.00, 900.00, '2024-08', 0, 'bank_transfer', 'paid', '2024-08-01', '2024-07-29 11:00:00', '2024-07-01 00:00:00'),
('PAY-2024-006', 2, 2, 'reservation_fee', 500.00, 0.00, 500.00, NULL, 0, 'cash', 'paid', '2024-06-01', '2024-05-30 14:00:00', '2024-05-16 11:30:00'),
('PAY-2024-007', 2, 2, 'advance_payment', 900.00, 0.00, 900.00, NULL, 0, 'cash', 'paid', '2024-06-01', '2024-05-30 14:05:00', '2024-05-16 11:30:00'),
('PAY-2024-009', 2, 2, 'monthly_rent', 900.00, 0.00, 900.00, '2024-07', 0, 'gcash', 'paid', '2024-07-01', '2024-06-30 10:00:00', '2024-06-01 00:00:00'),
('PAY-2024-010', 3, 3, 'reservation_fee', 500.00, 0.00, 500.00, NULL, 0, 'maya', 'paid', '2024-06-15', '2024-06-10 12:00:00', '2024-05-20 09:00:00'),
('PAY-2024-011', 3, 3, 'monthly_rent', 900.00, 0.00, 900.00, '2024-07', 0, 'maya', 'paid', '2024-07-15', '2024-07-12 09:00:00', '2024-06-15 00:00:00'),
('PAY-2024-012', 4, 4, 'reservation_fee', 500.00, 0.00, 500.00, NULL, 0, 'bank_transfer', 'paid', '2024-07-01', '2024-06-28 16:00:00', '2024-06-01 14:00:00'),
('PAY-2024-013', 4, 4, 'monthly_rent', 900.00, 0.00, 900.00, '2024-08', 0, 'gcash', 'paid', '2024-08-01', '2024-07-30 10:00:00', '2024-07-01 00:00:00'),
('PAY-2024-014', 5, 5, 'reservation_fee', 500.00, 0.00, 500.00, NULL, 0, 'cash', 'paid', '2024-07-01', '2024-06-29 11:00:00', '2024-06-02 16:00:00'),
('PAY-2024-015', 5, 5, 'monthly_rent', 900.00, 100.00, 0.00, '2024-08', 1, 'cash', 'overdue', '2024-08-01', NULL, '2024-07-01 00:00:00');

-- ============================================================
-- SEED DATA: Receipts
-- ============================================================
INSERT INTO `receipts` (`receipt_number`, `payment_id`, `issued_date`, `subtotal`, `discount`, `total`, `created_at`) VALUES
('REC-2024-001', 1, '2024-05-28', 500.00, 0.00, 500.00, '2024-05-28 10:00:00'),
('REC-2024-002', 2, '2024-05-28', 900.00, 0.00, 900.00, '2024-05-28 10:05:00'),
('REC-2024-003', 3, '2024-06-01', 900.00, 0.00, 900.00, '2024-06-01 09:00:00'),
('REC-2024-004', 4, '2024-06-28', 900.00, 0.00, 900.00, '2024-06-28 15:00:00'),
('REC-2024-005', 5, '2024-07-29', 900.00, 0.00, 900.00, '2024-07-29 11:00:00'),
('REC-2024-006', 6, '2024-05-30', 500.00, 0.00, 500.00, '2024-05-30 14:00:00'),
('REC-2024-007', 7, '2024-05-30', 900.00, 0.00, 900.00, '2024-05-30 14:05:00'),
('REC-2024-008', 8, '2024-06-01', 900.00, 0.00, 900.00, '2024-06-01 08:00:00'),
('REC-2024-009', 9, '2024-06-30', 900.00, 0.00, 900.00, '2024-06-30 10:00:00'),
('REC-2024-010', 10, '2024-06-10', 500.00, 0.00, 500.00, '2024-06-10 12:00:00'),
('REC-2024-011', 11, '2024-07-12', 900.00, 0.00, 900.00, '2024-07-12 09:00:00'),
('REC-2024-012', 12, '2024-06-28', 500.00, 0.00, 500.00, '2024-06-28 16:00:00'),
('REC-2024-013', 13, '2024-07-30', 900.00, 0.00, 900.00, '2024-07-30 10:00:00');

-- ============================================================
-- SEED DATA: Announcements
-- ============================================================
INSERT INTO `announcements` (`title`, `content`, `type`, `priority`, `is_published`, `published_at`, `created_by`, `created_at`) VALUES
('Welcome to the New School Year!', 'We welcome all new and returning students to our boarding house. Please check your rooms and report any issues to the management immediately. Have a great school year ahead!', 'general', 'medium', 1, NOW(), 1, NOW()),
('Fire Drill Schedule', 'There will be a mandatory fire drill on Saturday, August 10, 2024 at 10:00 AM. All residents must participate. Please review the evacuation plan posted on each floor.', 'urgent', 'high', 1, NOW(), 1, NOW()),
('Water Maintenance Notice', 'Water supply will be temporarily unavailable on Sunday, August 11, 2024 from 6:00 AM to 12:00 PM due to pipe maintenance. Please store water in advance.', 'maintenance', 'high', 1, NOW(), 1, NOW()),
('Study Room Now Available', 'The new study room on the 5th floor is now open for use. It is available from 7:00 AM to 10:00 PM daily. First come, first served.', 'general', 'low', 1, NOW(), 1, NOW()),
('Monthly Meeting Reminder', 'Monthly residents meeting this Friday at 7:00 PM in the common area. Agenda includes house rules review and upcoming events.', 'general', 'medium', 1, NOW(), 1, NOW());

-- ============================================================
-- SEED DATA: Gallery
-- ============================================================
INSERT INTO `gallery` (`title`, `description`, `image_path`, `category`, `sort_order`, `status`) VALUES
('Building Exterior', 'Our modern boarding house building', 'gallery_1.jpg', 'building', 1, 'active'),
('Reception Area', 'Welcoming reception and lobby', 'gallery_2.jpg', 'interior', 2, 'active'),
('Single Bedspace', 'Cozy single bedspace setup', 'gallery_3.jpg', 'rooms', 3, 'active'),
('Shared Bedspace', 'Spacious shared bedspace', 'gallery_4.jpg', 'rooms', 4, 'active'),
('Study Area', 'Dedicated study spaces', 'gallery_5.jpg', 'facilities', 5, 'active'),
('Kitchen', 'Shared kitchen facilities', 'gallery_6.jpg', 'facilities', 6, 'active'),
('Common Area', 'Comfortable common area', 'gallery_7.jpg', 'facilities', 7, 'active'),
('Roof Deck', 'Rooftop relaxation area', 'gallery_8.jpg', 'facilities', 8, 'active'),
('Bathroom', 'Clean bathroom facilities', 'gallery_9.jpg', 'facilities', 9, 'active'),
('Laundry Area', 'On-site laundry facilities', 'gallery_10.jpg', 'facilities', 10, 'active'),
('Parking Area', 'Secure parking space', 'gallery_11.jpg', 'facilities', 11, 'active'),
('Garden View', 'Peaceful garden area', 'gallery_12.jpg', 'exterior', 12, 'active');

-- ============================================================
-- SEED DATA: Testimonials
-- ============================================================
INSERT INTO `testimonials` (`student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`) VALUES
('Maria Santos', 'BS Business Administration, DLSU', 'The boarding house exceeded my expectations. The rooms are clean, the staff is friendly, and the location is perfect for my university. I highly recommend this to all students!', 5, 1, 'active'),
('Juan Dela Cruz', 'BS Computer Science, UST', 'Great value for money! The Wi-Fi is fast, the study areas are quiet, and the security is excellent. I feel safe and comfortable here.', 5, 1, 'active'),
('Pedro Garcia', 'BS IT, FEU', 'I love the community here. Met amazing friends and the management is very responsive to our needs. The monthly rates are very affordable.', 4, 1, 'active'),
('Ana Reyes', 'BS Nursing, UE', 'The location is very convenient - just a few minutes walk to my university. The rooms are well-maintained and the common areas are always clean.', 5, 0, 'active'),
('Carlos Mendoza', 'BS Mechanical Engineering, PUP', 'Best decision I made was to stay here. The facilities are top-notch and the management truly cares about the students welfare.', 5, 1, 'active');

-- ============================================================
-- SEED DATA: FAQs
-- ============================================================
INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`) VALUES
('How do I reserve a room?', 'You can reserve a room by creating an account on our website, browsing available rooms, and submitting a reservation request. Our team will review your application and get back to you within 24-48 hours.', 'reservation', 1, 'active'),
('What are the payment methods accepted?', 'We accept Cash, GCash, and Walk-In Payments. Payments can be made directly at the boarding house office or through GCash for a fast, secure, and convenient transaction.', 'payment', 2, 'active'),
('Is there a security deposit?', 'Yes, a security deposit equivalent to one month rent is required. This is refundable upon checkout, subject to room inspection and deduction for any damages.', 'payment', 3, 'active'),
('What is included in the rent?', 'Monthly rent includes Water, electricity (within reasonable use), water, basic cleaning of common areas, and 24/7 security.', 'general', 4, 'active'),
('Can I terminate my lease early?', 'Early termination requires a 30-day written notice. A penalty fee may apply as outlined in the lease agreement. Your security deposit will be subject to standard deduction procedures.', 'policy', 5, 'active'),
('Are visitors allowed?', 'Visitors are allowed from 7:00 AM to 9:00 PM only. They must register at the reception and are not permitted to stay overnight without prior approval from management.', 'policy', 6, 'active'),
('What happens if I damage something?', 'Any damage to the room or facilities beyond normal wear and tear will be deducted from your security deposit. For major damages, additional charges may apply.', 'policy', 7, 'active'),
('Is there parking available?', 'Yes, we have designated parking spaces for residents. Parking is available on a first-come, first-served basis for a minimal additional fee.', 'general', 8, 'active'),
('How do I report maintenance issues?', 'You can submit a maintenance request through your student dashboard or visit the management office. For urgent issues, you may call our hotline directly.', 'general', 9, 'active'),
('What are the house rules?', 'Key rules include: quiet hours from 10PM to 7AM, no smoking inside the building, no pets, visitors allowed until 9PM, and maintaining cleanliness in shared areas.', 'policy', 10, 'active');

-- ============================================================
-- SEED DATA: Maintenance Requests
-- ============================================================
INSERT INTO `maintenance_requests` (`request_code`, `student_id`, `room_id`, `title`, `description`, `category`, `priority`, `status`, `admin_response`, `created_at`) VALUES
('MNT-2024-001', 1, 2, 'Broken Aircon', 'The air conditioning unit is making unusual noises and not cooling properly since yesterday.', 'appliance', 'high', 'in_progress', 'Technician has been scheduled for tomorrow morning.', '2024-07-15 09:00:00'),
('MNT-2024-002', 2, 4, 'Leaking Faucet', 'The bathroom faucet has been dripping continuously for two days.', 'plumbing', 'medium', 'resolved', 'Faucet has been repaired. Please let us know if the issue persists.', '2024-07-10 14:00:00'),
('MNT-2024-003', 3, 5, 'Loose Electrical Outlet', 'One of the electrical outlets near the desk is loose and sometimes sparks.', 'electrical', 'urgent', 'pending', NULL, '2024-07-20 08:30:00');

-- ============================================================
-- SEED DATA: Complaints
-- ============================================================
INSERT INTO `complaints` (`complaint_code`, `student_id`, `subject`, `description`, `category`, `severity`, `status`, `admin_response`, `created_at`) VALUES
('CMP-2024-001', 1, 'Noise from neighboring room', 'The room next door plays loud music late at night, disturbing my sleep and study time.', 'noise', 'medium', 'under_review', 'We have spoken with the residents of the adjacent room. Please let us know if this continues.', '2024-07-12 22:00:00'),
('CMP-2024-002', 3, 'Dirty Common Kitchen', 'The common kitchen has not been cleaned properly. There are dirty dishes left by other residents.', 'cleanliness', 'medium', 'resolved', 'Cleaning schedule has been updated and additional cleaning staff assigned.', '2024-07-08 10:00:00');

-- ============================================================
-- SEED DATA: Feedback
-- ============================================================
INSERT INTO `feedback` (`student_id`, `name`, `email`, `subject`, `message`, `rating`, `category`, `status`) VALUES
(1, 'Juan Dela Cruz', 'juan.delacruz@student.edu', 'Great Experience Overall', 'The boarding house has been a wonderful place to stay. The management is very responsive and the facilities are well-maintained. Keep up the good work!', 5, 'compliment', 'new'),
(2, 'Maria Santos', 'maria.santos@student.edu', 'Suggestion for Improvement', 'It would be great if the study room on the 5th floor could be extended to be open 24/7 for students who study late. Maybe add a coffee machine too!', 4, 'suggestion', 'new'),
(3, 'Pedro Garcia', 'pedro.garcia@student.edu', 'Feedback on Cleaning Service', 'The regular cleaning service is excellent! My room is always clean and tidy. Thank you to the housekeeping team.', 5, 'compliment', 'read');

-- ============================================================
-- SEED DATA: Contact Messages
-- ============================================================
INSERT INTO `contact_messages` (`name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
('John Lim', 'john.lim@email.com', '+63 926 123 4567', 'Bedspace Inquiry', 'Good day! I would like to inquire about available bedspaces for the upcoming semester. Do you still have slots available?', 'new'),
('Sarah Pacquiao', 'sarah.p@email.com', '+63 927 234 5678', 'Tour Request', 'Hi! Can I schedule a tour of your facilities this weekend? I am interested in your premium bedspaces.', 'new');

-- ============================================================
-- SEED DATA: Notifications
-- ============================================================
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(4, 'Reservation Approved', 'Your reservation for Room 202 (Scholar''s Bedspace) has been approved.', 'reservation', 0, NOW()),
(4, 'Payment Reminder', 'Your monthly rent for August is due on August 1, 2024.', 'payment', 1, NOW()),
(5, 'Reservation Approved', 'Your reservation for Room 401 (Community Bedspace) has been approved.', 'reservation', 0, NOW()),
(1, 'New Maintenance Request', 'A new maintenance request has been submitted by Juan Dela Cruz.', 'maintenance', 0, NOW()),
(2, 'New Complaint', 'A complaint has been filed regarding noise from neighboring room.', 'complaint', 0, NOW()),
(4, 'New Announcement', 'Welcome to the new school year! Check out our latest announcements.', 'announcement', 0, NOW());

-- ============================================================
-- SEED DATA: Activity Logs
-- ============================================================
INSERT INTO `activity_logs` (`user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 'login', 'Super Admin logged in', '127.0.0.1', NOW()),
(2, 'login', 'Manager logged in', '127.0.0.1', NOW()),
(4, 'login', 'Student logged in', '127.0.0.1', NOW()),
(1, 'create_room', 'Created new room: Sunrise Single', '127.0.0.1', NOW()),
(2, 'approve_reservation', 'Approved reservation RES-2024-001', '127.0.0.1', NOW());

-- ============================================================
-- SEED DATA: Team Members
-- ============================================================
INSERT INTO `team_members` (`name`, `role`, `description`, `facebook_url`, `twitter_url`, `linkedin_url`, `sort_order`, `status`) VALUES
('Juan Dela Cruz', 'General Manager', 'Oversees all operations and ensures quality service delivery.', '', '', '', 1, 'active'),
('Maria Santos', 'Operations Manager', 'Manages daily operations and ensures smooth boarding house functions.', '', '', '', 2, 'active'),
('Jose Reyes', 'Student Relations', 'Handles student concerns, reservations, and community engagement.', '', '', '', 3, 'active'),
('Ana Garcia', 'Maintenance Lead', 'Ensures the facility is well-maintained, clean, and in top condition.', '', '', '', 4, 'active');

-- ============================================================
-- SEED DATA: About Values
-- ============================================================
INSERT INTO `about_values` (`icon`, `title`, `description`, `color`, `sort_order`, `status`) VALUES
('fas fa-hand-holding-heart', 'Integrity', 'We operate with honesty and transparency in all our dealings with students and staff.', '#2563eb', 1, 'active'),
('fas fa-star', 'Excellence', 'We strive for excellence in the quality of our accommodations and services.', '#f59e0b', 2, 'active'),
('fas fa-people-group', 'Community', 'We foster a sense of community and belonging among all our residents.', '#22c55e', 3, 'active'),
('fas fa-lightbulb', 'Innovation', 'We continuously improve our services and embrace modern solutions for student living.', '#8b5cf6', 4, 'active');

COMMIT;
