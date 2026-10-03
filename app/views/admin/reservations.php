<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Reservations</h1>
    <span class="badge bg-primary fs-6"><?= count($reservations) ?> total</span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/reservations') ?>" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="expired" <?= ($status ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('/admin/reservations') ?>" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
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
                        <th>Code</th>
                        <th>Student</th>
                        <th>Room</th>
                        <th>Move-in Date</th>
                        <th>Duration</th>
                        <th>Due Date</th>
                        <th>Final Due Date</th>
                        <th>Move-out Month</th>
                        <th>Monthly Rent</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reservations)): ?>
                        <?php foreach ($reservations as $r): ?>
                            <?php
                            $statusStyles = [
                                'pending'   => 'border-left:3px solid #f59e0b;background:rgba(245,158,11,0.06);',
                                'approved'  => 'border-left:3px solid #10b981;background:rgba(16,185,129,0.06);',
                                'rejected'  => 'border-left:3px solid #ef4444;background:rgba(239,68,68,0.06);',
                                'cancelled' => 'border-left:3px solid #6b7280;background:rgba(107,114,128,0.06);',
                                'expired'   => 'border-left:3px solid #8b5cf6;background:rgba(139,92,246,0.06);',
                            ];
                            ?>
                            <tr style="<?= $statusStyles[$r['status']] ?? '' ?>">
                                <td>
                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-bold" style="font-size:.8rem;">
                                        <?= e($r['reservation_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-user-graduate text-primary" style="font-size:.75rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                            <small class="text-muted"><?= e($r['student_id_number'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                            <i class="fas fa-door-open text-success" style="font-size:.75rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Room <?= e($r['room_number']) ?></div>
                                            <small class="text-muted"><?= e($r['room_name']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-calendar-day text-muted me-1" style="font-size:.75rem;"></i>
                                    <?= formatDate($r['move_in_date']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['expected_duration'])): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info"><?= e($r['expected_duration']) ?> mo</span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
<td>
                                     <?php if (!empty($r['expected_duration'])): ?>
                                         <i class="fas fa-calendar-check text-muted me-1" style="font-size:.75rem;"></i>
                                         <?= formatDate(addCalendarMonths($r['move_in_date'], (int)$r['expected_duration'])) ?>
                                     <?php else: ?>
                                         —
                                     <?php endif; ?>
                                 </td>
                                 <td>
                                     <?php if (!empty($r['final_due_date'])): ?>
                                         <i class="fas fa-calendar-alt text-primary me-1" style="font-size:.75rem;"></i>
                                         <span class="fw-semibold"><?= formatDate($r['final_due_date']) ?></span>
                                     <?php else: ?>
                                         —
                                     <?php endif; ?>
                                 </td>
                                 <td>
                                     <?php if (!empty($r['move_out_month'])): ?>
                                         <span class="badge bg-info bg-opacity-10 text-info"><?= e($r['move_out_month']) ?></span>
                                     <?php else: ?>
                                         —
                                     <?php endif; ?>
                                 </td>
                                 <td class="fw-bold text-success">
                                     <?= formatCurrency($r['monthly_rent'] ?? 0) ?>
                                     <small class="text-muted fw-normal">/mo</small>
                                 </td>
                                <td>
                                    <div>
                                        <i class="fas fa-clock text-muted me-1" style="font-size:.7rem;"></i>
                                        <?= formatDate($r['created_at']) ?>
                                    </div>
                                    <?php if (!empty($r['approved_at'])): ?>
                                        <small class="text-success"><i class="fas fa-check-circle"></i> <?= formatDate($r['approved_at']) ?></small>
                                    <?php elseif (!empty($r['rejected_at'])): ?>
                                        <small class="text-danger"><i class="fas fa-times-circle"></i> <?= formatDate($r['rejected_at']) ?></small>
                                    <?php elseif (!empty($r['cancelled_at'])): ?>
                                        <small class="text-secondary"><i class="fas fa-ban"></i> <?= formatDate($r['cancelled_at']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= statusBadge($r['status']) ?></span>
                                </td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $r['id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= url('/admin/reservation/approve/' . $r['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success" data-confirm="Approve this reservation?" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $r['id'] ?>" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['id'] ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                No reservations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($reservations)): ?>
    <?php foreach ($reservations as $r): ?>

        <!-- View Modal -->
        <div class="modal fade" id="viewModal<?= $r['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-file-invoice me-2" style="color:#60a5fa;"></i>
                            Reservation <?= e($r['reservation_code']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="row g-3">

                            <!-- Reservation Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-calendar-alt me-1"></i>Reservation Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Code</td><td class="fw-bold"><?= e($r['reservation_code']) ?></td></tr>
                                    <tr><td>Status</td><td><?= statusBadge($r['status']) ?></td></tr>
                                    <tr><td>Date Requested</td><td><?= formatDate($r['created_at']) ?></td></tr>
                                    <tr><td>Move-in Date</td><td class="fw-medium"><?= formatDate($r['move_in_date']) ?></td></tr>
                                    <tr><td>Duration</td><td><?= !empty($r['expected_duration']) ? (int)$r['expected_duration'] . ' month' . ((int)$r['expected_duration'] !== 1 ? 's' : '') : '—' ?></td></tr>
                                    <tr><td>Due Date</td><td class="fw-medium"><?= !empty($r['expected_duration']) ? formatDate(addCalendarMonths($r['move_in_date'], (int)$r['expected_duration'])) : '—' ?></td></tr>
                                    <?php if (!empty($r['final_due_date'])): ?>
                                        <tr><td>Final Due Date</td><td class="fw-bold text-primary"><?= formatDate($r['final_due_date']) ?></td></tr>
                                        <tr><td>Move-out Month</td><td class="fw-medium text-info"><?= e($r['move_out_month']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (!empty($r['approved_at'])): ?>
                                        <tr><td>Approved</td><td class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i><?= formatDate($r['approved_at']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (!empty($r['rejected_at'])): ?>
                                        <tr><td>Rejected</td><td class="text-danger fw-medium"><i class="fas fa-times-circle me-1"></i><?= formatDate($r['rejected_at']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (!empty($r['cancelled_at'])): ?>
                                        <tr><td>Cancelled</td><td class="text-secondary fw-medium"><i class="fas fa-ban me-1"></i><?= formatDate($r['cancelled_at']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (!empty($r['admin_notes'])): ?>
                                        <tr><td>Notes</td><td><?= e($r['admin_notes']) ?></td></tr>
                                    <?php endif; ?>
                                    <tr><td>Updated</td><td><?= formatDate($r['updated_at']) ?></td></tr>
                                </table>
                            </div>

                            <!-- Room Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-door-open me-1"></i>Room Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Room Name</td><td class="fw-bold"><?= e($r['room_name']) ?></td></tr>
                                    <tr><td>Room Number</td><td><?= e($r['room_number']) ?></td></tr>
                                    <tr><td>Monthly Rent</td><td class="fw-bold text-primary"><?= formatCurrency($r['monthly_rent'] ?? 0) ?></td></tr>
                                    <tr><td>Advance Payment</td><td class="fw-bold text-info"><?= formatCurrency($r['advance_payment'] ?? 0) ?></td></tr>
                                </table>
                            </div>

                            <!-- Student Info -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-user-graduate me-1"></i>Student Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td>Name</td><td class="fw-bold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td></tr>
                                    <tr><td>Student ID</td><td><?= e($r['student_id_number'] ?? 'N/A') ?></td></tr>
                                    <tr><td>Email</td><td><?= e($r['student_email'] ?? 'N/A') ?></td></tr>
                                    <?php if (!empty($r['student_phone'])): ?>
                                        <tr><td>Phone</td><td><?= e($r['student_phone']) ?></td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>

                            <!-- Valid ID -->
                            <div class="col-md-6 info-section nasa-glass-box">
                                <h6><i class="fas fa-id-card me-1"></i>Valid ID</h6>
                                <?php if (!empty($r['valid_id_path'])): ?>
                                    <?php $ext = strtolower(pathinfo($r['valid_id_path'], PATHINFO_EXTENSION)); ?>
                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                        <a href="<?= UPLOAD_URL . $r['valid_id_path'] ?>" target="_blank">
                                            <img src="<?= UPLOAD_URL . $r['valid_id_path'] ?>" alt="Valid ID" class="img-fluid rounded" style="max-height:200px;border:1px solid rgba(255,255,255,0.1);">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= UPLOAD_URL . $r['valid_id_path'] ?>" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                            <i class="fas fa-file-pdf me-1"></i>View PDF
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="mb-0" style="color:rgba(255,255,255,0.5);">
                                        <i class="fas fa-times-circle me-1"></i>No valid ID uploaded
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Rejection Reason -->
                            <?php if (!empty($r['rejection_reason'])): ?>
                                <div class="col-12 nasa-glass-box" style="background:rgba(239,68,68,0.15);border-color:rgba(239,68,68,0.3);">
                                    <div class="mb-0" style="color:#fca5a5;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Rejection Reason:</strong> <?= e($r['rejection_reason']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Close</button>
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="POST" action="<?= url('/admin/reservation/approve/' . $r['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success" data-confirm="Approve this reservation?">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger open-reject" data-id="<?= $r['id'] ?>">
                                <i class="fas fa-times me-1"></i>Reject
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <?php if ($r['status'] === 'pending'): ?>
            <div class="modal fade" id="rejectModal<?= $r['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="<?= url('/admin/reservation/reject/' . $r['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                <h5 class="modal-title fw-bold" style="color:#fff;">
                                    <i class="fas fa-times-circle me-2 text-danger"></i>Reject Reservation
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                            </div>
                            <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                <p style="color:rgba(255,255,255,0.85);">
                                    Reject reservation <strong style="color:#fff;"><?= e($r['reservation_code']) ?></strong>
                                    by <strong style="color:#fff;"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>?
                                </p>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold" style="color:rgba(255,255,255,0.7);">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..." required style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject Reservation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal<?= $r['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                        <h5 class="modal-title fw-bold" style="color:#fff;">
                            <i class="fas fa-trash me-2 text-danger"></i>Delete Reservation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                        <div class="nasa-glass-box" style="background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                            <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Are you sure you want to permanently delete reservation
                                <strong style="color:#fff;"><?= e($r['reservation_code']) ?></strong>
                                by <strong style="color:#fff;"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>?
                            </p>
                            <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                This action cannot be undone. If the reservation is approved, the room occupancy will be adjusted.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="<?= url('/admin/reservation/delete/' . $r['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete Reservation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>
