<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Notifications</h1>
    <div class="d-flex gap-2">
        <form method="POST" action="<?= url('/manager/notifications/read') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
        </form>
        <form method="POST" action="<?= url('/manager/notifications/clear') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="This will permanently delete all your notifications.">
                <i class="fas fa-trash-alt me-1"></i> Clear All
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (!empty($notifications)): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notif): ?>
            <?php
            $nType = $notif['type'] ?? 'system';
            $nColor = $nType === 'payment' ? '#16a34a' : ($nType === 'reservation' ? '#7c3aed' : ($nType === 'announcement' ? '#d97706' : ($nType === 'maintenance' ? '#2563eb' : ($nType === 'complaint' ? '#dc2626' : '#64748b'))));
            $nBg = $nType === 'payment' ? '#dcfce7' : ($nType === 'reservation' ? '#ede9fe' : ($nType === 'announcement' ? '#fef3c7' : ($nType === 'maintenance' ? '#dbeafe' : ($nType === 'complaint' ? '#fee2e2' : '#f1f5f9'))));
            $nIcon = $nType === 'payment' ? 'money-bill' : ($nType === 'reservation' ? 'calendar-check' : ($nType === 'announcement' ? 'bullhorn' : ($nType === 'maintenance' ? 'tools' : ($nType === 'complaint' ? 'exclamation-triangle' : 'bell'))));
            ?>
            <div class="list-group-item px-4 py-3 <?= empty($notif['is_read']) ? 'bg-light' : '' ?>">
                <div class="d-flex align-items-start gap-3">
                    <div style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;min-width:38px;background:<?= $nBg ?>;color:<?= $nColor ?>;">
                        <i class="fas fa-<?= $nIcon ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 small fw-semibold <?= empty($notif['is_read']) ? '' : 'text-muted' ?>" style="font-size:13px;">
                                <?php if (empty($notif['is_read'])): ?><span class="me-1" style="width:6px;height:6px;border-radius:50%;background:<?= $nColor ?>;display:inline-block;"></span><?php endif; ?>
                                <?= e($notif['title'] ?? '') ?>
                            </h6>
                            <small class="text-muted ms-2" style="font-size:11px;white-space:nowrap;"><i class="fas fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?></small>
                        </div>
                        <p class="mb-0 text-muted" style="font-size:12px;"><?= e($notif['message'] ?? '') ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No Notifications</h5>
            <p class="text-muted">You're all caught up!</p>
        </div>
        <?php endif; ?>
    </div>
</div>
