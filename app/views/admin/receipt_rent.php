<?php
$p = $payments[0] ?? [];
$statusBadge = match($status) {
    'paid' => 'bg-success',
    'partially_paid' => 'bg-warning text-dark',
    'pending' => 'bg-info text-dark',
    'overdue' => 'bg-danger',
    default => 'bg-secondary',
};
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Monthly Rent Receipt</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="window.print();">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <a href="<?= url('/admin/receipts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" id="rentReceiptArea">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="text-center mb-4" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                    <h4 class="fw-bold mb-1"><?= e(SITE_NAME) ?></h4>
                    <p class="text-muted mb-1">Monthly Rent Receipt</p>
                    <p class="text-muted small mb-0"><?= e(SITE_EMAIL) ?></p>
                    <h6 class="fw-bold mt-3 mb-0"><?= e($periodLabel) ?></h6>
                </div>

                <!-- Receipt Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="text-muted small text-uppercase mb-1">Billing Period</h6>
                        <p class="fw-bold mb-0"><?= e($period) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <h6 class="text-muted small text-uppercase mb-1">Statement Date</h6>
                        <p class="fw-bold mb-0"><?= formatDate($p['receipt_issued_date'] ?? serverDate()) ?></p>
                    </div>
                </div>

                <!-- Student Info -->
                <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px;">
                    <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1 small"><strong>Name:</strong> <?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></p>
                            <p class="mb-1 small"><strong>ID Number:</strong> <?= e($student['student_id_number'] ?? 'N/A') ?></p>
                            <p class="mb-1 small"><strong>Email:</strong> <?= e($student['student_email'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 small"><strong>Phone:</strong> <?= e($student['phone'] ?? 'N/A') ?></p>
                            <p class="mb-1 small"><strong>School:</strong> <?= e($student['school_university'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Rent Details -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-2"><i class="fas fa-money-bill me-2"></i>Rent Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Payment Code</td>
                                    <td class="fw-semibold"><?= e($p['payment_code'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Type</td>
                                    <td class="fw-semibold"><span class="badge bg-primary text-capitalize">Monthly Rent</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Status</td>
                                    <td><span class="badge <?= $statusBadge ?> text-capitalize"><?= str_replace('_', ' ', $status) ?></span></td>
                                </tr>
                                <?php if (!empty($p['due_date'])): ?>
                                <tr>
                                    <td class="text-muted">Due Date</td>
                                    <td class="fw-semibold"><?= formatDate($p['due_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($p['move_in_date'])): ?>
                                <tr>
                                    <td class="text-muted">Move-in Date</td>
                                    <td class="fw-semibold"><?= formatDate($p['move_in_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ((int)$duration > 0): ?>
                                <tr>
                                    <td class="text-muted">Rental Duration</td>
                                    <td class="fw-semibold"><?= (int)$duration ?> month<?= (int)$duration !== 1 ? 's' : '' ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ((float)$monthlyRent > 0): ?>
                                <tr>
                                    <td class="text-muted">Monthly Rent</td>
                                    <td class="fw-semibold"><?= formatCurrency((float)$monthlyRent) ?>/month</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Room</td>
                                    <td class="fw-semibold"><?= e(($p['room_name'] ?? 'N/A') . ' ' . ($p['room_number'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Method</td>
                                    <td class="fw-semibold"><?= $p['payment_method'] ? e(ucwords(str_replace('_', ' ', $p['payment_method']))) : '<span class="text-muted">—</span>' ?></td>
                                </tr>
                                <?php if (!empty($p['reference_number'])): ?>
                                <tr>
                                    <td class="text-muted">Reference Number</td>
                                    <td class="fw-semibold"><?= e($p['reference_number']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($p['paid_at'])): ?>
                                <tr>
                                    <td class="text-muted">Date Paid</td>
                                    <td class="fw-semibold"><?= formatDateTime($p['paid_at']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($p['notes'])): ?>
                                <tr>
                                    <td class="text-muted">Notes</td>
                                    <td class="fw-semibold"><?= e($p['notes']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (count($payments) > 1): ?>
                <!-- Payment Receipts -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-2"><i class="fas fa-receipt me-2"></i>Receipts Issued</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt Number</th>
                                    <th>Payment Code</th>
                                    <th>Date Paid</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($row['receipt_number'] ?? '—') ?></td>
                                    <td><?= e($row['payment_code']) ?></td>
                                    <td><?= !empty($row['paid_at']) ? formatDateTime($row['paid_at']) : formatDate($row['receipt_issued_date'] ?? '') ?></td>
                                    <td class="text-end fw-semibold"><?= formatCurrency((float)($row['receipt_total'] ?? $row['amount'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Amount Breakdown -->
                <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <h6 class="fw-bold mb-3"><i class="fas fa-calculator me-2"></i>Amount Breakdown</h6>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted"><?php
                                $covered = (float)$monthlyRent > 0 ? round((float)$rentDue / (float)$monthlyRent, 2) : 0.0;
                                echo ((int)$duration > 0 && abs($covered - (int)$duration) < 0.01) ? 'Rent (' . (int)$duration . ' month' . ((int)$duration !== 1 ? 's' : '') . ')' : 'Rent';
                                ?></td>
                                <td class="text-end fw-semibold"><?= formatCurrency((float)$rentDue) ?></td>
                            </tr>
                            <?php if ((float)$lateFee > 0): ?>
                            <tr>
                                <td class="text-muted">Late Fee / Penalty</td>
                                <td class="text-end fw-semibold text-danger">+ <?= formatCurrency((float)$lateFee) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr style="border-top: 2px solid #dee2e6;">
                                <td class="fw-bold">Total Due</td>
                                <td class="text-end fw-bold"><?= formatCurrency((float)$totalDue) ?></td>
                            </tr>
                            <?php if ((float)$amountPaid > 0): ?>
                            <tr>
                                <td class="text-muted">Amount Paid</td>
                                <td class="text-end fw-semibold text-success"><?= formatCurrency((float)$amountPaid) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ((float)$remaining > 0): ?>
                            <tr>
                                <td class="text-muted">Remaining Balance</td>
                                <td class="text-end fw-semibold text-warning"><?= formatCurrency((float)$remaining) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4 pt-3" style="border-top: 2px solid #e2e8f0;">
                    <p class="text-muted small mb-0">This is a system-generated rent receipt. No signature required.</p>
                    <p class="text-muted small">For inquiries, contact us at <?= e(SITE_EMAIL) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .page-header, .btn, .sidebar, .topbar { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .content-wrapper { padding: 0 !important; }
        #rentReceiptArea { border: none !important; box-shadow: none !important; }
    }
</style>
