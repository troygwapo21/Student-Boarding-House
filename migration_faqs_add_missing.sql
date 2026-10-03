-- ============================================================
-- MIGRATION: Add missing FAQs to the faqs table
-- Purpose: Move all FAQ details from the old hardcoded home
--          page list into the faqs table so the /faqs page
--          displays the full list.
-- Safe to run multiple times (each row checks NOT EXISTS).
-- ============================================================

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What is Alondes Dorm?', 'Alondes Dorm is a Student Boarding House Management System designed to help students find suitable boarding rooms, submit reservations, manage their stay, view payments, and communicate with boarding house staff.', 'general', 11, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What is Alondes Dorm?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Who is Alondes Dorm for?', 'The website is designed for students and tenants looking for boarding accommodation, as well as boarding house owners, managers, and staff who manage rooms, reservations, tenants, payments, announcements, and maintenance requests.', 'general', 12, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Who is Alondes Dorm for?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I find an available room?', 'Go to the Available Rooms section. You can view room details such as room number, monthly rent, capacity, amenities, current occupancy, and availability status.', 'general', 13, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I find an available room?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What happens after I submit a reservation?', 'Your reservation will remain pending until the authorized staff reviews it. You will receive an update once the reservation is approved, rejected, or requires further action.', 'reservation', 14, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What happens after I submit a reservation?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I know if a room is already full?', 'The system checks the room''s maximum capacity and current occupancy. When no slots are available, the room is marked as Fully Occupied and the reservation option is unavailable.', 'reservation', 15, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I know if a room is already full?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What information is required when registering?', 'Students need to provide the required personal and account information during registration. The system also validates important information such as username, password, age, phone number, and other required fields.', 'general', 16, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What information is required when registering?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What are the password requirements?', 'Your password must meet the security requirements shown during registration. Use at least 8 characters with uppercase and lowercase letters, a number, and a special character.', 'general', 17, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What are the password requirements?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I check my reservation status?', 'Log in to your student account and open your reservation section. You can view the current status and details of your submitted reservations.', 'reservation', 18, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I check my reservation status?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I check my payments?', 'Students can log in to their account and access the payment section to review payment records, payment status, due dates, and related information.', 'payment', 19, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I check my payments?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What happens if I miss my payment due date?', 'The system tracks payment due dates and identifies overdue payments. Applicable penalties and payment information are shown according to the boarding house''s configured payment rules.', 'payment', 20, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What happens if I miss my payment due date?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Will I receive payment reminders?', 'Yes. The system supports payment reminders before the due date. Notifications help students keep track of upcoming payments and overdue balances.', 'payment', 21, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Will I receive payment reminders?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I report a maintenance problem?', 'Log in to your student account and submit a maintenance request. Provide the relevant details so the boarding house staff can review and address the issue.', 'general', 22, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I report a maintenance problem?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I submit a complaint?', 'Students can use the complaint feature to report concerns related to their boarding house experience. Authorized staff can review and manage submitted complaints.', 'general', 23, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I submit a complaint?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I receive announcements?', 'Important announcements from the boarding house management appear in the notification or announcement section of your account.', 'general', 24, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I receive announcements?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Can I communicate with boarding house management?', 'Yes. The system provides communication features for students and authorized boarding house staff to exchange relevant information about reservations, payments, maintenance, complaints, and announcements.', 'general', 25, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Can I communicate with boarding house management?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Who manages the boarding house information?', 'Authorized users such as the Boarding House Owner, Manager, and Receptionist or Staff manage the information available through the management dashboard.', 'general', 26, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Who manages the boarding house information?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What information can boarding house staff manage?', 'Authorized staff can manage rooms, reservations, students or tenants, payments, announcements, maintenance requests, complaints, feedback, and related boarding house records.', 'general', 27, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What information can boarding house staff manage?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Can I cancel a reservation?', 'Reservation cancellation depends on the current reservation status and the boarding house''s policies. Check your reservation details for the available actions.', 'reservation', 28, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Can I cancel a reservation?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Is my account information protected?', 'The system uses account authentication and database security practices to protect user information. Access to management features is restricted according to the user''s assigned role.', 'general', 29, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Is my account information protected?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What should I do if I forget my password?', 'Use the Forgot Password option on the login page and follow the available account recovery instructions.', 'general', 30, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What should I do if I forget my password?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Why is my reservation still pending?', 'A pending reservation means the request is still waiting for review by authorized boarding house staff. Wait for the management team to process the request and check your notifications for updates.', 'reservation', 31, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Why is my reservation still pending?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'What should I do if the information on my account is incorrect?', 'Check your profile information and update the fields available to you. If the information requires staff assistance, contact the boarding house management.', 'general', 32, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'What should I do if the information on my account is incorrect?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'Can I view room amenities before reserving?', 'Yes. Room listings include available amenities and other relevant room information so students can review the accommodation before submitting a reservation.', 'general', 33, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'Can I view room amenities before reserving?');

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 'How do I contact Alondes Dorm?', 'Use the Contact section of the website to send a message or find the available contact information provided by the boarding house management.', 'general', 34, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `faqs` WHERE `question` = 'How do I contact Alondes Dorm?');