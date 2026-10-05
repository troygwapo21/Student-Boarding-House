<?php

class DashboardAnalyticsService
{
    private const PAID_STATUSES = ['paid', 'partially_paid', 'refunded'];

    public static function calculate(array $data): array
    {
        $asOfDateStr = $data['as_of_date'] ?? null;
        $asOfDate = self::parseDate($asOfDateStr);
        if (!$asOfDate) {
            $asOfDate = new DateTimeImmutable('today');
        }

        // Generate 12 months sequence ending at current month
        $months = self::monthSequence($asOfDate, 12);
        $monthKeys = [];
        $labels = [];
        foreach ($months as $m) {
            $monthKeys[] = $m->format('Y-m');
            $labels[] = $m->format('M');
        }
        $monthSet = array_flip($monthKeys);
        $lastMonthKey = end($monthKeys);

        $revenueByMonth = array_fill_keys($monthKeys, 0.0);
        $reservationsByMonth = array_fill_keys($monthKeys, 0);
        $revenueByType = [];

        // 1. Payments
        foreach ($data['payments'] ?? [] as $payment) {
            $status = strtolower(trim((string)($payment['status'] ?? '')));
            if (!in_array($status, self::PAID_STATUSES, true)) {
                continue;
            }
            $amountPaid = self::asNumber($payment['amount_paid'] ?? 0);
            if ($amountPaid <= 0) {
                continue;
            }
            $paymentDate = self::parseDate($payment['paid_at'] ?? null);
            if (!$paymentDate) {
                continue;
            }
            $monthKey = $paymentDate->format('Y-m');
            if (!isset($monthSet[$monthKey])) {
                continue;
            }
            $netAmount = $amountPaid - self::asNumber($payment['refunded_amount'] ?? 0);
            $revenueByMonth[$monthKey] += $netAmount;

            if ($monthKey === $lastMonthKey) {
                $paymentType = trim((string)($payment['payment_type'] ?? 'other')) ?: 'other';
                $revenueByType[$paymentType] = ($revenueByType[$paymentType] ?? 0.0) + $netAmount;
            }
        }

        // 2. Reservations
        foreach ($data['reservations'] ?? [] as $reservation) {
            $createdAt = self::parseDate($reservation['created_at'] ?? null);
            if (!$createdAt) {
                continue;
            }
            $monthKey = $createdAt->format('Y-m');
            if (isset($monthSet[$monthKey])) {
                $reservationsByMonth[$monthKey]++;
            }
        }

        // 3. Payment methods
        $methods = [];
        foreach ($data['payment_methods'] ?? [] as $row) {
            $status = strtolower(trim((string)($row['status'] ?? '')));
            if (!in_array($status, self::PAID_STATUSES, true)) {
                continue;
            }
            $amountPaid = self::asNumber($row['amount_paid'] ?? 0);
            if ($amountPaid <= 0) {
                continue;
            }
            $method = strtolower(trim((string)($row['method'] ?? 'cash'))) ?: 'cash';
            $net = $amountPaid - self::asNumber($row['refunded_amount'] ?? 0);
            $methods[$method] = ($methods[$method] ?? 0.0) + $net;
        }

        // 4. Rooms
        $roomTypes = [];
        $totalRooms = 0;
        $occupiedRooms = 0;
        $totalCapacity = 0;
        $occupiedBeds = 0;

        foreach ($data['rooms'] ?? [] as $room) {
            $roomType = strtolower(trim((string)($room['room_type'] ?? 'other'))) ?: 'other';
            $status = strtolower(trim((string)($room['status'] ?? '')));
            $capacity = (int)self::asNumber($room['max_capacity'] ?? 0);
            $beds = (int)self::asNumber($room['current_occupancy'] ?? 0);

            if (!isset($roomTypes[$roomType])) {
                $roomTypes[$roomType] = [
                    'room_type'     => $roomType,
                    'total_rooms'   => 0,
                    'occupied'      => 0,
                    'occupied_beds' => 0,
                    'capacity'      => 0,
                ];
            }

            $roomTypes[$roomType]['total_rooms']++;
            if ($status === 'occupied') {
                $roomTypes[$roomType]['occupied']++;
            }
            $roomTypes[$roomType]['occupied_beds'] += $beds;
            $roomTypes[$roomType]['capacity'] += $capacity;

            $totalRooms++;
            if ($status === 'occupied') {
                $occupiedRooms++;
            }
            $totalCapacity += $capacity;
            $occupiedBeds += $beds;
        }

        // 5. Students
        $genders = [];
        $studentsByYear = [];
        foreach ($data['students'] ?? [] as $student) {
            $gender = strtolower(trim((string)($student['gender'] ?? 'other'))) ?: 'other';
            $genders[$gender] = ($genders[$gender] ?? 0) + 1;

            $yearLevel = trim((string)($student['year_level'] ?? 'N/A')) ?: 'N/A';
            $studentsByYear[$yearLevel] = ($studentsByYear[$yearLevel] ?? 0) + 1;
        }

        // Format sorted results
        ksort($revenueByType);
        $revenueTypeRows = [];
        foreach ($revenueByType as $k => $v) {
            $revenueTypeRows[] = ['payment_type' => $k, 'total' => round($v, 2)];
        }

        ksort($methods);
        $revenueMethodRows = [];
        foreach ($methods as $k => $v) {
            $revenueMethodRows[] = ['method' => $k, 'total' => round($v, 2)];
        }

        ksort($roomTypes);
        $roomTypeRows = array_values($roomTypes);

        ksort($genders);
        $genderRows = [];
        foreach ($genders as $k => $v) {
            $genderRows[] = ['gender' => $k, 'c' => $v];
        }

        uksort($studentsByYear, function ($a, $b) use ($studentsByYear) {
            if ($studentsByYear[$a] !== $studentsByYear[$b]) {
                return $studentsByYear[$b] <=> $studentsByYear[$a];
            }
            return strcmp($a, $b);
        });
        $yearRows = [];
        $slice = array_slice($studentsByYear, 0, 6, true);
        foreach ($slice as $k => $v) {
            $yearRows[] = ['year_level' => $k, 'c' => $v];
        }

        $revenueList = [];
        $reservationsList = [];
        foreach ($monthKeys as $mk) {
            $revenueList[] = round($revenueByMonth[$mk], 2);
            $reservationsList[] = (int)$reservationsByMonth[$mk];
        }

        return [
            'charts' => [
                'monthly' => [
                    'labels'       => $labels,
                    'revenue'      => $revenueList,
                    'reservations' => $reservationsList,
                ],
            ],
            'revenue_by_type'     => $revenueTypeRows,
            'revenue_by_method'   => $revenueMethodRows,
            'room_type_stats'     => $roomTypeRows,
            'students_by_gender'  => $genderRows,
            'students_by_year'    => $yearRows,
            'total_capacity'      => $totalCapacity,
            'total_occupied_beds' => $occupiedBeds,
            'bed_occupancy_rate'  => self::percent($occupiedBeds, $totalCapacity),
            'occupancy_rate'      => self::percent($occupiedRooms, $totalRooms),
        ];
    }

    private static function parseDate($value): ?DateTimeImmutable
    {
        if (!$value) {
            return null;
        }
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }
        $str = trim((string)$value);
        if ($str === '') {
            return null;
        }
        try {
            return new DateTimeImmutable($str);
        } catch (Exception $e) {
            return null;
        }
    }

    private static function asNumber($value): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return 0.0;
    }

    private static function monthSequence(DateTimeImmutable $lastMonth, int $count): array
    {
        $months = [];
        $year = (int)$lastMonth->format('Y');
        $month = (int)$lastMonth->format('n');

        for ($i = 0; $i < $count; $i++) {
            $months[] = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            $month--;
            if ($month === 0) {
                $year--;
                $month = 12;
            }
        }

        return array_reverse($months);
    }

    private static function percent(float $num, float $den): int
    {
        if ($den <= 0) {
            return 0;
        }
        return (int)round(($num / $den) * 100);
    }
}
