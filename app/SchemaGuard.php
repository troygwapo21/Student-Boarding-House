<?php

/**
 * Runtime schema guard.
 *
 * The SQL migrations in the project root are meant to be applied manually and
 * the shipped dumps (student_boarding_house.sql, storage/backups/*.sql) predate
 * several of them. When a required table or column is missing, the request dies
 * with an uncaught PDOException (display_errors is on) — which is exactly what
 * broke walk-in registration (INSERT users.email_verified, students.school_id_path,
 * guardians, reservations.rent_amount) and login (email_verification_codes OTP).
 *
 * This guard re-applies only what is missing, using plain ALTER TABLE /
 * CREATE TABLE statements — no information_schema, which some hosts expose
 * read-only. It is idempotent, logs failures instead of throwing, and runs at
 * most once per request.
 */
class SchemaGuard {

    private static bool $ran = false;

    public static function ensure(): void {
        if (self::$ran) {
            return;
        }
        self::$ran = true;

        try {
            $db = Database::getInstance();
        } catch (\Throwable $e) {
            error_log('SchemaGuard: no database connection - ' . $e->getMessage());
            return;
        }

        self::guardUsers($db);
        self::guardStudents($db);
        self::guardReservations($db);
        self::guardPayments($db);
        self::guardTables($db);
    }

    // ------------------------------------------------------------------
    // users
    // ------------------------------------------------------------------
    private static function guardUsers(Database $db): void {
        $added = self::ensureColumns($db, 'users', [
            'username'           => 'VARCHAR(50) DEFAULT NULL',
            'email_verified'     => 'TINYINT(1) NOT NULL DEFAULT 0',
            'email_verified_at'  => 'TIMESTAMP NULL DEFAULT NULL',
            'password_changed_at'=> 'TIMESTAMP NULL DEFAULT NULL',
        ], [
            'username'            => 'email',
            'email_verified'      => 'status',
            'email_verified_at'   => 'email_verified',
            'password_changed_at' => 'locked_until',
        ]);

        // A freshly created flag starts at 0 for every pre-existing account.
        // Accounts that carry a verified timestamp are verified accounts, so
        // backfill in the same request the column appears (otherwise login
        // would bounce them to /verify-email).
        if (in_array('email_verified', $added, true)) {
            // When the flag AND its timestamp were created together there is no
            // historical verification data at all (fresh dump import): every
            // existing account predates the feature, so it is already verified.
            // Leaving them at 0 bounced admin/manager/tenant logins to
            // /verify-email without any code being sent.
            $stampAdded = in_array('email_verified_at', $added, true);
            try {
                $db->query(
                    $stampAdded
                        ? "UPDATE `users` SET `email_verified` = 1 WHERE `email_verified` = 0"
                        : "UPDATE `users` SET `email_verified` = 1
                            WHERE `email_verified_at` IS NOT NULL AND `email_verified` = 0"
                );
            } catch (\Throwable $e) {
                error_log('SchemaGuard: users email_verified backfill failed - ' . $e->getMessage());
            }
        }

        if ($added !== []) {
            self::ensureUniqueKey($db, 'users', 'uk_users_email', 'email');
            if (in_array('username', $added, true)) {
                self::ensureUniqueKey($db, 'users', 'uk_users_username', 'username');
            }
        }
    }

    // ------------------------------------------------------------------
    // students  (tenant + guardian fields written by walk-in registration)
    // ------------------------------------------------------------------
    private static function guardStudents(Database $db): void {
        self::ensureColumns($db, 'students', [
            'middle_name'       => 'VARCHAR(100) DEFAULT NULL',
            'suffix'            => 'VARCHAR(20) DEFAULT NULL',
            'civil_status'      => "ENUM('single','married','widowed','separated','divorced') DEFAULT NULL",
            'nationality'       => "VARCHAR(100) DEFAULT 'Filipino'",
            'school_id_path'    => 'VARCHAR(255) DEFAULT NULL',
            'house_unit'        => 'VARCHAR(50) DEFAULT NULL',
            'street'            => 'VARCHAR(255) DEFAULT NULL',
            'barangay'          => 'VARCHAR(255) DEFAULT NULL',
            'municipality_city' => 'VARCHAR(255) DEFAULT NULL',
            'province'          => 'VARCHAR(255) DEFAULT NULL',
            'zip_code'          => 'VARCHAR(10) DEFAULT NULL',
        ], [
            'middle_name'       => 'first_name',
            'suffix'            => 'last_name',
            'civil_status'      => 'gender',
            'nationality'       => 'civil_status',
            'school_id_path'    => 'valid_id_path',
            'house_unit'        => 'address',
            'street'            => 'house_unit',
            'barangay'          => 'street',
            'municipality_city' => 'barangay',
            'province'          => 'municipality_city',
            'zip_code'          => 'province',
        ]);
    }

    // ------------------------------------------------------------------
    // reservations  (rent frozen at approval + move-in time)
    // ------------------------------------------------------------------
    private static function guardReservations(Database $db): void {
        self::ensureColumns($db, 'reservations', [
            'move_in_time' => 'TIME DEFAULT NULL',
            'rent_amount'  => 'DECIMAL(10,2) DEFAULT NULL',
        ], [
            'move_in_time' => 'move_in_date',
            'rent_amount'  => 'expected_duration',
        ]);
    }

    // ------------------------------------------------------------------
    // payments  (billing columns + full_payment type used by billing)
    // ------------------------------------------------------------------
    private static function guardPayments(Database $db): void {
        self::ensureColumns($db, 'payments', [
            'late_fee'         => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'amount_paid'      => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'billing_period'   => 'VARCHAR(7) DEFAULT NULL',
            'penalty_applied'  => 'TINYINT(1) NOT NULL DEFAULT 0',
        ], [
            'late_fee'        => 'amount',
            'amount_paid'     => 'late_fee',
            'billing_period'  => 'amount_paid',
            'penalty_applied' => 'billing_period',
        ]);

        $types = self::columnTypes($db, 'payments');
        if (isset($types['payment_type']) && strpos($types['payment_type'], 'full_payment') === false) {
            try {
                $db->query(
                    "ALTER TABLE `payments` MODIFY COLUMN `payment_type`
                     ENUM('reservation_fee','advance_payment','monthly_rent','electric_bill','water_bill','other','full_payment') NOT NULL"
                );
            } catch (\Throwable $e) {
                error_log('SchemaGuard: payments.payment_type enum - ' . $e->getMessage());
            }
        }

        if (isset($types['status']) && strpos($types['status'], 'partially_paid') === false) {
            try {
                $db->query(
                    "ALTER TABLE `payments` MODIFY COLUMN `status`
                     ENUM('pending','upcoming','due_today','partially_paid','paid','overdue','cancelled','refunded') NOT NULL DEFAULT 'pending'"
                );
            } catch (\Throwable $e) {
                error_log('SchemaGuard: payments.status enum - ' . $e->getMessage());
            }
        }
    }

    // ------------------------------------------------------------------
    // tables created by migrations the shipped dumps do not contain
    // ------------------------------------------------------------------
    private static function guardTables(Database $db): void {
        $existing = self::tableNames($db);
        if ($existing === null) {
            return;
        }

        if (!in_array('email_verification_codes', $existing, true)) {
            self::createTable($db, 'email_verification_codes', self::ddlEmailVerificationCodes());
        }
        if (!in_array('password_history', $existing, true)) {
            self::createTable($db, 'password_history', self::ddlPasswordHistory());
        }
        if (!in_array('student_reservation_credits', $existing, true)) {
            self::createTable($db, 'student_reservation_credits', self::ddlStudentReservationCredits());
        }
        if (!in_array('guardians', $existing, true)) {
            if (!self::createTable($db, 'guardians', self::ddlGuardiansWithFk())) {
                self::createTable($db, 'guardians', self::ddlGuardiansWithoutFk());
            }
        }
    }

    // ------------------------------------------------------------------
    // DDL
    // ------------------------------------------------------------------
    private static function ddlEmailVerificationCodes(): string {
        return "CREATE TABLE IF NOT EXISTS `email_verification_codes` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `code` CHAR(6) NOT NULL,
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `expires_at` DATETIME NOT NULL,
            `used` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_evc_user_unused` (`user_id`, `used`),
            KEY `idx_evc_code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    private static function ddlPasswordHistory(): string {
        return "CREATE TABLE IF NOT EXISTS `password_history` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private static function ddlStudentReservationCredits(): string {
        return "CREATE TABLE IF NOT EXISTS `student_reservation_credits` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `student_id` INT NOT NULL,
            `original_reservation_id` INT NOT NULL,
            `original_reservation_code` VARCHAR(50) NOT NULL,
            `payment_type` VARCHAR(50) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `total_amount` DECIMAL(10,2) NOT NULL,
            `notes` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_student_credits` (`student_id`),
            KEY `idx_original_reservation` (`original_reservation_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    private static function ddlGuardiansWithFk(): string {
        return "CREATE TABLE `guardians` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `student_id` INT UNSIGNED NOT NULL,
            `first_name` VARCHAR(100) NOT NULL,
            `middle_name` VARCHAR(100) DEFAULT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `relationship` VARCHAR(100) NOT NULL,
            `mobile_number` VARCHAR(20) NOT NULL,
            `alternative_contact` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `house_unit` VARCHAR(50) DEFAULT NULL,
            `street` VARCHAR(255) DEFAULT NULL,
            `barangay` VARCHAR(255) DEFAULT NULL,
            `municipality_city` VARCHAR(255) DEFAULT NULL,
            `province` VARCHAR(255) DEFAULT NULL,
            `zip_code` VARCHAR(10) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_guardians_student` (`student_id`),
            CONSTRAINT `fk_guardians_student` FOREIGN KEY (`student_id`)
                REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private static function ddlGuardiansWithoutFk(): string {
        return "CREATE TABLE `guardians` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `student_id` INT UNSIGNED NOT NULL,
            `first_name` VARCHAR(100) NOT NULL,
            `middle_name` VARCHAR(100) DEFAULT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `relationship` VARCHAR(100) NOT NULL,
            `mobile_number` VARCHAR(20) NOT NULL,
            `alternative_contact` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `house_unit` VARCHAR(50) DEFAULT NULL,
            `street` VARCHAR(255) DEFAULT NULL,
            `barangay` VARCHAR(255) DEFAULT NULL,
            `municipality_city` VARCHAR(255) DEFAULT NULL,
            `province` VARCHAR(255) DEFAULT NULL,
            `zip_code` VARCHAR(10) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_guardians_student` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    // ------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------

    /**
     * @return array<string,string> column name => Type
     */
    private static function columnTypes(Database $db, string $table): array {
        try {
            $rows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
        } catch (\Throwable $e) {
            error_log("SchemaGuard: SHOW COLUMNS {$table} failed - " . $e->getMessage());
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $name = (string)($row['Field'] ?? '');
            if ($name !== '') {
                $out[$name] = (string)($row['Type'] ?? '');
            }
        }
        return $out;
    }

    /**
     * Adds any column from $definitions that is not present yet.
     *
     * @param array<string,string> $definitions column name => column definition
     * @param array<string,string> $after       column name => column to place it after
     * @return array<string> names of the columns that were actually added
     */
    private static function ensureColumns(Database $db, string $table, array $definitions, array $after = []): array {
        $types = self::columnTypes($db, $table);
        if ($types === []) {
            return [];
        }

        $added = [];
        foreach ($definitions as $name => $definition) {
            if (isset($types[$name])) {
                continue;
            }
            $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$definition}";
            $afterColumn = $after[$name] ?? null;
            if ($afterColumn !== null && isset($types[$afterColumn])) {
                $sql .= " AFTER `{$afterColumn}`";
            }
            try {
                $db->query($sql);
                $types[$name] = '';
                $added[] = $name;
            } catch (\Throwable $e) {
                // 1060 = duplicate column (another request won the race) — safe to ignore.
                error_log("SchemaGuard: {$table}.{$name} not added - " . $e->getMessage());
            }
        }
        return $added;
    }

    private static function ensureUniqueKey(Database $db, string $table, string $index, string $column): void {
        try {
            $db->query("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index}` (`{$column}`)");
        } catch (\Throwable $e) {
            // 1061 = index already exists; 1062 = duplicate rows. Both are logged, never fatal.
            error_log("SchemaGuard: {$table}.{$index} - " . $e->getMessage());
        }
    }

    /**
     * @return array<string>|null null when SHOW TABLES itself failed
     */
    private static function tableNames(Database $db): ?array {
        try {
            $rows = $db->fetchAll('SHOW TABLES');
        } catch (\Throwable $e) {
            error_log('SchemaGuard: SHOW TABLES failed - ' . $e->getMessage());
            return null;
        }
        $names = [];
        foreach ($rows as $row) {
            $names[] = (string)array_values($row)[0];
        }
        return $names;
    }

    private static function createTable(Database $db, string $name, string $sql): bool {
        try {
            $db->query($sql);
            return true;
        } catch (\Throwable $e) {
            error_log("SchemaGuard: CREATE TABLE {$name} failed - " . $e->getMessage());
            return false;
        }
    }
}
