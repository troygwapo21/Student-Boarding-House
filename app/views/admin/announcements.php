<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Announcements</h1>
    <a href="<?= url('/admin/announcement/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Announcement</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Title</th><th>Type</th><th>Priority</th><th>Published</th><th>Created</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td class="fw-bold"><?= e($a['title']) ?></td>
                                <td><?= statusBadge($a['type']) ?></td>
                                <td><?= statusBadge($a['priority']) ?></td>
                                <td><?php if ($a['is_published']): ?><i class="fas fa-check-circle text-success"></i> Yes<?php else: ?><i class="fas fa-times-circle text-muted"></i> No<?php endif; ?></td>
                                <td class="text-muted small"><?= timeAgo($a['published_at'] ?? $a['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/announcement/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="<?= url('/admin/announcement/delete/' . $a['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete announcement?"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No announcements found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
