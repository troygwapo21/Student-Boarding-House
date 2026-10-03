<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Maintenance Requests</h4>
    <?php if (!empty($isTenant)): ?>
    <a href="<?= url('/student/maintenance/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Request
    </a>
    <?php else: ?>
    <span class="text-muted small"><i class="fas fa-lock me-1"></i> Tenants only</span>
    <?php endif; ?>
</div>

<?php if (empty($isTenant)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fas fa-info-circle"></i>
    <span>Only tenants with an active reservation can submit maintenance requests.</span>
</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><strong><?= e($req['request_code']) ?></strong></td>
                        <td>
                            <a href="<?= url('/student/maintenance/detail?id=' . $req['id']) ?>" class="text-decoration-none fw-semibold">
                                <?= e($req['title']) ?>
                            </a>
                        </td>
                        <td><span class="text-capitalize"><?= e(str_replace('_', ' ', $req['category'])) ?></span></td>
                        <td><?= statusBadge($req['priority']) ?></td>
                        <td><?= !empty($req['room_number']) ? e($req['room_number']) : '-' ?></td>
                        <td><?= statusBadge($req['status']) ?></td>
                        <td><?= formatDate($req['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-tools fa-2x mb-2 d-block"></i>
                            No maintenance requests found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
