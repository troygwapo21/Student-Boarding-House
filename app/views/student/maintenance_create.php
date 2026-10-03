<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Submit Maintenance Request</h4>
    <a href="<?= url('/student/maintenance') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <form method="POST" action="<?= url('/student/maintenance/create') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" placeholder="Brief description of the issue" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="5" placeholder="Provide detailed description of the maintenance issue..." required></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option value="plumbing">Plumbing</option>
                            <option value="electrical">Electrical</option>
                            <option value="furniture">Furniture</option>
                            <option value="appliance">Appliance</option>
                            <option value="structural">Structural</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Room</label>
                    <?php if (!empty($activeReservation)): ?>
                        <input type="hidden" name="room_id" value="<?= $activeReservation['rid'] ?>">
                        <input type="text" class="form-control" value="<?= e($activeReservation['room_name'] . ' - Room ' . $activeReservation['room_number']) ?>" disabled>
                        <small class="text-muted">Room from your active reservation</small>
                    <?php else: ?>
                        <input type="text" class="form-control" value="No active reservation" disabled>
                        <input type="hidden" name="room_id" value="">
                        <small class="text-muted">You don't have an active reservation</small>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/student/maintenance') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
