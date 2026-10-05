<?php
$hour = (int)serverDate('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$adminName = $_SESSION['user_email'] ?? 'Admin';

$analyticsScript = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'analytics' . DIRECTORY_SEPARATOR . 'dashboard_analytics.py';
$analyticsInput = [
    'as_of_date' => $analyticsAsOfDate,
    'payments' => $analyticsChartPaymentRows,
    'reservations' => $analyticsReservationRows,
    'payment_methods' => $analyticsPaymentRows,
    'rooms' => $analyticsRoomRows,
    'students' => $analyticsStudentRows,
];

$analyticsAvailable = false;
$analyticsError = null;
$dashboardAnalytics = null;
$analyticsConfigPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dashboard_analytics.local.php';
$analyticsLocalConfig = is_file($analyticsConfigPath) ? require $analyticsConfigPath : [];
if (!is_array($analyticsLocalConfig)) {
    $analyticsError = 'The local dashboard analytics configuration must return an array.';
    $analyticsLocalConfig = [];
}
$analyticsApiUrl = trim((string)($analyticsLocalConfig['api_url'] ?? (getenv('DASHBOARD_ANALYTICS_API_URL') ?: '')));
$analyticsApiToken = (string)($analyticsLocalConfig['api_token'] ?? (getenv('DASHBOARD_ANALYTICS_API_TOKEN') ?: ''));

try {
    if ($analyticsError !== null) {
        throw new RuntimeException($analyticsError);
    }
    if ($analyticsApiUrl !== '') {
        $analyticsUrlParts = parse_url($analyticsApiUrl);
        if (
            !is_array($analyticsUrlParts)
            || strtolower($analyticsUrlParts['scheme'] ?? '') !== 'https'
            || empty($analyticsUrlParts['host'])
            || isset($analyticsUrlParts['user'])
            || isset($analyticsUrlParts['pass'])
            || isset($analyticsUrlParts['query'])
            || isset($analyticsUrlParts['fragment'])
            || strlen($analyticsApiToken) < 32
        ) {
            throw new RuntimeException('Configure a valid HTTPS analytics API URL and API token.');
        }

        $analyticsPayload = json_encode($analyticsInput, JSON_THROW_ON_ERROR);
        if (function_exists('curl_init')) {
            $analyticsCurl = curl_init($analyticsApiUrl);
            if ($analyticsCurl === false) {
                throw new RuntimeException('Could not initialize the HTTPS connection to the Python analytics API.');
            }
            $analyticsCurlConfigured = curl_setopt_array($analyticsCurl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $analyticsPayload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $analyticsApiToken,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 90,
            ]);
            if (!$analyticsCurlConfigured) {
                curl_close($analyticsCurl);
                throw new RuntimeException('Could not configure the HTTPS connection to the Python analytics API.');
            }
            $analyticsOutput = curl_exec($analyticsCurl);
            $analyticsHttpStatus = (int)curl_getinfo($analyticsCurl, CURLINFO_RESPONSE_CODE);
            $analyticsCurlError = curl_error($analyticsCurl);
            curl_close($analyticsCurl);
            if ($analyticsOutput === false) {
                throw new RuntimeException('Could not contact the Python analytics API: ' . $analyticsCurlError);
            }
        } else {
            $analyticsContext = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", [
                        'Content-Type: application/json',
                        'Accept: application/json',
                        'Authorization: Bearer ' . $analyticsApiToken,
                    ]),
                    'content' => $analyticsPayload,
                    'timeout' => 90,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $analyticsOutput = @file_get_contents($analyticsApiUrl, false, $analyticsContext);
            $analyticsStatusLine = $http_response_header[0] ?? '';
            preg_match('/\s(\d{3})\s/', $analyticsStatusLine, $analyticsStatusMatch);
            $analyticsHttpStatus = (int)($analyticsStatusMatch[1] ?? 0);
            if ($analyticsOutput === false) {
                throw new RuntimeException('Could not contact the Python analytics API.');
            }
        }

        if ($analyticsHttpStatus < 200 || $analyticsHttpStatus >= 300) {
            throw new RuntimeException('Python analytics API returned HTTP ' . $analyticsHttpStatus . '.');
        }
    } elseif (is_file($analyticsScript)) {
        $pythonCandidates = array_values(array_filter([
            getenv('PYTHON_EXECUTABLE') ?: null,
            'python3',
            '/usr/bin/python3',
            '/usr/local/bin/python3',
            'python',
            '/usr/bin/python',
            'py'
        ]));
        $pythonSuccess = false;

        if (function_exists('proc_open')) {
            $analyticsPayload = json_encode($analyticsInput, JSON_THROW_ON_ERROR);
            foreach ($pythonCandidates as $candidate) {
                $analyticsPipes = [];
                $analyticsProcess = @proc_open(
                    [$candidate, $analyticsScript],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                    $analyticsPipes,
                    dirname($analyticsScript)
                );
                if (is_resource($analyticsProcess)) {
                    $analyticsPayloadLength = strlen($analyticsPayload);
                    $analyticsWritten = 0;
                    $writeOk = true;
                    while ($analyticsWritten < $analyticsPayloadLength) {
                        $analyticsBytes = @fwrite($analyticsPipes[0], substr($analyticsPayload, $analyticsWritten));
                        if ($analyticsBytes === false || $analyticsBytes === 0) {
                            $writeOk = false;
                            break;
                        }
                        $analyticsWritten += $analyticsBytes;
                    }
                    @fclose($analyticsPipes[0]);
                    $out = @stream_get_contents($analyticsPipes[1]);
                    $err = @stream_get_contents($analyticsPipes[2]);
                    @fclose($analyticsPipes[1]);
                    @fclose($analyticsPipes[2]);
                    $code = @proc_close($analyticsProcess);
                    if ($code === 0 && !empty($out)) {
                        $analyticsOutput = $out;
                        $pythonSuccess = true;
                        break;
                    }
                }
            }
        }

        if (!$pythonSuccess && function_exists('exec')) {
            $analyticsPayload = json_encode($analyticsInput, JSON_THROW_ON_ERROR);
            foreach ($pythonCandidates as $candidate) {
                $analyticsTempFiles = [];
                try {
                    foreach (['input', 'output', 'error'] as $fileType) {
                        $analyticsTempFiles[$fileType] = tempnam(sys_get_temp_dir(), 'dashboard-analytics-');
                    }
                    if ($analyticsTempFiles['input'] && file_put_contents($analyticsTempFiles['input'], $analyticsPayload) === strlen($analyticsPayload)) {
                        $analyticsCommand = escapeshellarg($candidate)
                            . ' ' . escapeshellarg($analyticsScript)
                            . ' < ' . escapeshellarg($analyticsTempFiles['input'])
                            . ' > ' . escapeshellarg($analyticsTempFiles['output'])
                            . ' 2> ' . escapeshellarg($analyticsTempFiles['error']);
                        $analyticsCommandOutput = [];
                        $analyticsExitCode = -1;
                        @exec($analyticsCommand, $analyticsCommandOutput, $analyticsExitCode);
                        if ($analyticsExitCode === 0) {
                            $out = @file_get_contents($analyticsTempFiles['output']);
                            if (!empty($out)) {
                                $analyticsOutput = $out;
                                $pythonSuccess = true;
                                break;
                            }
                        }
                    }
                } finally {
                    foreach ($analyticsTempFiles as $tempFile) {
                        if (is_string($tempFile) && is_file($tempFile)) {
                            @unlink($tempFile);
                        }
                    }
                }
            }
        }

        // If Python process execution is not available on host, execute embedded analytics engine
        if (!$pythonSuccess) {
            require_once dirname(__DIR__, 2) . '/Services/DashboardAnalyticsService.php';
            $dashboardAnalytics = DashboardAnalyticsService::calculate($analyticsInput);
            $analyticsOutput = json_encode($dashboardAnalytics, JSON_THROW_ON_ERROR);
        }
    } else {
        require_once dirname(__DIR__, 2) . '/Services/DashboardAnalyticsService.php';
        $dashboardAnalytics = DashboardAnalyticsService::calculate($analyticsInput);
        $analyticsOutput = json_encode($dashboardAnalytics, JSON_THROW_ON_ERROR);
    }

    if (!is_string($analyticsOutput)) {
        throw new RuntimeException('Python analytics returned no response data.');
    }
    $dashboardAnalytics = json_decode((string)$analyticsOutput, true, 512, JSON_THROW_ON_ERROR);
    $requiredAnalyticsKeys = [
        'charts',
        'revenue_by_type',
        'revenue_by_method',
        'room_type_stats',
        'students_by_gender',
        'students_by_year',
        'total_capacity',
        'total_occupied_beds',
        'bed_occupancy_rate',
        'occupancy_rate',
    ];
    if (!is_array($dashboardAnalytics)) {
        throw new RuntimeException('Python analytics returned an invalid response shape.');
    }
    foreach ($requiredAnalyticsKeys as $key) {
        if (!array_key_exists($key, $dashboardAnalytics)) {
            throw new RuntimeException('Python analytics response is missing: ' . $key);
        }
    }
    if (
        !is_array($dashboardAnalytics['charts'])
        || !isset($dashboardAnalytics['charts']['monthly'])
        || !is_array($dashboardAnalytics['charts']['monthly'])
        || !is_array($dashboardAnalytics['charts']['monthly']['labels'] ?? null)
        || !is_array($dashboardAnalytics['charts']['monthly']['revenue'] ?? null)
        || !is_array($dashboardAnalytics['charts']['monthly']['reservations'] ?? null)
    ) {
        throw new RuntimeException('Python analytics returned an invalid monthly chart.');
    }
    foreach (['revenue_by_type', 'revenue_by_method', 'room_type_stats', 'students_by_gender', 'students_by_year'] as $rowsKey) {
        if (!is_array($dashboardAnalytics[$rowsKey])) {
            throw new RuntimeException('Python analytics returned invalid rows for ' . $rowsKey . '.');
        }
    }
    $monthlyChart = $dashboardAnalytics['charts']['monthly'];
    if (
        count($monthlyChart['labels']) !== 12
        || count($monthlyChart['revenue']) !== 12
        || count($monthlyChart['reservations']) !== 12
    ) {
        throw new RuntimeException('Python analytics must return exactly 12 monthly chart values.');
    }
    foreach ($monthlyChart['labels'] as $label) {
        if (!is_string($label)) {
            throw new RuntimeException('Python analytics returned an invalid chart label.');
        }
    }
    foreach ($monthlyChart['revenue'] as $value) {
        if (!is_numeric($value) || !is_finite((float)$value)) {
            throw new RuntimeException('Python analytics returned an invalid monthly revenue value.');
        }
    }
    foreach ($monthlyChart['reservations'] as $value) {
        if (!is_int($value) || $value < 0) {
            throw new RuntimeException('Python analytics returned an invalid reservation count.');
        }
    }
    foreach (['total_capacity', 'total_occupied_beds', 'bed_occupancy_rate', 'occupancy_rate'] as $numberKey) {
        if (!is_numeric($dashboardAnalytics[$numberKey]) || !is_finite((float)$dashboardAnalytics[$numberKey])) {
            throw new RuntimeException('Python analytics returned an invalid value for ' . $numberKey . '.');
        }
    }
    if (
        $dashboardAnalytics['total_capacity'] < 0
        || $dashboardAnalytics['total_occupied_beds'] < 0
        || $dashboardAnalytics['bed_occupancy_rate'] < 0
        || $dashboardAnalytics['bed_occupancy_rate'] > 100
        || $dashboardAnalytics['occupancy_rate'] < 0
        || $dashboardAnalytics['occupancy_rate'] > 100
    ) {
        throw new RuntimeException('Python analytics returned out-of-range occupancy values.');
    }
    $analyticsAvailable = true;
} catch (RuntimeException | JsonException $exception) {
    $analyticsError = $exception->getMessage();
    error_log('Admin dashboard Python analytics unavailable: ' . $analyticsError);
}

if (!$analyticsAvailable) {
    $dashboardAnalytics = [
        'charts' => ['monthly' => ['labels' => [], 'revenue' => [], 'reservations' => []]],
        'revenue_by_type' => [],
        'revenue_by_method' => [],
        'room_type_stats' => [],
        'students_by_gender' => [],
        'students_by_year' => [],
        'total_capacity' => 0,
        'total_occupied_beds' => 0,
        'bed_occupancy_rate' => 0,
        'occupancy_rate' => 0,
    ];
}

$revenueByType = $dashboardAnalytics['revenue_by_type'];
$revenueByMethod = $dashboardAnalytics['revenue_by_method'];
$roomTypeStats = $dashboardAnalytics['room_type_stats'];
$studentsByGender = $dashboardAnalytics['students_by_gender'];
$studentsByYear = $dashboardAnalytics['students_by_year'];
$totalCapacity = $dashboardAnalytics['total_capacity'];
$totalOccupiedBeds = $dashboardAnalytics['total_occupied_beds'];
$bedOccupancyRate = $dashboardAnalytics['bed_occupancy_rate'];
$occupancyRate = $dashboardAnalytics['occupancy_rate'];

$base = max(1, $totalRooms);
$roomCounts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'under_maintenance' => 0];
foreach (($roomStatus ?? []) as $rs) {
    if (isset($roomCounts[$rs['status']])) $roomCounts[$rs['status']] = (int)$rs['count'];
}
$roomPcts = [];
foreach ($roomCounts as $k => $v) $roomPcts[$k] = round(($v / $base) * 100);

$lastMonthRevenue = $lastMonthRevenue ?? 0;
if ($lastMonthRevenue > 0) {
    $revTrend = round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100);
} elseif ($thisMonthRevenue > 0) {
    $revTrend = 100;
} else {
    $revTrend = 0;
}

$monthlyAnalytics = $dashboardAnalytics['charts']['monthly'];
$chartLabels = $monthlyAnalytics['labels'];
$chartRevenue = $monthlyAnalytics['revenue'];
$chartReservations = $monthlyAnalytics['reservations'];

$typeLabelsMap = [
    'reservation_fee' => 'Reservation Fee',
    'advance_payment' => 'Advance Payment',
    'monthly_rent' => 'Monthly Rent',
    'electric_bill' => 'Electric Bill',
    'water_bill' => 'Water Bill',
    'other' => 'Other',
];
$typeColorsMap = [
    'reservation_fee' => '#8b5cf6',
    'advance_payment' => '#0ea5e9',
    'monthly_rent' => '#6366f1',
    'electric_bill' => '#f59e0b',
    'water_bill' => '#10b981',
    'other' => '#94a3b8',
];
$revTypeLabels = []; $revTypeData = []; $revTypeColors = [];
foreach (($revenueByType ?? []) as $rt) {
    $revTypeLabels[] = $typeLabelsMap[$rt['payment_type']] ?? ucwords(str_replace('_', ' ', $rt['payment_type']));
    $revTypeColors[] = $typeColorsMap[$rt['payment_type']] ?? '#94a3b8';
    $revTypeData[] = (float)$rt['total'];
}
$methodMap = ['cash' => ['Cash', '#10b981'], 'gcash' => ['GCash', '#6366f1']];
$methodLabels = []; $methodData = []; $methodColors = [];
foreach (($revenueByMethod ?? []) as $rm) {
    $methodLabels[] = $methodMap[$rm['method']][0] ?? ucfirst($rm['method']);
    $methodColors[] = $methodMap[$rm['method']][1] ?? '#94a3b8';
    $methodData[] = (float)$rm['total'];
}
$genderColors = ['male' => '#0ea5e9', 'female' => '#ec4899', 'other' => '#94a3b8'];
$genderLabels = []; $genderData = []; $genderColorsArr = [];
foreach (($studentsByGender ?? []) as $g) {
    $genderLabels[] = ucfirst($g['gender']);
    $genderData[] = (int)$g['c'];
    $genderColorsArr[] = $genderColors[$g['gender']] ?? '#94a3b8';
}
$roomTypeMeta = [
    'bedspacer' => ['label' => 'Bedspacer', 'icon' => 'fa-bed', 'color' => '#0ea5e9'],
    'single' => ['label' => 'Single', 'icon' => 'fa-user', 'color' => '#8b5cf6'],
    'studio' => ['label' => 'Studio', 'icon' => 'fa-building', 'color' => '#f59e0b'],
];
$roomTypeRows = [];
foreach (($roomTypeStats ?? []) as $rts) {
    $meta = $roomTypeMeta[$rts['room_type']] ?? ['label' => ucwords($rts['room_type']), 'icon' => 'fa-door-open', 'color' => '#94a3b8'];
    $cap = (int)($rts['capacity'] ?? 0);
    $roomTypeRows[] = [
        'label' => $meta['label'],
        'icon' => $meta['icon'],
        'color' => $meta['color'],
        'rooms_total' => (int)$rts['total_rooms'],
        'occupied' => (int)$rts['occupied'],
        'beds' => (int)($rts['occupied_beds'] ?? 0),
        'cap' => $cap,
        'pct' => $cap > 0 ? round(((int)($rts['occupied_beds'] ?? 0) / $cap) * 100) : 0,
    ];
}
$bestMonthLabel = is_array($bestMonth) ? (string)($bestMonth['label'] ?? 'N/A') : 'N/A';
$bestMonthTotal = is_array($bestMonth) ? (float)($bestMonth['total'] ?? 0) : 0;
$payTypeTotal = array_sum($revTypeData);
$methodTotal = array_sum($methodData);
$needsAttention = (int)$openMaintenance + (int)$openComplaints + (int)$unreadFeedback + (int)$newMessages;
?>

<style>
.s-go{max-width:1400px}
.s-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.s-head h2{font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-.5px}
.s-head p{margin:0;color:#64748b;font-size:13.5px}
.s-tools{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.s-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;text-decoration:none;color:#b45309;background:#fffbeb;border:1px solid #fde68a}
.s-pill.pink{color:#be185d;background:#fdf2f8;border-color:#fbcfe8}
.s-seg{display:flex;background:#eef2f7;border-radius:10px;padding:4px;gap:2px}
.s-seg span{cursor:pointer;padding:7px 14px;font-size:12.5px;font-weight:600;color:#64748b;border-radius:7px;transition:.15s;user-select:none}
.s-seg span:hover{color:#4f46e5}
.s-seg span.active{background:#fff;color:#4f46e5;box-shadow:0 1px 3px rgba(16,24,40,.12)}
.s-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:.2s;border:1.5px solid #e2e8f0;color:#475569;background:#fff}
.s-btn:hover{border-color:#6366f1;color:#4f46e5}
.s-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff}
.s-btn-primary:hover{color:#fff;box-shadow:0 6px 16px rgba(99,102,241,.35);border:none}

.s-card{background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);margin-bottom:20px}
.s-card-h{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:18px 22px;border-bottom:1px solid #f1f5f9}
.s-card-h h6{margin:0;font-weight:700;font-size:14.5px;color:#0f172a}
.s-card-h a{font-size:12.5px;font-weight:600;color:#6366f1;text-decoration:none}
.s-card-h a:hover{color:#4f46e5}
.s-card-b{padding:22px}

.s-kpi{position:relative;background:#fff;border-radius:16px;padding:20px 22px;height:100%;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);transition:transform .2s,box-shadow .2s}
.s-kpi:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(16,24,40,.08)}
.s-kpi-top{display:flex;align-items:flex-start;justify-content:space-between}
.s-kpi-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:19px}
.s-kpi-badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:4px 9px;border-radius:999px;white-space:nowrap}
.s-kpi-badge.up{background:#e7f8f1;color:#059669}
.s-kpi-badge.down{background:#fef2f2;color:#dc2626}
.s-kpi-badge.flat{background:#f1f5f9;color:#64748b}
.s-kpi-val{font-size:27px;font-weight:800;color:#0f172a;margin-top:14px;letter-spacing:-.5px;line-height:1.1}
.s-kpi-label{font-size:13px;color:#64748b;font-weight:500;margin-top:3px}
.s-kpi-foot{margin-top:13px;padding-top:12px;border-top:1px dashed #e9eef5;font-size:12px;color:#94a3b8;display:flex;align-items:center;gap:7px}
.s-kpi-foot i{font-size:11px}

.s-gauge-wrap{position:relative;height:190px}
.s-gauge-canvas{position:relative;max-width:280px;margin:0 auto}
.s-gauge-center{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
.s-gauge-num{font-size:30px;font-weight:800;color:#0f172a;line-height:1}
.s-gauge-cap{font-size:12px;color:#94a3b8;font-weight:600;margin-top:2px}
.s-gauge-labels{max-width:280px;margin:2px auto 0;padding:0 4px;display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;color:#94a3b8}
.s-gauge-status{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;margin-top:9px}
.s-gauge-stats{display:flex;gap:10px;margin-top:12px}
.s-gauge-stats .s-gs{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;background:#f8fafc;border:1px solid #eef2f7;border-radius:12px;padding:10px 6px}
.s-gauge-stats .s-gs i{font-size:13px}
.s-gauge-stats .s-gs b{font-size:15px;font-weight:800;color:#0f172a;line-height:1}
.s-gauge-stats .s-gs span{font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px}

.s-legend{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;color:#475569;background:#f8fafc;border:1px solid #e9eef5;padding:4px 10px;border-radius:999px}
.s-legend i{width:8px;height:8px;border-radius:50%;display:inline-block}
.s-chart-sum{display:flex;align-items:stretch;gap:12px;margin-top:14px;padding:12px 14px;background:#f8fafc;border:1px solid #eef2f7;border-radius:12px}
.s-chart-sum .c{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-align:center;min-width:0}
.s-chart-sum .c i{font-size:13px}
.s-chart-sum .c span{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.s-chart-sum .c b{font-size:15px;font-weight:800;color:#0f172a;white-space:nowrap}
.s-chart-sum .div{width:1px;align-self:stretch;background:#e6ecf3}

.s-prog{margin-bottom:18px}
.s-prog:last-child{margin-bottom:0}
.s-prog-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.s-prog-head .s-prog-name{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#334155}
.s-prog-head .s-prog-pct{font-size:13px;font-weight:700;color:#475569}
.s-prog-bar{height:9px;border-radius:6px;background:#f1f5f9;overflow:hidden}
.s-prog-fill{height:100%;border-radius:6px;transition:width .6s ease}

.s-tl{position:relative;padding-left:30px}
.s-tl::before{content:'';position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#eaf0f6}
.s-tl-item{position:relative;padding-bottom:16px}
.s-tl-item:last-child{padding-bottom:0}
.s-tl-dot{position:absolute;left:-30px;top:0;width:24px;height:24px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;min-width:24px}
.s-tl-text{font-size:13px;color:#334155;line-height:1.4}
.s-tl-time{font-size:11px;color:#94a3b8;margin-top:2px}

.s-list-item{display:flex;align-items:center;gap:14px;padding:13px 0;border-bottom:1px solid #f5f7fa}
.s-list-item:last-child{border-bottom:none;padding-bottom:0}
.s-list-ico{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:15px;min-width:38px}
.s-empty{text-align:center;padding:28px 12px;color:#94a3b8}
.s-empty i{font-size:34px;display:block;margin-bottom:10px;opacity:.5}
.s-empty p{margin:0;font-size:13px}
.s-table th{font-size:11.5px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;border-bottom:1px solid #f1f5f9;background:transparent!important;padding:.75rem}
.s-table td{font-size:13.5px;color:#334155;vertical-align:middle;border-bottom:1px solid #f5f7fa;padding:.8rem}
.s-table tr:last-child td{border-bottom:none}
.s-table tbody tr{transition:background .2s}
.s-table tbody tr:hover{background:#f8fafc}
.s-list-item{transition:background .2s;border-radius:10px;padding-left:8px;padding-right:8px;margin-left:-8px;margin-right:-8px}
.s-list-item:hover{background:#f8fafc}
.s-tl-item{transition:transform .2s}
.s-tl-item:hover{transform:translateX(3px)}
.s-card{transition:box-shadow .25s;}
.s-card:hover{box-shadow:0 1px 3px rgba(16,24,40,.05),0 14px 34px rgba(16,24,40,.08)}
.s-anim{opacity:0;transform:translateY(16px);transition:opacity .6s cubic-bezier(.22,.61,.36,1),transform .6s cubic-bezier(.22,.61,.36,1);transition-delay:var(--d,0s)}
.s-anim.in{opacity:1;transform:none}
.s-qa{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid rgba(226,232,240,.7);border-radius:14px;padding:10px 14px;margin-bottom:22px;box-shadow:0 1px 3px rgba(16,24,40,.04)}
.s-qa-title{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-right:4px}
.s-qa-btn{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#4f46e5;background:#eef2ff;border:1px solid #e0e7ff;border-radius:999px;padding:7px 14px;text-decoration:none;transition:all .2s}
.s-qa-btn:hover{background:#4f46e5;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(79,70,229,.28)}
.s-qa-btn small{font-weight:800}
.s-qa-btn.tenant{background:#fef3c7;border-color:#fde68a;color:#b45309}
.s-qa-btn.tenant:hover{background:#f59e0b;color:#fff;box-shadow:0 6px 16px rgba(245,158,11,.3)}
.s-qa-btn.purple{background:#f3eefe;border-color:#e9d5ff;color:#7c3aed}
.s-qa-btn.purple:hover{background:#8b5cf6;color:#fff;box-shadow:0 6px 16px rgba(139,92,246,.3)}
.s-qa-btn.green{background:#ecfdf5;border-color:#a7f3d0;color:#059669}
.s-qa-btn.green:hover{background:#10b981;color:#fff;box-shadow:0 6px 16px rgba(16,185,129,.3)}

/* Clickable card affordance: sheen sweep + pop-in "View" pill */
.s-click{position:relative;cursor:pointer}
.s-click::before{content:'';position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.65) 50%,transparent 60%);transform:translateX(-130%);transition:transform .75s ease}
.s-click:hover::before{transform:translateX(130%)}
.s-click .s-view-hint{position:absolute;left:50%;bottom:12px;transform:translate(-50%,14px);z-index:6;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:11px;font-weight:700;padding:6px 14px;border-radius:999px;box-shadow:0 8px 18px rgba(99,102,241,.4);opacity:0;transition:opacity .25s ease,transform .25s ease;pointer-events:none;white-space:nowrap}
.s-click:hover .s-view-hint{opacity:1;transform:translate(-50%,0)}
.s-kpi.s-click:hover{transform:translateY(-4px);box-shadow:0 16px 36px rgba(16,24,40,.14)}
.s-card.s-click{transition:transform .2s ease,box-shadow .25s ease;overflow:hidden}
.s-card.s-click:hover{transform:translateY(-4px);box-shadow:0 1px 3px rgba(16,24,40,.05),0 22px 46px rgba(16,24,40,.12)}
.s-list-item[role="button"]{cursor:pointer}
.s-table tbody tr[style*="cursor"]{transition:background .2s,transform .2s}
.s-table tbody tr[style*="cursor"]:hover{transform:translateX(2px)}
@media (prefers-reduced-motion: reduce){
  .s-anim,.s-prog-fill,.occupancy-fill,.s-kpi,.s-list-item,.s-tl-item,.s-card,.s-click{transition:none!important}
  .s-anim{opacity:1;transform:none}
  .s-click::before{display:none}
  .s-click .s-view-hint{opacity:0!important;transform:translate(-50%,14px)!important}
}
</style>

<div class="s-go">
    <?php if (!$analyticsAvailable): ?>
    <div class="alert alert-warning py-2 mb-3" role="status">
        Python analytics are currently unavailable. <?= e($analyticsError ?: 'Configure a Python analytics API endpoint for this host.') ?> Analytics values are not being calculated in PHP.
    </div>
    <?php endif; ?>
    <!-- Header -->
    <div class="s-head">
        <div>
            <h2 class="s-anim" style="--d:.02s">CDA MORM OVERVIEW</h2>
            <p class="s-anim" style="--d:.08s"><?= e($adminName) ?> &middot; <span id="liveClock"></span> &middot; here's your property performance at a glance.</p>
        </div>
        <div class="s-tools">
            <?php if ($pendingReservations > 0): ?>
            <a href="<?= url('/admin/reservations?status=pending') ?>" class="s-pill"><i class="fas fa-calendar-clock"></i> <?= $pendingReservations ?> Pending Reservation<?= $pendingReservations !== 1 ? 's' : '' ?></a>
            <?php endif; ?>
            <?php if ($pendingPayments > 0): ?>
            <a href="<?= url('/admin/payments?status=pending') ?>" class="s-pill pink"><i class="fas fa-money-bill"></i> <?= $pendingPayments ?> Pending Payment<?= $pendingPayments !== 1 ? 's' : '' ?></a>
            <?php endif; ?>
            <div class="s-seg" id="chartRange">
                <span data-months="3">3M</span>
                <span data-months="6" class="active">6M</span>
                <span data-months="12">12M</span>
            </div>
            <div class="dropdown">
                <button class="s-btn" data-bs-toggle="dropdown"><i class="fas fa-filter"></i> Filter</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= url('/admin/reservations?status=pending') ?>"><i class="fas fa-calendar-clock me-2"></i>Pending Reservations</a></li>
                    <li><a class="dropdown-item" href="<?= url('/admin/payments?status=pending') ?>"><i class="fas fa-money-bill me-2"></i>Pending Payments</a></li>
                    <li><a class="dropdown-item" href="<?= url('/admin/rooms?status=available') ?>"><i class="fas fa-door-open me-2"></i>Available Rooms</a></li>
                    <li><a class="dropdown-item" href="<?= url('/admin/students?status=active') ?>"><i class="fas fa-user-graduate me-2"></i>Active Students</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('/admin/reports') ?>"><i class="fas fa-chart-bar me-2"></i>Full Reports</a></li>
                </ul>
            </div>
            <a href="<?= url('/admin/reports') ?>" class="s-btn s-btn-primary"><i class="fas fa-download"></i> Export</a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="s-qa s-anim" style="--d:.03s;">
        <span class="s-qa-title"><i class="fas fa-bolt"></i> Quick Actions</span>
        <a href="<?= url('/admin/tenants') ?>" class="s-qa-btn tenant"><i class="fas fa-user-tie"></i> Tenants <small id="qaTenants"><?= (int)$activeTenants ?></small></a>
        <a href="<?= url('/admin/students/walk-in') ?>" class="s-qa-btn"><i class="fas fa-user-plus"></i> Walk-In Register</a>
        <a href="<?= url('/admin/students/walk-in-payment') ?>" class="s-qa-btn green"><i class="fas fa-cash-register"></i> Walk-In Payment</a>
        <a href="<?= url('/admin/students/walk-in-student-payment') ?>" class="s-qa-btn purple"><i class="fas fa-bolt"></i> Walk-In Student Payment</a>
    </div>

    <?php if ($needsAttention > 0): ?>
    <div class="s-qa s-anim" style="--d:.04s;border-color:#fee2e2;background:#fff7f7;margin-bottom:22px;">
        <span class="s-qa-title" style="color:#b91c1c;"><i class="fas fa-triangle-exclamation"></i> Needs Attention</span>
        <a href="<?= url('/admin/maintenance') ?>" class="s-qa-btn <?= $openMaintenance > 0 ? '' : 'disabled'?>" style="<?= $openMaintenance > 0 ? '' : 'opacity:.45;pointer-events:none;' ?>"><i class="fas fa-tools"></i> Maintenance <small><?= (int)$openMaintenance ?></small></a>
        <a href="<?= url('/admin/complaints') ?>" class="s-qa-btn <?= $openComplaints > 0 ? '' : ''?>" style="<?= $openComplaints > 0 ? '' : 'opacity:.45;pointer-events:none;' ?>"><i class="fas fa-flag"></i> Complaints <small><?= (int)$openComplaints ?></small></a>
        <a href="<?= url('/admin/feedback') ?>" class="s-qa-btn <?= $unreadFeedback > 0 ? '' : ''?>" style="<?= $unreadFeedback > 0 ? '' : 'opacity:.45;pointer-events:none;' ?>"><i class="fas fa-comment-dots"></i> Unreplied Feedback <small><?= (int)$unreadFeedback ?></small></a>
        <a href="<?= url('/admin/contact-messages') ?>" class="s-qa-btn <?= $newMessages > 0 ? '' : ''?>" style="<?= $newMessages > 0 ? '' : 'opacity:.45;pointer-events:none;' ?>"><i class="fas fa-envelope"></i> New Messages <small><?= (int)$newMessages ?></small></a>
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 s-anim" style="--d:.05s">
            <a href="<?= url('/admin/payments') ?>" class="text-decoration-none" title="View all payments">
                <div class="s-kpi s-click">
                    <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Payments</span>
                    <div class="s-kpi-top">
                        <div class="s-kpi-ico" style="background:#e7f8f1;color:#059669;"><i class="fas fa-coins"></i></div>
                    <span class="s-kpi-badge <?= $revTrend > 0 ? 'up' : ($revTrend < 0 ? 'down' : 'flat') ?>">
                        <i class="fas fa-<?= $revTrend > 0 ? 'arrow-up' : ($revTrend < 0 ? 'arrow-down' : 'minus') ?>"></i>
                        <?= $revTrend > 0 ? '+' : '' ?><?= $revTrend ?>%
                    </span>
                </div>
                <div class="s-kpi-val s-count" data-count="<?= (float)$thisMonthRevenue ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($thisMonthRevenue) ?></div>
                <div class="s-kpi-label">Total Sales (This Month)</div>
<div class="s-kpi-foot"><i class="fas fa-bolt"></i> <?= formatCurrency($todayRevenue) ?> today &middot; <i class="fas fa-calendar-week"></i> <?= formatCurrency($thisWeekRevenue) ?> this week</div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 s-anim" style="--d:.12s">
            <a href="<?= url('/admin/rooms') ?>" class="text-decoration-none" title="View rooms">
                <div class="s-kpi s-click">
                    <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Rooms</span>
                    <div class="s-kpi-top">
                        <div class="s-kpi-ico" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-door-open"></i></div>
                        <span class="s-kpi-badge flat"><i class="fas fa-chart-pie"></i> <?= $analyticsAvailable ? $bedOccupancyRate . '% bed fill' : 'Analytics unavailable' ?></span>
                    </div>
                    <div class="s-kpi-val s-count" data-count="<?= (int)$totalRooms ?>"><?= $totalRooms ?></div>
                    <div class="s-kpi-label">Total Rooms</div>
                    <div class="s-kpi-foot"><i class="fas fa-people-roof"></i> <?= $analyticsAvailable ? $totalOccupiedBeds . '/' . $totalCapacity . ' beds &middot; ' : '' ?><?= $occupiedRooms ?> rooms occupied</div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 s-anim" style="--d:.19s">
            <a href="<?= url('/admin/students') ?>" class="text-decoration-none" title="View students">
                <div class="s-kpi s-click">
                    <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Students</span>
                    <div class="s-kpi-top">
                        <div class="s-kpi-ico" style="background:#fef3c7;color:#d97706;"><i class="fas fa-user-graduate"></i></div>
                        <span class="s-kpi-badge up"><i class="fas fa-user-plus"></i> +<?= $newStudentsThisMonth ?> this month</span>
                    </div>
                    <div class="s-kpi-val s-count" data-count="<?= (int)$totalStudents ?>"><?= $totalStudents ?></div>
                    <div class="s-kpi-label">Total Students</div>
                    <div class="s-kpi-foot"><i class="fas fa-user-tie"></i> <?= $activeTenants ?> active tenants &middot; <i class="fas fa-calendar-plus"></i> +<?= $newReservationsThisMonth ?> reservations</div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 s-anim" style="--d:.26s">
            <a href="<?= url('/admin/rooms') ?>" class="text-decoration-none" title="View rooms">
                <div class="s-kpi s-click">
                    <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Rooms</span>
                    <div class="s-kpi-top">
                        <div class="s-kpi-ico" style="background:#e0f2fe;color:#0ea5e9;"><i class="fas fa-chart-pie"></i></div>
                    <span class="s-kpi-badge <?= $analyticsAvailable && $bedOccupancyRate >= 70 ? 'down' : 'flat' ?>"><i class="fas fa-fire"></i> <?= $analyticsAvailable ? ($bedOccupancyRate >= 70 ? 'High' : 'Healthy') : 'Unavailable' ?></span>
                </div>
                <div class="s-kpi-val <?= $analyticsAvailable ? 's-count' : '' ?>" <?= $analyticsAvailable ? 'data-count="' . (int)$bedOccupancyRate . '" data-suffix="%"' : '' ?>><?= $analyticsAvailable ? $bedOccupancyRate . '%' : 'N/A' ?></div>
                <div class="s-kpi-label">Occupancy Rate (Beds)</div>
                <div class="s-kpi-foot"><i class="fas fa-tag"></i> <?= $analyticsAvailable ? $totalOccupiedBeds . ' of ' . $totalCapacity . ' beds &middot; ' . $occupancyRate . '% rooms' : 'Python analytics unavailable' ?></div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="s-card s-anim" style="height:100%;margin-bottom:0;--d:.12s;">
                <div class="s-card-h">
                    <h6><i class="fas fa-chart-column me-2" style="color:#6366f1;"></i>Revenue &amp; Reservations</h6>
                    <div class="d-flex align-items-center gap-2 small flex-wrap">
                        <span class="s-legend"><i style="background:#6366f1"></i>Revenue</span>
                        <span class="s-legend"><i style="background:#f59e0b"></i>Reservations</span>
                        <a href="<?= url('/admin/payments') ?>" class="small ms-1">View Payments</a>
                    </div>
                </div>
                <div class="s-card-b" style="position:relative;">
                    <?php if ($analyticsAvailable): ?>
                    <div style="height:260px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="s-chart-sum">
                        <div class="c"><i class="fas fa-coins" style="color:#6366f1"></i><span>Revenue</span><b id="sumRev">--</b></div>
                        <div class="div"></div>
                        <div class="c"><i class="fas fa-chart-line" style="color:#f59e0b"></i><span>Avg / month</span><b id="avgRev">--</b></div>
                        <div class="div"></div>
                        <div class="c"><i class="fas fa-calendar-check" style="color:#8b5cf6"></i><span>Reservations</span><b id="sumRes">--</b></div>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-chart-column"></i><p>Chart data is unavailable until the Python analytics API is configured.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <?php $occStatus = $bedOccupancyRate >= 80 ? ['l' => 'High occupancy', 'i' => 'fa-fire', 'c' => '#059669', 'b' => '#ecfdf5'] : ($bedOccupancyRate >= 60 ? ['l' => 'Healthy', 'i' => 'fa-thumbs-up', 'c' => '#4f46e5', 'b' => '#eef2ff'] : ($bedOccupancyRate >= 40 ? ['l' => 'Moderate', 'i' => 'fa-signal', 'c' => '#d97706', 'b' => '#fffbeb'] : ['l' => 'Low occupancy', 'i' => 'fa-circle-info', 'c' => '#dc2626', 'b' => '#fef2f2'])); ?>
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.18s;" onclick="window.location='<?= url('/admin/rooms') ?>'" role="button" title="Manage rooms">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> Manage Rooms</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-gauge-high me-2" style="color:#0ea5e9;"></i>Occupancy Gauge</h6>
                    <a href="<?= url('/admin/rooms') ?>">Manage</a>
                </div>
                <div class="s-card-b">
                    <?php if ($analyticsAvailable): ?>
                    <div class="s-gauge-wrap">
                        <div class="s-gauge-canvas">
                            <canvas id="occupancyGauge" height="150"></canvas>
                            <div class="s-gauge-center">
                                <div class="s-gauge-num s-count" data-count="<?= (int)$bedOccupancyRate ?>" data-suffix="%"><?= $bedOccupancyRate ?>%</div>
                                <div class="s-gauge-cap">Beds Occupied</div>
                                <div class="s-gauge-status" style="background:<?= $occStatus['b'] ?>;color:<?= $occStatus['c'] ?>;"><i class="fas <?= $occStatus['i'] ?>"></i><?= $occStatus['l'] ?></div>
                            </div>
                        </div>
                        <div class="s-gauge-labels"><span>0%</span><span>50%</span><span>100%</span></div>
                    </div>
                    <div class="s-gauge-stats">
                        <div class="s-gs"><i class="fas fa-bed" style="color:#f59e0b"></i><b><?= $totalOccupiedBeds ?>/<?= $totalCapacity ?></b><span>Beds</span></div>
                        <div class="s-gs"><i class="fas fa-door-open" style="color:#10b981"></i><b><?= $availableRooms ?></b><span>Available</span></div>
                        <div class="s-gs"><i class="fas fa-lock" style="color:#8b5cf6"></i><b><?= $fullyOccupiedRooms ?></b><span>Full Rooms</span></div>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-gauge-high"></i><p>Occupancy analytics are unavailable until the Python analytics API is configured.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.2s;" onclick="window.location='<?= url('/admin/reports') ?>'" role="button" title="Open reports">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> Open Reports</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-layer-group me-2" style="color:#8b5cf6;"></i>Sales by Type (This Month)</h6>
                    <a href="<?= url('/admin/reports') ?>">Reports</a>
                </div>
                <div class="s-card-b">
                    <?php if ($payTypeTotal > 0): ?>
                    <div style="height:150px;position:relative;">
                        <canvas id="typeChart"></canvas>
                        <div class="s-gauge-center" style="pointer-events:none;">
                            <b style="font-size:22px;font-weight:800;color:#0f172a;"><?= formatCurrency($payTypeTotal) ?></b>
                            <div style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;">total</div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-column gap-2">
                        <?php foreach ($revenueByType as $rt): ?>
                        <?php $lbl = $typeLabelsMap[$rt['payment_type']] ?? ucwords(str_replace('_', ' ', $rt['payment_type'])); $clr = $typeColorsMap[$rt['payment_type']] ?? '#94a3b8'; $amt = (float)$rt['total']; ?>
                        <div class="d-flex align-items-center gap-2" style="font-size:12px;">
                            <i style="width:9px;height:9px;border-radius:50%;background:<?= $clr ?>;display:inline-block;"></i>
                            <span class="flex-grow-1" style="color:#64748b;font-weight:600;"><?= e($lbl) ?></span>
                            <span class="fw-bold" style="color:#0f172a;"><?= formatCurrency($amt) ?></span>
                            <span style="color:#94a3b8;font-size:11px;min-width:34px;text-align:right;"><?= $payTypeTotal > 0 ? round(($amt / $payTypeTotal) * 100) : 0 ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-receipt"></i><p><?= $analyticsAvailable ? 'No sales recorded this month.' : 'Sales analytics unavailable until the Python API is configured.' ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.26s;" onclick="window.location='<?= url('/admin/payments') ?>'" role="button" title="View all payments">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Payments</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-money-bill-wave me-2" style="color:#059669;"></i>Payment Method</h6>
                    <span class="s-legend">Lifetime</span>
                </div>
                <div class="s-card-b">
                    <?php if ($methodTotal > 0): ?>
                    <div style="height:150px;position:relative;">
                        <canvas id="methodChart"></canvas>
                        <div class="s-gauge-center" style="pointer-events:none;">
                            <b style="font-size:22px;font-weight:800;color:#0f172a;"><?= formatCurrency($methodTotal) ?></b>
                            <div style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;">collected</div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-column gap-2">
                        <?php foreach ($revenueByMethod as $rm): ?>
                        <?php $mlbl = $methodMap[$rm['method']][0] ?? ucfirst($rm['method']); $mclr = $methodMap[$rm['method']][1] ?? '#94a3b8'; $mamt = (float)$rm['total']; ?>
                        <div class="d-flex align-items-center gap-2" style="font-size:12px;">
                            <i style="width:9px;height:9px;border-radius:50%;background:<?= $mclr ?>;display:inline-block;"></i>
                            <span class="flex-grow-1" style="color:#64748b;font-weight:600;"><?= e($mlbl) ?></span>
                            <span class="fw-bold" style="color:#0f172a;"><?= formatCurrency($mamt) ?></span>
                            <span style="color:#94a3b8;font-size:11px;min-width:34px;text-align:right;"><?= $methodTotal > 0 ? round(($mamt / $methodTotal) * 100) : 0 ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-wallet"></i><p><?= $analyticsAvailable ? 'No payment records yet.' : 'Payment analytics unavailable until the Python API is configured.' ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.32s;" onclick="window.location='<?= url('/admin/reports') ?>'" role="button" title="Open full report">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> Full Report</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-chart-line me-2" style="color:#6366f1;"></i>Revenue Summary</h6>
                    <a href="<?= url('/admin/reports') ?>">Full Report</a>
                </div>
                <div class="s-card-b">
                    <?php
                    $weekTrend = $lastWeekRevenue > 0 ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100) : ($thisWeekRevenue > 0 ? 100 : 0);
                    $revSummary = [
                        ['icon' => 'fa-bolt', 'color' => '#d97706', 'bg' => '#fffbeb', 'label' => "Today's Sales", 'value' => formatCurrency($todayRevenue), 'trend' => ''],
                        ['icon' => 'fa-calendar-week', 'color' => '#059669', 'bg' => '#ecfdf5', 'label' => 'This Week vs Last', 'value' => formatCurrency($thisWeekRevenue), 'trend' => ($weekTrend !== 0 ? (($weekTrend > 0 ? '+' : '') . $weekTrend . '%') : '')],
                        ['icon' => 'fa-calendar-alt', 'color' => '#7c3aed', 'bg' => '#f3eefe', 'label' => 'Avg Monthly (Lifetime)', 'value' => formatCurrency($avgMonthlyRevenue), 'trend' => ''],
                        ['icon' => 'fa-trophy', 'color' => '#be185d', 'bg' => '#fdf2f8', 'label' => 'Best Month: ' . e($bestMonthLabel), 'value' => formatCurrency($bestMonthTotal), 'trend' => ''],
                        ['icon' => 'fa-piggy-bank', 'color' => '#0ea5e9', 'bg' => '#ecfeff', 'label' => 'Lifetime Sales', 'value' => formatCurrency($totalRevenue), 'trend' => ''],
                    ];
                    ?>
                    <?php foreach ($revSummary as $rs): ?>
                    <div class="d-flex align-items-center gap-3" style="padding:9px 0;border-bottom:1px dashed #f1f5f9;">
                        <div style="width:36px;height:36px;border-radius:10px;background:<?= $rs['bg'] ?>;color:<?= $rs['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:13px;min-width:36px;"><i class="fas <?= $rs['icon'] ?>"></i></div>
                        <div class="flex-grow-1">
                            <div style="font-size:11.5px;color:#64748b;font-weight:600;"><?= $rs['label'] ?></div>
                            <div style="font-size:15px;font-weight:800;color:#0f172a;line-height:1.2;"><?= $rs['value'] ?></div>
                        </div>
                        <?php if ($rs['trend'] !== ''): ?>
                        <span class="s-kpi-badge <?= $weekTrend > 0 ? 'up' : ($weekTrend < 0 ? 'down' : 'flat') ?>"><i class="fas fa-<?= $weekTrend > 0 ? 'arrow-up' : ($weekTrend < 0 ? 'arrow-down' : 'minus') ?>"></i> <?= $rs['trend'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.38s;" onclick="window.location='<?= url('/admin/students') ?>'" role="button" title="View students">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View Students</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-users me-2" style="color:#0ea5e9;"></i>Tenant Demographics</h6>
                    <a href="<?= url('/admin/students') ?>">Students</a>
                </div>
                <div class="s-card-b">
                <?php if ($analyticsAvailable): ?>
                <div style="height:150px;position:relative;">
                    <canvas id="genderChart"></canvas>
                        <div class="s-gauge-center" style="pointer-events:none;">
                            <b style="font-size:22px;font-weight:800;color:#0f172a;"><?= (int)$totalStudents ?></b>
                            <div style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;">students</div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex align-items-center gap-3 justify-content-center flex-wrap">
                        <?php foreach ($studentsByGender as $g): ?>
                        <?php $glbl = ucfirst($g['gender']); $gclr = $genderColors[$g['gender']] ?? '#94a3b8'; $gc = (int)$g['c']; ?>
                        <span style="font-size:12px;font-weight:600;color:#475569;"><i style="width:9px;height:9px;border-radius:50%;background:<?= $gclr ?>;display:inline-block;margin-right:4px;"></i><?= e($glbl ) ?> <?= $gc ?> (<?= $totalStudents > 0 ? round(($gc / $totalStudents) * 100) : 0 ?>%)</span>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-users"></i><p>Student demographics are unavailable until the Python API is configured.</p></div>
                    <?php endif; ?>
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e9eef5;">
                        <?php foreach (($studentsByYear ?? []) as $y): ?>
                        <?php $ypct = $totalStudents > 0 ? round(((int)$y['c'] / $totalStudents) * 100) : 0; ?>
                        <div class="d-flex align-items-center justify-content-between" style="font-size:12px;margin-bottom:6px;">
                            <span style="color:#64748b;font-weight:600;"><?= e($y['year_level']) ?></span>
                            <span class="fw-bold" style="color:#0f172a;"><?= (int)$y['c'] ?> <small style="color:#94a3b8;font-weight:600;">(<?= $ypct ?>%)</small></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress + Activity -->
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.24s;" onclick="window.location='<?= url('/admin/rooms') ?>'" role="button" title="Manage rooms">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> Manage Rooms</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-door-open me-2" style="color:#7c3aed;"></i>Room Status</h6>
                    <a href="<?= url('/admin/rooms') ?>">Manage Rooms</a>
                </div>
                <div class="s-card-b">
                    <?php
                    $roomMeta = [
                        'available' => ['label' => 'Available', 'color' => '#10b981', 'icon' => 'fa-check-circle'],
                        'occupied' => ['label' => 'Occupied', 'color' => '#f59e0b', 'icon' => 'fa-bed'],
                        'reserved' => ['label' => 'Reserved', 'color' => '#8b5cf6', 'icon' => 'fa-calendar-check'],
                        'under_maintenance' => ['label' => 'Maintenance', 'color' => '#ef4444', 'icon' => 'fa-tools'],
                    ];
                    ?>
                    <?php foreach ($roomMeta as $rk => $rm): $cnt = $roomCounts[$rk]; $pct = $roomPcts[$rk]; ?>
                    <div class="s-prog">
                        <div class="s-prog-head">
                            <span class="s-prog-name"><i class="fas <?= $rm['icon'] ?>" style="color:<?= $rm['color'] ?>;"></i><?= $rm['label'] ?></span>
                            <span class="s-prog-pct"><?= $cnt ?> <small style="font-weight:600;color:#94a3b8;">(&#8203;<?= $pct ?>%)</small></span>
                        </div>
                        <div class="s-prog-bar"><div class="s-prog-fill" data-w="<?= $pct ?>" style="width:0;background:<?= $rm['color'] ?>;"></div></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!empty($roomTypeRows)): ?>
                    <div style="margin-top:18px;padding-top:14px;border-top:1px dashed #e9eef5;">
                        <div style="font-size:11.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;"><i class="fas fa-layer-group me-1"></i> Occupancy by Room Type</div>
                        <?php foreach ($roomTypeRows as $rtr): ?>
                        <div class="s-prog">
                            <div class="s-prog-head">
                                <span class="s-prog-name"><i class="fas <?= $rtr['icon'] ?>" style="color:<?= $rtr['color'] ?>;"></i><?= e($rtr['label']) ?></span>
                                <span class="s-prog-pct"><?= $rtr['beds'] ?>/<?= $rtr['cap'] ?> beds &middot; <?= $rtr['occupied'] ?>/<?= $rtr['rooms_total'] ?> rooms &middot; <small style="font-weight:600;color:#94a3b8;"><?= $rtr['pct'] ?>%</small></span>
                            </div>
                            <div class="s-prog-bar"><div class="s-prog-fill" data-w="<?= $rtr['pct'] ?>" style="width:0;background:<?= $rtr['color'] ?>;"></div></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($totalRooms > 0): ?>
                    <div class="d-flex align-items-center justify-content-between" style="margin-top:18px;padding-top:16px;border-top:1px dashed #e9eef5;">
                        <span style="font-size:12.5px;font-weight:600;color:#64748b;">Overall Occupancy</span>
                        <span style="font-size:17px;font-weight:800;color:#4f46e5;"><?= $analyticsAvailable ? $occupancyRate . '%' : 'N/A' ?></span>
                    </div>
                    <div style="height:6px;background:#eef2f7;border-radius:3px;margin-top:8px;overflow:hidden;">
                        <div class="occupancy-fill" data-w="<?= $analyticsAvailable ? $occupancyRate : 0 ?>" style="height:100%;width:0;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;transition:width .6s;"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="s-card s-anim s-click" style="height:100%;margin-bottom:0;--d:.3s;" onclick="window.location='<?= url('/admin/activity-logs') ?>'" role="button" title="View activity logs">
                <span class="s-view-hint"><i class="fas fa-arrow-right me-1"></i> View All</span>
                <div class="s-card-h">
                    <h6><i class="fas fa-bolt me-2" style="color:#f59e0b;"></i>Recent Activity</h6>
                    <a href="<?= url('/admin/activity-logs') ?>">View All</a>
                </div>
                <div class="s-card-b">
                    <?php if (!empty($recentActivity)): ?>
                    <div class="s-tl">
                        <?php
                        $actIcons = [
                            'submit_payment' => ['icon' => 'money-bill', 'color' => '#16a34a', 'bg' => '#dcfce7'],
                            'create_reservation' => ['icon' => 'calendar-plus', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                            'approve_reservation' => ['icon' => 'calendar-check', 'color' => '#16a34a', 'bg' => '#dcfce7'],
                            'cancel_reservation' => ['icon' => 'calendar-times', 'color' => '#dc2626', 'bg' => '#fef2f2'],
                            'login' => ['icon' => 'sign-in-alt', 'color' => '#2563eb', 'bg' => '#dbeafe'],
                            'logout' => ['icon' => 'sign-out-alt', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                            'update_profile' => ['icon' => 'user-edit', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                            'submit_maintenance' => ['icon' => 'tools', 'color' => '#db2777', 'bg' => '#fce7f3'],
                            'create_maintenance' => ['icon' => 'tools', 'color' => '#db2777', 'bg' => '#fce7f3'],
                            'create_complaint' => ['icon' => 'flag', 'color' => '#ea580c', 'bg' => '#fff7ed'],
                            'create_feedback' => ['icon' => 'comment-dots', 'color' => '#0891b2', 'bg' => '#ecfeff'],
                            'verify_payment' => ['icon' => 'check-circle', 'color' => '#16a34a', 'bg' => '#dcfce7'],
                            'reject_payment' => ['icon' => 'times-circle', 'color' => '#dc2626', 'bg' => '#fef2f2'],
                        ];
                        ?>
                        <?php foreach ($recentActivity as $log): ?>
                        <?php $act = $actIcons[$log['action'] ?? 'other'] ?? ['icon' => 'info-circle', 'color' => '#64748b', 'bg' => '#f1f5f9']; ?>
                        <div class="s-tl-item">
                            <div class="s-tl-dot" style="background:<?= $act['bg'] ?>;color:<?= $act['color'] ?>;"><i class="fas fa-<?= $act['icon'] ?>"></i></div>
                            <div class="s-tl-text"><strong><?= e(truncate($log['email'] ?? 'System', 22)) ?></strong> <?= e($log['description'] ?? $log['action']) ?></div>
                            <div class="s-tl-time"><?= timeAgo($log['created_at']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-history"></i><p>No recent activity.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="s-card s-anim" style="height:100%;margin-bottom:0;--d:.36s;">
                <div class="s-card-h">
                    <h6><i class="fas fa-calendar-check me-2" style="color:#8b5cf6;"></i>Recent Reservations</h6>
                    <a href="<?= url('/admin/reservations') ?>">View All</a>
                </div>
                <div class="s-card-b" style="padding:10px 22px 18px;">
                    <?php if (!empty($recentReservations)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 s-table">
                            <thead><tr><th>Student</th><th>Room</th><th>Date</th><th class="text-end">Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentReservations as $r): ?>
                                <tr style="cursor:pointer;" onclick="window.location='<?= url('/admin/reservations') ?>'" title="View reservation">
                                    <td><span class="fw-semibold"><?= e(trim($r['first_name'] . ' ' . ($r['last_name'] ?? ''))) ?></span></td>
                                    <td><span style="color:#4f46e5;" class="fw-semibold"><?= e($r['room_number']) ?></span> <small class="text-muted">- <?= e($r['room_name']) ?></small></td>
                                    <td class="small text-muted"><?= formatDate($r['created_at']) ?></td>
                                    <td class="text-end"><?= statusBadge($r['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-calendar-times"></i><p>No reservations yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="s-card s-anim" style="height:100%;margin-bottom:0;--d:.42s;">
                <div class="s-card-h">
                    <h6><i class="fas fa-money-bill-wave me-2" style="color:#059669;"></i>Recent Payments</h6>
                    <a href="<?= url('/admin/payments') ?>">View All</a>
                </div>
                <div class="s-card-b">
                    <?php if (!empty($recentPayments)): ?>
                    <?php foreach (array_slice($recentPayments, 0, 5) as $i => $p): ?>
                    <?php
                    $payStatus = $p['status'] ?? 'pending';
                    $payColor = $payStatus === 'paid' ? '#16a34a' : ($payStatus === 'pending' ? '#d97706' : '#dc2626');
                    $payBg = $payStatus === 'paid' ? '#dcfce7' : ($payStatus === 'pending' ? '#fef3c7' : '#fee2e2');
                    ?>
                    <div class="s-list-item" style="<?= $i === 0 ? 'padding-top:0;' : '' ?>" onclick="window.location='<?= url('/admin/payments') ?>'" role="button">
                        <div class="s-list-ico" style="background:<?= $payBg ?>;color:<?= $payColor ?>;"><i class="fas fa-receipt"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?= e(trim($p['first_name'] . ' ' . ($p['last_name'] ?? ''))) ?></div>
                            <div style="font-size:11px;color:#94a3b8;"><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?> &middot; <?= formatDate($p['created_at'] ?? '') ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="color:<?= $payColor ?>;font-size:14px;"><?= formatCurrency((float)$p['amount']) ?></div>
                            <?= statusBadge($p['status']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="s-empty"><i class="fas fa-receipt"></i><p>No payments yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Totals Summary -->
    <div class="row g-4 mb-4">
        <div class="col-xl-12">
            <div class="s-card s-anim" style="margin-bottom:0;--d:.48s;">
                <div class="s-card-h">
                    <h6><i class="fas fa-calculator me-2" style="color:#059669;"></i>Financial Totals Summary</h6>
                    <a href="<?= url('/admin/reports') ?>">View Full Report</a>
                </div>
                <div class="s-card-b">
                    <div class="row g-3">
                        <!-- Revenue Period Totals -->
                        <div class="col-xl-4 col-md-6">
                            <div class="s-card" style="margin-bottom:0;background:#f8fafc;border-color:#eef2f7;">
                                <div class="s-card-b" style="padding:16px 18px;">
                                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-bolt me-1"></i> Revenue by Period</div>
                                    <table class="table mb-0" style="font-size:12.5px;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <th style="padding:.5rem 0;color:#64748b;font-weight:600;">Period</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Today</td><td class="text-end" style="padding:.5rem 0;color:#059669;font-weight:700;"><?= formatCurrency($todayRevenue ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">This Week</td><td class="text-end" style="padding:.5rem 0;color:#059669;font-weight:700;"><?= formatCurrency($thisWeekRevenue ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Last Week</td><td class="text-end" style="padding:.5rem 0;color:#64748b;"><?= formatCurrency($lastWeekRevenue ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">This Month</td><td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:800;"><?= formatCurrency($thisMonthRevenue ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Last Month</td><td class="text-end" style="padding:.5rem 0;color:#64748b;"><?= formatCurrency($lastMonthRevenue ?? 0) ?></td></tr>
                                            <tr style="border-top:2px solid #e2e8f0;"><td style="padding:.5rem 0;color:#0f172a;font-weight:800;">Lifetime</td><td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:800;"><?= formatCurrency($totalRevenue ?? 0) ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="s-card" style="margin-bottom:0;background:#f8fafc;border-color:#eef2f7;">
                                <div class="s-card-b" style="padding:16px 18px;">
                                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-chart-line me-1"></i> Key Metrics</div>
                                    <table class="table mb-0" style="font-size:12.5px;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <th style="padding:.5rem 0;color:#64748b;font-weight:600;">Metric</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Avg Monthly Revenue</td><td class="text-end" style="padding:.5rem 0;color:#7c3aed;font-weight:700;"><?= formatCurrency($avgMonthlyRevenue ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Best Month: <?= e($bestMonthLabel) ?></td><td class="text-end" style="padding:.5rem 0;color:#be185d;font-weight:700;"><?= formatCurrency($bestMonthTotal ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Total Payments Collected</td><td class="text-end" style="padding:.5rem 0;color:#059669;font-weight:700;"><?= number_format($totalPayments ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Avg Payment Amount</td><td class="text-end" style="padding:.5rem 0;color:#64748b;"><?= formatCurrency($avgPayment ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Pending Payments</td><td class="text-end" style="padding:.5rem 0;color:#d97706;font-weight:700;"><?= formatCurrency($pendingPaymentsAmount ?? 0) ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="s-card" style="margin-bottom:0;background:#f8fafc;border-color:#eef2f7;">
                                <div class="s-card-b" style="padding:16px 18px;">
                                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-building me-1"></i> Occupancy & Inventory</div>
                                    <table class="table mb-0" style="font-size:12.5px;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <th style="padding:.5rem 0;color:#64748b;font-weight:600;">Metric</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Total Rooms</td><td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:700;"><?= number_format($totalRooms ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Occupied Rooms</td><td class="text-end" style="padding:.5rem 0;color:#f59e0b;font-weight:700;"><?= number_format($occupiedRooms ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Available Rooms</td><td class="text-end" style="padding:.5rem 0;color:#10b981;font-weight:700;"><?= number_format($availableRooms ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Reserved Rooms</td><td class="text-end" style="padding:.5rem 0;color:#8b5cf6;font-weight:700;"><?= number_format($reservedRooms ?? 0) ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Occupancy Rate (Rooms)</td><td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:700;"><?= $analyticsAvailable ? number_format($occupancyRate, 1) . '%' : 'N/A' ?></td></tr>
                                            <tr><td style="padding:.5rem 0;color:#334155;font-weight:600;">Bed Occupancy Rate</td><td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:700;"><?= $analyticsAvailable ? number_format($bedOccupancyRate, 1) . '%' : 'N/A' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Type Breakdown -->
                    <div class="row g-3 mt-3">
                        <div class="col-xl-6 col-md-12">
                            <div class="s-card" style="margin-bottom:0;background:#f8fafc;border-color:#eef2f7;">
                                <div class="s-card-b" style="padding:16px 18px;">
                                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-layer-group me-1"></i> Revenue by Payment Type (This Month)</div>
                                    <?php if (!empty($revenueByType) && $payTypeTotal > 0): ?>
                                    <table class="table mb-0" style="font-size:12.5px;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <th style="padding:.5rem 0;color:#64748b;font-weight:600;">Type</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;">Amount</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;width:55px;">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($revenueByType as $rt): 
                                                $lbl = $typeLabelsMap[$rt['payment_type']] ?? ucwords(str_replace('_', ' ', $rt['payment_type']));
                                                $clr = $typeColorsMap[$rt['payment_type']] ?? '#94a3b8';
                                                $amt = (float)$rt['total'];
                                                $pct = $payTypeTotal > 0 ? round(($amt / $payTypeTotal) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td style="padding:.5rem 0;color:#334155;font-weight:600;">
                                                    <span style="display:inline-flex;align-items:center;gap:6px;">
                                                        <i style="width:8px;height:8px;border-radius:50%;background:<?= $clr ?>;display:inline-block;"></i>
                                                        <?= e($lbl) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end" style="padding:.5rem 0;color:#0f172a;font-weight:700;"><?= formatCurrency($amt) ?></td>
                                                <td class="text-end" style="padding:.5rem 0;color:#94a3b8;font-weight:600;"><?= $pct ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr style="border-top:2px solid #e2e8f0;">
                                                <td style="padding:.5rem 0;color:#0f172a;font-weight:800;">Total</td>
                                                <td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:800;"><?= formatCurrency($payTypeTotal) ?></td>
                                                <td class="text-end" style="padding:.5rem 0;color:#94a3b8;font-weight:700;">100%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php else: ?>
                                    <div class="s-empty" style="padding:16px;"><i class="fas fa-receipt"></i><p>No payment type data this month.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-12">
                            <div class="s-card" style="margin-bottom:0;background:#f8fafc;border-color:#eef2f7;">
                                <div class="s-card-b" style="padding:16px 18px;">
                                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-money-bill-wave me-1"></i> Revenue by Payment Method (Lifetime)</div>
                                    <?php if (!empty($revenueByMethod) && $methodTotal > 0): ?>
                                    <table class="table mb-0" style="font-size:12.5px;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <th style="padding:.5rem 0;color:#64748b;font-weight:600;">Method</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;">Amount</th>
                                                <th class="text-end" style="padding:.5rem 0;color:#64748b;font-weight:600;width:55px;">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($revenueByMethod as $rm): 
                                                $mlbl = $methodMap[$rm['method']][0] ?? ucfirst($rm['method']);
                                                $mclr = $methodMap[$rm['method']][1] ?? '#94a3b8';
                                                $mamt = (float)$rm['total'];
                                                $mpct = $methodTotal > 0 ? round(($mamt / $methodTotal) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td style="padding:.5rem 0;color:#334155;font-weight:600;">
                                                    <span style="display:inline-flex;align-items:center;gap:6px;">
                                                        <i style="width:8px;height:8px;border-radius:50%;background:<?= $mclr ?>;display:inline-block;"></i>
                                                        <?= e($mlbl) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end" style="padding:.5rem 0;color:#0f172a;font-weight:700;"><?= formatCurrency($mamt) ?></td>
                                                <td class="text-end" style="padding:.5rem 0;color:#94a3b8;font-weight:600;"><?= $mpct ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr style="border-top:2px solid #e2e8f0;">
                                                <td style="padding:.5rem 0;color:#0f172a;font-weight:800;">Total</td>
                                                <td class="text-end" style="padding:.5rem 0;color:#4f46e5;font-weight:800;"><?= formatCurrency($methodTotal) ?></td>
                                                <td class="text-end" style="padding:.5rem 0;color:#94a3b8;font-weight:700;">100%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php else: ?>
                                    <div class="s-empty" style="padding:16px;"><i class="fas fa-wallet"></i><p>No payment method data.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue + Reservations combo chart
    var allLabels = <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var allRevenue = <?= json_encode($chartRevenue, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var allRes = <?= json_encode($chartReservations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var symbol = window.APP_CURRENCY_SYMBOL || '\u20b1';
    var fmtMoney = function(v) { return symbol + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var sbhGridCharts = [];
    var sbhTrackCharts = [];

    var revCtx = document.getElementById('revenueChart');
    var revChart = null;
    if (revCtx) {
        revChart = new Chart(revCtx, {
            type: 'bar',
            data: { labels: [], datasets: [
                { type: 'bar', label: 'Revenue', yAxisID: 'y', data: [], backgroundColor: function(ctx) { var g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 260); g.addColorStop(0, '#6366f1'); g.addColorStop(1, '#a5b4fc'); return g; }, hoverBackgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 34 },
                { type: 'line', label: 'Reservations', yAxisID: 'y1', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', borderWidth: 2.5, tension: .4, pointBackgroundColor: '#f59e0b', pointRadius: 4, pointHoverRadius: 6, fill: true }
            ]},
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                onClick: function() { window.location = '<?= url('/admin/payments') ?>'; },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12, weight: '600' } } },
                    tooltip: {
                        backgroundColor: '#1e293b', titleFont: { size: 13, weight: '600' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.datasetIndex === 0) {
                                    var v = ctx.parsed.y;
                                    return 'Revenue: ' + symbol + (v === Math.round(v) ? Math.round(v).toLocaleString() : v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                                }
                                return 'Reservations: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', ticks: { font: { size: 11 }, color: '#94a3b8', callback: function(v) { return symbol + v.toLocaleString(); } }, grid: { color: isDark ? 'rgba(255,255,255,.08)' : '#f1f5f9', drawBorder: false } },
                    x: { grid: { display: false }, ticks: { font: { size: 11.5, weight: '600' }, color: isDark ? '#94a3b8' : '#64748b' } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 11 }, color: '#d97706', precision: 0 } }
                }
            }
        });

        function applyRange(months) {
            var n = Math.max(1, Math.min(months, allLabels.length));
            var slice = allLabels.slice(allLabels.length - n, allLabels.length);
            var rev = allRevenue.slice(allLabels.length - n, allLabels.length);
            var res = allRes.slice(allLabels.length - n, allLabels.length);
            revChart.data.labels = slice;
            revChart.data.datasets[0].data = rev;
            revChart.data.datasets[1].data = res;
            var sumRev = rev.reduce(function(a, b) { return a + b; }, 0);
            var sumRes = res.reduce(function(a, b) { return a + b; }, 0);
            var avg = res.length ? sumRev / res.length : 0;
            document.getElementById('sumRev').textContent = fmtMoney(sumRev);
            document.getElementById('avgRev').textContent = fmtMoney(avg);
            document.getElementById('sumRes').textContent = sumRes.toLocaleString();
            revChart.update();
        }
        applyRange(6);

        document.querySelectorAll('#chartRange span').forEach(function(el) {
            el.addEventListener('click', function() {
                document.querySelectorAll('#chartRange span').forEach(function(s) { s.classList.remove('active'); });
                el.classList.add('active');
                applyRange(parseInt(el.dataset.months, 10));
            });
        });

        if (revChart) sbhGridCharts.push(revChart);
    }

    // Occupancy semi-circle gauge
    var gaugeCtx = document.getElementById('occupancyGauge');
    if (gaugeCtx) {
        var occ = Math.max(0, Math.min(100, <?= (int)$bedOccupancyRate ?>));
        var occColor = occ >= 80 ? '#10b981' : occ >= 60 ? '#6366f1' : occ >= 40 ? '#f59e0b' : '#ef4444';
        var occChart = new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [occ, 100 - occ],
                    backgroundColor: [occColor, isDark ? 'rgba(255,255,255,.1)' : '#eef2f7'],
                    borderWidth: 0,
                    hoverOffset: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                rotation: -Math.PI,
                animation: { duration: 1200, easing: 'easeOutQuart' },
                circumference: Math.PI,
                cutout: '74%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
        sbhTrackCharts.push(occChart);
    }

    // Donut charts (financial analytics)
    var mkDonut = function(id, labels, data, colors) {
        var el = document.getElementById(id);
        if (!el || !data.length) return;
        new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderWidth: 0, hoverOffset: 5 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + symbol + ctx.parsed.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } } } }
            }
        });
    };
    mkDonut('typeChart', <?= json_encode($revTypeLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($revTypeData) ?>, <?= json_encode($revTypeColors) ?>);
    mkDonut('methodChart', <?= json_encode($methodLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($methodData) ?>, <?= json_encode($methodColors) ?>);
    mkDonut('genderChart', <?= json_encode($genderLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($genderData) ?>, <?= json_encode($genderColorsArr) ?>);

    // Animate progress bars (staggered, replay on every scroll)
    var progBars = Array.prototype.slice.call(document.querySelectorAll('.s-prog-fill, .occupancy-fill'));
    var runProg = function(el, i) {
        var w = parseInt(el.dataset.w, 10) || 0;
        el.style.transitionDelay = (i * 90) + 'ms';
        el.style.width = w + '%';
    };
    if ('IntersectionObserver' in window && !reduced) {
        var progObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(en) {
                if (en.isIntersecting) {
                    progBars.forEach(function(el, i) { runProg(el, i); });
                } else {
                    progBars.forEach(function(el) { el.style.width = '0'; });
                }
            });
        }, { threshold: 0.2 });
        progBars.forEach(function(el) { progObs.observe(el); });
    } else {
        progBars.forEach(function(el, i) { runProg(el, i); });
    }

    // Live clock
    var clockEl = document.getElementById('liveClock');
    if (clockEl) {
        var pad = function(n) { return (n < 10 ? '0' : '') + n; };
        var tick = function() {
            var d = new Date();
            var dateStr = d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            var h = ((d.getHours() % 12) || 12);
            var ampm = d.getHours() >= 12 ? 'PM' : 'AM';
            clockEl.textContent = dateStr + ' \u00b7 ' + h + ':' + pad(d.getMinutes()) + ' ' + ampm;
        };
        tick();
        setInterval(tick, 30000);
    }

    // Entrance reveals + animated counters (reduced-motion aware)
    var animEls = document.querySelectorAll('.s-anim, .s-count[data-count]');
    var runCount = function(el) {
        if (el.getAttribute('data-count') === null) return;
        var to = parseFloat(el.getAttribute('data-count')) || 0;
        var dec = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var prefix = el.getAttribute('data-prefix') || '';
        var suffix = el.getAttribute('data-suffix') || '';
        var fmt = function(v) { return prefix + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix; };
        if (reduced) { el.textContent = fmt(to); return; }
        var dur = 1100;
        var start = null;
        var step = function(ts) {
            if (!start) start = ts;
            var p = Math.min(1, (ts - start) / dur);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(to * eased);
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = fmt(to);
        };
        requestAnimationFrame(step);
    };

    if ('IntersectionObserver' in window && !reduced) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(en) {
                if (en.isIntersecting) {
                    en.target.classList.add('in');
                    runCount(en.target);
                } else {
                    en.target.classList.remove('in');
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -4% 0px' });
        animEls.forEach(function(el) { obs.observe(el); });
    } else {
        animEls.forEach(function(el) {
            if (el.classList.contains('s-anim')) el.classList.add('in');
            runCount(el);
        });
    }

    // Re-theme Chart.js grids/ticks/tracks on dark mode toggle
    window.addEventListener('sbh:theme', function(e) {
        var dark = e.detail.theme === 'dark';
        sbhGridCharts.forEach(function(ch) {
            var scales = ch.options.scales;
            if (scales) {
                if (scales.y && scales.y.grid) scales.y.grid.color = dark ? 'rgba(255,255,255,.08)' : '#f1f5f9';
                if (scales.x && scales.x.ticks) scales.x.ticks.color = dark ? '#94a3b8' : '#64748b';
            }
            ch.update();
        });
        sbhTrackCharts.forEach(function(ch) {
            var ds = ch.data.datasets[0];
            if (ds && ds.backgroundColor && ds.backgroundColor[1] !== undefined) {
                ds.backgroundColor[1] = dark ? 'rgba(255,255,255,.1)' : '#eef2f7';
            }
            ch.update();
        });
    });
});
</script>