<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Payments</h1>
    <span class="badge bg-primary fs-6"><?= count($payments) ?> total</span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/manager/payments') ?>" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select" aria-label="Filter by status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="upcoming" <?= ($status ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                    <option value="due_today" <?= ($status ?? '') === 'due_today' ? 'selected' : '' ?>>Due Today</option>
                    <option value="partially_paid" <?= ($status ?? '') === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="refunded" <?= ($status ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select" aria-label="Filter by payment type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="monthly_rent" <?= ($type ?? '') === 'monthly_rent' ? 'selected' : '' ?>>Monthly Rent</option>
                    <option value="advance_payment" <?= ($type ?? '') === 'advance_payment' ? 'selected' : '' ?>>Advance Payment</option>
                    <option value="reservation_fee" <?= ($type ?? '') === 'reservation_fee' ? 'selected' : '' ?>>Reservation Fee</option>
                    <option value="full_payment" <?= ($type ?? '') === 'full_payment' ? 'selected' : '' ?>>Full Payment</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="month" class="form-select" aria-label="Filter by month" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    <?php foreach (array_keys($monthOptions ?? []) as $ym): ?>
                        <?php $d = DateTime::createFromFormat('!Y-m', $ym); ?>
                        <option value="<?= e($ym) ?>" <?= ($month ?? '') === $ym ? 'selected' : '' ?>><?= e($d ? $d->format('F Y') : $ym) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript><div class="col-md-2"><button type="submit" class="btn btn-primary">Go</button></div></noscript>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Student</th>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Late Fee</th>
                        <th>Total Due</th>
                        <th>Full Required</th>
                        <th>Method</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $p): ?>
                            <?php
                            $statusStyles = [
                                'pending'          => 'border-left:3px solid #f59e0b;background:rgba(245,158,11,0.06);',
                                'upcoming'         => 'border-left:3px solid #3b82f6;background:rgba(59,130,246,0.06);',
                                'due_today'        => 'border-left:3px solid #f59e0b;background:rgba(245,158,11,0.08);',
                                'partially_paid'   => 'border-left:3px solid #f97316;background:rgba(249,115,22,0.06);',
                                'paid'             => 'border-left:3px solid #10b981;background:rgba(16,185,129,0.06);',
                                'overdue'          => 'border-left:3px solid #ef4444;background:rgba(239,68,68,0.06);',
                                'cancelled'        => 'border-left:3px solid #6b7280;background:rgba(107,114,128,0.06);',
                                'refunded'         => 'border-left:3px solid #8b5cf6;background:rgba(139,92,246,0.06);',
                            ];
                            ?>
                            <tr style="<?= $statusStyles[$p['status']] ?? '' ?>">
                                <td>
                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-bold" style="font-size:.8rem;">
                                        <?= e($p['payment_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-user-graduate text-primary" style="font-size:.75rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></div>
                                            <small class="text-muted"><?= e($p['student_id_number'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-door-open text-success" style="font-size:.75rem;"></i>
                                        </div>
                                        <div>
                                            <?php $roomNo = $p['room_number'] ?? ($p['alt_room_number'] ?? 'N/A'); ?>
                                            <?php $roomName = $p['room_name'] ?? ($p['alt_room_name'] ?? ''); ?>
                                            <div class="fw-semibold">Room <?= e($roomNo) ?></div>
                                            <small class="text-muted"><?= e($roomName) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $ptIcons = ['monthly_rent' => 'fa-home', 'advance_payment' => 'fa-hand-holding-dollar', 'reservation_fee' => 'fa-key'];
                                    $ptIcon = $ptIcons[$p['payment_type']] ?? 'fa-file-invoice';
                                    ?>
                                    <span class="badge bg-light text-dark"><?= $ptIcon ? '<i class="fas ' . $ptIcon . ' me-1"></i>' : '' ?><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></span>
                                </td>
                                <td class="fw-bold text-success"><?= formatCurrency($p['amount']) ?></td>
                                <td>
                                    <?php if ((float)($p['late_fee'] ?? 0) > 0): ?>
                                        <span class="text-danger fw-semibold">+<?= formatCurrency((float)$p['late_fee']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= formatCurrency((float)$p['amount'] + (float)($p['late_fee'] ?? 0)) ?></td>
                                <td>
                                    <?php
                                    $duration = (int)($p['expected_duration'] ?? 0);
                                    $monthlyRent = (float)($p['monthly_rent'] ?? 0);
                                    if ($duration > 0 && $monthlyRent > 0) {
                                        $fullReq = round($monthlyRent * $duration, 2);
                                        $taxRateRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'tax_rate'");
                                        $taxRate = $taxRateRow ? (float)$taxRateRow['setting_value'] : 0;
                                        if ($taxRate > 0) {
                                            $fullReq = round($fullReq + ($fullReq * $taxRate / 100), 2);
                                        }
                                        echo formatCurrency($fullReq);
                                        echo ' <small class="text-muted">(' . $duration . ' mo × ' . formatCurrency($monthlyRent) . ')</small>';
                                    } else {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    ?>
                                </td>
<td>
                                    <?php $amtPaid = (float)($p['amount_paid'] ?? 0); ?>
                                    <?php if ($amtPaid > 0 && !empty($p['payment_method'])): ?>
                                        <?= e(ucwords(str_replace('_', ' ', $p['payment_method']))) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['due_date'] && !in_array($p['status'], ['paid', 'cancelled'])):
                                        $dueDateObj = new DateTime($p['due_date']);
                                        $todayObj = new DateTime(serverDate());
                                        $diff = (int)$todayObj->diff($dueDateObj)->days;
                                        $isPast = $dueDateObj < $todayObj;
                                        if ($p['status'] === 'paid'):
                                    ?>
                                            <span class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i><?= formatDate($p['due_date']) ?></span>
                                        <?php elseif ($isPast): ?>
                                            <span class="text-danger fw-semibold"><i class="fas fa-exclamation-circle me-1"></i><?= $diff ?>d overdue</span>
                                            <br><small class="text-muted"><?= formatDate($p['due_date']) ?></small>
                                        <?php elseif ($diff === 0): ?>
                                            <span class="text-warning fw-semibold"><i class="fas fa-clock me-1"></i>Due today</span>
                                        <?php elseif ($diff <= 3): ?>
                                            <span class="text-primary fw-semibold"><i class="fas fa-hourglass-half me-1"></i><?= $diff ?>d left</span>
                                            <br><small class="text-muted"><?= formatDate($p['due_date']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-calendar-day me-1" style="font-size:.75rem;"></i><?= formatDate($p['due_date']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= $p['due_date'] ? formatDate($p['due_date']) : 'N/A' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $amtPaid = (float)($p['amount_paid'] ?? 0); ?>
                                    <?php if ($p['status'] === 'pending' && $amtPaid <= 0): ?>
                                        <span class="text-muted">Not Submitted</span>
                                    <?php else: ?>
                                        <span class="fw-semibold"><?= statusBadge($p['status']) ?></span>
                                        <?php if ((float)($p['refunded_amount'] ?? 0) > 0 && $p['status'] !== 'refunded'): ?>
                                            <br><small class="fw-semibold" style="color:#8b5cf6;"><i class="fas fa-rotate-left me-1"></i>Partial refund <?= formatCurrency((float)$p['refunded_amount']) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewPayment<?= $p['id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!in_array($p['status'], ['paid', 'cancelled'])): ?>
                                        <form method="POST" action="<?= url('/manager/payment/verify/' . $p['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="paid">
                                            <button type="submit" class="btn btn-sm btn-success" data-confirm="Verify this payment as paid?" title="Verify">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= url('/manager/payment/verify/' . $p['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Cancel this payment?" title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deletePayment<?= $p['id'] ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                No payments found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($payments)): ?>
    <?php foreach ($payments as $p): ?>

        <!-- View Payment Modal -->
        <div class="modal fade" id="viewPayment<?= $p['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-receipt me-2" style="color:#60a5fa;"></i>
                            Payment <?= e($p['payment_code']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="row g-3">

                            <!-- Payment Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-money-check me-1"></i>Payment Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Code</td><td class="fw-bold"><?= e($p['payment_code']) ?></td></tr>
                                    <tr><td>Status</td><td><?= statusBadge($p['status']) ?></td></tr>
                                    <tr><td>Type</td><td><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></td></tr>
                                    <tr><td>Amount</td><td class="fw-bold text-success"><?= formatCurrency($p['amount']) ?></td></tr>
                                    <?php if ((float)($p['late_fee'] ?? 0) > 0): ?>
                                    <tr><td>Late Fee</td><td class="fw-bold text-danger">+<?= formatCurrency((float)$p['late_fee']) ?></td></tr>
                                    <tr><td>Total Due</td><td class="fw-bold"><?= formatCurrency((float)$p['amount'] + (float)$p['late_fee']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if ((float)($p['amount_paid'] ?? 0) > 0): ?>
                                    <tr><td>Amount Paid</td><td class="fw-semibold text-success"><?= formatCurrency((float)$p['amount_paid']) ?></td></tr>
                                    <?php if ((float)($p['refunded_amount'] ?? 0) > 0): ?>
                                    <tr><td>Refunded</td><td class="fw-semibold" style="color:#8b5cf6;"><?= formatCurrency((float)$p['refunded_amount']) ?><?= $p['status'] === 'refunded' ? ' (full)' : ' (partial)' ?></td></tr>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <tr><td>Method</td><td><?= $p['payment_method'] ? e(ucwords(str_replace('_', ' ', $p['payment_method']))) : '<span class="text-muted">—</span>' ?></td></tr>
                                    <tr><td>Due Date</td><td><?= $p['due_date'] ? formatDate($p['due_date']) : 'N/A' ?></td></tr>
                                    <?php if (!empty($p['paid_at'])): ?>
                                        <tr><td>Paid At</td><td class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i><?= formatDate($p['paid_at']) ?></td></tr>
                                    <?php endif; ?>
                                    <tr><td>Created</td><td><?= formatDate($p['created_at']) ?></td></tr>
                                </table>
                            </div>

                            <!-- Student Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-user-graduate me-1"></i>Student Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Name</td><td class="fw-bold"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td></tr>
                                    <tr><td>Student ID</td><td><?= e($p['student_id_number'] ?? 'N/A') ?></td></tr>
                                </table>
                            </div>

                            <!-- Reservation / Room Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-door-open me-1"></i>Reservation / Room</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Reservation</td><td class="fw-bold"><?= e($p['reservation_code'] ?? 'N/A') ?></td></tr>
                                    <tr><td>Room</td><td><?php $rn = $p['room_number'] ?? ($p['alt_room_number'] ?? 'N/A'); echo e($rn); ?></td></tr>
                                    <tr><td>Room Name</td><td><?php $rnm = $p['room_name'] ?? ($p['alt_room_name'] ?? 'N/A'); echo e($rnm); ?></td></tr>
                                </table>
                            </div>

                            <!-- Verification Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-stamp me-1"></i>Verification</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Verified By</td><td><?= $p['verified_by'] ? 'User #' . $p['verified_by'] : 'Not yet verified' ?></td></tr>
                                    <tr><td>Verified At</td><td><?= $p['verified_at'] ? formatDate($p['verified_at']) : '—' ?></td></tr>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Payment Modal -->
        <div class="modal fade" id="deletePayment<?= $p['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-trash me-2 text-danger"></i>Delete Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="nasa-glass-box" style="background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                            <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Are you sure you want to permanently delete this payment?
                            </p>
                            <div class="mt-3 p-2 rounded" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Code:</span>
                                    <strong style="color:#fff;"><?= e($p['payment_code']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Student:</span>
                                    <strong style="color:#fff;"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Type:</span>
                                    <span style="color:#fff;"><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Amount:</span>
                                    <strong class="text-success"><?= formatCurrency($p['amount']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Status:</span>
                                    <span><?= statusBadge($p['status']) ?></span>
                                </div>
                                <?php if ((float)($p['late_fee'] ?? 0) > 0): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Late Fee:</span>
                                    <strong class="text-danger">+<?= formatCurrency((float)$p['late_fee']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Total Due:</span>
                                    <strong style="color:#fff;"><?= formatCurrency((float)$p['amount'] + (float)$p['late_fee']) ?></strong>
                                </div>
                                <?php endif; ?>
                                <?php if ((float)($p['amount_paid'] ?? 0) > 0): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color:rgba(255,255,255,0.5);font-size:.85rem;">Already Paid:</span>
                                    <strong class="text-info"><?= formatCurrency((float)$p['amount_paid']) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($p['status'] === 'overdue'): ?>
                            <div class="mt-2 p-2 rounded" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);">
                                <small style="color:rgba(255,255,255,0.7);"><i class="fas fa-exclamation-circle me-1 text-danger"></i>This is an overdue payment. Deleting it will remove all records and the late fee.</small>
                            </div>
                            <?php elseif ($p['payment_type'] === 'monthly_rent'): ?>
                            <div class="mt-2 p-2 rounded" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);">
                                <small style="color:rgba(255,255,255,0.7);"><i class="fas fa-info-circle me-1 text-primary"></i>This is a monthly rent bill. It will be auto-regenerated on the next system run.</small>
                            </div>
                            <?php endif; ?>
                            <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                This action cannot be undone. All related records (receipts, history, notifications) will also be removed.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="<?= url('/manager/payment/delete/' . $p['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>
