-- Migration: Add About Page tables and settings
-- Run this SQL if you already have the database created

-- New tables
CREATE TABLE IF NOT EXISTS `team_members` (
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

CREATE TABLE IF NOT EXISTS `about_values` (
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

-- Seed data: Team Members
INSERT INTO `team_members` (`name`, `role`, `description`, `facebook_url`, `twitter_url`, `linkedin_url`, `sort_order`, `status`) VALUES
('Juan Dela Cruz', 'General Manager', 'Oversees all operations and ensures quality service delivery.', '', '', '', 1, 'active'),
('Maria Santos', 'Operations Manager', 'Manages daily operations and ensures smooth boarding house functions.', '', '', '', 2, 'active'),
('Jose Reyes', 'Student Relations', 'Handles student concerns, reservations, and community engagement.', '', '', '', 3, 'active'),
('Ana Garcia', 'Maintenance Lead', 'Ensures the facility is well-maintained, clean, and in top condition.', '', '', '', 4, 'active');

-- Seed data: About Values
INSERT INTO `about_values` (`icon`, `title`, `description`, `color`, `sort_order`, `status`) VALUES
('fas fa-hand-holding-heart', 'Integrity', 'We operate with honesty and transparency in all our dealings with students and staff.', '#2563eb', 1, 'active'),
('fas fa-star', 'Excellence', 'We strive for excellence in the quality of our accommodations and services.', '#f59e0b', 2, 'active'),
('fas fa-people-group', 'Community', 'We foster a sense of community and belonging among all our residents.', '#22c55e', 3, 'active'),
('fas fa-lightbulb', 'Innovation', 'We continuously improve our services and embrace modern solutions for student living.', '#8b5cf6', 4, 'active');

-- Insert about page settings (skip if already exists)
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('about_title', 'About Us', 'text', 'About page title'),
('about_subtitle', 'Learn more about Alondes Dorm.', 'text', 'About page subtitle'),
('about_story_title', 'Welcome to Alondes Dorm', 'text', 'About story section title'),
('about_story_text', 'Founded with the vision of providing safe, comfortable, and affordable accommodation for students, Alondes Dorm has been a trusted home for hundreds of students pursuing their academic dreams.||We understand the challenges students face when looking for the right place to stay while studying. That''s why we''ve created a community-oriented living space that feels like home.', 'text', 'About story text (use || for paragraphs)'),
('about_years_label', '5+ Years', 'text', 'Years badge label'),
('about_years_sublabel', 'of Service', 'text', 'Years badge sublabel'),
('about_mission', 'To provide affordable, safe, and comfortable boarding house accommodations that support students in their academic journey. We strive to create a nurturing environment where students can thrive, build lasting friendships, and focus on their studies without the worry of their living arrangements.', 'text', 'Mission statement'),
('about_vision', 'To be the most trusted and preferred student dormitory, known for our commitment to quality service, student welfare, and community building. We envision a network of modern, well-maintained dormitories that set the standard for student living accommodations across the country.', 'text', 'Vision statement'),
('about_team_title', 'Meet Our Team', 'text', 'Team section title'),
('about_team_subtitle', 'The dedicated people behind Alondes Dorm.', 'text', 'Team section subtitle'),
('about_values_title', 'What We Stand For', 'text', 'Values section title'),
('about_text', '', 'textarea', 'Short intro text shown above the story section on the About page'),
('about_image', '', 'file', 'About page story image');
