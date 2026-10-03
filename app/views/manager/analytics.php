<?php
$map = function (array $rows, string $key, string $valKey) { $out = []; foreach ($rows as $r) $out[$r[$key]] = $r[$valKey]; return $out; };
$revMap = $map($monthlyRevenue ?? [], 'm', 't');
$resMap = $map($monthlyReservations ?? [], 'm', 't');
$stuMap = $map($monthlyStudents ?? [], 'm', 't');
$walkinMap = $map($walkinMonthly ?? [], 'm', 't');

$labels = []; $rev = []; $res = []; $stu = []; $walkin = [];
for ($i = 11; $i >= 0; $i--) {
    $d = new DateTime('first day of this month');
    $d->modify("-{$i} months");
    $k = $d->format('Y-m');
    $labels[] = $d->format('M Y');
    $rev[] = (float)($revMap[$k] ?? 0);
    $res[] = (int)($resMap[$k] ?? 0);
    $stu[] = (int)($stuMap[$k] ?? 0);
    $walkin[] = (float)($walkinMap[$k] ?? 0);
}

$wdNames = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$wdCnt = array_fill(1, 7, 0);
$wdTot = array_fill(1, 7, 0);
foreach (($weekdayPayments ?? []) as $w) {
    $wd = (int)$w['wd'];
    if ($wd >= 1 && $wd <= 7) { $wdCnt[$wd] = (int)$w['cnt']; $wdTot[$wd] = (float)$w['t']; }
}

$pmtMethods = ['cash' => 0, 'gcash' => 0];
foreach (($paymentMethod ?? []) as $pm) $pmtMethods[$pm['payment_method'] ?? 'cash'] = (float)$pm['t'];

$walkinMethods = ['cash' => 0, 'gcash' => 0];
foreach (($walkinByMethod ?? []) as $wm) $walkinMethods[$wm['payment_method'] ?? 'cash'] = (float)$wm['t'];

$rsvCounts = ['pending' => 0, 'approved' => 0, 'cancelled' => 0, 'rejected' => 0, 'expired' => 0];
foreach (($reservationStatus ?? []) as $r) if (isset($rsvCounts[$r['status']])) $rsvCounts[$r['status']] = (int)$r['c'];

$roomCounts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'under_maintenance' => 0];
foreach (($roomStatus ?? []) as $rs) if (isset($roomCounts[$rs['status']])) $roomCounts[$rs['status']] = (int)$rs['cnt'];

$topRoomsMax = 0;
foreach (($topRooms ?? []) as $tr) $topRoomsMax = max($topRoomsMax, (float)$tr['t']);

$peakIdx = 0; $peakVal = 0;
foreach ($rev as $i => $v) { if ($v > $peakVal) { $peakVal = $v; $peakIdx = $i; } }
$avgMonthly = count($rev) ? round(array_sum($rev) / count($rev)) : 0;
?>

<style>
.an-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.an-head h2{font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-.5px}
.an-head p{margin:0;color:#64748b;font-size:13.5px}
.an-tools{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.an-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:.2s;border:1.5px solid #e2e8f0;color:#475569;background:#fff}
.an-btn:hover{border-color:#6366f1;color:#4f46e5}

.an-card{background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,.7);box-shadow:0 1px 3px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.04);margin-bottom:20px;transition:box-shadow .25s}
.an-card:hover{box-shadow:0 1px 3px rgba(16,24,40,.05),0 14px 34px rgba(16,24,40,.08)}
.an-card-h{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:18px 22px;border-bottom:1px solid #f1f5f9}
.an-card-h h6{margin:0;font-weight:700;font-size:14.5px;color:#0f172a}
.an-card-h a{font-size:12.5px;font-weight:600;color:#6366f1;text-decoration:none}
.an-card-h a:hover{color:#4f46e5}
.an-card-b{padding:22px}

.an-mini{background:#fff;border-radius:14px;border:1px solid rgba(226,232,240,.7);padding:16px 18px;height:100%;box-shadow:0 1px 3px rgba(16,24,40,.04);transition:transform .22s ease,box-shadow .22s ease}
.an-mini:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(16,24,40,.08)}
.an-mini-top{display:flex;align-items:center;gap:12px}
.an-mini-ico{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:16px;min-width:40px}
.an-mini-val{font-size:20px;font-weight:800;color:#0f172a;line-height:1.1}
.an-mini-label{font-size:12px;color:#64748b;font-weight:500}

.an-gauge-wrap{position:relative;height:190px}
.an-gauge-canvas{position:relative;max-width:280px;margin:0 auto}
.an-gauge-center{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
.an-gauge-num{font-size:30px;font-weight:800;color:#0f172a;line-height:1}
.an-gauge-cap{font-size:12px;color:#94a3b8;font-weight:600;margin-top:2px}
.an-chip{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:8px;margin:3px}

.an-row{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid #f5f7fa}
.an-row:last-child{border-bottom:none;padding-bottom:0}
.an-row:first-child{padding-top:0}
.an-row{transition:background .2s}
.an-row:hover{background:#f8fafc}
.an-mini-ico{transition:transform .25s ease}
.an-mini:hover .an-mini-ico{transform:scale(1.08) rotate(-3deg)}
.an-bar-fill{height:100%;border-radius:5px;transition:width .7s cubic-bezier(.22,.61,.36,1)}
.an-anim{opacity:0;transform:translateY(16px);transition:opacity .6s cubic-bezier(.22,.61,.36,1),transform .6s cubic-bezier(.22,.61,.36,1);transition-delay:var(--d,0s)}
.an-anim.in{opacity:1;transform:none}
@media (prefers-reduced-motion: reduce){
  .an-anim,.an-bar-fill,.an-mini,.an-card,.an-row,.an-mini-ico{transition:none!important}
  .an-anim{opacity:1;transform:none}
}
.an-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;margin-top:14px}
.an-legend span{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:8px}
.an-empty{text-align:center;padding:28px 12px;color:#94a3b8}
.an-empty i{font-size:34px;display:block;margin-bottom:10px;opacity:.5}
.an-empty p{margin:0;font-size:13px}

/* Dark theme */
html[data-theme="dark"] .an-head h2{color:#f1f5f9}
html[data-theme="dark"] .an-head p{color:#94a3b8}
html[data-theme="dark"] .an-btn{background:#1e293b;border-color:#334155;color:#cbd5e1}
html[data-theme="dark"] .an-btn:hover{border-color:#6366f1;color:#a5b4fc}
html[data-theme="dark"] .an-card{background:#1e293b;border-color:rgba(255,255,255,.08);box-shadow:0 1px 3px rgba(0,0,0,.3),0 8px 24px rgba(0,0,0,.2)}
html[data-theme="dark"] .an-card:hover{box-shadow:0 1px 3px rgba(0,0,0,.3),0 14px 34px rgba(0,0,0,.3)}
html[data-theme="dark"] .an-card-h{border-bottom-color:rgba(255,255,255,.07)}
html[data-theme="dark"] .an-card-h h6{color:#f1f5f9}
html[data-theme="dark"] .an-card-h a{color:#a5b4fc}
html[data-theme="dark"] .an-card-h a:hover{color:#c7d2fe}
html[data-theme="dark"] .an-mini{background:#1e293b;border-color:rgba(255,255,255,.08);box-shadow:0 1px 3px rgba(0,0,0,.3)}
html[data-theme="dark"] .an-mini:hover{box-shadow:0 12px 28px rgba(0,0,0,.35)}
html[data-theme="dark"] .an-mini-val{color:#f1f5f9}
html[data-theme="dark"] .an-mini-label{color:#94a3b8}
html[data-theme="dark"] .an-gauge-num{color:#f1f5f9}
html[data-theme="dark"] .an-row{border-bottom-color:rgba(255,255,255,.06)}
html[data-theme="dark"] .an-row:hover{background:rgba(255,255,255,.04)}
html[data-theme="dark"] .an-bar-fill,html[data-theme="dark"] span[style*="background:#f1f5f9"],html[data-theme="dark"] div[style*="background:#f1f5f9"]{background:#334155!important}
html[data-theme="dark"] b[style*="color:"],html[data-theme="dark"] div[style*="color:"],html[data-theme="dark"] span[style*="color:"],html[data-theme="dark"] small[style*="color:"]{filter:brightness(1.15)}
</style>

<div>
    <!-- Header -->
    <div class="an-head">
        <div>
            <h2 class="an-anim" style="--d:.02s">Analytics</h2>
            <p class="an-anim" style="--d:.08s">Performance insights &amp; growth trends &middot; last 12 months (<?= $labels[0] ?> &ndash; <?= $labels[11] ?>)</p>
        </div>
        <div class="an-tools">
            <a href="<?= url('/manager/dashboard') ?>" class="an-btn"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a href="<?= url('/manager/reports') ?>" class="an-btn"><i class="fas fa-chart-bar"></i> Reports</a>
        </div>
    </div>

    <!-- KPI strip -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 an-anim" style="--d:.05s">
            <div class="an-mini">
                <div class="an-mini-top">
                    <div class="an-mini-ico" style="background:#e7f8f1;color:#059669;"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="an-mini-val an-count" data-count="<?= (float)$avgMonthly ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($avgMonthly) ?></div>
                        <div class="an-mini-label">Avg. Monthly Revenue</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 an-anim" style="--d:.12s">
            <div class="an-mini">
                <div class="an-mini-top">
                    <div class="an-mini-ico" style="background:#fef3c7;color:#d97706;"><i class="fas fa-rocket"></i></div>
                    <div>
                        <div class="an-mini-val" style="font-size:15px;"><?= e($labels[$peakIdx]) ?></div>
                        <div class="an-mini-label">Peak Month &middot; <span class="an-count" data-count="<?= (float)$peakVal ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($peakVal) ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 an-anim" style="--d:.19s">
            <div class="an-mini">
                <div class="an-mini-top">
                    <div class="an-mini-ico" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="an-mini-val an-count" data-count="<?= (int)$totalReservations12 ?>"><?= number_format($totalReservations12) ?></div>
                        <div class="an-mini-label">Reservations (12M)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 an-anim" style="--d:.26s">
            <div class="an-mini">
                <div class="an-mini-top">
                    <div class="an-mini-ico" style="background:#e0f2fe;color:#0ea5e9;"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="an-mini-val an-count" data-count="<?= (int)$totalStudents12 ?>"><?= number_format($totalStudents12) ?></div>
                        <div class="an-mini-label">New Students (12M)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.12s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-chart-line me-2" style="color:#6366f1;"></i>Revenue Trend (12 Months)</h6>
                    <a href="<?= url('/manager/payments') ?>">View Payments</a>
                </div>
                <div class="an-card-b"><div style="height:280px;"><canvas id="revenueChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.18s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-gauge-high me-2" style="color:#0ea5e9;"></i>Occupancy</h6>
                    <a href="<?= url('/manager/rooms') ?>">Manage</a>
                </div>
                <div class="an-card-b">
                    <div class="an-gauge-wrap">
                        <div class="an-gauge-canvas">
                            <canvas id="occupancyGauge" height="150"></canvas>
                            <div class="an-gauge-center">
                                <div class="an-gauge-num an-count" data-count="<?= (int)$occupancyRate ?>" data-suffix="%"><?= $occupancyRate ?>%</div>
                                <div class="an-gauge-cap">Occupied</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center mt-2">
                        <span class="an-chip" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i> Available: <?= $roomCounts['available'] ?></span>
                        <span class="an-chip" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bed"></i> Occupied: <?= $roomCounts['occupied'] ?></span>
                        <span class="an-chip" style="background:#f3eefe;color:#7c3aed;"><i class="fas fa-calendar-check"></i> Reserved: <?= $roomCounts['reserved'] ?></span>
                        <span class="an-chip" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-tools"></i> Maintenance: <?= $roomCounts['under_maintenance'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.24s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-user-graduate me-2" style="color:#d97706;"></i>New Students &amp; Reservations</h6>
                    <a href="<?= url('/manager/students') ?>">Students</a>
                </div>
                <div class="an-card-b"><div style="height:260px;"><canvas id="studentsChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.3s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-calendar-day me-2" style="color:#0ea5e9;"></i>Payments by Day of Week</h6>
                    <a href="<?= url('/manager/reports') ?>">Reports</a>
                </div>
                <div class="an-card-b"><div style="height:260px;"><canvas id="weekdayChart"></canvas></div></div>
            </div>
        </div>
    </div>

    <!-- Walk-In Payment Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.54s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-store me-2" style="color:#f59e0b;"></i>Walk-In Collections (12 Months)</h6>
                    <a href="<?= url('/manager/students/walk-in-payment') ?>">Walk-In Payments</a>
                </div>
                <div class="an-card-b">
                    <div style="height:240px;"><canvas id="walkinChart"></canvas></div>
                    <div class="d-flex justify-content-around mt-3" style="border-top:1px dashed #e9eef5;padding-top:14px;">
                        <div class="text-center">
                            <div style="font-size:17px;font-weight:800;color:#0f172a;"><span class="an-count" data-count="<?= (float)$walkinTotal12 ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($walkinTotal12) ?></span></div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:700;">COLLECTED (12M)</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:17px;font-weight:800;color:#0f172a;"><span class="an-count" data-count="<?= (float)$walkinThisMonth ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($walkinThisMonth) ?></span></div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:700;">THIS MONTH</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:17px;font-weight:800;color:#0f172a;"><?= number_format($walkinCount12) ?></div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:700;">COLLECTIONS (12M)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.6s;">
                <div class="an-card-h"><h6><i class="fas fa-hand-holding-dollar me-2" style="color:#059669;"></i>Walk-In Overview</h6></div>
                <div class="an-card-b">
                    <?php $wTotal = max(1, $walkinMethods['cash'] + $walkinMethods['gcash']); ?>
                    <div class="d-flex gap-3 mb-3" style="padding-bottom:14px;border-bottom:1px dashed #e9eef5;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1"><span style="font-size:12.5px;font-weight:700;color:#334155;">Cash</span><b style="font-size:13px;color:#0f172a;"><span class="an-count" data-count="<?= (float)$walkinMethods['cash'] ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($walkinMethods['cash']) ?></span></b></div>
                            <div style="height:8px;background:#f1f5f9;border-radius:5px;overflow:hidden;"><div class="an-bar-fill" data-w="<?= round($walkinMethods['cash'] / $wTotal * 100) ?>" style="width:0;background:#10b981;"></div></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1"><span style="font-size:12.5px;font-weight:700;color:#334155;">GCash</span><b style="font-size:13px;color:#0f172a;"><span class="an-count" data-count="<?= (float)$walkinMethods['gcash'] ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency($walkinMethods['gcash']) ?></span></b></div>
                            <div style="height:8px;background:#f1f5f9;border-radius:5px;overflow:hidden;"><div class="an-bar-fill" data-w="<?= round($walkinMethods['gcash'] / $wTotal * 100) ?>" style="width:0;background:#0ea5e9;"></div></div>
                        </div>
                    </div>
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin:0 0 8px;">Recent Walk-In Collections</div>
                    <?php if (!empty($walkinRecent)): ?>
                        <?php foreach ($walkinRecent as $wkI => $wk): ?>
                        <div class="an-row an-anim" style="--d:<?= 0.06 + $wkI * 0.07 ?>s;">
                            <span class="an-chip" style="background:#ecfdf5;color:#059669;width:auto;padding:6px 9px;"><i class="fas fa-coins"></i></span>
                            <div class="flex-grow-1">
                                <div style="font-size:13px;font-weight:600;color:#334155;"><?= e($wk['first_name'] . ' ' . $wk['last_name']) ?></div>
                                <div style="font-size:11px;color:#94a3b8;"><?= e(strtoupper($wk['payment_method'])) ?> &middot; <?= e(ucwords(str_replace('_', ' ', $wk['payment_type']))) ?></div>
                            </div>
                            <b style="font-size:13px;color:#0f172a;"><?= formatCurrency((float)$wk['amount_paid']) ?></b>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="an-empty"><i class="fas fa-store"></i><p>No walk-in collections yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.36s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-trophy me-2" style="color:#d97706;"></i>Top Rooms by Revenue</h6>
                    <a href="<?= url('/manager/rooms') ?>">Rooms</a>
                </div>
                <div class="an-card-b">
                    <?php if (!empty($topRooms)): ?>
                        <?php foreach ($topRooms as $i => $tr): $pct = $topRoomsMax > 0 ? round(((float)$tr['t'] / $topRoomsMax) * 100) : 0; ?>
                        <div class="an-row">
                            <span style="width:26px;height:26px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;<?= $i === 0 ? 'background:#fef3c7;color:#d97706;' : 'background:#f1f5f9;color:#64748b;' ?>"><?= $i + 1 ?></span>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size:13px;font-weight:600;color:#334155;"><?= e($tr['room_number']) ?> <small class="text-muted" style="font-weight:500;">- <?= e($tr['room_name']) ?></small></span>
                                    <span style="font-size:12.5px;font-weight:700;color:#475569;"><span class="an-count" data-count="<?= (float)$tr['t'] ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="2"><?= formatCurrency((float)$tr['t']) ?></span></span>
                                </div>
                                <div style="height:8px;background:#f1f5f9;border-radius:5px;overflow:hidden;">
                                    <div class="an-bar-fill" data-w="<?= $pct ?>" style="width:0;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="an-empty"><i class="fas fa-trophy"></i><p>No room revenue data yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.42s;">
                <div class="an-card-h"><h6><i class="fas fa-truck-fast me-2" style="color:#059669;"></i>Payment Method Split</h6></div>
                <div class="an-card-b">
                    <div style="height:210px;"><canvas id="methodChart"></canvas></div>
                    <?php $totalM = $pmtMethods['cash'] + $pmtMethods['gcash']; ?>
                    <div class="d-flex justify-content-around mt-3" style="border-top:1px dashed #e9eef5;padding-top:14px;">
                        <div class="text-center">
                            <div style="font-size:13px;font-weight:800;color:#10b981;"><?= $pmtMethods['cash'] ? number_format($pmtMethods['cash'] / max(1, $totalM) * 100) : 0 ?>%</div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:600;">CASH</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:13px;font-weight:800;color:#0ea5e9;"><?= $pmtMethods['gcash'] ? number_format($pmtMethods['gcash'] / max(1, $totalM) * 100) : 0 ?>%</div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:600;">GCASH</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="an-card an-anim" style="height:100%;margin-bottom:0;--d:.48s;">
                <div class="an-card-h">
                    <h6><i class="fas fa-filter me-2" style="color:#8b5cf6;"></i>Reservation Funnel</h6>
                    <a href="<?= url('/manager/reservations') ?>">Reservations</a>
                </div>
                <div class="an-card-b">
                    <?php
                    $rsvMeta = ['approved' => ['label' => 'Approved', 'color' => '#16a34a', 'bg' => '#dcfce7'], 'pending' => ['label' => 'Pending', 'color' => '#f59e0b', 'bg' => '#fef3c7'], 'cancelled' => ['label' => 'Cancelled', 'color' => '#dc2626', 'bg' => '#fee2e2'], 'rejected' => ['label' => 'Rejected', 'color' => '#dc2626', 'bg' => '#fee2e2'], 'expired' => ['label' => 'Expired', 'color' => '#64748b', 'bg' => '#f1f5f9']];
                    $totalRsv = max(1, array_sum($rsvCounts));
                    ?>
                    <?php foreach ($rsvMeta as $rk => $rm): $count = $rsvCounts[$rk]; ?>
                    <div class="an-row">
                        <span class="an-chip" style="background:<?= $rm['bg'] ?>;color:<?= $rm['color'] ?>;width:104px;justify-content:center;"><?= $rm['label'] ?></span>
                        <div class="flex-grow-1">
                            <div style="height:8px;background:#f1f5f9;border-radius:5px;overflow:hidden;">
                                <div class="an-bar-fill" data-w="<?= round($count / $totalRsv * 100) ?>" style="width:0;background:<?= $rm['color'] ?>;"></div>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var fmt = function(n) { return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var symbol = window.APP_CURRENCY_SYMBOL || '\u20b1';
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    var axisGrid = dark ? 'rgba(255,255,255,.06)' : '#f1f5f9';
    var tickMuted = dark ? '#94a3b8' : '#94a3b8';
    var tickStrong = dark ? '#cbd5e1' : '#64748b';
    var tooltipBg = dark ? '#0f172a' : '#1e293b';

    // Revenue trend
    var revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: { labels: <?= json_encode($labels) ?>, datasets: [
                { type: 'bar', label: 'Revenue', yAxisID: 'y', data: <?= json_encode($rev) ?>, backgroundColor: 'rgba(99,102,241,.7)', hoverBackgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 30, order: 2 },
                { type: 'line', label: 'Reservations', yAxisID: 'y1', data: <?= json_encode($res) ?>, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', borderWidth: 2.5, tension: .4, pointRadius: 4, pointHoverRadius: 6, fill: true, order: 1 }
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 1100, easing: 'easeOutQuart' },
                onClick: function() { window.location = '<?= url('/manager/payments') ?>'; },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12, weight: '600' }, color: dark ? '#cbd5e1' : undefined } },
                    tooltip: { backgroundColor: tooltipBg, titleFont: { size: 13, weight: '600' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 8,
                        callbacks: { label: function(ctx) { return ctx.datasetIndex === 0 ? 'Revenue: ' + symbol + fmt(ctx.parsed.y) : 'Reservations: ' + ctx.parsed.y; } } }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', ticks: { font: { size: 11 }, color: tickMuted, callback: function(v) { return symbol + v.toLocaleString(); } }, grid: { color: axisGrid, drawBorder: false } },
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: tickStrong } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 11 }, color: tickStrong, precision: 0 } }
                }
            }
        });
    }

    // Students & reservations
    var stuCtx = document.getElementById('studentsChart');
    if (stuCtx) {
        new Chart(stuCtx, {
            type: 'line',
            data: { labels: <?= json_encode($labels) ?>, datasets: [
                { type: 'bar', label: 'New Students', yAxisID: 'y', data: <?= json_encode($stu) ?>, backgroundColor: 'rgba(245,158,11,.75)', hoverBackgroundColor: '#f59e0b', borderRadius: 6, maxBarThickness: 30, order: 2 },
                { type: 'line', label: 'Reservations', yAxisID: 'y', data: <?= json_encode($res) ?>, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.12)', borderWidth: 2.5, tension: .4, pointRadius: 4, pointHoverRadius: 6, fill: true, order: 1 }
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 1100, easing: 'easeOutQuart' },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12, weight: '600' }, color: dark ? '#cbd5e1' : undefined } },
                    tooltip: { backgroundColor: tooltipBg, titleFont: { size: 13, weight: '600' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 8 }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 11 }, color: tickMuted, precision: 0 }, grid: { color: axisGrid, drawBorder: false } },
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: tickStrong } }
                }
            }
        });
    }

    // Weekday payments
    var wdCtx = document.getElementById('weekdayChart');
    if (wdCtx) {
        var wdLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        var wdCounts = [<?= implode(',', [$wdCnt[1], $wdCnt[2], $wdCnt[3], $wdCnt[4], $wdCnt[5], $wdCnt[6], $wdCnt[7]]) ?>];
        var wdTotals = [<?= implode(',', [$wdTot[1], $wdTot[2], $wdTot[3], $wdTot[4], $wdTot[5], $wdTot[6], $wdTot[7]]) ?>];
        new Chart(wdCtx, {
            type: 'bar',
            data: { labels: wdLabels, datasets: [
                { label: 'Payments', data: wdCounts, backgroundColor: 'rgba(14,165,233,.8)', hoverBackgroundColor: '#0ea5e9', borderRadius: 6, maxBarThickness: 36 },
                { label: 'Amount', data: wdTotals, borderColor: '#8b5cf6', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 4, type: 'line', yAxisID: 'y1', order: 1 }
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12, weight: '600' }, color: dark ? '#cbd5e1' : undefined } },
                    tooltip: { backgroundColor: tooltipBg, titleFont: { size: 13, weight: '600' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 8,
                        callbacks: { label: function(ctx) { return ctx.datasetIndex === 0 ? 'Payments: ' + ctx.parsed.y : 'Amount: ' + symbol + fmt(ctx.parsed.y); } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 11 }, color: tickMuted, precision: 0 }, grid: { color: axisGrid, drawBorder: false } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 11 }, color: tickStrong, callback: function(v) { return symbol + v.toLocaleString(); } } },
                    x: { grid: { display: false }, ticks: { font: { size: 12, weight: '600' }, color: tickStrong } }
                }
            }
        });
    }

    // Walk-in collections: bars rise in sequence when the chart scrolls into view
    var wCtx = document.getElementById('walkinChart');
    if (wCtx) {
        var winChart = null;
        var buildWinChart = function() {
            if (winChart) {
                var ds = winChart.data.datasets[0];
                var real = ds.data.slice();
                ds.data = real.map(function() { return 0; });
                winChart.update('none');
                ds.data = real;
                winChart.update();
                return;
            }
            winChart = new Chart(wCtx, {
                type: 'bar',
                data: { labels: <?= json_encode($labels) ?>, datasets: [
                    { label: 'Walk-In', data: <?= json_encode($walkin) ?>,
                      backgroundColor: function(ctx) { if (!ctx.chart || !ctx.chart.ctx) return 'rgba(16,185,129,.78)'; var g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 240); g.addColorStop(0, '#059669'); g.addColorStop(1, '#6ee7b7'); return g; },
                      hoverBackgroundColor: '#059669', borderRadius: 7, borderSkipped: false, maxBarThickness: 28 }
                ]},
                options: {
                    responsive: true, maintainAspectRatio: false,
                    animation: reduced ? false : { delay: function(ctx) { return ctx.type === 'data' && ctx.mode === 'default' ? ctx.dataIndex * 80 : 0; }, duration: 900, easing: 'easeOutQuart' },
                    onClick: function() { window.location = '<?= url('/manager/students/walk-in-payment') ?>'; },
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: tooltipBg, padding: 12, cornerRadius: 8, callbacks: { label: function(ctx) { return 'Walk-In: ' + symbol + fmt(ctx.parsed.y); } } }
                    },
                    scales: {
                        y: { beginAtZero: true, grace: '5%', ticks: { font: { size: 11 }, color: tickMuted, callback: function(v) { return symbol + v.toLocaleString(); } }, grid: { color: axisGrid, drawBorder: false } },
                        x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: tickStrong } }
                    }
                }
            });
        };
    if ('IntersectionObserver' in window && !reduced) {
        var wObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(en) {
                if (en.isIntersecting) { buildWinChart(); }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px 0px 0px' });
        wObs.observe(wCtx);
        setTimeout(buildWinChart, 4000);
    } else {
        buildWinChart();
    }
    }

    // Occupancy gauge
    var gaugeCtx = document.getElementById('occupancyGauge');
    if (gaugeCtx) {
        var occ = Math.max(0, Math.min(100, <?= (int)$occupancyRate ?>));
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: { datasets: [{ data: [occ, 100 - occ], backgroundColor: ['#6366f1', dark ? '#334155' : '#eef2f7'], borderWidth: 0, hoverOffset: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, rotation: -Math.PI, circumference: Math.PI, cutout: '74%', animation: { duration: 1300, easing: 'easeOutQuart' }, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }

    // Payment method doughnut
    var mCtx = document.getElementById('methodChart');
    if (mCtx) {
        new Chart(mCtx, {
            type: 'doughnut',
            data: { labels: ['Cash', 'GCash'], datasets: [{ data: [<?= $pmtMethods['cash'] ?>, <?= $pmtMethods['gcash'] ?>], backgroundColor: ['#10b981', '#0ea5e9'], borderWidth: 0, hoverOffset: 6 }] },
            options: { responsive: true, cutout: '70%', animation: { duration: 1100, easing: 'easeOutQuart' }, plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', font: { size: 12, weight: '600' }, color: dark ? '#cbd5e1' : undefined } }, tooltip: { backgroundColor: tooltipBg, padding: 12, cornerRadius: 8, callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + symbol + fmt(ctx.parsed); } } } } }
        });
    }

    // Stagger-animate progress bars as their card scrolls into view
    var runBars = function(card) {
        var bars = card.querySelectorAll('.an-bar-fill');
        bars.forEach(function(el, i) {
            var w = parseInt(el.dataset.w, 10) || 0;
            el.style.transitionDelay = (i * 70) + 'ms';
            el.style.width = w + '%';
        });
    };
    var barCards = Array.prototype.filter.call(document.querySelectorAll('.an-card'), function(c) { return c.querySelector('.an-bar-fill'); });
        if ('IntersectionObserver' in window && !reduced) {
            var barObs = new IntersectionObserver(function(entries) {
                entries.forEach(function(en) {
                    if (en.isIntersecting) {
                        runBars(en.target);
                    } else {
                        en.target.querySelectorAll('.an-bar-fill').forEach(function(el) { el.style.width = '0'; });
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });
        barCards.forEach(function(c) { barObs.observe(c); });
    } else {
        barCards.forEach(function(c) { runBars(c); });
    }

    // Entrance reveals + animated counters (reduced-motion aware)
    var animEls = document.querySelectorAll('.an-anim, .an-count[data-count]');
    var runCount = function(el) {
        if (el.getAttribute('data-count') === null) return;
        var to = parseFloat(el.getAttribute('data-count')) || 0;
        var dec = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var prefix = el.getAttribute('data-prefix') || '';
        var suffix = el.getAttribute('data-suffix') || '';
        var fmtEl = function(v) { return prefix + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix; };
        if (reduced) { el.textContent = fmtEl(to); return; }
        var dur = 1100;
        var start = null;
        var step = function(ts) {
            if (!start) start = ts;
            var p = Math.min(1, (ts - start) / dur);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmtEl(to * eased);
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = fmtEl(to);
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
            if (el.classList.contains('an-anim')) el.classList.add('in');
            runCount(el);
        });
    }
});

// Re-render charts with correct axis/legend colors on theme change.
window.addEventListener('sbh:theme', function() { window.location.reload(); });
</script>