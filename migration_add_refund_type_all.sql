-- Migration: Add 'all' (All Payments) refund type
-- Refund type: 'monthly' (one month's rent), 'advance' (advance payments/deposit),
-- or 'all' (combined refund of monthly rent and advance payments).
-- When an 'all' refund is approved, paid monthly rent and advance payments are
-- automatically marked 'refunded' up to the refund amount.

ALTER TABLE `refund_requests`
    MODIFY COLUMN `refund_type` ENUM('monthly','advance','all') NOT NULL DEFAULT 'monthly';
