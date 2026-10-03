<?php $old = $old ?? []; $errors = $errors ?? []; $baseUrl = $baseUrl ?? '/admin'; $student = $student ?? null; $roomRent = $roomRent ?? 0; $roomAdvance = $roomAdvance ?? 0; $tenantDuration = $tenantDuration ?? 0; $tenantMoveInDate = $tenantMoveInDate ?? ''; $tenantLastDueDate = $tenantLastDueDate ?? ''; $tenantMinMoveOutDate = $tenantMinMoveOutDate ?? ''; ?>
<?php if (!function_exists('fe3')) {
    function fe3(string $f, array $e): string { $msg = $e[$f] ?? ''; return $msg ? '<div class="field-error"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($msg) . '</div>' : ''; }
}
if (!function_exists('hasErr3')) {
    function hasErr3(string $f, array $e): string { return !empty($e[$f]) ? ' is-invalid' : ''; }
} ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>Walk-In Payment</h1>
    <a href="<?= url($baseUrl . '/students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Students</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-exclamation-triangle me-1"></i>Please check the following:
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url($baseUrl . '/students/walk-in-payment') ?>" id="tenantSelectForm" class="row g-3 align-items-end">
            <div class="col-md-10">
                <label class="form-label small text-muted">Select tenant <span class="text-danger">*</span></label>
                <select name="student_id" id="tenantSelect" class="form-select">
                    <option value="">-- Select a tenant --</option>
                    <?php foreach ($allStudents as $st): ?>
                    <option value="<?= (int)$st['id'] ?>" <?= $studentId === (int)$st['id'] ? 'selected' : '' ?>
                        data-room="<?= e(($st['room_number'] ?? '') . ' - ' . ($st['room_name'] ?? '')) ?>"
                        data-rent="<?= (float)($st['monthly_rent'] ?? 0) ?>"
                        data-advance="<?= (float)($st['advance_payment'] ?? 0) ?>">
                        <?= e($st['first_name'] . ' ' . $st['last_name']) ?> (<?= e($st['student_id_number']) ?>)<?= ($st['status'] ?? '') === 'suspended' ? ' [Suspended]' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Load</button>
            </div>
        </form>
    </div>
</div>

<?php if ($student): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="bg-light rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Tenant</div>
                    <div class="fw-bold"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
                    <div class="small text-muted"><?= e($student['student_id_number'] ?? 'N/A') ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Contact</div>
                    <div class="small"><?= e($student['email'] ?? '') ?></div>
                    <div class="small text-muted"><?= e($student['phone'] ?? 'N/A') ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Room</div>
                    <div class="small fw-semibold"><?= e($roomLabel ?? 'No room assigned') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light rounded-3 p-3 h-100" style="border-left:3px solid #2563eb;">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Monthly Rent</div>
                    <div class="fs-5 fw-bold" style="color:#2563eb;"><?= $roomRent > 0 ? formatCurrency($roomRent) : 'N/A' ?></div>
                    <div class="small text-muted">Advance: <?= $roomAdvance > 0 ? formatCurrency($roomAdvance) : 'N/A' ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Total Due</div>
                    <div class="fs-5 fw-bold" id="autoTotalDue" style="color:#059669;"><?= $roomRent > 0 ? formatCurrency($roomRent + $roomAdvance) : 'N/A' ?></div>
                    <div class="small text-muted">Rent + Advance</div>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="<?= url($baseUrl . '/students/walk-in-payment') ?>" method="POST" id="walkInPaymentForm" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-file-invoice me-2 text-primary"></i>Outstanding Bills</div>
        <div class="card-body p-0">
            <?php if (empty($bills)): ?>
            <div class="text-center text-muted py-4 small">No outstanding bills for this tenant. You can still record a custom payment below.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:36px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                            <th>Bill</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Late Fee</th>
                            <th class="text-end">Total Due</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input bill-check" name="payment_ids[]" value="<?= (int)$bill['id'] ?>" <?= in_array((int)$bill['id'], (array)($old['payment_ids'] ?? []), true) ? 'checked' : '' ?>
                                    data-due="<?= (float)$bill['amount'] + (float)$bill['late_fee'] ?>">
                            </td>
                            <td class="fw-bold small"><?= e($bill['payment_code']) ?></td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= ucwords(str_replace('_', ' ', $bill['payment_type'])) ?></span></td>
                            <td class="small text-muted"><?= e($bill['billing_period'] ?? '—') ?></td>
                            <td class="text-end small"><?= formatCurrency((float)$bill['amount']) ?></td>
                            <td class="text-end small"><?= (float)$bill['late_fee'] > 0 ? formatCurrency((float)$bill['late_fee']) : '—' ?></td>
                            <td class="text-end fw-bold"><?= formatCurrency((float)$bill['amount'] + (float)$bill['late_fee']) ?></td>
                            <td>
                                <span class="badge <?= $bill['status'] === 'overdue' ? 'bg-danger bg-opacity-10 text-danger' : ($bill['status'] === 'partially_paid' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-secondary bg-opacity-10 text-secondary') ?>">
                                    <?= ucwords(str_replace('_', ' ', $bill['status'])) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= $bill['due_date'] ? e(formatDate($bill['due_date'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-receipt me-2 text-success"></i>Custom Payment <span class="text-muted fw-normal small">(for bills not auto-generated)</span></div>
        <div class="card-body">
            <?php if ($student && $tenantDuration > 0): ?>
            <div class="bg-info bg-opacity-10 rounded-3 p-3 mb-3">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="small text-muted">Move-In Date</div>
                        <div class="fw-bold"><?= $tenantMoveInDate ? e(formatDate($tenantMoveInDate)) : '—' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Duration (Original)</div>
                        <div class="fw-bold"><?= $tenantDuration ?> month<?= $tenantDuration > 1 ? 's' : '' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Last Due Date</div>
                        <div class="fw-bold"><?= $tenantLastDueDate ? e(formatDate($tenantLastDueDate)) : '—' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Monthly Rent</div>
                        <div class="fw-bold"><?= formatCurrency($roomRent) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Payment Type</label>
                    <select name="custom_type" id="customType" class="form-select">
                        <option value="monthly_rent" <?= ($old['custom_type'] ?? 'monthly_rent') === 'monthly_rent' ? 'selected' : '' ?>>Monthly Rent</option>
                        <option value="advance_payment" <?= ($old['custom_type'] ?? '') === 'advance_payment' ? 'selected' : '' ?>>Advance Payment</option>
                    </select>
                    <?= fe3('custom_type',$errors) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Move-out Date <span class="text-danger">*</span></label>
                    <input type="date" name="custom_move_out_date" id="customMoveOutDate" class="form-control<?= hasErr3('custom_duration',$errors) ?>" required value="<?= e($old['custom_move_out_date'] ?? '') ?>" <?= $tenantMinMoveOutDate ? 'min="' . e($tenantMinMoveOutDate) . '"' : '' ?>>
                    <input type="hidden" name="custom_duration" id="customDuration" value="<?= e($old['custom_duration'] ?? '1') ?>">
                    <div class="form-text">Select move-out date. Min: <?= $tenantMinMoveOutDate ? e(formatDate($tenantMinMoveOutDate)) : 'N/A' ?>. Duration auto-calculated.</div>
                    <?= fe3('custom_duration',$errors) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Custom Amount</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= e(getCurrencySymbol()) ?></span>
                        <input type="number" step="1" min="1" name="custom_amount" id="customAmount" class="form-control<?= hasErr3('custom_amount',$errors) ?>" placeholder="0" value="<?= e($old['custom_amount'] ?? '') ?>" readonly style="background:#f1f5f9;">
                    </div>
                    <?= fe3('custom_amount',$errors) ?>
                    <div class="form-text" id="customAmountHint">Auto-filled based on room rate × duration.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Due Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" name="custom_due_date" id="customDueDate" class="form-control" value="<?= e($old['custom_due_date'] ?? '') ?>" readonly style="background:#f1f5f9;">
                    </div>
                    <div class="form-text">Auto-calculated from today + duration.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="paymentMethodSelect" class="form-select<?= hasErr3('payment_method',$errors) ?>">
                        <option value="cash" <?= ($old['payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="gcash" <?= ($old['payment_method'] ?? '') === 'gcash' ? 'selected' : '' ?>>GCash</option>
                    </select>
                    <?= fe3('payment_method',$errors) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Reference / OR Number <span class="text-danger d-none" id="refRequired">*</span></label>
                    <input type="text" name="payment_reference" id="paymentReference" class="form-control" placeholder="Official receipt or GCash reference no." value="<?= e($old['payment_reference'] ?? '') ?>">
                    <div class="form-text d-none" id="refHint">Required for GCash payments.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="payment_notes" class="form-control" placeholder="Optional notes" value="<?= e($old['payment_notes'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 d-flex flex-wrap align-items-center gap-3">
                        <div class="me-auto small text-muted">Selected bills + custom amount total</div>
                        <div class="fs-5 fw-bold" id="selectedTotal"><?= formatCurrency(0) ?></div>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-money-bill-wave me-1"></i>Record Walk-In Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-user-plus fa-3x text-primary mb-3 d-block"></i>
        <h5 class="fw-bold text-muted mb-1">Select a tenant to get started</h5>
        <p class="text-muted mb-0">Choose a tenant above to record an over-the-counter payment for them.</p>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3"><i class="fas fa-history me-2 text-primary"></i>Recent Walk-In Payments</div>
    <div class="card-body p-0">
        <?php if (empty($walkInPayments)): ?>
        <div class="text-center text-muted py-4 small">No walk-in payments recorded yet.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tenant</th>
                        <th>Bill</th>
                        <th>Type</th>
                        <th>Room</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Paid</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($walkInPayments as $wp): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold small"><?= e($wp['first_name'] . ' ' . $wp['last_name']) ?></div>
                            <div class="text-muted small"><?= e($wp['student_id_number']) ?></div>
                        </td>
                        <td class="fw-bold small"><?= e($wp['payment_code']) ?></td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= ucwords(str_replace('_', ' ', $wp['payment_type'])) ?></span></td>
                        <td class="small"><?= e(trim(($wp['room_number'] ?? '') . ' ' . ($wp['room_name'] ?? '')) ?: 'N/A') ?></td>
                        <td class="text-end small"><?= formatCurrency((float)$wp['amount']) ?></td>
                        <td class="text-end small fw-bold"><?= formatCurrency((float)$wp['amount_paid']) ?></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info"><?= e(strtoupper($wp['payment_method'])) ?></span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success"><?= ucwords(str_replace('_', ' ', $wp['new_status'])) ?></span></td>
                        <td class="small text-muted"><?= e(formatDateTime($wp['created_at'])) ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?= url($baseUrl . '/students/walk-in-payment/delete/' . (int)$wp['payment_id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete walk-in payment <?= e($wp['payment_code']) ?>? This will also remove its receipt and payment history. This action cannot be undone." title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- GCash QR Modal -->
<div class="modal fade" id="paymentAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
            <div class="modal-body text-center p-4">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="mb-3 mt-3"><i class="fas fa-qrcode fa-3x text-primary"></i></div>
                <h5 class="fw-bold mb-1">GCash</h5>
                <p class="text-muted small mb-4">Pay using your GCash app</p>
                <div class="mb-3 d-flex justify-content-center">
                    <div style="background:#fff;border:2px dashed #dee2e6;border-radius:16px;padding:16px;width:220px;text-align:center">
                        <img src="<?= url('/public/images/gcash_qr.jpg') ?>" alt="GCash QR Code" style="width:180px;height:auto;border-radius:8px">
                        <p class="text-muted small mt-2 mb-0">Scan to pay</p>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i> Go back to payment methods
                    </button>
                </div>
                <hr class="my-4">
                <small class="text-muted d-block">After payment, enter the reference number above and submit.</small>
            </div>
        </div>
    </div>
</div>

<style>
.field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;font-weight:500;}
.form-control.is-invalid,.form-select.is-invalid{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.15);}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantSelect = document.getElementById('tenantSelect');

    if (tenantSelect) {
        tenantSelect.addEventListener('change', function() {
            if (this.value !== '') document.getElementById('tenantSelectForm').submit();
        });
    }

    const checkAll = document.getElementById('checkAll');
    const billChecks = document.querySelectorAll('.bill-check');
    const customAmount = document.getElementById('customAmount');
    const customType = document.getElementById('customType');
    const customDuration = document.getElementById('customDuration');
    const customMoveOutDate = document.getElementById('customMoveOutDate');
    const customDueDate = document.getElementById('customDueDate');
    const totalEl = document.getElementById('selectedTotal');
    const currencySymbol = <?= json_encode(getCurrencySymbol()) ?>;
    const monthlyRentAmount = <?= json_encode((float)($roomRent ?? 0)) ?>;
    const advancePaymentAmount = <?= json_encode((float)($roomAdvance ?? 0)) ?>;
    const tenantLastDueDate = <?= json_encode($tenantLastDueDate ?? '') ?>;
    const tenantMoveInDate = <?= json_encode($tenantMoveInDate ?? '') ?>;
    const tenantDuration = <?= json_encode((int)($tenantDuration ?? 0)) ?>;
    const tenantMinMoveOutDate = <?= json_encode($tenantMinMoveOutDate ?? '') ?>;
    let lastTotal = 0;

    function fmt(n) {
        return currencySymbol + Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function calculateDuration() {
        if (!customMoveOutDate || !customDuration) return 1;
        const moveOut = customMoveOutDate.value;
        if (!moveOut) return 1;
        const end = new Date(moveOut + 'T00:00:00');
        let start;
        if (tenantLastDueDate) {
            start = new Date(tenantLastDueDate + 'T00:00:00');
        } else if (tenantMoveInDate) {
            start = new Date(tenantMoveInDate + 'T00:00:00');
            // Move to first day of next month after move-in
            start.setMonth(start.getMonth() + 1);
            start.setDate(1);
        } else {
            start = new Date();
            start.setHours(0, 0, 0, 0);
        }
        if (end <= start) return 1;
        const diffMonths = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
        const daysDiff = end.getDate() - start.getDate();
        const months = Math.max(1, diffMonths + (daysDiff >= 0 ? 0 : -1));
        return Math.min(24, Math.max(1, months));
    }

    function updateDuration() {
        if (!customMoveOutDate || !customDuration) return;
        const months = calculateDuration();
        customDuration.value = months;
    }

    function validateMoveOutDate() {
        if (!customMoveOutDate) return true;
        const moveOut = customMoveOutDate.value;
        if (!moveOut) return true;
        const end = new Date(moveOut + 'T00:00:00');
        
        // Use server-calculated min date if available, otherwise calculate
        let minDate;
        if (tenantMinMoveOutDate) {
            minDate = new Date(tenantMinMoveOutDate + 'T00:00:00');
        } else {
            let start;
            if (tenantLastDueDate) {
                start = new Date(tenantLastDueDate + 'T00:00:00');
            } else if (tenantMoveInDate) {
                start = new Date(tenantMoveInDate + 'T00:00:00');
                start.setMonth(start.getMonth() + 1);
                start.setDate(1);
            } else {
                start = new Date();
                start.setHours(0, 0, 0, 0);
            }
            minDate = start;
        }
        
        if (end < minDate) {
            const minStr = minDate.toISOString().split('T')[0];
            customMoveOutDate.setCustomValidity('Move-out date cannot be before ' + minStr + ' (next due date)');
            return false;
        }
        const diffMonths = (end.getFullYear() - minDate.getFullYear()) * 12 + (end.getMonth() - minDate.getMonth());
        if (diffMonths > 24) {
            customMoveOutDate.setCustomValidity('Maximum duration is 24 months');
            return false;
        }
        customMoveOutDate.setCustomValidity('');
        return true;
    }

    function getDuration() {
        if (!customDuration) return 1;
        var v = parseInt(customDuration.value, 10);
        return (isNaN(v) || v < 1) ? 1 : v;
    }

    function calcDueDate(months) {
        var baseDate;
        if (tenantLastDueDate) {
            // Continue from last due date
            baseDate = new Date(tenantLastDueDate + 'T00:00:00');
        } else if (tenantMoveInDate) {
            // Calculate from move-in date: first due is 1st of next month after move-in
            baseDate = new Date(tenantMoveInDate + 'T00:00:00');
            baseDate.setMonth(baseDate.getMonth() + 1);
            baseDate.setDate(1);
        } else {
            baseDate = new Date();
        }
        baseDate.setMonth(baseDate.getMonth() + months);
        var yyyy = baseDate.getFullYear();
        var mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        var dd = String(baseDate.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function applyAutoAmount() {
        if (!customType || !customAmount) return;
        var base = customType.value === 'advance_payment' ? advancePaymentAmount : monthlyRentAmount;
        var months = getDuration();
        var total = Math.trunc(base * months);
        if (total > 0) customAmount.value = String(total);
        else customAmount.value = '';
        if (customDueDate) customDueDate.value = calcDueDate(months);
        updateSelectedTotal();
    }

    function updateSelectedTotal() {
        var total = 0;
        billChecks.forEach(function(cb) {
            if (cb.checked) total += parseFloat(cb.dataset.due || 0);
        });
        if (customAmount) total += parseFloat(customAmount.value) || 0;
        lastTotal = Math.round(total);
        totalEl.textContent = fmt(lastTotal);
        if (checkAll) {
            checkAll.checked = billChecks.length > 0 && [...billChecks].every(function(cb) { return cb.checked; });
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            billChecks.forEach(function(cb) { cb.checked = checkAll.checked; });
            updateSelectedTotal();
        });
    }
    billChecks.forEach(function(cb) { cb.addEventListener('change', updateSelectedTotal); });
    if (customType) customType.addEventListener('change', applyAutoAmount);
    if (customMoveOutDate) {
        customMoveOutDate.addEventListener('change', function() {
            updateDuration();
            applyAutoAmount();
            validateMoveOutDate();
            // Set min attribute on date input for calendar UI
            if (customMoveOutDate && tenantMinMoveOutDate) {
                customMoveOutDate.min = tenantMinMoveOutDate;
            }
        });
    }
    if (customAmount) {
        customAmount.addEventListener('input', function() {
            updateSelectedTotal();
        });
    }

    applyAutoAmount();
    validateMoveOutDate();

    var payMethod = document.getElementById('paymentMethodSelect');
    var refInput = document.getElementById('paymentReference');
    var refReq = document.getElementById('refRequired');
    var refHint = document.getElementById('refHint');
    function toggleRefRequired() {
        var isGcash = payMethod && payMethod.value === 'gcash';
        if (refInput) refInput.required = isGcash;
        if (refReq) refReq.classList.toggle('d-none', !isGcash);
        if (refHint) refHint.classList.toggle('d-none', !isGcash);
    }
    if (payMethod) payMethod.addEventListener('change', toggleRefRequired);
    toggleRefRequired();

    // Show GCash QR modal when GCash is selected
    var gcashQrModal = new bootstrap.Modal(document.getElementById('paymentAppModal'));
    function showGcashQr() {
        if (payMethod && payMethod.value === 'gcash') gcashQrModal.show();
    }
    if (payMethod) payMethod.addEventListener('change', showGcashQr);
    showGcashQr();
});
</script>
