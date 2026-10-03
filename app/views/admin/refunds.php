<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Refund Requests</h1>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/refunds') ?>" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Code</th><th>Student</th><th>Room</th><th>Type</th><th>Amount</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($refunds)): ?>
                        <?php foreach ($refunds as $rf): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($rf['refund_code']) ?></td>
                                <td><?= e($rf['first_name'] . ' ' . $rf['last_name']) ?></td>
                                <td><?= e(($rf['room_name'] ?? '') . ' ' . ($rf['room_number'] ?? '')) ?></td>
                                <td><?= refundTypeBadge($rf['refund_type'] ?? 'monthly') ?></td>
                                <td><?= formatCurrency((float)$rf['amount']) ?></td>
                                <td><?= statusBadge($rf['status']) ?></td>
                                <td class="text-muted small"><?= timeAgo($rf['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/refund/' . $rf['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="<?= url('/admin/refund/delete/' . $rf['id']) ?>" style="display:inline" onsubmit="return confirm('Delete this refund request?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $rf['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No refund requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
