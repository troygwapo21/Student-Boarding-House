<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Notifications</h4>
    <div class="d-flex gap-2">
        <form method="POST" action="<?= url('/student/notifications/read') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
        </form>
        <form method="POST" action="<?= url('/student/notifications/clear') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="This will permanently delete all your notifications.">
                <i class="fas fa-trash-alt me-1"></i> Clear All
            </button>
        </form>
    </div>
</div>

<?php
function getNotifUrgencyStyle(string $title): array {
    $t = strtolower($title);
    if (strpos($t, 'overdue') !== false) {
        return ['bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca', 'icon_bg' => '#fee2e2', 'icon_color' => '#dc2626', 'icon' => 'exclamation-circle', 'badge' => 'bg-danger', 'badge_text' => 'Overdue'];
    }
    if (strpos($t, 'due today') !== false) {
        return ['bg' => '#fff7ed', 'color' => '#ea580c', 'border' => '#fed7aa', 'icon_bg' => '#ffedd5', 'icon_color' => '#ea580c', 'icon' => 'clock', 'badge' => 'bg-warning text-dark', 'badge_text' => 'Due Today'];
    }
    if (strpos($t, 'due tomorrow') !== false) {
        return ['bg' => '#fefce8', 'color' => '#ca8a04', 'border' => '#fef08a', 'icon_bg' => '#fef9c3', 'icon_color' => '#ca8a04', 'icon' => 'clock', 'badge' => 'bg-warning text-dark', 'badge_text' => 'Due Tomorrow'];
    }
    if (strpos($t, 'due in 3') !== false || strpos($t, 'due in 1') !== false) {
        return ['bg' => '#eff6ff', 'color' => '#2563eb', 'border' => '#bfdbfe', 'icon_bg' => '#dbeafe', 'icon_color' => '#2563eb', 'icon' => 'info-circle', 'badge' => 'bg-info', 'badge_text' => 'Upcoming'];
    }
    if (strpos($t, 'bill') !== false && strpos($t, 'due') !== false) {
        return ['bg' => '#fefce8', 'color' => '#a16207', 'border' => '#fef08a', 'icon_bg' => '#fef9c3', 'icon_color' => '#a16207', 'icon' => 'money-bill', 'badge' => 'bg-warning text-dark', 'badge_text' => 'New Bill'];
    }
    return ['bg' => '', 'color' => '', 'border' => '', 'icon_bg' => '', 'icon_color' => '', 'icon' => '', 'badge' => '', 'badge_text' => ''];
}
?>

<div class="content-card">
    <?php if (!empty($notifications)): ?>
    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $notif): ?>
        <?php $urgency = getNotifUrgencyStyle($notif['title'] ?? ''); ?>
        <div class="list-group-item py-3 <?= empty($notif['is_read']) ? 'bg-light' : '' ?>"
             <?php if (!empty($urgency['border']) && empty($notif['is_read'])): ?>
             style="border-left: 3px solid <?= $urgency['color'] ?>; background: <?= $urgency['bg'] ?>;"
             <?php endif; ?>>
            <div class="d-flex align-items-start gap-3">
                <?php if (!empty($urgency['icon']) && empty($notif['is_read'])): ?>
                <div style="width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; min-width: 42px;
                    background: <?= $urgency['icon_bg'] ?>; color: <?= $urgency['icon_color'] ?>;">
                    <i class="fas fa-<?= $urgency['icon'] ?>"></i>
                </div>
                <?php else: ?>
                <div style="width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; min-width: 42px;
                    background: <?= empty($notif['is_read']) ? '#e0e7ff' : '#f1f5f9' ?>;
                    color: <?= empty($notif['is_read']) ? '#4f46e5' : '#94a3b8' ?>;">
                    <i class="fas fa-<?= ($notif['type'] ?? 'info') === 'payment' ? 'money-bill' : (($notif['type'] ?? '') === 'reservation' ? 'calendar-check' : (($notif['type'] ?? '') === 'announcement' ? 'bullhorn' : (($notif['type'] ?? '') === 'maintenance' ? 'tools' : (($notif['type'] ?? '') === 'complaint' ? 'exclamation-triangle' : 'bell')))) ?>"></i>
                </div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="mb-1 fw-semibold <?= empty($notif['is_read']) ? '' : 'text-muted' ?>" style="font-size: 14px;">
                            <?php if (empty($notif['is_read'])): ?>
                                <span class="me-1" style="width: 6px; height: 6px; border-radius: 50%; background: <?= !empty($urgency['color']) ? $urgency['color'] : '#4f46e5' ?>; display: inline-block;"></span>
                            <?php endif; ?>
                            <?= e($notif['title'] ?? '') ?>
                            <?php if (!empty($urgency['badge']) && empty($notif['is_read'])): ?>
                                <span class="badge <?= $urgency['badge'] ?> ms-2" style="font-size: 10px; vertical-align: middle;"><?= $urgency['badge_text'] ?></span>
                            <?php endif; ?>
                        </h6>
                        <small class="text-muted ms-2" style="white-space: nowrap;"><?= timeAgo($notif['created_at']) ?></small>
                    </div>
                    <p class="mb-0 text-muted" style="font-size: 13px;"><?= e($notif['message'] ?? '') ?></p>
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
