ALTER TABLE `payments`
    ADD COLUMN `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `amount`,
    ADD COLUMN `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `late_fee`,
    ADD COLUMN `billing_period` VARCHAR(7) DEFAULT NULL AFTER `amount_paid`,
    ADD COLUMN `penalty_applied` TINYINT(1) NOT NULL DEFAULT 0 AFTER `billing_period`,
    ADD KEY `idx_payments_billing_period` (`billing_period`);
