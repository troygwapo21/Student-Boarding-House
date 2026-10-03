-- Migration: Add refund_type to refund_requests
-- Refund type: 'monthly' (one month's rent) or 'advance' (advance payments/deposit)
-- When a refund is approved, paid payments of the matching type are automatically marked 'refunded'.

ALTER TABLE `refund_requests`
    ADD COLUMN `refund_type` ENUM('monthly','advance') NOT NULL DEFAULT 'monthly' AFTER `amount`;
