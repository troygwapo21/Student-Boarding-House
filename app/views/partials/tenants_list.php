<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Tenants</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75">Total Tenants</div><div class="fs-4 fw-bold"><?= (int)$totalTenants ?></div></div>
                    <i class="fas fa-user-tag fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url($baseUrl . '/tenants') ?>" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, ID number, or email..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Select Tenant</label>
                <select name="tenant_id" id="tenantSelect" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select a tenant to view payments --</option>
                    <?php if (!empty($tenants)): ?>
                        <?php foreach ($tenants as $t): ?>
                            <option value="<?= (int)$t['reservation_id'] ?>" <?= ($selectedTenantId ?? 0) == (int)$t['reservation_id'] ? 'selected' : '' ?>>
                                <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['student_id_number'] ?? 'N/A') ?>) - <?= e($t['room_number'] ?? 'No room') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= url($baseUrl . '/tenants') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($selectedTenant)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">
            <i class="fas fa-user-tag me-2 text-primary"></i>
            <?= e($selectedTenant['first_name'] . ' ' . $selectedTenant['last_name']) ?>
            <small class="text-muted ms-2">(<?= e($selectedTenant['student_id_number'] ?? 'N/A') ?>)</small>
        </h5>
        <div class="d-flex gap-2 flex-wrap">
            
            <a href="<?= url($baseUrl . '/student/' . (int)$selectedTenant['id']) ?>" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i> View Profile
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Room</div>
                        <div class="fw-bold"><?= !empty($selectedTenant['room_number']) ? e($selectedTenant['room_number'] . ' - ' . $selectedTenant['room_name']) : 'No room assigned' ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Monthly Rent</div>
                        <div class="fw-bold text-success"><?= formatCurrency($selectedTenant['monthly_rent'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Advance Payment</div>
                        <div class="fw-bold text-info"><?= formatCurrency($selectedTenant['advance_payment'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Move-in Date</div>
                        <div class="fw-bold"><?= !empty($selectedTenant['move_in_date']) ? formatDate($selectedTenant['move_in_date']) : '—' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <?php if (isset($finalDueDate) || isset($nextDueDate)): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 bg-light border-start border-info border-3">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Total Months Paid</div>
                        <div class="fw-bold text-info"><?= (int)($totalMonthsPaid ?? 0) ?> mo</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light border-start border-warning border-3">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted">Next Due Date</div>
                        <div class="fw-bold text-warning"><?= isset($nextDueDate) ? formatDate($nextDueDate) : '—' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Payment Records</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($tenantPayments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Code</th>
                                <th>Type</th>
                                <th>Room</th>
                                <th>Billing Period</th>
                                <th>Amount</th>
                                <th>Paid Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Paid Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenantPayments as $p): ?>
                                <?php
                                $payStatus = $p['status'] ?? 'pending';
                                $payColor = $payStatus === 'paid' ? '#16a34a' : ($payStatus === 'pending' ? '#d97706' : ($payStatus === 'overdue' ? '#dc2626' : '#64748b'));
                                $payBg = $payStatus === 'paid' ? '#dcfce7' : ($payStatus === 'pending' ? '#fef3c7' : ($payStatus === 'overdue' ? '#fee2e2' : '#f1f5f9'));
                                $amount = (float)($p['amount'] ?? 0);
                                $paidAmount = (float)($p['amount_paid'] ?? 0);
                                ?>
                                <tr>
                                    <td class="fw-bold small"><?= e($p['payment_code'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e(ucwords(str_replace('_', ' ', $p['payment_type']))) ?></span>
                                    </td>
                                    <td class="small"><?= e(trim(($p['room_number'] ?? '') . ' ' . ($p['room_name'] ?? '')) ?: 'N/A') ?></td>
                                    <td class="text-muted small"><?= e($p['billing_period'] ?? '—') ?></td>
                                    <td class="fw-semibold"><?= formatCurrency($amount) ?></td>
                                    <td class="fw-bold text-success"><?= formatCurrency($paidAmount) ?></td>
                                    <td class="text-capitalize small"><?= e($p['payment_method'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge" style="background:<?= $payBg ?>;color:<?= $payColor ?>;"><?= e(ucwords(str_replace('_', ' ', $payStatus))) ?></span>
                                    </td>
                                    <td class="text-muted small"><?= !empty($p['due_date']) ? formatDate($p['due_date']) : '—' ?></td>
                                    <td class="text-muted small"><?= !empty($p['paid_at']) ? formatDate($p['paid_at']) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                    <p>No payment records found for this tenant.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Room</th>
                        <th>Move-in Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tenants)): ?>
                        <?php foreach ($tenants as $s): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($s['student_id_number'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px">
                                            <i class="fas fa-user-tag text-primary small"></i>
                                        </div>
                                        <div class="fw-semibold"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                    </div>
                                </td>
                                <td class="text-muted small"><?= e($s['email'] ?? '') ?></td>
                                <td class="text-muted small"><?= e($s['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($s['room_number'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success"><?= e($s['room_number']) ?> - <?= e($s['room_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">No room</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= !empty($s['move_in_date']) ? formatDate($s['move_in_date']) : '—' ?></td>
                                <td><?= statusBadge($s['user_status'] ?? 'active') ?></td>
                                <td class="text-end">
                                    <a href="<?= url($baseUrl . '/tenants?tenant_id=' . (int)$s['reservation_id']) ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-list me-1"></i>Payments</a>
                                    <a href="<?= url($baseUrl . '/student/' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-eye me-1"></i>View</a>
                                    <form method="POST" action="<?= url($baseUrl . '/tenant/remove/' . (int)$s['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Remove <?= e($s['first_name'] . ' ' . $s['last_name']) ?> as a tenant? Their approved reservation will be cancelled and the room freed. This action cannot be undone."><i class="fas fa-user-minus me-1"></i>Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No tenants yet. Students appear here automatically once their reservation is approved.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
