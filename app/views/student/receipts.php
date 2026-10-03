<div class="page-header">
    <h4>My Receipts</h4>
</div>

<?php if (!empty($monthOptions) || !empty($paymentTypes)): ?>
<form method="GET" action="<?= url('/student/receipts') ?>" class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <?php if (!empty($paymentTypes)): ?>
    <label for="typeFilter" class="text-muted small fw-semibold mb-0"><i class="fas fa-tags me-1"></i> Type:</label>
    <select name="type" id="typeFilter" class="form-select form-select-sm" style="width:auto;" aria-label="Filter receipts by payment type"
            onchange="this.form.submit()">
        <option value="">All Types</option>
        <?php foreach ($paymentTypes as $ptKey => $ptLabel): ?>
        <option value="<?= e($ptKey) ?>" <?= ($activeType === $ptKey) ? 'selected' : '' ?>><?= e($ptLabel) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <?php if (!empty($monthOptions)): ?>
    <label for="monthFilter" class="text-muted small fw-semibold mb-0"><i class="fas fa-calendar-days me-1"></i> Month:</label>
    <select name="month" id="monthFilter" class="form-select form-select-sm" style="width:auto;" aria-label="Filter receipts by month"
            onchange="this.form.submit()">
        <option value="">All Months</option>
        <?php foreach (array_keys($monthOptions) as $ym): ?>
        <option value="<?= e($ym) ?>" <?= ($month ?? '') === $ym ? 'selected' : '' ?>><?= e(date('F Y', strtotime($ym . '-01'))) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <noscript><button type="submit" class="btn btn-primary btn-sm">Go</button></noscript>
</form>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt Number</th>
                    <th>Payment Code</th>
                    <th>Type</th>
                    <th>Total Amount</th>
                    <th>Issued Date</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($months)): ?>
                    <?php foreach ($months as $ym => $group): ?>
                    <?php
                    $hasRent = false;
                    foreach ($group['receipts'] as $r) {
                        if (($r['payment_type'] ?? '') === 'monthly_rent') {
                            $hasRent = true;
                            break;
                        }
                    }
                    ?>
                    <tr class="table-light">
                        <td colspan="6" class="py-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="fw-bold"><i class="fas fa-calendar-alt me-1 text-primary"></i><?= e($group['label']) ?></span>
                                <span class="text-muted small"><?= (int)$group['count'] ?> receipt<?= (int)$group['count'] !== 1 ? 's' : '' ?> &middot; Total: <strong class="text-success"><?= formatCurrency((float)$group['total']) ?></strong></span>
                                <span class="d-flex gap-2">
                                    <?php if ($hasRent): ?>
                                    <a href="<?= url('/student/receipt/rent/' . e($ym)) ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-invoice me-1"></i> Rent Statement
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('/student/receipt/month/' . e($ym)) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-invoice me-1"></i> Monthly Statement
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php foreach ($group['receipts'] as $receipt): ?>
                    <tr>
                        <td><strong><?= e($receipt['receipt_number']) ?></strong></td>
                        <td><?= e($receipt['payment_code']) ?></td>
                        <td><span class="text-capitalize"><?= e(str_replace('_', ' ', $receipt['payment_type'])) ?></span></td>
                        <td class="fw-semibold"><?= formatCurrency((float)($receipt['total'] ?? $receipt['amount'] ?? 0)) ?></td>
                        <td><?= formatDate($receipt['issued_date']) ?></td>
                        <td class="text-center">
                            <a href="<?= url('/student/receipt/' . $receipt['id']) ?>" class="btn btn-outline-primary btn-sm me-1">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <?php if (!in_array($receipt['payment_type'] ?? '', ['monthly_rent', 'advance_payment'], true) && !in_array($receipt['payment_status'] ?? '', ['paid', 'due_today', 'overdue'], true)): ?>
                            <form method="POST" action="<?= url('/student/payment/delete/' . (int)$receipt['payment_id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete receipt <?= e($receipt['receipt_number']) ?> and its payment <?= e($receipt['payment_code']) ?>? This action cannot be undone." title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php
                        $typeLabel = !empty($activeType) ? (string)($paymentTypes[$activeType] ?? ucwords(str_replace('_', ' ', $activeType))) : '';
                        $monthLabel = '';
                        if (!empty($month)) {
                            $selD = DateTime::createFromFormat('!Y-m', $month);
                            $monthLabel = $selD ? $selD->format('F Y') : $month;
                        }
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            <?php if (!empty($typeLabel) && !empty($monthLabel)): ?>
                                No <?= e($typeLabel) ?> receipts in <?= e($monthLabel) ?>.
                            <?php elseif (!empty($typeLabel)): ?>
                                No <?= e($typeLabel) ?> receipts found.
                            <?php elseif (!empty($monthLabel)): ?>
                                No receipts in <?= e($monthLabel) ?>.
                            <?php else: ?>
                                No receipts found.
                            <?php endif; ?>
                            <div class="mt-2">
                                <a href="<?= url('/student/receipts') ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-filter-circle-xmark me-1"></i> Clear Filters
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
