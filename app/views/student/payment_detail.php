<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Payment Details</h4>
    <a href="<?= url('/student/payments') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <!-- Header -->
            <div class="text-center mb-4" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                <h4 class="fw-bold mb-1"><?= e(SITE_NAME) ?></h4>
                <p class="text-muted mb-1">Payment Details</p>
                <p class="text-muted small"><?= e(SITE_EMAIL) ?></p>
            </div>

            <!-- Payment Info -->
            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="text-muted small text-uppercase mb-1">Payment Code</h6>
                    <p class="fw-bold mb-0"><?= e($payment['payment_code']) ?></p>
                </div>
                <div class="col-6 text-end">
                    <h6 class="text-muted small text-uppercase mb-1">Status</h6>
                    <p class="mb-0"><?= paymentStatusCell($payment) ?></p>
                </div>
            </div>

            <!-- Student Info -->
            <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px;">
                <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <p class="mb-1 small"><strong>Name:</strong> <?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></p>
                        <p class="mb-1 small"><strong>Email:</strong> <?= e($_SESSION['user_email'] ?? '') ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="mb-1 small"><strong>Phone:</strong> <?= e($student['phone'] ?? 'N/A') ?></p>
                        <p class="mb-1 small"><strong>School:</strong> <?= e($student['school_university'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <!-- Payment Details Table -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-money-bill me-2"></i>Payment Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 200px;">Payment Type</td>
                                <td class="fw-semibold text-capitalize"><?= e(str_replace('_', ' ', $payment['payment_type'] ?? '')) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Amount</td>
                                <td class="fw-bold text-success"><?= formatCurrency((float)$payment['amount']) ?></td>
                            </tr>
                            <?php if ((float)($payment['late_fee'] ?? 0) > 0): ?>
                            <tr>
                                <td class="text-muted">Late Fee</td>
                                <td class="fw-bold text-danger">+<?= formatCurrency((float)$payment['late_fee']) ?></td>
                            </tr>
                            <?php $totalDue = (float)$payment['amount'] + (float)$payment['late_fee']; ?>
                            <tr>
                                <td class="text-muted">Total Due</td>
                                <td class="fw-bold"><?= formatCurrency($totalDue) ?></td>
                            </tr>
                            <?php else: ?>
                            <?php $totalDue = (float)$payment['amount']; ?>
                            <?php endif; ?>
                            <?php $amountPaid = (float)($payment['amount_paid'] ?? 0); ?>
                            <?php if ($amountPaid > 0): ?>
                            <tr>
                                <td class="text-muted">Amount Paid</td>
                                <td class="fw-semibold text-success"><?= formatCurrency($amountPaid) ?></td>
                            </tr>
                            <?php if ($amountPaid < $totalDue): ?>
                            <tr>
                                <td class="text-muted">Remaining Balance</td>
                                <td class="fw-bold text-danger"><?= formatCurrency($totalDue - $amountPaid) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endif; ?>
                            <tr>
                                <td class="text-muted">Payment Method</td>
                                <td class="fw-semibold"><?php if (!empty($payment['payment_method']) && (float)($payment['amount_paid'] ?? 0) > 0): ?><?= e(ucwords(str_replace('_', ' ', $payment['payment_method']))) ?><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                            </tr>
                            <?php if (!empty($payment['reservation_code'])): ?>
                            <tr>
                                <td class="text-muted">Reservation Code</td>
                                <td class="fw-semibold"><?= e($payment['reservation_code']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($payment['room_number'])): ?>
                            <tr>
                                <td class="text-muted">Room</td>
                                <td class="fw-semibold"><?= e(($payment['room_name'] ?? '') . ' - ' . $payment['room_number']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($payment['reference_number'])): ?>
                            <tr>
                                <td class="text-muted">Reference Number</td>
                                <td class="fw-semibold"><?= e($payment['reference_number']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($payment['move_in_date'])): ?>
                            <tr>
                                <td class="text-muted">Move-in Date</td>
                                <td class="fw-semibold"><?= formatDate($payment['move_in_date']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="text-muted">Due Date</td>
                                <td class="fw-semibold"><?= !empty($payment['due_date']) ? formatDate($payment['due_date']) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date Submitted</td>
                                <td class="fw-semibold"><?= formatDateTime($payment['created_at'] ?? '') ?></td>
                            </tr>
                            <?php if (!empty($payment['paid_at'])): ?>
                            <tr>
                                <td class="text-muted">Date Paid</td>
                                <td class="fw-semibold text-success"><i class="fas fa-check-circle me-1"></i><?= formatDateTime($payment['paid_at']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($payment['notes'])): ?>
                            <tr>
                                <td class="text-muted">Notes</td>
                                <td class="fw-semibold"><?= e($payment['notes']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($payment['proof_of_payment'])): ?>
            <!-- Proof of Payment -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-image me-2"></i>Proof of Payment</h6>
                <div class="p-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <?php
                    $proofPath = UPLOAD_PATH . $payment['proof_of_payment'];
                    $ext = strtolower(pathinfo($payment['proof_of_payment'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])):
                    ?>
                        <img src="<?= SITE_URL . '/uploads/' . $payment['proof_of_payment'] ?>" alt="Proof of Payment" class="img-fluid rounded" style="max-height: 400px;">
                    <?php elseif ($ext === 'pdf'): ?>
                        <a href="<?= SITE_URL . '/uploads/' . $payment['proof_of_payment'] ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-file-pdf me-1"></i> View PDF Receipt
                        </a>
                    <?php else: ?>
                        <p class="text-muted mb-0">File uploaded: <?= e($payment['proof_of_payment']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($payment['verified_by'])): ?>
            <!-- Verification Info -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-stamp me-2"></i>Verification</h6>
                <div class="p-3" style="background: #f0fdf4; border-radius: 10px; border: 1px solid #bbf7d0;">
                    <p class="mb-1 small"><strong>Verified by:</strong> Admin/Manager</p>
                    <p class="mb-0 small"><strong>Verified at:</strong> <?= formatDateTime($payment['verified_at']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
