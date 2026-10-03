<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Refund Requests</h4>
    <?php if (!empty($isTenant)): ?>
    <a href="<?= url('/student/refund-request/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Refund Request
    </a>
    <?php else: ?>
    <span class="text-muted small"><i class="fas fa-lock me-1"></i> Tenants only</span>
    <?php endif; ?>
</div>

<?php if (empty($isTenant)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fas fa-info-circle"></i>
    <span>Only tenants with an active reservation can request a refund.</span>
</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Refund Code</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($refunds)): ?>
                    <?php foreach ($refunds as $rf): ?>
                    <tr>
                        <td><strong><?= e($rf['refund_code']) ?></strong></td>
                        <td><?= e(($rf['room_name'] ?? '') . ' ' . ($rf['room_number'] ?? '')) ?></td>
                        <td><?= refundTypeBadge($rf['refund_type'] ?? 'monthly') ?></td>
                        <td><?= formatCurrency((float)$rf['amount']) ?></td>
                        <td><?= statusBadge($rf['status']) ?></td>
                        <td><?= formatDate($rf['created_at']) ?></td>
                        <td class="text-end">
                            <?php if ($rf['status'] === 'pending'): ?>
                            <form method="POST" action="<?= url('/student/refund-request/cancel/' . $rf['id']) ?>" style="display:inline" onsubmit="return confirm('Cancel this refund request?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $rf['id'] ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>Cancel</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                            No refund requests found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
