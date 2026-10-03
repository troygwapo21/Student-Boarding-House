<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Payments</h4>
    <a href="<?= url('/student/payment/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Submit Payment
    </a>
</div>

<?php if (!empty($monthOptions) || !empty($typeOptions)): ?>
<form method="GET" action="<?= url('/student/payments') ?>" class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <?php if (!empty($monthOptions)): ?>
    <label for="monthFilter" class="text-muted small fw-semibold mb-0"><i class="fas fa-calendar-days me-1"></i> Month:</label>
    <select name="month" id="monthFilter" class="form-select form-select-sm" style="width:auto;" aria-label="Filter payments by month"
            onchange="this.form.submit()">
        <option value="">All Months</option>
        <?php foreach (array_keys($monthOptions) as $ym): ?>
            <?php $d = DateTime::createFromFormat('!Y-m', $ym); ?>
            <option value="<?= e($ym) ?>" <?= ($month ?? '') === $ym ? 'selected' : '' ?>><?= e($d ? $d->format('F Y') : $ym) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <?php if (!empty($typeOptions)): ?>
    <label for="typeFilter" class="text-muted small fw-semibold mb-0"><i class="fas fa-tags me-1"></i> Type:</label>
    <select name="type" id="typeFilter" class="form-select form-select-sm" style="width:auto;" aria-label="Filter payments by type"
            onchange="this.form.submit()">
        <option value="">All Types</option>
        <?php foreach (array_keys($typeOptions) as $t): ?>
            <option value="<?= e($t) ?>" <?= ($type ?? '') === $t ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $t))) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <noscript><button type="submit" class="btn btn-primary btn-sm">Go</button></noscript>
</form>
<?php endif; ?>

<?php
$billingLateFee = (float)($billingLateFee ?? 100);
$billingGraceDays = max(0, (int)($billingGraceDays ?? 0));
$outstandingTotal = 0.0;
$overdueCount = 0;
$overdueAmount = 0.0;
$nextDueDate = null;
$hasUnpaid = false;
foreach (($summaryPayments ?? $payments ?? []) as $p) {
    if (in_array($p['status'], ['paid', 'cancelled', 'refunded'], true)) continue;
    $hasUnpaid = true;
    $tDue = (float)$p['amount'] + (float)($p['late_fee'] ?? 0);
    $rem = max(0.0, $tDue - (float)($p['amount_paid'] ?? 0));
    $outstandingTotal += $rem;
    if ($p['status'] === 'overdue') {
        $overdueCount++;
        $overdueAmount += $rem;
    }
    if (!empty($p['due_date']) && $p['status'] !== 'overdue') {
        if ($nextDueDate === null || $p['due_date'] < $nextDueDate) $nextDueDate = $p['due_date'];
    }
}
?>

<?php if ($hasUnpaid): ?>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="table-card p-3 d-flex align-items-center h-100" style="border-left:4px solid #ef4444;">
            <i class="fas fa-scale-balanced fa-lg me-3 text-danger"></i>
            <div>
                <div class="text-muted small">Outstanding Balance</div>
                <div class="fw-bold fs-5"><?= formatCurrency($outstandingTotal) ?></div>
                <div class="text-muted" style="font-size:.75rem;">includes late fees</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="table-card p-3 d-flex align-items-center h-100" style="border-left:4px solid <?= $overdueCount > 0 ? '#ef4444' : '#10b981' ?>;">
            <i class="fas <?= $overdueCount > 0 ? 'fa-triangle-exclamation text-danger' : 'fa-circle-check text-success' ?> fa-lg me-3"></i>
            <div>
                <div class="text-muted small">Overdue</div>
                <?php if ($overdueCount > 0): ?>
                    <div class="fw-bold fs-5 text-danger"><?= $overdueCount ?> payment<?= $overdueCount > 1 ? 's' : '' ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= formatCurrency($overdueAmount) ?> payable</div>
                <?php else: ?>
                    <div class="fw-bold fs-6 text-success">None</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="table-card p-3 d-flex align-items-center h-100" style="border-left:4px solid #f59e0b;">
            <i class="fas fa-calendar-day fa-lg me-3 text-warning"></i>
            <div>
                <div class="text-muted small">Next Due Date</div>
                <?php if ($nextDueDate !== null): ?>
                    <div class="fw-bold fs-6"><?= formatDate($nextDueDate) ?></div>
                <?php else: ?>
                    <div class="fw-bold fs-6 text-muted">—</div>
                <?php endif; ?>
                <?php if ($billingLateFee > 0 && $nextDueDate !== null): ?>
                    <div class="text-muted" style="font-size:.75rem;">+<?= formatCurrency($billingLateFee) ?> fee applies after grace</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($hasUnpaid): ?>
    <?php if ($billingLateFee > 0): ?>
        <div class="alert alert-warning d-flex align-items-start mb-3" style="border-left:4px solid #f59e0b;">
            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
            <div class="small">
                <strong>Automatic late fee policy:</strong>
                unpaid bills become overdue <?= $billingGraceDays > 0 ? "{$billingGraceDays} day" . ($billingGraceDays > 1 ? 's' : '') . " after the due date" : "immediately after the due date" ?>
                and a one-time <strong><?= formatCurrency($billingLateFee) ?></strong> penalty is added automatically.
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info d-flex align-items-start mb-3" style="border-left:4px solid #3b82f6;">
            <i class="fas fa-circle-info me-2 mt-1"></i>
            <div class="small">No late fees are currently applied to overdue payments.</div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment Code</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Late Fee</th>
                    <th>Total Due</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $pay):
                        $totalDue = (float)$pay['amount'] + (float)($pay['late_fee'] ?? 0);
                        $amountPaid = (float)($pay['amount_paid'] ?? 0);
                        $rowStyle = '';
                        if ($pay['status'] === 'overdue') $rowStyle = 'background:rgba(239,68,68,0.06);border-left:3px solid #ef4444;';
                        elseif ($pay['status'] === 'due_today') $rowStyle = 'background:rgba(245,158,11,0.06);border-left:3px solid #f59e0b;';
                        elseif ($pay['status'] === 'partially_paid') $rowStyle = 'background:rgba(249,115,22,0.06);border-left:3px solid #f97316;';
                        elseif ($pay['status'] === 'paid') $rowStyle = 'background:rgba(16,185,129,0.04);';
                    ?>
                    <tr style="<?= $rowStyle ?>">
                        <td><strong><?= e($pay['payment_code']) ?></strong></td>
                        <td><span class="text-capitalize"><?= e(str_replace('_', ' ', $pay['payment_type'])) ?></span></td>
                        <td class="fw-semibold"><?= formatCurrency((float)$pay['amount']) ?></td>
                        <td>
                            <?php if ((float)($pay['late_fee'] ?? 0) > 0): ?>
                                <span class="text-danger fw-semibold">+<?= formatCurrency((float)$pay['late_fee']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= formatCurrency($totalDue) ?></td>
                        <td>
                            <?php if ($amountPaid > 0): ?>
                                <span class="text-success fw-semibold"><?= formatCurrency($amountPaid) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $remainingBal = max(0.0, $totalDue - $amountPaid); ?>
                            <?php if ($remainingBal > 0): ?>
                                <span class="<?= $pay['status'] === 'overdue' ? 'text-danger' : 'text-warning' ?> fw-semibold"><?= formatCurrency($remainingBal) ?></span>
                            <?php else: ?>
                                <span class="text-success fw-semibold">₱0</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(ucwords(str_replace('_', ' ', $pay['payment_method'] ?? 'N/A'))) ?></td>
                        <td><?= paymentStatusCell($pay) ?></td>
                        <td>
                            <?php if (!empty($pay['due_date']) && !in_array($pay['status'], ['paid', 'cancelled'])):
                                $dueDateObj = new DateTime($pay['due_date']);
                                $todayObj = new DateTime(serverDate());
                                $diff = (int)$todayObj->diff($dueDateObj)->days;
                                $isPast = $dueDateObj < $todayObj;
                                if ($pay['status'] === 'paid'):
                            ?>
                                <span class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i><?= formatDate($pay['due_date']) ?></span>
                            <?php elseif ($isPast): ?>
                                <span class="text-danger fw-semibold"><i class="fas fa-exclamation-circle me-1"></i><?= $diff ?>d overdue</span>
                                <br><small class="text-muted"><?= formatDate($pay['due_date']) ?></small>
                            <?php elseif ($diff === 0): ?>
                                <span class="text-warning fw-semibold"><i class="fas fa-clock me-1"></i>Due today</span>
                            <?php elseif ($diff <= 3): ?>
                                <span class="text-primary fw-semibold"><i class="fas fa-hourglass-half me-1"></i><?= $diff ?>d left</span>
                                <br><small class="text-muted"><?= formatDate($pay['due_date']) ?></small>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-calendar-day me-1" style="font-size:.75rem;"></i><?= formatDate($pay['due_date']) ?></span>
                            <?php endif; ?>
                            <?php else: ?>
                                <?= !empty($pay['due_date']) ? formatDate($pay['due_date']) : '-' ?>
                            <?php endif; ?>
                            <?php if ($billingLateFee > 0 && !empty($pay['due_date']) && !in_array($pay['status'], ['overdue', 'paid', 'cancelled'], true)):
                                $penaltyFrom = (new DateTime($pay['due_date']))->modify('+' . ($billingGraceDays + 1) . ' days')->format('Y-m-d');
                            ?>
                                <br><small class="text-muted"><i class="fas fa-plus-circle me-1"></i>+<?= formatCurrency($billingLateFee) ?> late fee from <?= formatDate($penaltyFrom) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!in_array($pay['status'], ['paid', 'cancelled'])): ?>
                                <a href="<?= url('/student/payment/create?id=' . $pay['id']) ?>" class="btn btn-success btn-sm me-1" title="Pay Now">
                                    <i class="fas fa-money-bill-wave me-1"></i> Pay Now
                                </a>
                            <?php endif; ?>
                            <a href="<?= url('/student/payment/' . $pay['id']) ?>" class="btn btn-outline-primary btn-sm me-1">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <?php if (!in_array($pay['payment_type'], ['monthly_rent', 'advance_payment'], true) && !in_array($pay['status'], ['paid', 'due_today', 'overdue'], true)): ?>
                            <form method="POST" action="<?= url('/student/payment/delete/' . $pay['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete payment <?= e($pay['payment_code']) ?>? This will also remove its receipt and payment history. This action cannot be undone." title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php
                        $typeLabel = !empty($type) ? ucwords(str_replace('_', ' ', $type)) : '';
                        $monthLabel = '';
                        if (!empty($month)) {
                            $selD = DateTime::createFromFormat('!Y-m', $month);
                            $monthLabel = $selD ? $selD->format('F Y') : $month;
                        }
                    ?>
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="fas fa-money-bill fa-2x mb-2 d-block"></i>
                            <?php if (!empty($typeLabel) && !empty($monthLabel)): ?>
                                No <?= e($typeLabel) ?> payments in <?= e($monthLabel) ?>.
                            <?php elseif (!empty($typeLabel)): ?>
                                No <?= e($typeLabel) ?> payments found.
                            <?php elseif (!empty($monthLabel)): ?>
                                No payments in <?= e($monthLabel) ?>.
                            <?php else: ?>
                                No payments found.
                            <?php endif; ?>
                            <div class="mt-2">
                                <?php if (!empty($typeLabel) || !empty($monthLabel)): ?>
                                    <a href="<?= url('/student/payments') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-filter-circle-xmark me-1"></i> Clear Filters
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('/student/payment/create') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Submit Your First Payment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
