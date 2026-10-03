-- ============================================================
-- Migration: Update Monthly Rent, Advance Payment & Penalty
-- Monthly Rent = ₱900, Advance Payment = ₱900, Penalty = ₱100
-- ============================================================

-- Update all rooms to ₱900 monthly rent and ₱900 advance payment
UPDATE `rooms` SET `monthly_rent` = 900.00, `advance_payment` = 900.00;

-- Update the late fee (penalty) to ₱100
UPDATE `system_settings` SET `setting_value` = '100', `description` = 'Late payment fee' WHERE `setting_key` = 'late_fee';
