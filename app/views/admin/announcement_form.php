<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= isset($announcement) ? 'Edit Announcement' : 'Create Announcement' ?></h1>
    <a href="<?= url('/admin/announcements') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url(isset($announcement) ? '/admin/announcement/edit/' . $announcement['id'] : '/admin/announcement/create') ?>">
            <?= csrf_field() ?>
            <?php if (!empty($announcement)): ?>
                <input type="hidden" name="id" value="<?= $announcement['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= e($announcement['title'] ?? '') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="6" required><?= e($announcement['content'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="general" <?= ($announcement['type'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                        <option value="important" <?= ($announcement['type'] ?? '') === 'important' ? 'selected' : '' ?>>Important</option>
                        <option value="urgent" <?= ($announcement['type'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        <option value="maintenance" <?= ($announcement['type'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="event" <?= ($announcement['type'] ?? '') === 'event' ? 'selected' : '' ?>>Event</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low" <?= ($announcement['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($announcement['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($announcement['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= ($announcement['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="isPublished" <?= ($announcement['is_published'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isPublished">Publish Immediately</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= isset($announcement) ? 'Update' : 'Create' ?> Announcement</button>
                <a href="<?= url('/admin/announcements') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
