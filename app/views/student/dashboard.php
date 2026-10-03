<?php
$fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['suffix'] ?? ''));
$initials = strtoupper(substr($student['first_name'] ?? '', 0, 1)) . strtoupper(substr($student['last_name'] ?? '', 0, 1));
$initials = $initials ?: 'S';
$hour = (int)serverDate('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>

<!-- Welcome Banner -->
<div class="dash-welcome dash-anim" style="--d:.02s;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h3><?= $greeting ?>, <?= e($student['first_name'] ?? 'Student') ?>!</h3>
            <p>Here's what's happening with your account today. <span style="opacity:.6"><?= serverNow()->format('l, F j, Y') ?></span></p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($upcomingPayments)): ?>
            <a href="<?= url('/student/payments') ?>" class="btn btn-light btn-sm" style="border-radius:10px;font-weight:600;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);">
                <i class="fas fa-exclamation-circle me-1"></i> <?= $pendingPayments ?> Pending Payment<?= $pendingPayments !== 1 ? 's' : '' ?>
            </a>
            <?php endif; ?>
            <a href="<?= url('/student/payment/create') ?>" class="btn btn-light btn-sm" style="border-radius:10px;font-weight:600;background:#fff;color:#4f46e5;border:none;">
                <i class="fas fa-circle me-1"></i> <?= e($student['first_name'] ?? 'Student') ?>
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6 dash-anim" style="--d:.05s">
        <a href="<?= url('/student/reservations') ?>" class="text-decoration-none dash-click" title="View reservations">
            <div class="dash-stat">
                <span class="dash-stat-view"><i class="fas fa-arrow-right"></i> View</span>
                <div class="d-flex align-items-center gap-3">
                    <div class="ds-icon" style="background: #ede9fe; color: #7c3aed;">
                        <i class="fas fa-home"></i>
                    </div>
                    <div>
                        <div class="ds-value" id="stat-active-reservation" data-count="<?= !empty($activeReservation) ? 1 : 0 ?>"><?= !empty($activeReservation) ? '1' : '0' ?></div>
                        <div class="ds-label">Active Reservation</div>
                    </div>
                </div>
                <div class="ds-accent" style="background: #7c3aed;"></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 dash-anim" style="--d:.1s">
        <a href="<?= url('/student/payments') ?>" class="text-decoration-none dash-click" title="View payments">
            <div class="dash-stat">
                <span class="dash-stat-view"><i class="fas fa-arrow-right"></i> View</span>
                <div class="d-flex align-items-center gap-3">
                    <div class="ds-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="ds-value" id="stat-pending-payments" data-count="<?= (int)$pendingPayments ?>"><?= $pendingPayments ?></div>
                        <div class="ds-label">Pending Payments</div>
                    </div>
                </div>
                <div class="ds-accent" style="background: #d97706;"></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 dash-anim" style="--d:.15s">
        <a href="<?= url('/student/receipts') ?>" class="text-decoration-none dash-click" title="View receipts">
            <div class="dash-stat">
                <span class="dash-stat-view"><i class="fas fa-arrow-right"></i> View</span>
                <div class="d-flex align-items-center gap-3">
                    <div class="ds-icon" style="background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="ds-value" id="stat-total-paid" data-count="<?= (float)$totalPaid ?>" data-prefix="<?= e(getCurrencySymbol()) ?>" data-decimals="<?= (float)$totalPaid == (int)$totalPaid ? 0 : 2 ?>"><?= formatCurrency($totalPaid) ?></div>
                        <div class="ds-label">Total Paid</div>
                    </div>
                </div>
                <div class="ds-accent" style="background: #16a34a;"></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 dash-anim" style="--d:.2s">
        <a href="<?= url('/student/maintenance') ?>" class="text-decoration-none dash-click" title="View maintenance requests">
            <div class="dash-stat">
                <span class="dash-stat-view"><i class="fas fa-arrow-right"></i> View</span>
                <div class="d-flex align-items-center gap-3">
                    <div class="ds-icon" style="background: #fce7f3; color: #db2777;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <div class="ds-value" id="stat-open-maintenance" data-count="<?= (int)$openMaintenance ?>"><?= $openMaintenance ?></div>
                        <div class="ds-label">Open Maintenance</div>
                    </div>
                </div>
                <div class="ds-accent" style="background: #db2777;"></div>
            </div>
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="dash-quick mb-4 dash-anim" style="--d:.25s">
    <a href="<?= url('/student/payment/create') ?>"><i class="fas fa-money-bill-wave"></i> Pay Now</a>
    <a href="<?= url('/student/reservations') ?>"><i class="fas fa-calendar-plus"></i> Reservation</a>
    <?php if (!empty($isTenant)): ?>
    <a href="<?= url('/student/maintenance/create') ?>"><i class="fas fa-tools"></i> Maintenance</a>
    <a href="<?= url('/student/complaints/create') ?>"><i class="fas fa-flag"></i> Complaint</a>
    <a href="<?= url('/student/refund-requests') ?>"><i class="fas fa-hand-holding-usd"></i> Refund Request</a>
    <?php endif; ?>
    <a href="<?= url('/student/feedback/create') ?>"><i class="fas fa-comment-dots"></i> Feedback</a>
    <a href="<?= url('/student/receipts') ?>"><i class="fas fa-receipt"></i> Receipts</a>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-xl-8">

        <!-- Active Reservation + Quick Stats Row -->
        <div class="row g-4 mb-0">
            <div class="col-md-6">
                <div class="dash-section dash-anim" style="margin-bottom:0;height:100%;--d:.3s;">
                    <div class="ds-header">
                        <h6><i class="fas fa-home me-2 text-primary"></i>Active Reservation</h6>
                        <?php if (!empty($activeReservation)): ?>
                            <a href="<?= url('/student/reservations') ?>" class="badge bg-success text-decoration-none">Active</a>
                        <?php endif; ?>
                    </div>
                    <div class="ds-body">
                        <?php if (!empty($activeReservation)): ?>
                        <a href="<?= url('/student/reservations') ?>" class="text-decoration-none">
                        <div class="dash-reservation">
                            <span class="dash-res-btn"><i class="fas fa-arrow-right"></i> View Details</span>
                            <?php if (!empty($activeReservation['primary_image'])): ?>
                            <img src="<?= UPLOAD_URL . e($activeReservation['primary_image']) ?>" alt="<?= e($activeReservation['room_name'] ?? '') ?>" class="w-100 mb-3" style="height:100px;object-fit:cover;border-radius:8px;">
                            <?php endif; ?>
                            <div class="dr-label">Current Room</div>
                            <div class="dr-room"><?= e($activeReservation['room_name'] ?? '') ?></div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="dr-detail">Room Number: <strong><?= e($activeReservation['room_number'] ?? '') ?></strong></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="dr-detail">Move-in: <strong><?= formatDate($activeReservation['move_in_date'] ?? '') ?></strong></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="dr-detail">Duration: <strong><?= e($activeReservation['expected_duration'] ?? '') ?> month(s)</strong></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="dr-detail">Rent: <strong><?= formatCurrency((float)($activeReservation['monthly_rent'] ?? 0)) ?></strong></div>
                                </div>
                            </div>
                        </div>
                        </a>
                        <?php else: ?>
                        <div class="dash-empty">
                            <i class="fas fa-bed"></i>
                            <p>No active reservation yet.</p>
                        </div>
                        <?php if (!empty($availableRooms)): ?>
                        <div class="mt-3">
                            <div class="small fw-semibold text-muted text-uppercase mb-2" style="letter-spacing:.5px;font-size:11px;">Available Rooms</div>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach (array_slice($availableRooms, 0, 3) as $room): ?>
                                <a href="<?= url('/student/reservations?open=reserve&room=' . (int)$room['id']) ?>" class="text-decoration-none">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 border" style="transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                                        <?php if (!empty($room['primary_image'])): ?>
                                        <img src="<?= UPLOAD_URL . e($room['primary_image']) ?>" alt="<?= e($room['room_name']) ?>" style="width:58px;height:44px;object-fit:cover;border-radius:8px;min-width:58px;">
                                        <?php else: ?>
                                        <div style="width:58px;height:44px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;min-width:58px;"><i class="fas fa-bed text-muted"></i></div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="fw-semibold small text-dark text-truncate"><?= e($room['room_name']) ?> <span class="text-muted fw-normal">(<?= e($room['room_number']) ?>)</span></div>
                                            <div class="text-muted" style="font-size:11px;"><?= formatCurrency((float)$room['monthly_rent']) ?>/mo &middot; <?= ucwords(str_replace('_', ' ', $room['room_type'] ?? 'bedspacer')) ?></div>
                                        </div>
                                        <span class="btn btn-primary btn-sm" style="white-space:nowrap;"><i class="fas fa-calendar-plus me-1"></i>Reserve Now</span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?= url('/student/reservations') ?>" class="btn btn-outline-primary btn-sm w-100 mt-2"><i class="fas fa-list me-1"></i> Browse All Rooms</a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dash-section dash-anim" style="margin-bottom:0;height:100%;--d:.36s;">
                    <div class="ds-header">
                        <h6><i class="fas fa-chart-pie me-2 text-info"></i>Quick Stats</h6>
                    </div>
                    <div class="ds-body">
                        <div class="row g-2">
                            <a href="<?= url('/student/payments') ?>" class="col-6 text-decoration-none">
                                <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                    <div style="font-size:18px;font-weight:800;color:#16a34a;"><?= $totalPayments ?></div>
                                    <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Paid</div>
                                </div>
                            </a>
                            <a href="<?= url('/student/payments') ?>" class="col-6 text-decoration-none">
                                <div style="background:#fff7ed;border-radius:10px;padding:12px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                    <div style="font-size:18px;font-weight:800;color:#d97706;"><?= $pendingPayments ?></div>
                                    <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Pending</div>
                                </div>
                            </a>
                            <a href="<?= url('/student/payments') ?>" class="col-6 text-decoration-none">
                                <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                    <div style="font-size:18px;font-weight:800;color:#dc2626;"><?= $overduePayments ?></div>
                                    <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Overdue</div>
                                </div>
                            </a>
                            <a href="<?= url('/student/receipts') ?>" class="col-6 text-decoration-none">
                                <div style="background:#eff6ff;border-radius:10px;padding:12px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                    <div style="font-size:18px;font-weight:800;color:#2563eb;"><?= $totalReceipts ?></div>
                                    <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Receipts</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Receipts by Payment Type -->
        <?php if (!empty($latestReceipts)): ?>
        <div class="dash-section mt-4 dash-anim" style="--d:.34s;">
            <div class="ds-header">
                <h6><i class="fas fa-file-invoice me-2 text-success"></i>Latest Receipts by Payment Type</h6>
                <a href="<?= url('/student/receipts') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All Receipts</a>
            </div>
            <div class="ds-body">
                <div class="row g-3">
                    <?php foreach ($latestReceipts as $lr):
                        $lrType = (string)($lr['payment_type'] ?? '');
                        $lrLabel = ucwords(str_replace('_', ' ', $lrType));
                        $lrBadge = match($lrType) {
                            'monthly_rent' => 'bg-primary',
                            'advance_payment' => 'bg-success',
                            'reservation_fee' => 'bg-info',
                            'electric_bill' => 'bg-warning text-dark',
                            'water_bill' => 'bg-info',
                            default => 'bg-secondary',
                        };
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge <?= $lrBadge ?> text-capitalize"><?= e($lrLabel) ?></span>
                                <a href="<?= url('/student/receipt/' . (int)$lr['id']) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Receipt Number</div>
                            <div class="fw-semibold small mt-1 mb-2"><?= e($lr['receipt_number']) ?></div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase" style="font-size:10px;">Period</div>
                                    <div class="fw-semibold small mt-1"><?= e($lr['billing_period'] ?? '—') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase" style="font-size:10px;">Amount</div>
                                    <div class="fw-semibold small mt-1" style="color:#059669;"><?= formatCurrency((float)($lr['total'] ?? $lr['amount'] ?? 0)) ?></div>
                                </div>
                            </div>
                            <?php if ($lrType === 'monthly_rent' && !empty($lr['billing_period'])): ?>
                            <div class="mt-2">
                                <a href="<?= url('/student/receipt/rent/' . e($lr['billing_period'])) ?>" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-invoice me-1"></i> Rent Statement
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment Overview Chart -->
        <div class="dash-section mt-4 dash-anim" style="--d:.38s;">
            <div class="ds-header">
                <h6><i class="fas fa-chart-bar me-2 text-info"></i>Payment Overview</h6>
                <a href="<?= url('/student/payments') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body" style="min-height: 190px;">
                <canvas id="paymentChart" height="160"></canvas>
            </div>
        </div>

        <!-- Payment Trend Chart -->
        <div class="dash-section dash-anim" style="--d:.42s;">
            <div class="ds-header">
                <h6><i class="fas fa-chart-line me-2 text-success"></i>Payment Trend (6 Months)</h6>
                <a href="<?= url('/student/payments') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body" style="min-height: 170px;">
                <canvas id="trendChart" height="140"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="dash-section dash-anim" style="--d:.46s;">
            <div class="ds-header">
                <h6><i class="fas fa-exchange-alt me-2 text-success"></i>Recent Transactions</h6>
                <a href="<?= url('/student/payments') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body-plain">
                <?php if (!empty($recentPayments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $pay): ?>
                            <tr style="cursor:pointer;" onclick="window.location='<?= url('/student/payment/' . $pay['id']) ?>'" title="View details">
                                <td><span class="badge bg-dark bg-opacity-10 text-dark fw-bold" style="font-size:.78rem;"><?= e($pay['payment_code']) ?></span></td>
                                <td class="text-capitalize small"><?= e(str_replace('_', ' ', $pay['payment_type'])) ?></td>
                                <td class="fw-bold" style="color:#059669;"><?= formatCurrency((float)$pay['amount']) ?></td>
                                <td class="small"><?= $pay['payment_method'] ? e(ucwords(str_replace('_', ' ', $pay['payment_method']))) : '<span class="text-muted">—</span>' ?></td>
                                <td class="small text-muted"><?= formatDate($pay['created_at'] ?? '') ?></td>
                                <td><?= paymentStatusCell($pay) ?></td>
                                <td class="text-center">
                                    <?php if (!in_array($pay['payment_type'] ?? '', ['monthly_rent', 'advance_payment', 'full_payment'], true) && !in_array($pay['status'], ['paid', 'due_today', 'overdue'], true)): ?>
                                    <form method="POST" action="<?= url('/student/payment/delete/' . $pay['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation();" data-confirm="Delete payment <?= e($pay['payment_code']) ?>? This will also remove its receipt and payment history. This action cannot be undone." title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions yet.</p>
                    <a href="<?= url('/student/payment/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Submit Payment</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="dash-section dash-anim" style="--d:.5s;">
            <div class="ds-header">
                <h6><i class="fas fa-bullhorn me-2 text-warning"></i>Recent Announcements</h6>
                <a href="<?= url('/student/announcements') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body-plain">
                <?php if (!empty($recentAnnouncements)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentAnnouncements as $ann): ?>
                    <a href="<?= url('/student/announcement/' . $ann['id']) ?>" class="list-group-item list-group-item-action px-4 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="me-3">
                                <h6 class="mb-1 fw-semibold small"><?= e($ann['title']) ?></h6>
                                <p class="mb-1 text-muted" style="font-size: 13px;"><?= e(truncate($ann['content'] ?? '', 80)) ?></p>
                                <small class="text-muted"><i class="fas fa-clock me-1"></i><?= timeAgo($ann['published_at'] ?? $ann['created_at']) ?></small>
                            </div>
                            <?= statusBadge($ann['type'] ?? 'general') ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No announcements yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-xl-4">

        <!-- Upcoming Payments -->
        <?php if (!empty($upcomingPayments)): ?>
        <div class="dash-section dash-anim" style="--d:.3s;">
            <div class="ds-header">
                <h6><i class="fas fa-calendar-alt me-2 text-warning"></i>Upcoming Due</h6>
                <a href="<?= url('/student/payments') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body-plain">
                <div class="list-group list-group-flush">
                    <?php foreach ($upcomingPayments as $up): ?>
                    <?php
                    $daysLeft = (int)((strtotime($up['due_date']) - strtotime(serverDate())) / 86400);
                    $urgColor = $daysLeft <= 0 ? '#dc2626' : ($daysLeft <= 1 ? '#ea580c' : ($daysLeft <= 3 ? '#ca8a04' : '#2563eb'));
                    $urgBg = $daysLeft <= 0 ? '#fef2f2' : ($daysLeft <= 1 ? '#fff7ed' : ($daysLeft <= 3 ? '#fefce8' : '#eff6ff'));
                    ?>
                    <a href="<?= url('/student/payment/' . $up['id']) ?>" class="list-group-item list-group-item-action px-4 py-3" style="border-left:3px solid <?= $urgColor ?>;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold small text-capitalize"><?= e(str_replace('_', ' ', $up['payment_type'])) ?></h6>
                                <p class="mb-1 fw-bold" style="font-size:14px;color:<?= $urgColor ?>;"><?= formatCurrency((float)$up['amount']) ?></p>
                                <small class="text-muted">Due: <?= formatDate($up['due_date']) ?></small>
                            </div>
                            <span class="badge" style="background:<?= $urgBg ?>;color:<?= $urgColor ?>;font-size:11px;font-weight:600;">
                                <?= $daysLeft <= 0 ? 'Overdue' : ($daysLeft === 0 ? 'Today' : ($daysLeft === 1 ? 'Tomorrow' : $daysLeft . ' days')) ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reservation Payment Credits -->
        <?php if (!empty($reservationCredits)): ?>
        <div class="dash-section dash-anim" style="--d:.33s;">
            <div class="ds-header">
                <h6><i class="fas fa-piggy-bank me-2 text-warning"></i>Payment Credits from Deleted Reservations</h6>
                <small class="text-muted">These will be auto-applied to your next approved reservation</small>
            </div>
            <div class="ds-body-plain">
                <div class="list-group list-group-flush">
                    <?php 
                    $totalCredit = 0;
                    foreach ($reservationCredits as $credit): 
                        $totalCredit += (float)$credit['total_amount'];
                        $typeLabel = ucwords(str_replace('_', ' ', $credit['payment_type']));
                    ?>
                    <div class="list-group-item list-group-item-action px-4 py-3" style="border-left:3px solid #f59e0b;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold small"><?= e($typeLabel) ?></h6>
                                <p class="mb-1 fw-bold" style="font-size:14px;color:#f59e0b;"><?= formatCurrency((float)$credit['total_amount']) ?></p>
                                <small class="text-muted">From: <?= e($credit['original_reservation_code']) ?></small>
                                <br><small class="text-muted"><?= e($credit['notes'] ?? '') ?></small>
                            </div>
                            <span class="badge bg-warning text-dark" style="font-size:11px;font-weight:600;">Available</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="list-group-item px-4 py-3" style="background:#fffbeb;border-top:1px solid #fde68a;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total Available Credit</span>
                            <span class="fw-bold" style="font-size:16px;color:#f59e0b;"><?= formatCurrency($totalCredit) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Notifications -->
        <div class="dash-section dash-anim" style="--d:.36s;">
            <div class="ds-header">
                <h6><i class="fas fa-bell me-2 text-primary"></i>Notifications</h6>
                <a href="<?= url('/student/notifications') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">View All</a>
            </div>
            <div class="ds-body-plain">
                <?php if (!empty($notifications)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notif): ?>
                    <?php
                    $dashTitle = strtolower($notif['title'] ?? '');
                    $isUrgent = (strpos($dashTitle, 'overdue') !== false || strpos($dashTitle, 'due today') !== false);
                    $isWarning = (strpos($dashTitle, 'due tomorrow') !== false || strpos($dashTitle, 'due in 3') !== false);
                    $dashBorderColor = $isUrgent ? '#dc2626' : ($isWarning ? '#ca8a04' : '');
                    ?>
                    <a href="<?= url('/student/notifications') ?>" class="list-group-item list-group-item-action px-4 py-3 text-decoration-none <?= empty($notif['is_read']) ? 'bg-light' : '' ?>"
                         <?php if ($dashBorderColor && empty($notif['is_read'])): ?>
                         style="border-left: 3px solid <?= $dashBorderColor ?>;"
                         <?php endif; ?>>
                        <div class="d-flex align-items-start gap-3">
                            <?php if ($isUrgent && empty($notif['is_read'])): ?>
                            <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;min-width:34px;background:#fee2e2;color:#dc2626;">
                                <i class="fas fa-<?= strpos($dashTitle, 'overdue') !== false ? 'exclamation-circle' : 'clock' ?>"></i>
                            </div>
                            <?php elseif ($isWarning && empty($notif['is_read'])): ?>
                            <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;min-width:34px;background:#fef9c3;color:#ca8a04;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <?php else: ?>
                            <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;min-width:34px;background:<?= empty($notif['is_read']) ? '#ede9fe' : '#f1f5f9' ?>;color:<?= empty($notif['is_read']) ? '#7c3aed' : '#94a3b8' ?>;">
                                <i class="fas fa-<?= ($notif['type'] ?? 'info') === 'payment' ? 'money-bill' : (($notif['type'] ?? '') === 'reservation' ? 'calendar-check' : (($notif['type'] ?? '') === 'announcement' ? 'bullhorn' : 'bell')) ?>"></i>
                            </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small fw-semibold <?= empty($notif['is_read']) ? '' : 'text-muted' ?>" style="font-size:13px;">
                                    <?= e($notif['title'] ?? '') ?>
                                </h6>
                                <p class="mb-0 small text-muted" style="font-size:11px;"><?= e(truncate($notif['message'] ?? '', 60)) ?></p>
                                <small class="text-muted" style="font-size:10px;"><i class="fas fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?></small>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dash-section dash-anim" style="--d:.42s;">
            <div class="ds-header">
                <h6><i class="fas fa-history me-2 text-secondary"></i>Recent Activity</h6>
            </div>
            <div class="ds-body" style="padding:14px 18px;">
                <?php if (!empty($activityLogs)): ?>
                <div class="activity-timeline">
                    <?php foreach ($activityLogs as $log): ?>
                    <?php
                    $actIcons = [
                        'submit_payment' => ['icon' => 'money-bill', 'color' => '#16a34a', 'bg' => '#dcfce7'],
                        'login' => ['icon' => 'sign-in-alt', 'color' => '#2563eb', 'bg' => '#dbeafe'],
                        'logout' => ['icon' => 'sign-out-alt', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                        'update_profile' => ['icon' => 'user-edit', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        'submit_maintenance' => ['icon' => 'tools', 'color' => '#db2777', 'bg' => '#fce7f3'],
                        'submit_complaint' => ['icon' => 'flag', 'color' => '#ea580c', 'bg' => '#fff7ed'],
                        'submit_feedback' => ['icon' => 'comment-dots', 'color' => '#0891b2', 'bg' => '#ecfeff'],
                        'create_reservation' => ['icon' => 'calendar-plus', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        'cancel_reservation' => ['icon' => 'calendar-times', 'color' => '#dc2626', 'bg' => '#fef2f2'],
                    ];
                    $actKey = $log['action'] ?? 'other';
                    $act = $actIcons[$actKey] ?? ['icon' => 'info-circle', 'color' => '#64748b', 'bg' => '#f1f5f9'];
                    ?>
                    <div class="at-item">
                        <div class="at-dot" style="background:<?= $act['bg'] ?>;color:<?= $act['color'] ?>;">
                            <i class="fas fa-<?= $act['icon'] ?>"></i>
                        </div>
                        <div class="at-content">
                            <div class="at-text"><?= e($log['description'] ?? $log['action']) ?></div>
                            <div class="at-time"><?= timeAgo($log['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-history"></i>
                    <p>No activity recorded yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Account Summary -->
        <div class="dash-section dash-anim" style="--d:.48s;">
            <div class="ds-header">
                <h6><i class="fas fa-user-circle me-2 text-secondary"></i>Account Info</h6>
                <a href="<?= url('/student/profile') ?>" class="text-decoration-none small fw-semibold" style="color:#4f46e5;">Edit</a>
            </div>
            <div class="ds-body">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;margin-right:14px;min-width:48px;">
                        <?= $initials ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= e($fullName) ?></div>
                        <small class="text-muted"><?= e($_SESSION['user_email'] ?? '') ?></small>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?= url('/student/profile') ?>" class="text-decoration-none">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">ID Number</div>
                            <div class="fw-bold mt-1 small" style="color:#1e293b;"><?= e($student['student_id_number'] ?? 'N/A') ?></div>
                        </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px;">
                            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">School</div>
                            <div class="fw-bold mt-1 small" style="color:#1e293b;"><?= e($student['school_university'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px;">
                            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Course</div>
                            <div class="fw-bold mt-1 small" style="color:#1e293b;"><?= e($student['course_program'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px;">
                            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Year Level</div>
                            <div class="fw-bold mt-1 small" style="color:#1e293b;"><?= e($student['year_level'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                </div>
                <a href="<?= url('/student/profile') ?>" class="btn btn-outline-primary btn-sm w-100 mt-3" style="border-radius:10px;"><i class="fas fa-user-edit me-1"></i> Edit Profile</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Activity Timeline */
.activity-timeline { position: relative; }
.activity-timeline::before {
    content: ''; position: absolute; left: 17px; top: 0; bottom: 0;
    width: 2px; background: #e2e8f0;
}
.at-item { display: flex; gap: 12px; margin-bottom: 12px; position: relative; }
.at-item:last-child { margin-bottom: 0; }
.at-dot {
    width: 34px; height: 34px; border-radius: 10px; display: flex;
    align-items: center; justify-content: center; min-width: 34px;
    font-size: 13px; z-index: 1;
}
.at-content { flex: 1; min-width: 0; }
.at-text { font-size: 13px; color: #334155; line-height: 1.4; }
.at-time { font-size: 11px; color: #94a3b8; margin-top: 2px; }

/* ----- Dashboard polish: animations + click affordances ----- */
.dash-anim{opacity:0;transform:translateY(14px);transition:opacity .55s cubic-bezier(.22,.61,.36,1),transform .55s cubic-bezier(.22,.61,.36,1);transition-delay:var(--d,0s)}
.dash-anim.in{opacity:1;transform:none}
.dash-welcome{background:linear-gradient(120deg,#4f46e5 0%,#7c3aed 50%,#a855f7 100%);background-size:220% 220%;animation:dashGrad 14s ease infinite}
@keyframes dashGrad{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
.dash-welcome::before,.dash-welcome::after{animation:dashFloat 9s ease-in-out infinite}
@keyframes dashFloat{0%,100%{transform:translate(0,0)}50%{transform:translate(-8px,6px)}}
.dash-click{position:relative}
.dash-stat{transition:transform .22s cubic-bezier(.22,.61,.36,1),box-shadow .22s cubic-bezier(.22,.61,.36,1)}
.dash-stat::after{content:'';position:absolute;inset:0;background:linear-gradient(105deg,transparent 42%,rgba(255,255,255,.7) 50%,transparent 58%);transform:translateX(-130%);transition:transform .7s ease;pointer-events:none}
.dash-click:hover .dash-stat::after{transform:translateX(130%)}
.dash-click:hover .dash-stat{transform:translateY(-3px);box-shadow:0 10px 24px rgba(15,23,42,.08)}
.dash-stat-view{position:absolute;top:12px;right:12px;display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:800;letter-spacing:.3px;color:#4f46e5;background:#fff;padding:3px 9px;border-radius:999px;box-shadow:0 2px 8px rgba(15,23,42,.14);opacity:0;transform:translateY(-3px);transition:opacity .22s ease,transform .22s ease;z-index:2}
.dash-click:hover .dash-stat-view{opacity:1;transform:none}
.dash-section{transition:box-shadow .25s ease,border-color .25s ease}
.dash-section:hover{box-shadow:0 10px 26px rgba(15,23,42,.07);border-color:#c7d2fe}
.dash-reservation{position:relative}
.dash-res-btn{position:absolute;top:10px;right:10px;display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:800;letter-spacing:.3px;color:#fff;background:#4f46e5;padding:4px 10px;border-radius:999px;box-shadow:0 4px 12px rgba(79,70,229,.35);opacity:0;transform:translateY(-4px);transition:opacity .22s ease,transform .22s ease;z-index:2}
.dash-reservation:hover .dash-res-btn{opacity:1;transform:none}
.at-item{transition:transform .2s ease}
.at-item:hover{transform:translateX(3px)}
@media (prefers-reduced-motion: reduce){
  .dash-anim{opacity:1;transform:none;transition:none}
  .dash-welcome,.dash-welcome::before,.dash-welcome::after{animation:none}
  .dash-stat::after,.dash-stat-view,.at-item{transition:none}
}

/* ----- Dark mode: dashboard background fixes ----- */
html[data-theme="dark"] .dash-reservation{background:linear-gradient(135deg,rgba(16,185,129,.14) 0%,rgba(16,185,129,.2) 100%);border-color:rgba(16,185,129,.3)}
html[data-theme="dark"] .dash-reservation .dr-label{color:#6ee7b7}
html[data-theme="dark"] .dash-reservation .dr-room{color:#bbf7d0}
html[data-theme="dark"] .dash-reservation .dr-detail{color:#94a3b8}
html[data-theme="dark"] .dash-reservation .dr-detail strong{color:#e2e8f0}
html[data-theme="dark"] .activity-timeline::before{background:rgba(255,255,255,.08)}
html[data-theme="dark"] .at-text{color:#cbd5e1}
html[data-theme="dark"] .at-time{color:#64748b}
html[data-theme="dark"] [style*="color:#1e293b"]{color:#e2e8f0!important}
html[data-theme="dark"] [style*="border:1px solid #e2e8f0"]{border-color:#334155!important}
html[data-theme="dark"] .badge.bg-dark.bg-opacity-10{background:rgba(255,255,255,.12)!important}
</style>

<script>
var CSYM = <?= json_encode(getCurrencySymbol()) ?>;
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var sbhGridCharts = [];
    // Payment Overview Chart (Clickable)
    var ctx = document.getElementById('paymentChart');
    if (ctx) {
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Paid', 'Pending', 'Overdue'],
                datasets: [{
                    label: 'Payments',
                    data: [
                        <?= (int)($totalPayments ?? 0) ?>,
                        <?= (int)($pendingPayments ?? 0) ?>,
                        <?= (int)($overduePayments ?? 0) ?>
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    hoverBackgroundColor: ['#059669', '#d97706', '#dc2626'],
                    borderRadius: 8,
                    barThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        window.location = '<?= url('/student/payments') ?>';
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(ctx) { return ctx.parsed.y + ' payment(s)'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 12 }, color: '#94a3b8' },
                        grid: { color: isDark ? 'rgba(255,255,255,.08)' : '#f1f5f9', drawBorder: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' }, color: isDark ? '#94a3b8' : '#64748b' }
                    }
                }
            }
        });
        sbhGridCharts.push(chart);
    }

    // Payment Trend Line Chart (Clickable)
    var trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        var trendData = <?= json_encode($monthlyTrend ?? []) ?>;
        var paidByMonth = {};
        var pendingByMonth = {};
        var overdueByMonth = {};

        // Build month labels
        var months = [];
        for (var i = 5; i >= 0; i--) {
            var d = new Date();
            d.setMonth(d.getMonth() - i);
            var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            var label = d.toLocaleString('default', { month: 'short' });
            months.push({ key: key, label: label });
            paidByMonth[key] = 0;
            pendingByMonth[key] = 0;
            overdueByMonth[key] = 0;
        }

        // Aggregate data
        trendData.forEach(function(row) {
            var mk = row.month_key;
            if (paidByMonth.hasOwnProperty(mk)) {
                if (row.status === 'paid') paidByMonth[mk] += parseFloat(row.total);
                else if (row.status === 'pending') pendingByMonth[mk] += parseFloat(row.total);
                else if (row.status === 'overdue') overdueByMonth[mk] += parseFloat(row.total);
            }
        });

        var labels = months.map(function(m) { return m.label; });
        var paidData = months.map(function(m) { return paidByMonth[m.key] || 0; });
        var pendingData = months.map(function(m) { return pendingByMonth[m.key] || 0; });

        var trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Paid',
                        data: paidData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Pending',
                        data: pendingData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function() {
                    window.location = '<?= url('/student/payments') ?>';
                },
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, padding: 20, font: { size: 12, weight: '600' }, color: isDark ? '#cbd5e1' : '#64748b' }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                var v = ctx.parsed.y;
                                return ctx.dataset.label + ': ' + CSYM + (v === Math.round(v) ? Math.round(v).toLocaleString() : v.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            color: '#94a3b8',
                            callback: function(v) { return CSYM + v.toLocaleString(); }
                        },
                        grid: { color: isDark ? 'rgba(255,255,255,.08)' : '#f1f5f9', drawBorder: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' }, color: isDark ? '#94a3b8' : '#64748b' }
                    }
                }
            }
        });
        sbhGridCharts.push(trendChart);
    }

    // Reveal on scroll + animated counters (reduced-motion aware)
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var runCount = function(el) {
        if (el.getAttribute('data-count') === null) return;
        var to = parseFloat(el.getAttribute('data-count')) || 0;
        var dec = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var prefix = el.getAttribute('data-prefix') || '';
        var suffix = el.getAttribute('data-suffix') || '';
        var fmt = function(v) { return prefix + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix; };
        if (reduced) { el.textContent = fmt(to); return; }
        var dur = 1100, start = null;
        var step = function(ts) {
            if (!start) start = ts;
            var p = Math.min(1, (ts - start) / dur);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(to * eased);
            if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(to);
        };
        requestAnimationFrame(step);
    };
    var revealEls = document.querySelectorAll('.dash-anim');
    if ('IntersectionObserver' in window && !reduced) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(en) {
                if (en.isIntersecting) {
                    en.target.classList.add('in');
                    en.target.querySelectorAll('[data-count]').forEach(runCount);
                    obs.unobserve(en.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -4% 0px' });
        revealEls.forEach(function(el) { obs.observe(el); });
    } else {
        revealEls.forEach(function(el) {
            el.classList.add('in');
            el.querySelectorAll('[data-count]').forEach(runCount);
        });
    }

    // Re-theme Chart.js grids/ticks/legends on dark mode toggle
    window.addEventListener('sbh:theme', function(e) {
        var dark = e.detail.theme === 'dark';
        sbhGridCharts.forEach(function(ch) {
            var scales = ch.options.scales;
            if (scales) {
                if (scales.y && scales.y.grid) scales.y.grid.color = dark ? 'rgba(255,255,255,.08)' : '#f1f5f9';
                if (scales.x && scales.x.ticks) scales.x.ticks.color = dark ? '#94a3b8' : '#64748b';
            }
            var legend = ch.options.plugins && ch.options.plugins.legend;
            if (legend && legend.labels) legend.labels.color = dark ? '#cbd5e1' : '#64748b';
            ch.update();
        });
    });
});
</script>
