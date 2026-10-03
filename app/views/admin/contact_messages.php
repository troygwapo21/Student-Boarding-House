<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contact Messages</h1>
    <span class="badge bg-primary fs-6"><?= count($messages) ?> total<?php if ($newCount > 0): ?> &middot; <span class="text-danger fw-bold"><?= $newCount ?> new</span><?php endif; ?></span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/contact-messages') ?>" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="new" <?= ($status ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="read" <?= ($status ?? '') === 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="replied" <?= ($status ?? '') === 'replied' ? 'selected' : '' ?>>Replied</option>
                    <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="subject" class="form-select">
                    <option value="">All Subjects</option>
                    <option value="Room Inquiry" <?= ($subject ?? '') === 'Room Inquiry' ? 'selected' : '' ?>>Room Inquiry</option>
                    <option value="Reservation" <?= ($subject ?? '') === 'Reservation' ? 'selected' : '' ?>>Reservation</option>
                    <option value="Payment" <?= ($subject ?? '') === 'Payment' ? 'selected' : '' ?>>Payment</option>
                    <option value="Maintenance" <?= ($subject ?? '') === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="General Inquiry" <?= ($subject ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                    <option value="Feedback" <?= ($subject ?? '') === 'Feedback' ? 'selected' : '' ?>>Feedback</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('/admin/contact-messages') ?>" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
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
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $m): ?>
                            <?php
                            $statusStyles = [
                                'new'      => 'border-left:3px solid #3b82f6;background:rgba(59,130,246,0.06);',
                                'read'     => 'border-left:3px solid #f59e0b;background:rgba(245,158,11,0.06);',
                                'replied'  => 'border-left:3px solid #10b981;background:rgba(16,185,129,0.06);',
                                'archived' => 'border-left:3px solid #6b7280;background:rgba(107,114,128,0.06);',
                            ];
                            ?>
                            <tr style="<?= $statusStyles[$m['status']] ?? '' ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-user text-primary" style="font-size:.75rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= e($m['name']) ?></div>
                                            <small class="text-muted"><?= e($m['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark"><?= e($m['subject']) ?></span></td>
                                <td class="text-muted" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e(truncate($m['message'], 50)) ?></td>
                                <td><span class="fw-semibold"><?= statusBadge($m['status']) ?></span></td>
                                <td><i class="fas fa-clock text-muted me-1" style="font-size:.7rem;"></i><?= timeAgo($m['created_at']) ?></td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <a href="<?= url('/admin/contact-message/' . $m['id']) ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMsg<?= $m['id'] ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-envelope-open fa-2x mb-2 d-block"></i>
                                No contact messages found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $m): ?>
        <div class="modal fade" id="deleteMsg<?= $m['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;"><i class="fas fa-trash me-2 text-danger"></i>Delete Message</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                            <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Are you sure you want to permanently delete this message from <strong style="color:#fff;"><?= e($m['name']) ?></strong> regarding <strong style="color:#fff;"><?= e($m['subject']) ?></strong>?
                            </p>
                            <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;"><i class="fas fa-info-circle me-1"></i>This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="<?= url('/admin/contact-message/delete/' . $m['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
