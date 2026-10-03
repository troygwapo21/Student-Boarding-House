<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Student Details</h1>
    <div>
        <a href="<?= url($baseUrl . '/students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        <form method="POST" action="<?= url($baseUrl . '/student/delete/' . $student['id']) ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger" data-confirm="Delete student <?= e($student['first_name'] . ' ' . $student['last_name']) ?>? This will permanently remove their account, reservations, payments, and all related data. This action cannot be undone."><i class="fas fa-trash me-2"></i>Delete Student</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                    <i class="fas fa-user fa-2x text-primary"></i>
                </div>
                <h5 class="mb-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h5>
                <p class="text-muted mb-3"><?= e($student['email'] ?? '') ?></p>
                <?= statusBadge($student['user_status'] ?? 'active') ?>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-3">Change Status</h6>
                <form method="POST" action="<?= url($baseUrl . '/student/' . $student['id']) ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="input-group input-group-sm">
                        <select class="form-select" name="user_status">
                            <option value="active" <?= ($student['user_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($student['user_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="suspended" <?= ($student['user_status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                        <button type="submit" class="btn btn-primary" data-confirm="Change student status?"><i class="fas fa-save"></i></button>
                    </div>
                </form>
                <h6 class="text-muted small text-uppercase mb-3">Student Information</h6>
                <div class="mb-2"><small class="text-muted">ID Number:</small><div class="fw-semibold"><?= e($student['student_id_number'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Phone:</small><div class="fw-semibold"><?= e($student['phone'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">School:</small><div class="fw-semibold"><?= e($student['school_university'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Course:</small><div class="fw-semibold"><?= e($student['course_program'] ?? 'N/A') ?></div></div>
                <div><small class="text-muted">Address:</small><div class="fw-semibold"><?= e($student['address'] ?? 'N/A') ?></div></div>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-3">Guardian Information</h6>
                <?php if (!empty($guardian)): ?>
                <div class="mb-2"><small class="text-muted">Guardian Name:</small><div class="fw-semibold"><?= e(trim(($guardian['first_name'] ?? '') . ' ' . ($guardian['middle_name'] ?? '') . ' ' . ($guardian['last_name'] ?? ''))) ?></div></div>
                <div class="mb-2"><small class="text-muted">Relationship:</small><div class="fw-semibold"><?= e($guardian['relationship'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Mobile:</small><div class="fw-semibold"><?= e($guardian['mobile_number'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Email:</small><div class="fw-semibold"><?= e($guardian['email'] ?? 'N/A') ?></div></div>
                <div><small class="text-muted">Address:</small><div class="fw-semibold"><?= e(trim(implode(', ', array_filter([$guardian['house_unit'] ?? '', $guardian['street'] ?? '', $guardian['barangay'] ?? '', $guardian['municipality_city'] ?? '', $guardian['province'] ?? '', $guardian['zip_code'] ?? '']))) ?: 'N/A') ?></div></div>
                <?php else: ?>
                <p class="text-muted mb-0">No guardian on file.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-home me-2"></i>Active Reservation</h6></div>
            <div class="card-body">
                <?php if (!empty($reservation)): ?>
                    <div class="row g-3">
                        <div class="col-md-4"><small class="text-muted">Reservation Code</small><div class="fw-bold"><?= e($reservation['reservation_code']) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Room</small><div class="fw-bold"><?= e($reservation['room_number']) ?> - <?= e($reservation['room_name']) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Status</small><div><?= statusBadge($reservation['status']) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Move-in Date</small><div class="fw-bold"><?= formatDate($reservation['move_in_date']) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Next Due Date</small><div class="fw-bold text-danger fs-5"><?= !empty($reservation['next_due_date']) ? formatDate($reservation['next_due_date']) : '—' ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Final Due Date</small><div class="fw-bold text-primary"><?= !empty($reservation['final_due_date']) ? formatDate($reservation['final_due_date']) : '—' ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Move-out Month</small>
                            <?php if (!empty($reservation['move_out_month'])): ?>
                                <div class="fw-bold text-info"><?= e($reservation['move_out_month']) ?></div>
                            <?php else: ?>
                                <div class="fw-bold">—</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4"><small class="text-muted">Monthly Rent</small><div class="fw-bold text-success"><?= formatCurrency($reservation['monthly_rent'] ?? 0) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Advance Payment</small><div class="fw-bold text-info"><?= formatCurrency($reservation['advance_payment'] ?? 0) ?></div></div>
                        <div class="col-md-4"><small class="text-muted">Months from Advance</small><div class="fw-bold text-info"><?= (int)($reservation['months_from_advance'] ?? 0) ?> mo</div></div>
                        <div class="col-md-4"><small class="text-muted">Months from Monthly Rent</small><div class="fw-bold text-success"><?= (int)($reservation['months_from_monthly'] ?? 0) ?> mo</div></div>
                        <div class="col-md-4"><small class="text-muted">Total Months Paid</small><div class="fw-bold text-primary"><?= (int)($reservation['total_months_paid'] ?? 0) ?> mo</div></div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3 mb-0">No active reservation.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment History</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentTotalsModal">
                    <i class="fas fa-calculator me-1"></i> Show All Totals
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Code</th><th>Type</th><th>Room</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (!empty($payments)): ?>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td class="fw-bold small"><?= e($p['payment_code']) ?></td>
                                        <td><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></td>
                                        <td class="small"><?= e(trim(($p['room_number'] ?? '') . ' ' . ($p['room_name'] ?? '')) ?: 'N/A') ?></td>
                                        <td class="fw-bold"><?= formatCurrency($p['amount']) ?></td>
                                        <td><?= $p['payment_method'] && (float)($p['amount_paid'] ?? 0) > 0 ? e(ucwords(str_replace('_', ' ', $p['payment_method']))) : '<span class="text-muted">—</span>' ?></td>
                                        <td><?= (float)($p['amount_paid'] ?? 0) <= 0 && $p['status'] === 'pending' ? '<span class="text-muted">Not Submitted</span>' : statusBadge($p['status']) ?></td>
                                        <td class="text-muted small"><?= formatDate($p['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">No payment history.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Totals Modal -->
        <div class="modal fade" id="paymentTotalsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calculator me-2 text-primary"></i>Payment Totals Summary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-white">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card border border-light">
                                    <div class="card-body text-center">
                                        <div class="small text-muted">Total Paid</div>
                                        <div class="fs-4 fw-bold text-success"><?= formatCurrency($payment_totals['total_paid'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border border-light">
                                    <div class="card-body text-center">
                                        <div class="small text-muted">Pending</div>
                                        <div class="fs-4 fw-bold text-warning"><?= formatCurrency($payment_totals['total_pending'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border border-light">
                                    <div class="card-body text-center">
                                        <div class="small text-muted">Overdue</div>
                                        <div class="fs-4 fw-bold text-danger"><?= formatCurrency($payment_totals['total_overdue'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border border-light">
                                    <div class="card-body text-center">
                                        <div class="small text-muted">Refunded</div>
                                        <div class="fs-4 fw-bold text-info"><?= formatCurrency($payment_totals['total_refunded'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border border-light bg-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Net Total (Paid - Refunded)</span>
                                    <span class="fs-4 fw-bold text-primary"><?= formatCurrency($payment_totals['net_total'] ?? 0) ?></span>
                                </div>
                                <small class="text-muted">Total Transactions: <?= (int)($payment_totals['count'] ?? 0) ?></small>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="fas fa-list me-2"></i>All Payment Records</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Room</th>
                                        <th>Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Paid Date</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_payments)): ?>
                                        <?php foreach ($all_payments as $p): ?>
                                            <?php
                                            $payStatus = $p['status'] ?? 'pending';
                                            $payColor = $payStatus === 'paid' ? '#16a34a' : ($payStatus === 'pending' ? '#d97706' : ($payStatus === 'overdue' ? '#dc2626' : ($payStatus === 'refunded' ? '#8b5cf6' : '#64748b')));
                                            $payBg = $payStatus === 'paid' ? '#dcfce7' : ($payStatus === 'pending' ? '#fef3c7' : ($payStatus === 'overdue' ? '#fee2e2' : ($payStatus === 'refunded' ? '#ede9fe' : '#f1f5f9')));
                                            $amt = (float)($p['amount'] ?? 0);
                                            $paidAmt = (float)($p['amount_paid'] ?? 0);
                                            ?>
                                            <tr>
                                                <td class="fw-bold small"><?= e($p['payment_code'] ?? 'N/A') ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></span></td>
                                                <td class="small"><?= e(trim(($p['room_number'] ?? '') . ' ' . ($p['room_name'] ?? '')) ?: 'N/A') ?></td>
                                                <td class="fw-semibold"><?= formatCurrency($amt) ?></td>
                                                <td class="fw-bold text-success"><?= formatCurrency($paidAmt) ?></td>
                                                <td class="text-capitalize small"><?= e($p['payment_method'] ?? '—') ?></td>
                                                <td><span class="badge" style="background:<?= $payBg ?>;color:<?= $payColor ?>;"><?= e(ucwords(str_replace('_', ' ', $payStatus))) ?></span></td>
                                                <td class="text-muted small"><?= !empty($p['due_date']) ? formatDate($p['due_date']) : '—' ?></td>
                                                <td class="text-muted small"><?= !empty($p['paid_at']) ? formatDate($p['paid_at']) : '—' ?></td>
                                                <td class="text-muted small"><?= formatDate($p['created_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10" class="text-center text-muted py-3">No payment records.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
