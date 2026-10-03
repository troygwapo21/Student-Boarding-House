<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Refund Request Details</h1>
    <a href="<?= url('/manager/refunds') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Refund: <?= e($refund['refund_code']) ?></h6>
                    <?= statusBadge($refund['status']) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><small class="text-muted">Refund Type</small><div class="fw-bold"><?= refundTypeBadge($refund['refund_type'] ?? 'monthly') ?></div></div>
                    <div class="col-md-6"><small class="text-muted">Requested Amount</small><div class="fw-bold"><?= formatCurrency((float)$refund['amount']) ?></div></div>
                    <div class="col-md-6"><small class="text-muted">Room</small><div class="fw-bold"><?= e(($refund['room_name'] ?? 'N/A') . ' ' . ($refund['room_number'] ?? '')) ?></div></div>
                    <div class="col-md-6"><small class="text-muted">GCash Number (send refund here)</small><div class="fw-bold"><?= !empty($refund['gcash_number']) ? '<i class="fas fa-mobile-screen text-success me-1"></i>' . e($refund['gcash_number']) : '<span class="text-muted">N/A</span>' ?></div></div>
                </div>
                <div class="mb-3"><small class="text-muted">Reason</small><div class="mt-1"><?= nl2br(e($refund['reason'] ?? '')) ?></div></div>
                <?php if (!empty($refund['admin_notes'])): ?>
                    <div class="bg-light rounded p-3">
                        <small class="text-muted fw-bold">Admin Notes</small>
                        <div class="mt-1"><?= nl2br(e($refund['admin_notes'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($refund['status'] === 'approved'): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Automatic Deduction</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($deductedPayments)): ?>
                    <div class="alert alert-success py-2"><i class="fas fa-check-circle me-2"></i><?= count($deductedPayments) ?> matching paid payment(s) were automatically marked as refunded (deducted).</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Payment Code</th><th>Type</th><th>Amount</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deductedPayments as $dp): ?>
                                    <tr>
                                        <td class="fw-bold small"><?= e($dp['payment_code']) ?></td>
                                        <td><?= e(str_replace('_', ' ', ucwords($dp['payment_type'], '_'))) ?></td>
                                        <td><?= formatCurrency((float)$dp['amount_paid']) ?></td>
                                        <td><?= statusBadge($dp['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning py-2 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>No paid payment was found to deduct. The refund was approved but no matching paid payment could be automatically deducted.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h6></div>
            <div class="card-body">
                <div class="mb-2"><small class="text-muted">Student:</small><div class="fw-bold"><?= e($refund['first_name'] . ' ' . $refund['last_name']) ?></div></div>
                <div class="mb-2"><small class="text-muted">Student ID:</small><div class="fw-bold"><?= e($refund['student_id_number'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Phone:</small><div class="fw-bold"><?= e($refund['student_phone'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Reservation:</small><div class="fw-bold"><?= e($refund['reservation_code'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Submitted:</small><div class="fw-bold"><?= formatDateTime($refund['created_at']) ?></div></div>
                <?php if (!empty($refund['reviewed_at'])): ?>
                    <div class="mb-2"><small class="text-muted">Reviewed:</small><div class="fw-bold"><?= formatDateTime($refund['reviewed_at']) ?></div></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($refund['status'] === 'pending'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-edit me-2"></i>Review Request</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/refund/update/' . $refund['id']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="approved">
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Enter notes..."><?= e($refund['admin_notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-2" data-confirm="Approve this refund request?"><i class="fas fa-check me-2"></i>Approve</button>
                </form>
                <form method="POST" action="<?= url('/manager/refund/update/' . $refund['id']) ?>" class="mt-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="rejected">
                    <div class="mb-3">
                        <label class="form-label">Rejection Note</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100" data-confirm="Reject this refund request?"><i class="fas fa-times me-2"></i>Reject</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
