<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Complaints</h4>
    <?php if (!empty($isTenant)): ?>
    <a href="<?= url('/student/complaints/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Complaint
    </a>
    <?php else: ?>
    <span class="text-muted small"><i class="fas fa-lock me-1"></i> Tenants only</span>
    <?php endif; ?>
</div>

<?php if (empty($isTenant)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fas fa-info-circle"></i>
    <span>Only tenants with an active reservation can submit complaints.</span>
</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Complaint Code</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $cmp): ?>
                    <tr>
                        <td><strong><?= e($cmp['complaint_code']) ?></strong></td>
                        <td>
                            <a href="<?= url('/student/complaint/detail?id=' . $cmp['id']) ?>" class="text-decoration-none fw-semibold">
                                <?= e($cmp['subject']) ?>
                            </a>
                        </td>
                        <td><span class="text-capitalize"><?= e(str_replace('_', ' ', $cmp['category'])) ?></span></td>
                        <td><?= statusBadge($cmp['severity']) ?></td>
                        <td><?= statusBadge($cmp['status']) ?></td>
                        <td><?= formatDate($cmp['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                            No complaints found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
