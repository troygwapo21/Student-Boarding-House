-- Student Boarding House Backup
-- Date: 2026-07-19 12:03:59

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `about_values`;
CREATE TABLE `about_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(100) NOT NULL DEFAULT 'fas fa-star',
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#2563eb',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_about_values_status` (`status`),
  KEY `idx_about_values_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `about_values` (`id`, `icon`, `title`, `description`, `color`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('1', 'fas fa-hand-holding-heart', 'Integrity', 'We operate with honesty and transparency in all our dealings with students and staff.', '#2563eb', '1', 'active', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `about_values` (`id`, `icon`, `title`, `description`, `color`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('2', 'fas fa-star', 'Excellence', 'We strive for excellence in the quality of our accommodations and services.', '#f59e0b', '2', 'active', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `about_values` (`id`, `icon`, `title`, `description`, `color`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('3', 'fas fa-people-group', 'Community', 'We foster a sense of community and belonging among all our residents.', '#22c55e', '3', 'active', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `about_values` (`id`, `icon`, `title`, `description`, `color`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('4', 'fas fa-lightbulb', 'Innovation', 'We continuously improve our services and embrace modern solutions for student living.', '#8b5cf6', '4', 'active', '2026-07-15 17:11:27', '2026-07-15 17:11:27');

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=352 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', '1', 'login', 'Super Admin logged in', '127.0.0.1', NULL, '2026-07-14 19:40:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', '2', 'login', 'Manager logged in', '127.0.0.1', NULL, '2026-07-14 19:40:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', '4', 'login', 'Student logged in', '127.0.0.1', NULL, '2026-07-14 19:40:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', '1', 'create_room', 'Created new room: Sunrise Single', '127.0.0.1', NULL, '2026-07-14 19:40:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('5', '2', 'approve_reservation', 'Approved reservation RES-2024-001', '127.0.0.1', NULL, '2026-07-14 19:40:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('6', NULL, 'register', 'New student registered: wiljohnjumantoc58@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:17:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('7', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:17:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('8', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:25:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('9', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:26:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('10', '1', 'delete_room', 'Room 101 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:27:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('11', '1', 'delete_room', 'Room 103 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:27:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('12', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:28:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('13', '1', 'approve_reservation', 'Reservation RES-2024-006 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:29:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('14', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:47:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('15', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:48:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('16', '2', 'update_profile', 'Manager profile updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:50:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('17', '2', 'delete_room', 'Room 302 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:51:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('18', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:59:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('19', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 20:59:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('20', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:02:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('21', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:02:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('22', '1', 'create_room', 'Room 105 created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:06:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('23', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:06:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('24', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:21:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('25', '2', 'update_room', 'Room 105 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:22:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('26', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:22:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('27', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:22:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('28', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:24:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('29', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:24:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('30', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:27:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('31', '1', 'delete_gallery', 'Gallery item #1 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:27:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('32', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:27:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('33', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:27:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('34', '9', 'create_feedback', 'Feedback submitted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:30:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('35', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:30:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('36', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:30:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('37', '1', 'reply_feedback', 'Replied to feedback #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:31:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('38', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:31:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('39', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:31:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('40', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:32:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('41', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:33:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('42', '9', 'create_reservation', 'Reservation RES-2026-501691 created for room 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:41:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('43', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:41:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('44', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:41:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('45', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:50:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('46', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 21:51:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('47', '2', 'approve_reservation', 'Reservation RES-2026-501691 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:03:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('48', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:03:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('49', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:03:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('50', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:04:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('51', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:04:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('52', '9', 'cancel_reservation', 'Reservation RES-2026-501691 cancelled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:05:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('53', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:05:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('54', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:05:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('55', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:05:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('56', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:08:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('57', '1', 'update_room', 'Room 105 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:08:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('58', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:08:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('59', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:09:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('60', '1', 'update_room', 'Room 105 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:09:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('61', '1', 'replace_room_image', 'Room image #1 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-14 22:42:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('62', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 08:20:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('63', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:01:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('64', '1', 'delete_amenity', 'Amenity #10 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('65', '1', 'delete_amenity', 'Amenity #2 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('66', '1', 'delete_amenity', 'Amenity #3 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('67', '1', 'delete_amenity', 'Amenity #1 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('68', '1', 'delete_amenity', 'Amenity #6 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('69', '1', 'delete_amenity', 'Amenity #14 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('70', '1', 'delete_amenity', 'Amenity #7 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('71', '1', 'delete_amenity', 'Amenity #5 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:07:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('72', '1', 'delete_amenity', 'Amenity #4 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('73', '1', 'delete_gallery', 'Gallery item #2 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('74', '1', 'delete_gallery', 'Gallery item #3 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('75', '1', 'delete_gallery', 'Gallery item #4 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('76', '1', 'delete_gallery', 'Gallery item #5 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('77', '1', 'delete_gallery', 'Gallery item #6 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('78', '1', 'delete_gallery', 'Gallery item #7 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('79', '1', 'delete_gallery', 'Gallery item #12 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('80', '1', 'delete_gallery', 'Gallery item #8 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('81', '1', 'delete_gallery', 'Gallery item #9 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('82', '1', 'delete_gallery', 'Gallery item #10 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('83', '1', 'delete_gallery', 'Gallery item #11 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:08:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('84', '1', 'create_gallery', 'Gallery item: Room 101', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:09:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('85', '1', 'create_gallery', 'Gallery item: Bathroom', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:10:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('86', '1', 'delete_announcement', 'Announcement #1 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:10:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('87', '1', 'delete_announcement', 'Announcement #2 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:11:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('88', '1', 'delete_announcement', 'Announcement #3 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:11:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('89', '1', 'delete_announcement', 'Announcement #4 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:11:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('90', '1', 'delete_announcement', 'Announcement #5 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:11:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('91', '1', 'create_announcement', 'Announcement: Especial Reminder!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:12:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('92', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:12:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('93', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:12:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('94', '2', 'create_announcement', 'Announcement: Sa wala pa ka bayad!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:13:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('95', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:13:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('96', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:14:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('97', '9', 'update_profile', 'Student profile updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:15:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('98', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 09:16:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('99', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 13:52:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('100', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 17:12:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('101', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 17:16:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('102', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 17:16:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('103', '2', 'create_gallery', 'Gallery item: beedspacer', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-15 17:36:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('104', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 13:36:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('105', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 17:54:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('106', '1', 'create_gallery', 'Gallery item: eme eme lang', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:15:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('107', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:17:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('108', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:18:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('109', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:18:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('110', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:18:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('111', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:18:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('112', '1', 'delete_room_image', 'Room image #5 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:20:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('113', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:20:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('114', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:20:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('115', '1', 'delete_room_image', 'Room image #4 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:20:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('116', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:20:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('117', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('118', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('119', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('120', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('121', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('122', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:21:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('123', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:22:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('124', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:31:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('125', '1', 'update_room', 'Room 102 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:36:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('126', '1', 'replace_room_image', 'Room image #2 replaced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:36:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('127', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:37:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('128', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:38:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('129', '1', 'add_room_image', 'Image added after #2 to room #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:57:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('130', '1', 'add_room_image', 'Image added after #6 to room #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:57:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('131', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:57:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('132', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 18:58:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('133', '1', 'delete_room', 'Room 201 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('134', '1', 'delete_room', 'Room 601 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('135', '1', 'delete_room', 'Room 502 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('136', '1', 'delete_room', 'Room 501 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('137', '1', 'delete_room', 'Room 402 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('138', '1', 'delete_room', 'Room 401 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('139', '1', 'delete_room', 'Room 301 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('140', '1', 'delete_room', 'Room 203 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('141', '1', 'delete_room', 'Room 202 deleted (active reservations auto-cancelled)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:11:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('142', '1', 'delete_reservation', 'Reservation RES-2024-001 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 19:53:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('143', '1', 'delete_reservation', 'Reservation RES-2026-501691 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:09:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('144', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:09:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('145', NULL, 'register', 'New student registered: wiljohnjumantoc24@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:11:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('146', '10', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:11:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('147', '10', 'create_reservation', 'Reservation RES-2026-912865 created for room 102', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:12:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('148', '10', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:13:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('149', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:13:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('150', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:13:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('151', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:13:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('152', '9', 'create_reservation', 'Reservation RES-2026-547612 created for room 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:15:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('153', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:15:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('154', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:15:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('155', '1', 'approve_reservation', 'Reservation RES-2026-547612 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:16:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('156', '1', 'delete_reservation', 'Reservation RES-2026-547612 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:16:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('157', '1', 'reject_reservation', 'Reservation RES-2026-912865 rejected', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:16:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('158', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:16:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('159', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:16:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('160', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:17:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('161', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:17:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('162', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:32:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('163', '10', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:33:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('164', '10', 'create_reservation', 'Reservation RES-2026-584953 created for room 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:33:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('165', '10', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:34:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('166', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:34:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('167', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:34:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('168', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:34:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('169', '9', 'create_reservation', 'Reservation RES-2026-997777 created for room 102', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:35:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('170', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:35:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('171', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:35:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('172', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:44:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('173', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:44:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('174', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:44:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('175', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:44:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('176', '9', 'cancel_reservation', 'Reservation RES-2026-997777 cancelled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:45:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('177', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:45:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('178', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:45:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('179', '1', 'delete_reservation', 'Reservation RES-2026-997777 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:46:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('180', '1', 'reject_reservation', 'Reservation RES-2026-584953 rejected', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:46:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('181', '1', 'delete_reservation', 'Reservation RES-2026-584953 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:47:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('182', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:47:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('183', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:47:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('184', '9', 'create_reservation', 'Reservation RES-2026-716701 created for room 102', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:48:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('185', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:48:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('186', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 20:48:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('187', '1', 'approve_reservation', 'Reservation RES-2026-716701 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:38:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('188', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:38:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('189', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:38:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('190', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:38:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('191', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:38:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('192', '1', 'update_student_status', 'Student John paul Espliguera status: active → active', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:39:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('193', '1', 'delete_payment', 'Payment PAY-2024-005 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('194', '1', 'delete_payment', 'Payment PAY-2024-013 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('195', '1', 'delete_payment', 'Payment PAY-2024-015 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('196', '1', 'delete_payment', 'Payment PAY-2024-011 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('197', '1', 'delete_payment', 'Payment PAY-2024-014 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('198', '1', 'delete_payment', 'Payment PAY-2024-004 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('199', '1', 'delete_payment', 'Payment PAY-2024-012 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('200', '1', 'delete_payment', 'Payment PAY-2024-009 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('201', '1', 'delete_payment', 'Payment PAY-2024-010 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('202', '1', 'delete_payment', 'Payment PAY-2024-006 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('203', '1', 'delete_payment', 'Payment PAY-2024-007 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('204', '1', 'delete_payment', 'Payment PAY-2024-008 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('205', '1', 'delete_payment', 'Payment PAY-2024-001 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:55:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('206', '1', 'create_gallery', 'Gallery item: wws', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:56:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('207', '1', 'update_amenity', 'Amenity updated: Balcony', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 21:56:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('208', '1', 'update_settings', 'System settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 22:08:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('209', '1', 'delete_feedback', 'Feedback \"Feedback on Cleaning Service\" deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 22:09:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('210', '1', 'delete_feedback', 'Feedback \"Great Experience Overall\" deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 22:09:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('211', '1', 'delete_feedback', 'Feedback \"Suggestion for Improvement\" deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 22:09:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('212', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-16 22:09:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('213', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 16:59:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('214', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:02:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('215', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:02:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('216', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:13:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('217', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:20:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('218', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:21:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('219', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:21:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('220', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:22:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('221', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 17:22:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('222', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:18:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('223', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:33:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('224', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:33:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('225', '9', 'submit_payment', 'Payment PAY-2026-882145 submitted by student Wiljohn Jumantoc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:44:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('226', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:45:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('227', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:45:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('228', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:46:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('229', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:46:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('230', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:56:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('231', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:56:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('232', '1', 'delete_student', 'Student Juan Dela Cruz (juan.delacruz@student.edu) deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:57:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('233', '1', 'delete_student', 'Student Carlos Mendoza (carlos.mendoza@student.edu) deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:57:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('234', '1', 'delete_student', 'Student Ana Reyes (ana.reyes@student.edu) deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:57:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('235', '1', 'delete_student', 'Student Pedro Garcia (pedro.garcia@student.edu) deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:57:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('236', '1', 'delete_student', 'Student Maria Santos (maria.santos@student.edu) deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:57:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('237', '1', 'delete_payment', 'Payment PAY-2026-882145 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 18:58:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('238', '1', 'update_settings', 'System settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:01:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('239', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:01:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('240', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:01:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('241', '9', 'cancel_reservation', 'Reservation RES-2026-716701 cancelled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:03:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('242', '9', 'create_reservation', 'Reservation RES-2026-664608 created for room 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:04:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('243', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('244', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('245', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('246', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('247', '2', 'approve_reservation', 'Reservation RES-2026-664608 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('248', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:05:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('249', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:06:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('250', '9', 'submit_payment', 'Payment PAY-2026-839863 submitted by student Wiljohn Jumantoc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:08:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('251', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:08:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('252', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:08:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('253', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:08:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('254', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:08:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('255', '2', 'verify_payment', 'Payment PAY-2026-839863 paid', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:18:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('256', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:18:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('257', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:18:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('258', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:21:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('259', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-18 19:23:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('260', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:13:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('261', '1', 'delete_team_member', 'Team member #1 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:14:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('262', '1', 'delete_team_member', 'Team member #2 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:14:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('263', '1', 'delete_team_member', 'Team member #3 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:15:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('264', '1', 'delete_team_member', 'Team member #4 deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:15:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('265', '1', 'add_team_member', 'Team member added: Villeganio Warlito', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:25:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('266', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:26:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('267', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:27:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('268', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:29:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('269', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:33:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('270', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:36:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('271', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:45:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('272', '1', 'update_manager', 'Manager updated: warlitovelliganio@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:45:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('273', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:45:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('274', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:45:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('275', '2', 'change_password', 'Password changed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:46:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('276', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:47:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('277', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:48:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('278', '1', 'update_manager', 'Manager updated: warlitovilleganio@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:49:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('279', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:49:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('280', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 07:49:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('281', '2', 'add_room_image', 'Image added after #3 to room #14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:00:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('282', '2', 'add_room_image', 'Image added after #8 to room #14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:00:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('283', '2', 'add_room_image', 'Image added after #9 to room #14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:01:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('284', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:03:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('285', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:08:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('286', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:14:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('287', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:14:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('288', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:20:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('289', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:28:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('290', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:31:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('291', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:31:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('292', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:32:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('293', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 08:47:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('294', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 10:57:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('295', '1', 'delete_contact_message', 'Contact message from Sarah Pacquiao deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 10:57:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('296', '1', 'delete_contact_message', 'Contact message from John Lim deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 10:57:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('297', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:05:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('298', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:05:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('299', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:05:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('300', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:05:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('301', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:05:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('302', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:06:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('303', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:08:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('304', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:11:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('305', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:11:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('306', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:11:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('307', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:11:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('308', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:14:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('309', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:14:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('310', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:15:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('311', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:16:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('312', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:16:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('313', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:16:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('314', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:19:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('315', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:20:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('316', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:20:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('317', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:21:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('318', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:21:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('319', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:25:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('320', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:25:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('321', '9', 'create_maintenance', 'Maintenance request MNT-2026-517123 created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:27:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('322', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:27:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('323', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:27:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('324', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:27:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('325', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:27:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('326', '1', 'update_maintenance', 'Maintenance MNT-2026-517123 updated to in_progress', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:32:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('327', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:32:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('328', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:32:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('329', '9', 'create_complaint', 'Complaint CMP-2026-684429 created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:34:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('330', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:34:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('331', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:34:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('332', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:38:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('333', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:38:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('334', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:39:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('335', '9', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:39:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('336', '9', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:40:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('337', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:40:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('338', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:46:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('339', '2', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:46:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('340', '2', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:46:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('341', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:47:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('342', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:56:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('343', '10', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:57:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('344', '10', 'create_reservation', 'Reservation RES-2026-177800 created for room 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:57:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('345', '10', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:57:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('346', '10', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:58:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('347', '10', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:58:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('348', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:58:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('349', '1', 'approve_reservation', 'Reservation RES-2026-177800 approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:58:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('350', '1', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:58:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES ('351', '1', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-19 11:59:06');

DROP TABLE IF EXISTS `amenities`;
CREATE TABLE `amenities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('8', 'Kitchen Access', 'fa-utensils', 'Shared kitchen facilities', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('9', 'Common Area', 'fa-couch', 'Shared living/recreation area', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('11', 'CCTV Surveillance', 'fa-video', 'Closed-circuit television monitoring', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('12', 'Parking Space', 'fa-parking', 'Designated parking area', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('13', 'Water Dispenser', 'fa-tint', 'Free drinking water dispenser', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('15', 'Cleaning Service', 'fa-broom', 'Regular room cleaning service', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('16', 'Bed Linens', 'fa-bed', 'Provided bed sheets and pillow', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('17', 'Electrical Outlet', 'fa-plug', 'Ample power outlets', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `amenities` (`id`, `name`, `icon`, `description`, `status`, `created_at`, `updated_at`) VALUES ('18', 'Balcony', 'fa-door-open', 'Private balcony', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('general','important','urgent','maintenance','event') NOT NULL DEFAULT 'general',
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_announcements_published` (`is_published`),
  KEY `idx_announcements_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `announcements` (`id`, `title`, `content`, `type`, `priority`, `is_published`, `published_at`, `expires_at`, `created_by`, `created_at`, `updated_at`) VALUES ('6', 'Especial Reminder!', 'Always observe a cleanliness', 'general', 'high', '1', '2026-07-15 09:12:27', NULL, '1', '2026-07-15 09:12:27', '2026-07-15 09:12:27');
INSERT INTO `announcements` (`id`, `title`, `content`, `type`, `priority`, `is_published`, `published_at`, `expires_at`, `created_by`, `created_at`, `updated_at`) VALUES ('7', 'Sa wala pa ka bayad!', 'Pamayad na kamo para dili kamo ma dak an!', 'important', 'critical', '1', '2026-07-15 09:13:53', NULL, '2', '2026-07-15 09:13:53', '2026-07-15 09:13:53');

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(10) unsigned DEFAULT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_table` (`table_name`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE `complaints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `complaint_code` varchar(20) NOT NULL,
  `student_id` int(10) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('noise','cleanliness','security','roommate','management','other') NOT NULL DEFAULT 'other',
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','under_review','resolved','closed') NOT NULL DEFAULT 'open',
  `admin_response` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_complaints_code` (`complaint_code`),
  KEY `idx_complaints_student` (`student_id`),
  KEY `idx_complaints_status` (`status`),
  CONSTRAINT `fk_complaints_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `complaints` (`id`, `complaint_code`, `student_id`, `subject`, `description`, `category`, `severity`, `status`, `admin_response`, `resolved_at`, `created_at`, `updated_at`) VALUES ('3', 'CMP-2026-684429', '6', 'visitor', 'grave ka langas', 'roommate', 'high', 'open', NULL, NULL, '2026-07-19 11:34:25', '2026-07-19 11:34:25');

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_response`, `created_at`, `updated_at`) VALUES ('3', 'John paul Espliguera Espliguera', 'johnpaul@gmail.com', '09679936511', 'Room Inquiry', 'u have a available room?', 'new', NULL, '2026-07-19 08:28:38', '2026-07-19 08:28:38');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_response`, `created_at`, `updated_at`) VALUES ('4', 'Wiljohn Jumantoc', 'wiljohnjumantoc58@gmail.com', '09679936511', 'Room Inquiry', 'dshgsd', 'new', NULL, '2026-07-19 10:57:05', '2026-07-19 10:57:05');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_response`, `created_at`, `updated_at`) VALUES ('5', 'John paul Espliguera Espliguera', 'wiljohnjumantoc58@gmail.com', '09679936511', 'Payment', 'wrwefsdfd', 'new', NULL, '2026-07-19 11:40:48', '2026-07-19 11:40:48');

DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('1', 'How do I reserve a room?', 'You can reserve a room by creating an account on our website, browsing available rooms, and submitting a reservation request. Our team will review your application and get back to you within 24-48 hours.', 'reservation', '1', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('2', 'What are the payment methods accepted?', 'We accept cash, bank transfer, GCash, Maya (PayMaya), and other digital payment methods. Payments can be made at our office or online through our payment portal.', 'payment', '2', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('3', 'Is there a security deposit?', 'Yes, a security deposit equivalent to one month rent is required. This is refundable upon checkout, subject to room inspection and deduction for any damages.', 'payment', '3', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('4', 'What is included in the rent?', 'Monthly rent includes Wi-Fi, electricity (within reasonable use), water, basic cleaning of common areas, and 24/7 security. Individual room amenities may vary.', 'general', '4', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('5', 'Can I terminate my lease early?', 'Early termination requires a 30-day written notice. A penalty fee may apply as outlined in the lease agreement. Your security deposit will be subject to standard deduction procedures.', 'policy', '5', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('6', 'Are visitors allowed?', 'Visitors are allowed from 7:00 AM to 9:00 PM only. They must register at the reception and are not permitted to stay overnight without prior approval from management.', 'policy', '6', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('7', 'What happens if I damage something?', 'Any damage to the room or facilities beyond normal wear and tear will be deducted from your security deposit. For major damages, additional charges may apply.', 'policy', '7', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('8', 'Is there parking available?', 'Yes, we have designated parking spaces for residents. Parking is available on a first-come, first-served basis for a minimal additional fee.', 'general', '8', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('9', 'How do I report maintenance issues?', 'You can submit a maintenance request through your student dashboard or visit the management office. For urgent issues, you may call our hotline directly.', 'general', '9', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('10', 'What are the house rules?', 'Key rules include: quiet hours from 10PM to 7AM, no smoking inside the building, no pets, visitors allowed until 9PM, and maintaining cleanliness in shared areas.', 'policy', '10', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `category` enum('suggestion','compliment','complaint','inquiry','other') NOT NULL DEFAULT 'other',
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_feedback_student` (`student_id`),
  KEY `idx_feedback_status` (`status`),
  CONSTRAINT `fk_feedback_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `feedback` (`id`, `student_id`, `name`, `email`, `subject`, `message`, `rating`, `category`, `status`, `admin_response`, `created_at`, `updated_at`) VALUES ('4', '6', 'Wiljohn Jumantoc', 'wiljohnjumantoc58@gmail.com', 'ka dorm', 'mga damak akon ka dorm', '3', 'other', 'replied', 'kalma lang sir', '2026-07-14 21:30:19', '2026-07-14 21:31:13');

DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('13', 'Room 101', 'nature view', 'gallery/6a56dde059bf3_744437476_2431905627298687_6879462106645094742_n.jpg', 'rooms', '1', 'active', '2026-07-15 09:09:52', '2026-07-15 09:09:52');
INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('14', 'Bathroom', 'clean', 'gallery/6a56de0dbd0bb_743496830_1002576495874226_8439245135865046701_n.jpg', 'facilities', '2', 'active', '2026-07-15 09:10:37', '2026-07-15 09:10:37');
INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('15', 'beedspacer', 'goods for 2 person', 'gallery/6a5754a6f3f38_744485888_1332327229082235_4498440532453830251_n.jpg', 'rooms', '3', 'active', '2026-07-15 17:36:39', '2026-07-15 17:36:39');
INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('16', 'eme eme lang', 'Wala rajod', 'gallery/6a58af46d77ba_2b10cc69-a560-45c9-8af6-64699f017e75.jpg', 'events', '4', 'active', '2026-07-16 18:15:34', '2026-07-16 18:15:34');
INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `category`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('17', 'wws', 'sdfas', 'gallery/6a58e312555de_44864ef1-53e6-4c6b-bfcb-36cb447807c2.jpg', 'amenities', '5', 'active', '2026-07-16 21:56:34', '2026-07-16 21:56:34');

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_email` (`email`),
  KEY `idx_login_ip` (`ip_address`),
  KEY `idx_login_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('1', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 20:17:57');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('2', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 20:26:14');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('3', 'manager@studentboardinghouse.com', '::1', '0', '2026-07-14 20:47:39');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('4', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-14 20:48:04');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('5', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 20:59:52');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('6', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 21:02:56');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('7', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-14 21:21:56');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('8', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 21:22:52');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('9', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 21:24:33');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('10', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 21:27:57');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('11', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 21:30:29');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('12', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 21:31:26');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('13', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 21:33:06');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('14', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 21:41:57');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('15', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-14 21:51:01');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('16', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-14 22:03:55');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('17', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-14 22:04:59');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('18', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-14 22:05:20');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('19', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 22:08:13');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('20', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-14 22:09:16');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('21', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-15 08:20:15');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('22', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-15 09:01:36');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('23', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-15 09:12:43');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('24', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-15 09:14:04');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('25', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-15 13:52:23');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('26', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-15 17:12:20');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('27', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-15 17:16:42');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('28', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-16 13:36:34');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('29', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 17:54:54');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('30', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 18:38:23');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('31', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 18:58:20');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('32', 'wiljohnjumantoc24@gmail.com', '::1', '0', '2026-07-16 20:10:22');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('33', 'wiljohnjumantoc24@gmail.com', '::1', '0', '2026-07-16 20:10:30');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('34', 'wiljohnjumantoc24@gmail.com', '::1', '1', '2026-07-16 20:11:36');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('35', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:13:11');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('36', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 20:13:53');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('37', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:15:24');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('38', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 20:16:44');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('39', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:17:52');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('40', 'wiljohnjumantoc24@gmail.com', '::1', '1', '2026-07-16 20:33:12');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('41', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:34:05');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('42', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 20:34:57');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('43', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:35:34');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('44', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:44:34');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('45', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 20:44:48');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('46', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:45:49');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('47', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 20:47:29');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('48', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 20:48:09');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('49', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-16 21:38:17');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('50', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-16 21:38:46');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('51', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 16:59:38');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('52', 'juliusmonicillo@mcc.edu', '::1', '0', '2026-07-18 17:02:24');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('53', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-18 17:02:37');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('54', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 17:20:16');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('55', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-18 17:21:16');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('56', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 17:22:15');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('57', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 18:18:40');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('58', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 18:33:08');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('59', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 18:45:26');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('60', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 18:46:19');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('61', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 18:56:31');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('62', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 19:01:39');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('63', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 19:05:18');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('64', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-18 19:05:29');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('65', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 19:06:17');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('66', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-18 19:08:24');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('67', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-18 19:08:35');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('68', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 19:18:33');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('69', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-18 19:23:31');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('70', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 07:13:22');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('71', 'manager@studentboardinghouse.com', '::1', '1', '2026-07-19 07:27:23');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('72', 'manager@studentboardinghouse.com', '::1', '0', '2026-07-19 07:30:04');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('73', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:30:37');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('74', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:31:19');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('75', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:31:26');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('76', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:32:41');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('77', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:32:42');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('78', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:32:43');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('79', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:33:17');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('80', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 07:33:31');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('81', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:44:47');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('82', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:44:59');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('83', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 07:45:07');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('84', 'warlitovelliganio@gmail.com', '::1', '1', '2026-07-19 07:45:43');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('85', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:47:05');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('86', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:47:39');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('87', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:47:50');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('88', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:48:11');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('89', 'warlitovilleganio@gmail.com', '::1', '0', '2026-07-19 07:48:38');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('90', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 07:48:43');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('91', 'warlitovilleganio@gmail.com', '::1', '1', '2026-07-19 07:49:41');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('92', 'warlitovilleganio@gmail.com', '::1', '1', '2026-07-19 08:08:29');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('93', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 08:14:10');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('94', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 08:28:44');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('95', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 08:31:31');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('96', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 08:47:26');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('97', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 10:57:10');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('98', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:05:21');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('99', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:05:30');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('100', 'admin@gmail.com', '::1', '0', '2026-07-19 11:05:47');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('101', 'admin@gmail.com', '::1', '0', '2026-07-19 11:06:06');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('102', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:06:11');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('103', 'alondes@gmail.com', '::1', '0', '2026-07-19 11:08:42');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('104', 'alondes@gmail.com', '::1', '0', '2026-07-19 11:09:49');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('105', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:11:34');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('106', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:11:44');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('107', 'admin@studentboardinghouse.com', '::1', '1', '2026-07-19 11:14:38');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('108', 'admin@studentboardinghouse.com', '::1', '0', '2026-07-19 11:15:31');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('109', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:15:44');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('110', 'manager@gmail.com', '::1', '1', '2026-07-19 11:16:23');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('111', 'manager@gmail.com', '::1', '1', '2026-07-19 11:19:39');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('112', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:20:10');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('113', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:21:55');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('114', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-19 11:25:51');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('115', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-19 11:27:32');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('116', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:27:41');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('117', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-19 11:32:52');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('118', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:34:32');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('119', 'wiljohnjumantoc58@gmail.com', '::1', '1', '2026-07-19 11:38:09');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('120', 'wiljohnjumantoc58@gmail.com', '::1', '0', '2026-07-19 11:39:50');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('121', 'wiljohnjumantoc58@gmail.com', '::1', '0', '2026-07-19 11:39:53');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('122', 'wiljohnjumantoc@gmail.com', '::1', '1', '2026-07-19 11:39:59');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('123', 'manager@gmail.com', '::1', '1', '2026-07-19 11:40:56');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('124', 'manager@gmail.com', '::1', '1', '2026-07-19 11:46:41');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('125', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:47:01');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('126', 'wiljohnjumantoc24@gmail.com', '::1', '1', '2026-07-19 11:57:05');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('127', 'admin@mcc.edu', '::1', '0', '2026-07-19 11:57:58');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('128', 'wiljohnjumantoc24@gmail.com', '::1', '1', '2026-07-19 11:58:04');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('129', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:58:18');
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES ('130', 'alondes@gmail.com', '::1', '1', '2026-07-19 11:59:06');

DROP TABLE IF EXISTS `maintenance_requests`;
CREATE TABLE `maintenance_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_code` varchar(20) NOT NULL,
  `student_id` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('plumbing','electrical','furniture','appliance','structural','other') NOT NULL DEFAULT 'other',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_maintenance_code` (`request_code`),
  KEY `idx_maintenance_student` (`student_id`),
  KEY `idx_maintenance_room` (`room_id`),
  KEY `idx_maintenance_status` (`status`),
  CONSTRAINT `fk_maintenance_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_maintenance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `maintenance_requests` (`id`, `request_code`, `student_id`, `room_id`, `title`, `description`, `category`, `priority`, `status`, `admin_response`, `resolved_at`, `created_at`, `updated_at`) VALUES ('4', 'MNT-2026-517123', '6', '14', 'lababo', 'lababo na stock', 'appliance', 'urgent', 'in_progress', 'waiting for manog ayo', NULL, '2026-07-19 11:27:16', '2026-07-19 11:32:28');

DROP TABLE IF EXISTS `managers`;
CREATE TABLE `managers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_managers_user` (`user_id`),
  CONSTRAINT `fk_managers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `managers` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `address`, `profile_picture`, `created_at`, `updated_at`) VALUES ('1', '2', 'Warlito', 'Villeganio', '+63 917 123 4567', 'Pili, Madredijos, Cebu', NULL, '2026-07-14 19:40:06', '2026-07-14 20:50:41');

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('reservation','payment','announcement','maintenance','complaint','system') NOT NULL DEFAULT 'system',
  `reference_id` int(10) unsigned DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_read` (`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('4', '1', 'New Maintenance Request', 'A new maintenance request has been submitted by Juan Dela Cruz.', 'maintenance', NULL, NULL, '0', '2026-07-14 19:40:06');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('5', '2', 'New Complaint', 'A complaint has been filed regarding noise from neighboring room.', 'complaint', NULL, NULL, '0', '2026-07-14 19:40:06');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('8', '9', 'Feedback Reply', 'Management has replied to your feedback: ka dorm', 'system', NULL, NULL, '1', '2026-07-14 21:31:13');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('9', '9', 'Reservation Approved', 'Your reservation for Room 105 (Alondes) has been approved.', 'reservation', '7', 'reservation', '1', '2026-07-14 22:03:33');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('15', '9', 'New Announcement', 'Especial Reminder!', 'announcement', NULL, NULL, '1', '2026-07-15 09:12:27');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('21', '9', 'New Announcement', 'Sa wala pa ka bayad!', 'announcement', NULL, NULL, '1', '2026-07-15 09:13:53');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('23', '9', 'Reservation Deleted', 'Your reservation RES-2026-501691 has been deleted by the administrator.', 'reservation', '7', 'reservation', '0', '2026-07-16 20:09:49');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('24', '9', 'Reservation Approved', 'Your reservation for Room 105 (Alondes) has been approved.', 'reservation', '9', 'reservation', '0', '2026-07-16 20:16:03');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('25', '9', 'Reservation Deleted', 'Your reservation RES-2026-547612 has been deleted by the administrator.', 'reservation', '9', 'reservation', '0', '2026-07-16 20:16:16');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('26', '10', 'Reservation Rejected', 'Your reservation has been rejected. Reason: full', 'reservation', '8', 'reservation', '0', '2026-07-16 20:16:35');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('27', '9', 'Reservation Deleted', 'Your reservation RES-2026-997777 has been deleted by the administrator.', 'reservation', '11', 'reservation', '0', '2026-07-16 20:46:20');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('28', '10', 'Reservation Rejected', 'Your reservation has been rejected. Reason: baho', 'reservation', '10', 'reservation', '0', '2026-07-16 20:46:56');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('29', '10', 'Reservation Deleted', 'Your reservation RES-2026-584953 has been deleted by the administrator.', 'reservation', '10', 'reservation', '0', '2026-07-16 20:47:00');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('30', '9', 'Reservation Approved', 'Your reservation for Room 102 (Boys only) has been approved.', 'reservation', '12', 'reservation', '0', '2026-07-16 21:38:07');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('47', '9', 'Payment Deleted', 'Your payment PAY-2026-882145 has been deleted by the administrator.', 'payment', '16', 'payment', '0', '2026-07-18 18:58:42');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('48', '9', 'Reservation Approved', 'Your reservation for Room 105 (Alondes) has been approved.', 'reservation', '13', 'reservation', '0', '2026-07-18 19:05:55');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('49', '9', 'Payment Paid', 'Your payment PAY-2026-839863 has been paid.', 'payment', '17', 'payment', '0', '2026-07-18 19:18:14');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('50', '9', 'Maintenance Update', 'Your maintenance request MNT-2026-517123 status: in progress.', 'maintenance', '4', 'maintenance', '0', '2026-07-19 11:32:28');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`) VALUES ('51', '10', 'Reservation Approved', 'Your reservation for Room 105 (Alondes) has been approved.', 'reservation', '14', 'reservation', '0', '2026-07-19 11:58:42');

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payment_history`;
CREATE TABLE `payment_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` int(10) unsigned NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ph_payment` (`payment_id`),
  CONSTRAINT `fk_ph_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_history` (`id`, `payment_id`, `action`, `old_status`, `new_status`, `notes`, `performed_by`, `created_at`) VALUES ('1', '17', 'verify', 'pending', 'paid', NULL, '2', '2026-07-18 19:18:14');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_code` varchar(20) NOT NULL,
  `student_id` int(10) unsigned NOT NULL,
  `reservation_id` int(10) unsigned DEFAULT NULL,
  `payment_type` enum('reservation_fee','advance_payment','security_deposit','monthly_rent','electric_bill','water_bill','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','gcash','maya','other') DEFAULT 'cash',
  `status` enum('pending','paid','overdue','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payments_code` (`payment_code`),
  KEY `idx_payments_student` (`student_id`),
  KEY `idx_payments_reservation` (`reservation_id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_type` (`payment_type`),
  CONSTRAINT `fk_payments_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `payment_code`, `student_id`, `reservation_id`, `payment_type`, `amount`, `payment_method`, `status`, `reference_number`, `proof_of_payment`, `notes`, `due_date`, `paid_at`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES ('17', 'PAY-2026-839863', '6', '13', 'monthly_rent', '800.00', 'maya', 'paid', NULL, NULL, 'fhhiuyuy', '2026-08-19', '2026-07-18 19:18:14', '2', '2026-07-18 19:18:14', '2026-07-18 19:08:07', '2026-07-18 19:18:14');

DROP TABLE IF EXISTS `receipts`;
CREATE TABLE `receipts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(20) NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `issued_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_receipts_number` (`receipt_number`),
  KEY `idx_receipts_payment` (`payment_id`),
  CONSTRAINT `fk_receipts_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `receipts` (`id`, `receipt_number`, `payment_id`, `issued_date`, `subtotal`, `discount`, `total`, `notes`, `created_at`) VALUES ('15', 'REC-2026-084258', '17', '2026-07-18', '800.00', '0.00', '800.00', NULL, '2026-07-18 19:18:14');

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_code` varchar(20) NOT NULL,
  `student_id` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned NOT NULL,
  `move_in_date` date NOT NULL,
  `expected_duration` int(10) unsigned DEFAULT NULL COMMENT 'months',
  `status` enum('pending','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `valid_id_path` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reservations_code` (`reservation_code`),
  KEY `idx_reservations_student` (`student_id`),
  KEY `idx_reservations_room` (`room_id`),
  KEY `idx_reservations_status` (`status`),
  CONSTRAINT `fk_reservations_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reservations` (`id`, `reservation_code`, `student_id`, `room_id`, `move_in_date`, `expected_duration`, `status`, `rejection_reason`, `admin_notes`, `valid_id_path`, `approved_at`, `rejected_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES ('8', 'RES-2026-912865', '7', '2', '2026-07-17', '1', 'rejected', 'full', NULL, 'reservations/6a58cac064ac2_64662706-43c7-4724-9935-8f25ce975ca5.jpg', NULL, '2026-07-16 20:16:35', NULL, '2026-07-16 20:12:48', '2026-07-16 20:16:35');
INSERT INTO `reservations` (`id`, `reservation_code`, `student_id`, `room_id`, `move_in_date`, `expected_duration`, `status`, `rejection_reason`, `admin_notes`, `valid_id_path`, `approved_at`, `rejected_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES ('12', 'RES-2026-716701', '6', '2', '2026-07-17', '2', 'cancelled', NULL, NULL, NULL, '2026-07-16 21:38:07', NULL, '2026-07-18 19:03:32', '2026-07-16 20:48:02', '2026-07-18 19:03:32');
INSERT INTO `reservations` (`id`, `reservation_code`, `student_id`, `room_id`, `move_in_date`, `expected_duration`, `status`, `rejection_reason`, `admin_notes`, `valid_id_path`, `approved_at`, `rejected_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES ('13', 'RES-2026-664608', '6', '14', '2026-07-19', '1', 'approved', NULL, NULL, 'reservations/6a5b5da3026c2_64662706-43c7-4724-9935-8f25ce975ca5.jpg', '2026-07-18 19:05:55', NULL, NULL, '2026-07-18 19:04:03', '2026-07-18 19:05:55');
INSERT INTO `reservations` (`id`, `reservation_code`, `student_id`, `room_id`, `move_in_date`, `expected_duration`, `status`, `rejection_reason`, `admin_notes`, `valid_id_path`, `approved_at`, `rejected_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES ('14', 'RES-2026-177800', '7', '14', '2026-07-20', '1', 'approved', NULL, NULL, NULL, '2026-07-19 11:58:42', NULL, NULL, '2026-07-19 11:57:47', '2026-07-19 11:58:42');

DROP TABLE IF EXISTS `room_amenities`;
CREATE TABLE `room_amenities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` int(10) unsigned NOT NULL,
  `amenity_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_room_amenity` (`room_id`,`amenity_id`),
  KEY `idx_ra_amenity` (`amenity_id`),
  CONSTRAINT `fk_ra_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ra_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('155', '14', '8', '2026-07-14 22:09:39');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('156', '14', '9', '2026-07-14 22:09:39');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('158', '14', '11', '2026-07-14 22:09:39');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('159', '14', '12', '2026-07-14 22:09:39');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('160', '14', '17', '2026-07-14 22:09:39');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('191', '2', '11', '2026-07-16 18:36:31');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('192', '2', '13', '2026-07-16 18:36:31');
INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`, `created_at`) VALUES ('193', '2', '17', '2026-07-16 18:36:31');

DROP TABLE IF EXISTS `room_categories`;
CREATE TABLE `room_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('1', 'Single Room', 'Private room for one occupant with basic amenities', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('2', 'Double Room', 'Shared room for two occupants with standard amenities', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('3', 'Triple Room', 'Shared room for three occupants with standard amenities', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('4', 'Dormitory', 'Large shared room with multiple beds and lockers', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('5', 'Premium Room', 'Spacious private room with premium amenities and en-suite bathroom', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `room_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES ('6', 'Studio Room', 'Self-contained unit with kitchenette and private bathroom', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');

DROP TABLE IF EXISTS `room_images`;
CREATE TABLE `room_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_room_images_room` (`room_id`),
  CONSTRAINT `fk_room_images_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('1', '14', 'rooms/6a564acf0ed57_744911151_1523800812827162_3356672555064922421_n.jpg', NULL, '1', '0', '2026-07-14 21:06:21');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('2', '2', 'rooms/6a58b43d83ae6_2b10cc69-a560-45c9-8af6-64699f017e75.jpg', NULL, '1', '1784035621', '2026-07-14 21:27:01');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('3', '14', 'rooms/6a5642f297c92_742510945_27511552415204987_7775651407280938740_n.jpg', NULL, '0', '1784038130', '2026-07-14 22:08:50');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('6', '2', 'rooms/6a58b90784cf5_744437476_2431905627298687_6879462106645094742_n.jpg', NULL, '0', '1784035622', '2026-07-16 18:57:11');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('7', '2', 'rooms/6a58b92756986_744485888_1332327229082235_4498440532453830251_n.jpg', NULL, '0', '1784035623', '2026-07-16 18:57:43');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('8', '14', 'rooms/6a5c13a11d4fe_743496830_1002576495874226_8439245135865046701_n.jpg', NULL, '0', '1784038131', '2026-07-19 08:00:33');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('9', '14', 'rooms/6a5c13b13a5a0_746908659_1584073749955699_183429810876718481_n.jpg', NULL, '0', '1784038132', '2026-07-19 08:00:49');
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('10', '14', 'rooms/6a5c13c5338e7_743940935_1664546284836122_2263954615434151613_n.jpg', NULL, '0', '1784038133', '2026-07-19 08:01:09');

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `monthly_rent` decimal(10,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_payment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_capacity` int(10) unsigned NOT NULL DEFAULT 1,
  `current_occupancy` int(10) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` enum('available','reserved','occupied','under_maintenance') NOT NULL DEFAULT 'available',
  `floor` int(11) DEFAULT NULL,
  `size_sqm` decimal(6,2) DEFAULT NULL,
  `has_bathroom` tinyint(1) NOT NULL DEFAULT 0,
  `has_balcony` tinyint(1) NOT NULL DEFAULT 0,
  `has_aircon` tinyint(1) NOT NULL DEFAULT 0,
  `house_rules` text DEFAULT NULL,
  `furniture` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rooms_number` (`room_number`),
  KEY `idx_rooms_category` (`category_id`),
  KEY `idx_rooms_status` (`status`),
  CONSTRAINT `fk_rooms_category` FOREIGN KEY (`category_id`) REFERENCES `room_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rooms` (`id`, `room_number`, `room_name`, `category_id`, `monthly_rent`, `security_deposit`, `advance_payment`, `max_capacity`, `current_occupancy`, `description`, `status`, `floor`, `size_sqm`, `has_bathroom`, `has_balcony`, `has_aircon`, `house_rules`, `furniture`, `created_at`, `updated_at`) VALUES ('2', '102', 'Boys only', '1', '5000.00', '5000.00', '800.00', '8', '0', 'Well-lit single room with east-facing window. Ideal for early risers who love natural light.', 'available', '1', '12.50', '1', '1', '1', 'No smoking inside the room. No pets allowed. Quiet hours from 10PM to 7PM. Visitors allowed until 9PM only.', 'Single bed, Study desk and chair, Wardrobe, Night stand', '2026-07-14 19:40:06', '2026-07-18 19:03:32');
INSERT INTO `rooms` (`id`, `room_number`, `room_name`, `category_id`, `monthly_rent`, `security_deposit`, `advance_payment`, `max_capacity`, `current_occupancy`, `description`, `status`, `floor`, `size_sqm`, `has_bathroom`, `has_balcony`, `has_aircon`, `house_rules`, `furniture`, `created_at`, `updated_at`) VALUES ('14', '105', 'Alondes', '2', '800.00', '1600.00', '800.00', '2', '2', 'No visitor allowed', 'occupied', '1', '50.00', '0', '0', '0', 'observe cleanliness', 'Peaceful', '2026-07-14 21:06:21', '2026-07-19 11:58:42');

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `student_id_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `school_university` varchar(255) DEFAULT NULL,
  `course_program` varchar(255) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `emergency_contact_name` varchar(200) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `valid_id_path` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_students_user` (`user_id`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `students` (`id`, `user_id`, `student_id_number`, `first_name`, `last_name`, `phone`, `address`, `date_of_birth`, `gender`, `school_university`, `course_program`, `year_level`, `emergency_contact_name`, `emergency_contact_phone`, `valid_id_path`, `profile_picture`, `created_at`, `updated_at`) VALUES ('6', '9', 'STU-2026-009', 'Wiljohn', 'Jumantoc', '+639679936511', '', NULL, NULL, 'Madredijos', 'I.T', '', '', '', 'valid_ids/6a56df2037978_64662706-43c7-4724-9935-8f25ce975ca5.jpg', 'profiles/6a56df20368b4_ac704530-1417-4cbb-ab62-fada252d2705.jpg', '2026-07-14 20:17:52', '2026-07-15 09:15:12');
INSERT INTO `students` (`id`, `user_id`, `student_id_number`, `first_name`, `last_name`, `phone`, `address`, `date_of_birth`, `gender`, `school_university`, `course_program`, `year_level`, `emergency_contact_name`, `emergency_contact_phone`, `valid_id_path`, `profile_picture`, `created_at`, `updated_at`) VALUES ('7', '10', 'STU-2026-010', 'John paul', 'Espliguera', '+639679936511', NULL, NULL, NULL, 'Madredijos', 'HM', NULL, NULL, NULL, NULL, NULL, '2026-07-16 20:11:31', '2026-07-16 20:11:31');

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json','file') NOT NULL DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('1', 'site_name', 'Student Boarding House', 'text', 'Website name', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('2', 'site_tagline', 'Your Home Away From Home', 'text', 'Website tagline', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('3', 'site_email', 'admin@enrollment.edu', 'text', 'Contact email', '2026-07-14 19:40:06', '2026-07-18 19:01:26');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('4', 'site_phone', '+63 912 345 6789', 'text', 'Contact phone', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('5', 'site_address', 'Pili Madredijos, Cebu', 'text', 'Physical address', '2026-07-14 19:40:06', '2026-07-16 22:08:44');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('6', 'site_logo', 'settings/6a58e5ece2672_746791029_2457606058053383_3628484729389491937_n.jpg', 'file', 'Site logo', '2026-07-14 19:40:06', '2026-07-16 22:08:44');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('7', 'currency', 'PHP', 'text', 'Currency code', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('8', 'currency_symbol', '₱', 'text', 'Currency symbol', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('9', 'tax_rate', '0', 'number', 'Tax rate percentage', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('10', 'late_fee', '500', 'number', 'Late payment fee', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('11', 'grace_period_days', '5', 'number', 'Payment grace period', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('12', 'max_login_attempts', '10', 'number', 'Max failed login attempts', '2026-07-14 19:40:06', '2026-07-16 22:08:44');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('13', 'lockout_duration', '30', 'number', 'Account lockout duration in minutes', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('14', 'reservation_expiry_days', '5', 'number', 'Reservation expiry in days', '2026-07-14 19:40:06', '2026-07-16 22:08:44');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('15', 'smtp_host', 'smtp.gmail.com', 'text', 'SMTP host', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('16', 'smtp_port', '586', 'number', 'SMTP port', '2026-07-14 19:40:06', '2026-07-16 22:08:44');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('17', 'smtp_username', '', 'text', 'SMTP username', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('18', 'smtp_password', '', 'text', 'SMTP password', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('19', 'smtp_encryption', 'tls', 'text', 'SMTP encryption', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('20', 'facebook_url', 'https://facebook.com', 'text', 'Facebook URL', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('21', 'twitter_url', 'https://twitter.com', 'text', 'Twitter URL', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('22', 'instagram_url', 'https://instagram.com', 'text', 'Instagram URL', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('23', 'about_text', 'Student Boarding House provides comfortable and affordable accommodation for students near major universities.', 'text', 'About section text', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('24', 'google_maps_embed', '', 'text', 'Google Maps embed code', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('25', 'about_title', 'About Us', 'text', 'About page title', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('26', 'about_subtitle', 'Learn more about Alondes Dorm.', 'text', 'About page subtitle', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('27', 'about_story_title', 'Welcome to Alondes Dorm', 'text', 'About story section title', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('28', 'about_story_text', 'Founded with the vision of providing safe, comfortable, and affordable accommodation for students, Alondes Dorm has been a trusted home for hundreds of students pursuing their academic dreams.||We understand the challenges students face when looking for the right place to stay while studying. That&amp;#039;s why we&amp;#039;ve created a community-oriented living space that feels like home.', 'text', 'About story text (use || for paragraphs)', '2026-07-15 17:11:27', '2026-07-18 19:01:26');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('29', 'about_years_label', '5+ Years', 'text', 'Years badge label', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('30', 'about_years_sublabel', 'of Service', 'text', 'Years badge sublabel', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('31', 'about_mission', 'To provide affordable, safe, and comfortable boarding house accommodations that support students in their academic journey. We strive to create a nurturing environment where students can thrive, build lasting friendships, and focus on their studies without the worry of their living arrangements.', 'text', 'Mission statement', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('32', 'about_vision', 'To be the most trusted and preferred student dormitory, known for our commitment to quality service, student welfare, and community building. We envision a network of modern, well-maintained dormitories that set the standard for student living accommodations across the country.', 'text', 'Vision statement', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('33', 'about_team_title', 'Meet Our Team', 'text', 'Team section title', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('34', 'about_team_subtitle', 'The dedicated people behind Alondes Dorm.', 'text', 'Team section subtitle', '2026-07-15 17:11:27', '2026-07-15 17:11:27');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('35', 'about_values_title', 'What We Stand For', 'text', 'Values section title', '2026-07-15 17:11:27', '2026-07-15 17:11:27');

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE `team_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_team_members_status` (`status`),
  KEY `idx_team_members_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_members` (`id`, `name`, `role`, `description`, `image_path`, `facebook_url`, `twitter_url`, `linkedin_url`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES ('5', 'Villeganio Warlito', 'Manager', 'contact here for more detail!', 'team/6a5c0b4ea03aa_dca91587-01cf-4304-8e81-0106847103f6.jpg', 'https://web.facebook.com/villeganio.warlie?rdid=3YwdqOjQXbmoa91u&amp;share_url=https%3A%2F%2Fweb.facebook.com%2Fshare%2F1E6hMgMS2R%2F%3F_rdc%3D1%26_rdr#', '', '', '1', 'active', '2026-07-19 07:25:02', '2026-07-19 07:25:02');

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_name` varchar(200) NOT NULL,
  `student_course` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`id`, `student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES ('1', 'Maria Santos', 'BS Business Administration, DLSU', 'The boarding house exceeded my expectations. The rooms are clean, the staff is friendly, and the location is perfect for my university. I highly recommend this to all students!', '5', '1', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `testimonials` (`id`, `student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES ('2', 'Juan Dela Cruz', 'BS Computer Science, UST', 'Great value for money! The Wi-Fi is fast, the study areas are quiet, and the security is excellent. I feel safe and comfortable here.', '5', '1', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `testimonials` (`id`, `student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES ('3', 'Pedro Garcia', 'BS IT, FEU', 'I love the community here. Met amazing friends and the management is very responsive to our needs. The monthly rates are very affordable.', '4', '1', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `testimonials` (`id`, `student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES ('4', 'Ana Reyes', 'BS Nursing, UE', 'The location is very convenient - just a few minutes walk to my university. The rooms are well-maintained and the common areas are always clean.', '5', '0', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');
INSERT INTO `testimonials` (`id`, `student_name`, `student_course`, `content`, `rating`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES ('5', 'Carlos Mendoza', 'BS Mechanical Engineering, PUP', 'Best decision I made was to stay here. The facilities are top-notch and the management truly cares about the students welfare.', '5', '1', 'active', '2026-07-14 19:40:06', '2026-07-14 19:40:06');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','manager','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','suspended','locked') NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `remember_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES ('1', 'alondes@gmail.com', '$2y$10$DNikf/NUcKEWWZRo6wO3TejbiaVS7/aDXWZhVLIj/lR6tDWaoq3VO', 'super_admin', 'active', '2026-07-14 19:40:06', NULL, '0', NULL, '2026-07-14 19:40:06', '2026-07-19 11:14:57');
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `remember_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES ('2', 'manager@gmail.com', '$2y$10$DNikf/NUcKEWWZRo6wO3TejbiaVS7/aDXWZhVLIj/lR6tDWaoq3VO', 'manager', 'active', '2026-07-14 19:40:06', NULL, '0', NULL, '2026-07-14 19:40:06', '2026-07-19 11:14:57');
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `remember_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES ('3', 'manager2@gmail.com', '$2y$10$DNikf/NUcKEWWZRo6wO3TejbiaVS7/aDXWZhVLIj/lR6tDWaoq3VO', 'manager', 'active', '2026-07-14 19:40:06', NULL, '0', NULL, '2026-07-14 19:40:06', '2026-07-19 11:14:57');
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `remember_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES ('9', 'wiljohnjumantoc58@gmail.com', '$2y$10$/GjzSb66BjaPgwq3XK/eKuY.Rz3uFaxL18dWsx/DFnMkgKZp.Lnc6', 'student', 'active', '2026-07-14 20:17:52', NULL, '0', NULL, '2026-07-14 20:17:52', '2026-07-19 11:40:11');
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `email_verified_at`, `remember_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES ('10', 'wiljohnjumantoc24@gmail.com', '$2y$10$FWpRatb40qqSxFT10YPe3.OrZQggiWOp.pShS4tYCtHosKsSKbAZi', 'student', 'active', '2026-07-16 20:11:31', NULL, '0', NULL, '2026-07-16 20:11:31', '2026-07-16 20:11:31');

SET FOREIGN_KEY_CHECKS = 1;
