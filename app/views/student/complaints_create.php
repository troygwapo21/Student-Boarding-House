<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Submit Complaint</h4>
    <a href="<?= url('/student/complaints') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <form method="POST" action="<?= url('/student/complaints/create') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="subject" placeholder="Brief subject of your complaint" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="6" placeholder="Provide detailed description of your complaint..." required></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option value="noise">Noise</option>
                            <option value="cleanliness">Cleanliness</option>
                            <option value="security">Security</option>
                            <option value="roommate">Roommate</option>
                            <option value="management">Management</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Severity <span class="text-danger">*</span></label>
                        <select class="form-select" name="severity" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/student/complaints') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Submit Complaint</button>
                </div>
            </form>
        </div>
    </div>
</div>
