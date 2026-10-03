<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Maintenance</h1>
    <button type="button" class="btn btn-outline-primary" data-mark-all-read="maintenance">
        <i class="fas fa-check-double me-2"></i>Mark All Read
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/maintenance') ?>" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= ($status ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= ($status ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
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
                    <tr><th>Code</th><th>Student</th><th>Title</th><th>Category</th><th>Priority</th><th>Room</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($r['request_code']) ?></td>
                                <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                <td><?= e(truncate($r['title'], 40)) ?></td>
                                <td><span class="badge bg-light text-dark"><?= e(ucwords(str_replace('_', ' ', $r['category'] ?? ''))) ?></span></td>
                                <td><?= statusBadge($r['priority'] ?? 'medium') ?></td>
                                <td><?= e($r['room_number'] ?? 'N/A') ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/maintenance/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="<?= url('/admin/maintenance/delete/' . $r['id']) ?>" style="display:inline" onsubmit="return confirm('Delete this maintenance request?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No maintenance requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
