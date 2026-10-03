<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Complaints</h1>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/complaints') ?>" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" <?= ($status ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="under_review" <?= ($status ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
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
                    <tr><th>Code</th><th>Student</th><th>Subject</th><th>Category</th><th>Severity</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($complaints)): ?>
                        <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($c['complaint_code']) ?></td>
                                <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
                                <td><?= e(truncate($c['subject'], 40)) ?></td>
                                <td><span class="badge bg-light text-dark"><?= e(ucwords(str_replace('_', ' ', $c['category'] ?? ''))) ?></span></td>
                                <td><?= statusBadge($c['severity'] ?? 'medium') ?></td>
                                <td><?= statusBadge($c['status']) ?></td>
                                <td class="text-muted small"><?= timeAgo($c['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/complaint/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="<?= url('/admin/complaint/delete/' . $c['id']) ?>" style="display:inline" onsubmit="return confirm('Delete this complaint?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No complaints found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
