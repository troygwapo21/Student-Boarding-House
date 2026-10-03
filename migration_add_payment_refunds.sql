-- Migration: Add refund tracking columns to payments
-- Tracks how much of a payment has been refunded so Total Paid / Total Revenue
-- can be deducted accurately (full or partial refunds) when a refund request
-- is approved by an admin/manager.

ALTER TABLE `payments`
    ADD COLUMN `refunded_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `amount_paid`,
    ADD COLUMN `refund_request_id` INT UNSIGNED DEFAULT NULL AFTER `refunded_amount`,
    ADD KEY `idx_payments_refund_request` (`refund_request_id`);
