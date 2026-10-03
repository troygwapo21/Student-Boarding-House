<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Monthly Statement</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="window.print();">
            <i class="fas fa-print me-1"></i> Print Statement
        </button>
        <a href="<?= url('/student/receipts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="content-card" id="statementArea">
            <!-- Header -->
            <div class="text-center mb-4" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                <h4 class="fw-bold mb-1"><?= e(SITE_NAME) ?></h4>
                <p class="text-muted mb-1">Monthly Payment Statement</p>
                <p class="text-muted small"><?= e(SITE_EMAIL) ?></p>
                <h6 class="fw-bold mt-3 mb-0"><?= e($monthLabel) ?></h6>
            </div>

            <!-- Student Info -->
            <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 10px;">
                <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <p class="mb-1 small"><strong>Name:</strong> <?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></p>
                        <p class="mb-1 small"><strong>ID Number:</strong> <?= e($student['student_id_number'] ?? 'N/A') ?></p>
                        <p class="mb-1 small"><strong>Email:</strong> <?= e($_SESSION['user_email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="mb-1 small"><strong>Phone:</strong> <?= e($student['phone'] ?? 'N/A') ?></p>
                        <p class="mb-1 small"><strong>School:</strong> <?= e($student['school_university'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <!-- Receipts for the month -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-receipt me-2"></i>Receipts - <?= e($monthLabel) ?></h6>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt Number</th>
                                <th>Payment Code</th>
                                <th>Type</th>
                                <th>Billing Period</th>
                                <th>Date Paid</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($receipt['receipt_number']) ?></td>
                                <td><?= e($receipt['payment_code']) ?></td>
                                <td class="text-capitalize"><?= e(str_replace('_', ' ', $receipt['payment_type'])) ?></td>
                                <td><?= e($receipt['billing_period'] ?? '—') ?></td>
                                <td><?= !empty($receipt['paid_at']) ? formatDateTime($receipt['paid_at']) : formatDate($receipt['issued_date']) ?></td>
                                <td class="text-end fw-semibold"><?= formatCurrency((float)($receipt['total'] ?? $receipt['amount'] ?? 0)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="border-top: 2px solid #dee2e6;">
                                <td colspan="5" class="text-end fw-bold">Month Total</td>
                                <td class="text-end fw-bold text-success"><?= formatCurrency((float)$monthTotal) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4 pt-3" style="border-top: 2px solid #e2e8f0;">
                <p class="text-muted small mb-0">This is a system-generated monthly statement. No signature required.</p>
                <p class="text-muted small">For inquiries, contact us at <?= e(SITE_EMAIL) ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .page-header, .btn, .sidebar, .topbar { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .content-wrapper { padding: 0 !important; }
        #statementArea { border: none !important; box-shadow: none !important; }
    }
</style>
