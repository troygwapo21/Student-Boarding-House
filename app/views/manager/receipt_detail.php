<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Receipt Details</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="window.print();">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <a href="<?= url('/manager/receipts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" id="receiptArea">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="text-center mb-4" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                    <h4 class="fw-bold mb-1"><?= e(SITE_NAME) ?></h4>
                    <p class="text-muted mb-1">Official Payment Receipt</p>
                    <p class="text-muted small mb-0"><?= e(SITE_EMAIL) ?></p>
                </div>

                <!-- Receipt Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="text-muted small text-uppercase mb-1">Receipt Number</h6>
                        <p class="fw-bold mb-0"><?= e($receipt['receipt_number']) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <h6 class="text-muted small text-uppercase mb-1">Issued Date</h6>
                        <p class="fw-bold mb-0"><?= formatDate($receipt['issued_date'] ?? $receipt['created_at']) ?></p>
                    </div>
                </div>

                <!-- Student Info -->
                <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px;">
                    <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1 small"><strong>Name:</strong> <?= e($receipt['first_name'] . ' ' . $receipt['last_name']) ?></p>
                            <p class="mb-1 small"><strong>ID Number:</strong> <?= e($receipt['student_id_number'] ?? 'N/A') ?></p>
                            <p class="mb-1 small"><strong>Email:</strong> <?= e($receipt['student_email'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 small"><strong>Phone:</strong> <?= e($receipt['phone'] ?? 'N/A') ?></p>
                            <p class="mb-1 small"><strong>School:</strong> <?= e($receipt['school_university'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <?php
                $payType = (string)($receipt['payment_type'] ?? '');
                $payTypeLabel = ucwords(str_replace('_', ' ', $payType));
                $payTypeBadge = match($payType) {
                    'monthly_rent' => 'bg-primary',
                    'advance_payment' => 'bg-success',
                    'reservation_fee' => 'bg-info',
                    'electric_bill' => 'bg-warning text-dark',
                    'water_bill' => 'bg-info',
                    default => 'bg-secondary',
                };
                $baseAmount = (float)($receipt['amount'] ?? 0);
                $lateFee = (float)($receipt['late_fee'] ?? 0);
                $totalDue = $baseAmount + $lateFee;
                $amountPaid = (float)($receipt['amount_paid'] ?? 0);
                $remaining = max(0, $totalDue - $amountPaid);
                $monthlyRent = (float)($receipt['rent_amount'] ?? $receipt['monthly_rent'] ?? 0);
                $duration = (int)($receipt['expected_duration'] ?? 0);
                $isRent = $payType === 'monthly_rent';
                $isAdvance = $payType === 'advance_payment';
                $baseLabel = $isRent
                    ? ($duration > 0 ? "Rent ({$duration} month" . ($duration !== 1 ? 's' : '') . ")" : 'Rent')
                    : ($isAdvance ? 'Advance Payment' : 'Base Amount');
                ?>
                <div class="mb-4">
                    <h6 class="fw-bold mb-2"><i class="fas fa-money-bill me-2"></i>Payment Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Payment Code</td>
                                    <td class="fw-semibold"><?= e($receipt['payment_code']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Type</td>
                                    <td class="fw-semibold text-capitalize">
                                        <span class="badge <?= $payTypeBadge ?>"><?= e($payTypeLabel) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Status</td>
                                    <td>
                                        <?php
                                        $statusBadge = match($receipt['payment_status'] ?? '') {
                                            'paid' => 'bg-success',
                                            'partially_paid' => 'bg-warning text-dark',
                                            'pending' => 'bg-info text-dark',
                                            'overdue' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= $statusBadge ?> text-capitalize"><?= str_replace('_', ' ', $receipt['payment_status'] ?? '') ?></span>
                                    </td>
                                </tr>
                                <?php if (!empty($receipt['billing_period'])): ?>
                                <tr>
                                    <td class="text-muted">Billing Period</td>
                                    <td class="fw-semibold"><?= e($receipt['billing_period']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($receipt['due_date'])): ?>
                                <tr>
                                    <td class="text-muted">Due Date</td>
                                    <td class="fw-semibold"><?= formatDate($receipt['due_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($receipt['move_in_date'])): ?>
                                <tr>
                                    <td class="text-muted">Move-in Date</td>
                                    <td class="fw-semibold"><?= formatDate($receipt['move_in_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($duration > 0): ?>
                                <tr>
                                    <td class="text-muted">Rental Duration</td>
                                    <td class="fw-semibold"><?= (int)$duration ?> month<?= (int)$duration !== 1 ? 's' : '' ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($isRent && $monthlyRent > 0): ?>
                                <tr>
                                    <td class="text-muted">Monthly Rent</td>
                                    <td class="fw-semibold"><?= formatCurrency($monthlyRent) ?>/month</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Room</td>
                                    <td class="fw-semibold"><?= e(($receipt['room_name'] ?? 'N/A') . ' ' . ($receipt['room_number'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Method</td>
                                    <td class="fw-semibold"><?= $receipt['payment_method'] ? e(ucwords(str_replace('_', ' ', $receipt['payment_method']))) : '<span class="text-muted">—</span>' ?></td>
                                </tr>
                                <?php if (!empty($receipt['reference_number'])): ?>
                                <tr>
                                    <td class="text-muted">Reference Number</td>
                                    <td class="fw-semibold"><?= e($receipt['reference_number']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Date Paid</td>
                                    <td class="fw-semibold"><?= formatDateTime($receipt['paid_at'] ?? '') ?></td>
                                </tr>
                                <?php if (!empty($receipt['notes'])): ?>
                                <tr>
                                    <td class="text-muted">Notes</td>
                                    <td class="fw-semibold"><?= e($receipt['notes']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <h6 class="fw-bold mb-3"><i class="fas fa-calculator me-2"></i>Amount Breakdown</h6>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted"><?= e($baseLabel) ?></td>
                                <td class="text-end fw-semibold"><?= formatCurrency($baseAmount) ?></td>
                            </tr>
                            <?php if ($lateFee > 0): ?>
                            <tr>
                                <td class="text-muted">Late Fee / Penalty</td>
                                <td class="text-end fw-semibold text-danger">+ <?= formatCurrency($lateFee) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr style="border-top: 2px solid #dee2e6;">
                                <td class="fw-bold">Total Due</td>
                                <td class="text-end fw-bold"><?= formatCurrency($totalDue) ?></td>
                            </tr>
                            <?php if ($amountPaid > 0): ?>
                            <tr>
                                <td class="text-muted">Amount Paid</td>
                                <td class="text-end fw-semibold text-success"><?= formatCurrency($amountPaid) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($remaining > 0): ?>
                            <tr>
                                <td class="text-muted">Remaining Balance</td>
                                <td class="text-end fw-semibold text-warning"><?= formatCurrency($remaining) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="text-end p-3" style="background: #f0fdf4; border-radius: 10px; border: 1px solid #bbf7d0;">
                    <h5 class="mb-0">Total Paid: <span class="text-success fw-bold"><?= formatCurrency((float)($receipt['total'] ?? $receipt['amount'] ?? 0)) ?></span></h5>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4 pt-3" style="border-top: 2px solid #e2e8f0;">
                    <p class="text-muted small mb-0">This is a system-generated receipt. No signature required.</p>
                    <p class="text-muted small mb-0">For inquiries, contact us at <?= e(SITE_EMAIL) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .d-flex.justify-content-between, .btn, .sidebar, .topbar, nav { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .content-wrapper { padding: 0 !important; }
        #receiptArea { border: none !important; box-shadow: none !important; margin: 0 !important; }
        .card-body { padding: 0 !important; }
    }
</style>
