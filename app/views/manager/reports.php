<?php
$revMap = []; $resMap = [];
foreach (($monthlyPayments ?? []) as $m) $revMap[$m['month']] = (float)$m['total'];
foreach (($monthlyReservations ?? []) as $m) $resMap[$m['month']] = (int)$m['total'];
$rangeLabels = []; $rangeRev = []; $rangeRes = [];
$d = new DateTime($dateFrom);
$dEnd = (new DateTime($dateTo))->modify('first day of next month');
while ($d < $dEnd) {
    $k = $d->format('Y-m');
    $rangeLabels[] = $d->format('M Y');
    $rangeRev[] = $revMap[$k] ?? 0;
    $rangeRes[] = $resMap[$k] ?? 0;
    $d->modify('first day of next month');
}

$rsCounts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'under_maintenance' => 0];
foreach (($roomStatus ?? []) as $rs) {
    if (isset($rsCounts[$rs['status']])) $rsCounts[$rs['status']] = (int)$rs['count'];
}
$rsvCounts = ['pending' => 0, 'approved' => 0, 'cancelled' => 0, 'rejected' => 0, 'expired' => 0];
foreach (($reservationStatus ?? []) as $r) {
    if (isset($rsvCounts[$r['status']])) $rsvCounts[$r['status']] = (int)$r['count'];
}
$pmtMethods = []; $pmtTotal = 0;
foreach (($paymentMethod ?? []) as $pm) {
    $v = $pm['payment_method'] ?? 'cash';
    $pmtMethods[$v] = (float)$pm['total'];
    $pmtTotal += (float)$pm['total'];
}
$occupancyRate = $occupancyRate ?? 0;
?>

<style>
.rp-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.rp-head h2{font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-.5px}
.rp-head p{margin:0;color:#64748b;font-size:13.5px}
.rp-tools{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.rp-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:.2s;border:1.5px solid #e2e8f0;color:#475569;background:#fff;cursor:pointer}
.rp-btn:hover{border-color:#6366f1;color:#4f46e5}
.rp-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff}
.rp-btn-primary:hover{color:#fff;box-shadow:0 6px 16px rgba(99,102,241,.35);border:none}

.rp-filter{background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);padding:16px 20px;margin-bottom:20px}
.rp-presets{display:flex;gap:8px;flex-wrap:wrap}
.rp-preset{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;font-size:12.5px;font-weight:600;color:#475569;background:#f8fafc;border:1.5px solid #e2e8f0;cursor:pointer;transition:all .18s ease;box-shadow:0 1px 2px rgba(16,24,40,.03)}
.rp-preset:hover{background:#fff;border-color:#6366f1;color:#4f46e5;box-shadow:0 4px 12px rgba(99,102,241,.15);transform:translateY(-1px)}
.rp-preset.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);border-color:#6366f1;color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.35)}
.rp-preset:active:not(.active){transform:translateY(0);box-shadow:0 1px 2px rgba(16,24,40,.05)}
.rp-preset i{font-size:11px;opacity:.85}
.rp-date-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.rp-date-input{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:.55rem .8rem;font-size:13.5px;color:#334155;transition:.2s}
.rp-date-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

.rp-card{background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);margin-bottom:20px}
.rp-card-h{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:18px 22px;border-bottom:1px solid #f1f5f9}
.rp-card-h h6{margin:0;font-weight:700;font-size:14.5px;color:#0f172a}
.rp-card-h a{font-size:12.5px;font-weight:600;color:#6366f1;text-decoration:none}
.rp-card-h a:hover{color:#4f46e5}
.rp-card-b{padding:22px}

.rp-kpi{position:relative;background:#fff;border-radius:16px;padding:20px 22px;height:100%;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);transition:transform .2s,box-shadow .2s}
.rp-kpi:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(16,24,40,.08)}
.rp-kpi-top{display:flex;align-items:flex-start;justify-content:space-between}
.rp-kpi-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:19px}
.rp-kpi-badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:4px 9px;border-radius:999px;white-space:nowrap}
.rp-kpi-badge.up{background:#e7f8f1;color:#059669}
.rp-kpi-badge.down{background:#fef2f2;color:#dc2626}
.rp-kpi-badge.flat{background:#f1f5f9;color:#64748b}
.rp-kpi-val{font-size:26px;font-weight:800;color:#0f172a;margin-top:14px;letter-spacing:-.5px;line-height:1.1}
.rp-kpi-label{font-size:13px;color:#64748b;font-weight:500;margin-top:3px}
.rp-kpi-foot{margin-top:13px;padding-top:12px;border-top:1px dashed #e9eef5;font-size:12px;color:#94a3b8;display:flex;align-items:center;gap:7px}
.rp-kpi-foot i{font-size:11px}

.rp-mini{background:#fff;border-radius:14px;border:1px solid rgba(226,232,240,.7);padding:16px 18px;height:100%;box-shadow:0 1px 3px rgba(16,24,40,.04)}
.rp-mini-top{display:flex;align-items:center;gap:12px}
.rp-mini-ico{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:16px;min-width:40px}
.rp-mini-val{font-size:20px;font-weight:800;color:#0f172a;line-height:1.1}
.rp-mini-label{font-size:12px;color:#64748b;font-weight:500}

.rp-gauge-wrap{position:relative;height:190px}
.rp-gauge-canvas{position:relative;max-width:280px;margin:0 auto}
.rp-gauge-center{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
.rp-gauge-num{font-size:30px;font-weight:800;color:#0f172a;line-height:1}
.rp-gauge-cap{font-size:12px;color:#94a3b8;font-weight:600;margin-top:2px}
.rp-chip{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:8px;margin:3px}

.rp-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f5f7fa}
.rp-row:last-child{border-bottom:none;padding-bottom:0}
.rp-row:first-child{padding-top:0}

.rp-table th{font-size:11.5px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;border-bottom:1px solid #f1f5f9;background:transparent!important;padding:.75rem}
.rp-table td{font-size:13.5px;color:#334155;vertical-align:middle;border-bottom:1px solid #f5f7fa;padding:.8rem}
.rp-table tr:last-child td{border-bottom:none}
.rp-empty{text-align:center;padding:28px 12px;color:#94a3b8}
.rp-empty i{font-size:34px;display:block;margin-bottom:10px;opacity:.5}
.rp-empty p{margin:0;font-size:13px}
</style>

<div>
    <!-- Header -->
    <div class="rp-head">
        <div>
            <h2>Reports</h2>
            <p>Analytics &amp; performance overview &middot; <?= formatDate($dateFrom, 'M d, Y') ?> &ndash; <?= formatDate($dateTo, 'M d, Y') ?></p>
        </div>
        <div class="rp-tools">
            <button class="rp-btn" onclick="exportReportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
            <button class="rp-btn rp-btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Filter -->
    <div class="rp-filter">
        <form method="GET" action="<?= url('/manager/reports') ?>" id="reportFilterForm" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <div class="rp-presets" id="presets">
                    <button type="button" class="rp-preset" data-range="today"><i class="fas fa-calendar-day"></i> Today</button>
                    <button type="button" class="rp-preset" data-range="7d"><i class="fas fa-calendar-week"></i> 7 Days</button>
                    <button type="button" class="rp-preset active" data-range="month"><i class="fas fa-calendar-alt"></i> This Month</button>
                    <button type="button" class="rp-preset" data-range="lastmonth"><i class="fas fa-calendar-minus"></i> Last Month</button>
                    <button type="button" class="rp-preset" data-range="year"><i class="fas fa-calendar"></i> This Year</button>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="rp-date-label">From</div>
                <input type="date" name="date_from" id="dateFrom" class="rp-date-input" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-lg-2">
                <div class="rp-date-label">To</div>
                <input type="date" name="date_to" id="dateTo" class="rp-date-input" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-lg-4 d-flex gap-2 align-items-end">
                <button type="submit" class="rp-btn" style="background:#4f46e5;border:none;color:#fff;"><i class="fas fa-filter me-1"></i> Apply Filter</button>
                <a href="<?= url('/manager/reports') ?>" class="rp-btn"><i class="fas fa-rotate-left me-1"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="rp-kpi">
                <div class="rp-kpi-top">
                    <div class="rp-kpi-ico" style="background:#e7f8f1;color:#059669;"><i class="fas fa-coins"></i></div>
                    <span class="rp-kpi-badge <?= $revenueTrendPct > 0 ? 'up' : ($revenueTrendPct < 0 ? 'down' : 'flat') ?>">
                        <i class="fas fa-<?= $revenueTrendPct > 0 ? 'arrow-up' : ($revenueTrendPct < 0 ? 'arrow-down' : 'minus') ?>"></i>
                        <?= $revenueTrendPct > 0 ? '+' : '' ?><?= $revenueTrendPct ?>%
                    </span>
                </div>
                <div class="rp-kpi-val"><?= formatCurrency($totalRevenue) ?></div>
                <div class="rp-kpi-label">Total Revenue</div>
                <div class="rp-kpi-foot"><i class="fas fa-circle-info"></i> <?= formatCurrency($previousRevenue) ?> previous period</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-kpi">
                <div class="rp-kpi-top">
                    <div class="rp-kpi-ico" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-receipt"></i></div>
                    <span class="rp-kpi-badge flat"><i class="fas fa-divide"></i> avg <?= formatCurrency($avgPayment) ?></span>
                </div>
                <div class="rp-kpi-val"><?= number_format($totalPayments) ?></div>
                <div class="rp-kpi-label">Payments Collected</div>
                <div class="rp-kpi-foot"><i class="fas fa-money-check"></i> verified &amp; paid transactions</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-kpi">
                <div class="rp-kpi-top">
                    <div class="rp-kpi-ico" style="background:#e0f2fe;color:#0ea5e9;"><i class="fas fa-chart-pie"></i></div>
                    <span class="rp-kpi-badge <?= $occupancyRate >= 70 ? 'down' : 'up' ?>"><i class="fas fa-fire"></i> <?= $occupancyRate >= 70 ? 'High' : 'Healthy' ?></span>
                </div>
                <div class="rp-kpi-val"><?= number_format($occupancyRate, 1) ?>%</div>
                <div class="rp-kpi-label">Occupancy Rate</div>
                <div class="rp-kpi-foot"><i class="fas fa-bed"></i> <?= $occupiedRooms ?> of <?= $totalRooms ?> rooms occupied</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-kpi">
                <div class="rp-kpi-top">
                    <div class="rp-kpi-ico" style="background:#fef3c7;color:#d97706;"><i class="fas fa-calendar-check"></i></div>
                    <span class="rp-kpi-badge up"><i class="fas fa-user-plus"></i> +<?= $newStudents ?> students</span>
                </div>
                <div class="rp-kpi-val"><?= number_format($totalReservations) ?></div>
                <div class="rp-kpi-label">Reservations</div>
                <div class="rp-kpi-foot"><i class="fas fa-tag"></i> created within selected period</div>
            </div>
        </div>
    </div>

    <!-- Mini KPI strip -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="rp-mini">
                <div class="rp-mini-top">
                    <div class="rp-mini-ico" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="rp-mini-val"><?= number_format($newStudents) ?></div>
                        <div class="rp-mini-label">New Students</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-mini">
                <div class="rp-mini-top">
                    <div class="rp-mini-ico" style="background:#fef3c7;color:#d97706;"><i class="fas fa-money-bill"></i></div>
                    <div>
                        <div class="rp-mini-val"><?= number_format($pendingPayments) ?></div>
                        <div class="rp-mini-label">Pending Payments</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-mini">
                <div class="rp-mini-top">
                    <div class="rp-mini-ico" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-calendar-clock"></i></div>
                    <div>
                        <div class="rp-mini-val"><?= number_format($pendingReservations) ?></div>
                        <div class="rp-mini-label">Pending Reservations</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rp-mini">
                <div class="rp-mini-top">
                    <div class="rp-mini-ico" style="background:#e0f2fe;color:#0ea5e9;"><i class="fas fa-arrow-trend-up"></i></div>
                    <div>
                        <div class="rp-mini-val"><?= formatCurrency($avgPayment) ?></div>
                        <div class="rp-mini-label">Avg. Payment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h">
                    <h6><i class="fas fa-chart-column me-2" style="color:#6366f1;"></i>Revenue &amp; Reservations Trend</h6>
                    <a href="<?= url('/manager/payments') ?>">View Payments</a>
                </div>
                <div class="rp-card-b"><div style="height:270px;"><canvas id="revenueChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h">
                    <h6><i class="fas fa-gauge-high me-2" style="color:#0ea5e9;"></i>Occupancy</h6>
                    <a href="<?= url('/manager/rooms') ?>">Manage</a>
                </div>
                <div class="rp-card-b">
                    <div class="rp-gauge-wrap">
                        <div class="rp-gauge-canvas">
                            <canvas id="occupancyGauge" height="150"></canvas>
                            <div class="rp-gauge-center">
                                <div class="rp-gauge-num"><?= number_format($occupancyRate, 1) ?>%</div>
                                <div class="rp-gauge-cap">Occupied</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center mt-2">
                        <span class="rp-chip" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i> Available: <?= $availableRooms ?></span>
                        <span class="rp-chip" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bed"></i> Occupied: <?= $occupiedRooms ?></span>
                        <span class="rp-chip" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-calendar-check"></i> Reserved: <?= $reservedRooms ?></span>
                    </div>
                    <div style="height:6px;background:#eef2f7;border-radius:3px;margin-top:8px;overflow:hidden;">
                        <div style="height:100%;width:<?= $occupancyRate ?>%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h"><h6><i class="fas fa-chart-pie me-2" style="color:#1cc88a;"></i>Revenue by Payment Type</h6></div>
                <div class="rp-card-b">
                    <div style="height:230px;"><canvas id="paymentByTypeChart"></canvas></div>
                    <div id="typeLegend" class="mt-3 d-flex flex-wrap justify-content-center"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h"><h6><i class="fas fa-truck-fast me-2" style="color:#d97706;"></i>Payment Method</h6></div>
                <div class="rp-card-b">
                    <?php $methods = [['k' => 'cash', 'label' => 'Cash', 'color' => '#10b981', 'icon' => 'fa-money-bill-wave'], ['k' => 'gcash', 'label' => 'GCash', 'color' => '#0ea5e9', 'icon' => 'fa-mobile-screen']]; ?>
                    <?php foreach ($methods as $mk): $v = $pmtMethods[$mk['k']] ?? 0; $pct = $pmtTotal > 0 ? round(($v / $pmtTotal) * 100) : 0; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:13px;font-weight:600;color:#334155;"><i class="fas <?= $mk['icon'] ?> me-1" style="color:<?= $mk['color'] ?>;"></i><?= $mk['label'] ?></span>
                            <span style="font-size:13px;font-weight:700;color:#475569;"><?= formatCurrency($v) ?> <small style="font-weight:600;color:#94a3b8;">(<?= $pct ?>%)</small></span>
                        </div>
                        <div style="height:9px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct ?>%;background:<?= $mk['color'] ?>;border-radius:6px;transition:width .6s;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex align-items-center justify-content-between" style="padding-top:12px;border-top:1px dashed #e9eef5;">
                        <span style="font-size:12.5px;font-weight:600;color:#64748b;">Total Collected</span>
                        <span style="font-size:16px;font-weight:800;color:#4f46e5;"><?= formatCurrency($pmtTotal) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h"><h6><i class="fas fa-calendar-check me-2" style="color:#8b5cf6;"></i>Reservation Pipeline</h6></div>
                <div class="rp-card-b">
                    <?php
                    $rsvMeta = ['pending' => ['label' => 'Pending', 'color' => '#f59e0b', 'bg' => '#fef3c7'], 'approved' => ['label' => 'Approved', 'color' => '#16a34a', 'bg' => '#dcfce7'], 'cancelled' => ['label' => 'Cancelled', 'color' => '#dc2626', 'bg' => '#fee2e2'], 'rejected' => ['label' => 'Rejected', 'color' => '#dc2626', 'bg' => '#fee2e2'], 'expired' => ['label' => 'Expired', 'color' => '#64748b', 'bg' => '#f1f5f9']];
                    ?>
                    <?php foreach ($rsvMeta as $rk => $rm): $count = $rsvCounts[$rk]; ?>
                    <div class="rp-row">
                        <span class="rp-chip" style="background:<?= $rm['bg'] ?>;color:<?= $rm['color'] ?>;width:110px;justify-content:center;"><?= $rm['label'] ?></span>
                        <div class="flex-grow-1">
                            <div style="height:8px;background:#f1f5f9;border-radius:5px;overflow:hidden;">
                                <div style="height:100%;width:<?= $count > 0 ? min(100, round($count / max(1, array_sum($rsvCounts)) * 100)) : 0 ?>%;background:<?= $rm['color'] ?>;border-radius:5px;"></div>
                            </div>
                        </div>
                        <b style="font-size:14px;color:#334155;min-width:34px;text-align:right;"><?= $count ?></b>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex align-items-center justify-content-between" style="padding-top:12px;border-top:1px dashed #e9eef5;">
                        <span style="font-size:12.5px;font-weight:600;color:#64748b;">Total</span>
                        <span style="font-size:16px;font-weight:800;color:#4f46e5;"><?= number_format(array_sum($rsvCounts)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h"><h6><i class="fas fa-crown me-2" style="color:#d97706;"></i>Top Paying Students</h6><a href="<?= url('/manager/students') ?>">Students</a></div>
                <div class="rp-card-b" style="padding:10px 22px 18px;">
                    <?php if (!empty($topStudents)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 rp-table" id="topStudentsTable">
                            <thead><tr><th style="width:34px;">#</th><th>Student</th><th class="text-end">Payments</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($topStudents as $i => $ts): ?>
                                <tr>
                                    <td>
                                        <span style="width:30px;height:30px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;<?= $i === 0 ? 'background:#fef3c7;color:#d97706;' : 'background:#f1f5f9;color:#64748b;' ?>"><?= $i + 1 ?></span>
                                    </td>
                                    <td><span class="fw-semibold"><?= e(trim($ts['first_name'] . ' ' . $ts['last_name'])) ?></span></td>
                                    <td class="text-end text-muted"><?= number_format($ts['payments']) ?></td>
                                    <td class="text-end fw-bold" style="color:#059669;"><?= formatCurrency($ts['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="rp-empty"><i class="fas fa-crown"></i><p>No payments in this period.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="rp-card" style="height:100%;margin-bottom:0;">
                <div class="rp-card-h"><h6><i class="fas fa-money-bill-wave me-2" style="color:#059669;"></i>Recent Payments</h6><a href="<?= url('/manager/payments') ?>">View All</a></div>
                <div class="rp-card-b" style="padding:10px 22px 18px;">
                    <?php if (!empty($recentPayments)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 rp-table">
                            <thead><tr><th>Student</th><th>Type</th><th class="text-end">Amount</th><th class="text-end">Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?= e(trim($p['first_name'] . ' ' . ($p['last_name'] ?? ''))) ?></span><br><small class="text-muted"><?= formatDate($p['created_at'] ?? '') ?></small></td>
                                    <td class="text-muted"><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></td>
                                    <td class="text-end fw-bold" style="color:#334155;"><?= formatCurrency((float)$p['amount']) ?></td>
                                    <td class="text-end"><?= statusBadge($p['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="rp-empty"><i class="fas fa-receipt"></i><p>No payments in this period.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary by Type -->
    <div class="rp-card">
        <div class="rp-card-h"><h6><i class="fas fa-table me-2" style="color:#f6c23e;"></i>Payment Summary by Type</h6></div>
        <div class="rp-card-b" style="padding:10px 22px 18px;">
            <?php if (!empty($paymentByType)): ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0 rp-table" id="paymentSummaryTable">
                    <thead><tr><th>Payment Type</th><th class="text-end">Count</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($paymentByType as $pt): ?>
                        <tr>
                            <td><span class="fw-semibold"><?= e(ucwords(str_replace('_', ' ', $pt['payment_type']))) ?></span></td>
                            <td class="text-end text-muted"><?= number_format($pt['count']) ?></td>
                            <td class="text-end fw-bold" style="color:#059669;"><?= formatCurrency($pt['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="rp-empty"><i class="fas fa-table"></i><p>No payment data in this period.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var fmt = function(n) { return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var symbol = window.APP_CURRENCY_SYMBOL || '\u20b1';

    // Revenue + Reservations trend
    var revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'bar',
            data: { labels: <?= json_encode($rangeLabels) ?>, datasets: [
                { type: 'bar', label: 'Revenue', yAxisID: 'y', data: <?= json_encode($rangeRev) ?>, backgroundColor: 'rgba(99,102,241,.85)', hoverBackgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 40 },
                { type: 'line', label: 'Reservations', yAxisID: 'y1', data: <?= json_encode($rangeRes) ?>, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', borderWidth: 2.5, tension: .4, pointRadius: 4, pointHoverRadius: 6, fill: true }
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                onClick: function() { window.location = '<?= url('/manager/payments') ?>'; },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12, weight: '600' } } },
                    tooltip: { backgroundColor: '#1e293b', titleFont: { size: 13, weight: '600' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 8,
                        callbacks: { label: function(ctx) { return ctx.datasetIndex === 0 ? 'Revenue: ' + symbol + fmt(ctx.parsed.y) : 'Reservations: ' + ctx.parsed.y; } } }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', ticks: { font: { size: 11 }, color: '#94a3b8', callback: function(v) { return symbol + v.toLocaleString(); } }, grid: { color: '#f1f5f9', drawBorder: false } },
                    x: { grid: { display: false }, ticks: { font: { size: 11.5, weight: '600' }, color: '#64748b' } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 11 }, color: '#d97706', precision: 0 } }
                }
            }
        });
    }

    // Occupancy gauge
    var gaugeCtx = document.getElementById('occupancyGauge');
    if (gaugeCtx) {
        var occ = Math.max(0, Math.min(100, <?= round((float)$occupancyRate, 1) ?>));
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: { datasets: [{ data: [occ, 100 - occ], backgroundColor: ['#6366f1', '#eef2f7'], borderWidth: 0, hoverOffset: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, rotation: -Math.PI, circumference: Math.PI, cutout: '74%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }

    // Revenue by payment type donut
    var ptData = <?= json_encode($paymentByType ?? []) ?>;
    var ptCtx = document.getElementById('paymentByTypeChart');
    if (ptCtx && ptData.length > 0) {
        var ptColors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#8b5cf6'];
        var labels = ptData.map(function(d) { return d.payment_type.charAt(0).toUpperCase() + d.payment_type.slice(1).replace('_', ' '); });
        var vals = ptData.map(function(d) { return parseFloat(d.total); });
        new Chart(ptCtx, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: vals, backgroundColor: ptColors, borderWidth: 0, hoverOffset: 6 }] },
            options: { responsive: true, cutout: '68%', plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + symbol + fmt(ctx.parsed); } } } } }
        });
        var legend = document.getElementById('typeLegend');
        if (legend) {
            labels.forEach(function(l, i) {
                var span = document.createElement('span');
                span.className = 'rp-chip';
                span.style.background = ptColors[i % ptColors.length] + '1a';
                span.style.color = ptColors[i % ptColors.length];
                span.style.margin = '3px';
                span.innerHTML = '<i class="fas fa-circle" style="font-size:7px"></i> ' + l;
                legend.appendChild(span);
            });
        }
    } else if (ptCtx) {
        document.querySelector('#paymentByTypeChart').closest('.rp-card').querySelector('.rp-card-b').innerHTML = '<div class="rp-empty" style="min-height:230px;"><i class="fas fa-chart-pie"></i><p>No payments in this period.</p></div>';
    }
});

var dateFromInput = document.getElementById('dateFrom');
    var dateToInput = document.getElementById('dateTo');
    if (dateFromInput && typeof flatpickr !== 'undefined') {
        flatpickr(dateFromInput, {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }
    if (dateToInput && typeof flatpickr !== 'undefined') {
        flatpickr(dateToInput, {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }

    // Quick range presets
    (function() {
        function iso(d) { var m = String(d.getMonth() + 1).padStart(2, '0'); var day = String(d.getDate()).padStart(2, '0'); return d.getFullYear() + '-' + m + '-' + day; }
        document.querySelectorAll('#presets .rp-preset').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#presets .rp-preset').forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                var d = new Date();
                var from = new Date(d.getFullYear(), d.getMonth(), 1), to = new Date(d);
                var range = btn.dataset.range;
                if (range === 'today') { from = new Date(d); to = new Date(d); }
                else if (range === '7d') { from = new Date(d.getFullYear(), d.getMonth(), d.getDate() - 6); to = new Date(d); }
                else if (range === 'month') { from = new Date(d.getFullYear(), d.getMonth(), 1); to = new Date(d); }
                else if (range === 'lastmonth') { from = new Date(d.getFullYear(), d.getMonth() - 1, 1); to = new Date(d.getFullYear(), d.getMonth(), 0); }
                else if (range === 'year') { from = new Date(d.getFullYear(), 0, 1); to = new Date(d); }
                document.getElementById('dateFrom').value = iso(from);
                document.getElementById('dateTo').value = iso(to);
                document.getElementById('reportFilterForm').submit();
            });
        });
    })();

function exportReportCSV() {
    var table = document.getElementById('paymentSummaryTable');
    if (!table) return;
    var rows = [];
    var head = [];
    table.querySelectorAll('thead th').forEach(function(th) { head.push(th.innerText.trim()); });
    rows.push(head);
    table.querySelectorAll('tbody tr').forEach(function(tr) {
        var r = [];
        tr.querySelectorAll('td').forEach(function(td) { r.push('"' + td.innerText.trim().replace(/"/g, '""') + '"'); });
        rows.push(r.join(','));
    });
    var csv = rows.join('\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'reports_payment_summary.csv';
    a.click();
}
</script>