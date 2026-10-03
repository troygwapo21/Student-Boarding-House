<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Feedback</h1>
    <span class="badge bg-primary fs-6"><?= count($feedback) ?> total</span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/feedback') ?>" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="new" <?= ($status ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="read" <?= ($status ?? '') === 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="replied" <?= ($status ?? '') === 'replied' ? 'selected' : '' ?>>Replied</option>
                    <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('/admin/feedback') ?>" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($feedback)): ?>
                        <?php foreach ($feedback as $f): ?>
                            <?php
                            $statusStyles = [
                                'new'      => 'border-left:3px solid #3b82f6;background:rgba(59,130,246,0.06);',
                                'read'     => 'border-left:3px solid #f59e0b;background:rgba(245,158,11,0.06);',
                                'replied'  => 'border-left:3px solid #10b981;background:rgba(16,185,129,0.06);',
                                'archived' => 'border-left:3px solid #6b7280;background:rgba(107,114,128,0.06);',
                            ];
                            ?>
                            <tr style="<?= $statusStyles[$f['status']] ?? '' ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-user text-primary" style="font-size:.75rem;"></i>
                                        </div>
                                        <div class="fw-semibold"><?= e(($f['first_name'] ?? 'Anonymous') . ' ' . ($f['last_name'] ?? '')) ?></div>
                                    </div>
                                </td>
                                <td class="fw-bold"><?= e(truncate($f['subject'], 40)) ?></td>
                                <td><span class="badge bg-light text-dark"><?= e(ucwords(str_replace('_', ' ', $f['category'] ?? 'general'))) ?></span></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= ($f['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:.75rem"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><span class="fw-semibold"><?= statusBadge($f['status']) ?></span></td>
                                <td>
                                    <i class="fas fa-clock text-muted me-1" style="font-size:.7rem;"></i>
                                    <?= timeAgo($f['created_at']) ?>
                                </td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewFeedback<?= $f['id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($f['status'] !== 'replied'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#replyModal<?= $f['id'] ?>" title="Reply">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteFeedback<?= $f['id'] ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                                No feedback found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($feedback)): ?>
    <?php foreach ($feedback as $f): ?>

        <!-- View Feedback Modal -->
        <div class="modal fade" id="viewFeedback<?= $f['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-comment-dots me-2" style="color:#60a5fa;"></i>
                            <?= e($f['subject']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="row g-3">

                            <!-- Student Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-user me-1"></i>Student Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Name</td><td class="fw-bold"><?= e(($f['first_name'] ?? 'Anonymous') . ' ' . ($f['last_name'] ?? '')) ?></td></tr>
                                    <?php if (!empty($f['email'])): ?>
                                        <tr><td>Email</td><td><?= e($f['email']) ?></td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>

                            <!-- Feedback Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-info-circle me-1"></i>Feedback Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Status</td><td><?= statusBadge($f['status']) ?></td></tr>
                                    <tr><td>Category</td><td><?= e(ucwords(str_replace('_', ' ', $f['category'] ?? 'general'))) ?></td></tr>
                                    <tr><td>Rating</td><td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= ($f['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:.8rem"></i>
                                        <?php endfor; ?>
                                    </td></tr>
                                    <tr><td>Created</td><td><?= formatDate($f['created_at']) ?></td></tr>
                                </table>
                            </div>

                            <!-- Message -->
                            <div class="col-12 nasa-glass-box">
                                <h6><i class="fas fa-envelope me-1"></i>Message</h6>
                                <p class="mb-0" style="color:rgba(255,255,255,0.85);"><?= nl2br(e($f['message'] ?? '')) ?></p>
                            </div>

                            <!-- Admin Response -->
                            <?php if (!empty($f['admin_response'])): ?>
                                <div class="col-12 nasa-glass-box" style="background:rgba(16,185,129,0.12);border-color:rgba(16,185,129,0.3);">
                                    <h6 style="color:#6ee7b7;"><i class="fas fa-reply me-1"></i>Admin Response</h6>
                                    <p class="mb-0" style="color:rgba(255,255,255,0.85);"><?= nl2br(e($f['admin_response'])) ?></p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Modal -->
        <?php if ($f['status'] !== 'replied'): ?>
            <div class="modal fade" id="replyModal<?= $f['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="<?= url('/admin/feedback/reply/' . $f['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                <h5 class="modal-title fw-bold" style="color:#fff;">
                                    <i class="fas fa-reply me-2" style="color:#60a5fa;"></i>Reply to Feedback
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                            </div>
                            <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color:rgba(255,255,255,0.7);">Subject</label>
                                    <p class="mb-0" style="color:#fff;"><?= e($f['subject']) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color:rgba(255,255,255,0.7);">Message</label>
                                    <p class="mb-0" style="color:rgba(255,255,255,0.85);"><?= nl2br(e($f['message'] ?? '')) ?></p>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold" style="color:rgba(255,255,255,0.7);">Your Response <span class="text-danger">*</span></label>
                                    <textarea name="admin_response" class="form-control" rows="4" required placeholder="Enter your response..." style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i>Send Reply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Delete Feedback Modal -->
        <div class="modal fade" id="deleteFeedback<?= $f['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-trash me-2 text-danger"></i>Delete Feedback
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="nasa-glass-box" style="background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                            <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Are you sure you want to permanently delete this feedback
                                <strong style="color:#fff;"><?= e($f['subject']) ?></strong>
                                from <strong style="color:#fff;"><?= e(($f['first_name'] ?? 'Anonymous') . ' ' . ($f['last_name'] ?? '')) ?></strong>?
                            </p>
                            <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="<?= url('/admin/feedback/delete/' . $f['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete Feedback</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>
