<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Activity Logs</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>User</th><th>Action</th><th>Description</th><th>IP Address</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($log['email'] ?? 'System') ?></td>
                                <td><span class="badge bg-primary"><?= e($log['action']) ?></span></td>
                                <td class="text-muted"><?= e(truncate($log['description'], 60)) ?></td>
                                <td class="text-muted small"><code><?= e($log['ip_address'] ?? '') ?></code></td>
                                <td class="text-muted small">
                                    <div><?= formatDateTime($log['created_at']) ?></div>
                                    <small class="text-muted"><?= timeAgo($log['created_at']) ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No activity logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">Previous</a>
        </li>
        <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
