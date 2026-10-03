<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Receipts</h1>
    <span class="badge bg-primary fs-6"><?= count($receipts) ?> total</span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/manager/receipts') ?>" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by receipt #, payment code, or student name..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="monthly_rent" <?= ($type ?? '') === 'monthly_rent' ? 'selected' : '' ?>>Monthly Rent</option>
                    <option value="advance_payment" <?= ($type ?? '') === 'advance_payment' ? 'selected' : '' ?>>Advance Payment</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="partially_paid" <?= ($status ?? '') === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    <?php foreach (array_keys($monthOptions ?? []) as $ym): ?>
                        <?php $d = DateTime::createFromFormat('!Y-m', $ym); ?>
                        <option value="<?= e($ym) ?>" <?= ($month ?? '') === $ym ? 'selected' : '' ?>><?= e($d ? $d->format('F Y') : $ym) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('/manager/receipts') ?>" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
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
                        <th>Receipt #</th>
                        <th>Student</th>
                        <th>Payment Code</th>
                        <th>Type</th>
                        <th>Room</th>
                        <th>Period</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Late Fee</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($receipts)): ?>
                        <?php foreach ($receipts as $r): ?>
                            <?php
                            $lateFee = (float)($r['late_fee'] ?? 0);
                            $totalDue = (float)$r['amount'] + $lateFee;
                            $statusBadge = match($r['payment_status'] ?? '') {
                                'paid' => 'bg-success',
                                'partially_paid' => 'bg-warning text-dark',
                                'pending' => 'bg-info text-dark',
                                'overdue' => 'bg-danger',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-secondary',
                            };
                            $typeBadge = match($r['payment_type'] ?? '') {
                                'monthly_rent' => 'bg-primary',
                                'advance_payment' => 'bg-success',
                                'reservation_fee' => 'bg-info',
                                'electric_bill' => 'bg-warning text-dark',
                                'water_bill' => 'bg-info',
                                default => 'bg-secondary',
                            };
                            ?>
                            <tr>
                                <td><strong class="text-primary"><?= e($r['receipt_number']) ?></strong></td>
                                <td>
                                    <div class="fw-semibold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                    <small class="text-muted"><?= e($r['student_id_number'] ?? '') ?></small>
                                </td>
                                <td><span class="text-muted"><?= e($r['payment_code']) ?></span></td>
                                <td><span class="badge <?= $typeBadge ?> text-capitalize"><?= e(str_replace('_', ' ', $r['payment_type'])) ?></span></td>
                                <td><?= e(($r['room_name'] ?? 'N/A') . ' ' . ($r['room_number'] ?? '')) ?></td>
                                <td><?= e($r['billing_period'] ?? '—') ?></td>
                                <td class="text-end"><?= formatCurrency((float)$r['amount']) ?></td>
                                <td class="text-end <?= $lateFee > 0 ? 'text-danger' : '' ?>"><?= $lateFee > 0 ? formatCurrency($lateFee) : '—' ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency((float)($r['total'] ?? $totalDue)) ?></td>
                                <td><span class="badge <?= $statusBadge ?> text-capitalize"><?= str_replace('_', ' ', $r['payment_status'] ?? '') ?></span></td>
                                <td><small><?= formatDate($r['issued_date']) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= url('/manager/receipt/' . $r['id']) ?>" class="btn btn-outline-primary btn-sm" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (($r['payment_type'] ?? '') === 'monthly_rent' && !empty($r['billing_period'])): ?>
                                    <a href="<?= url('/manager/receipt/rent/' . e($r['billing_period']) . '?student=' . (int)$r['student_id']) ?>" class="btn btn-outline-success btn-sm" title="Monthly Rent Receipt">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <?php endif; ?>
                                    <form method="POST" action="<?= url('/manager/receipt/delete/' . (int)$r['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete receipt <?= e($r['receipt_number']) ?>? The associated payment will not be affected." title="Delete Receipt">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 d-block opacity-50"></i>
                                <h5>No receipts found</h5>
                                <p class="mb-0">Receipts are created automatically when payments are verified.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
