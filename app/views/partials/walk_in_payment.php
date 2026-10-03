<?php $old = $old ?? []; $errors = $errors ?? []; $baseUrl = $baseUrl ?? '/admin'; $roomRent = $roomRent ?? 0; $roomAdvance = $roomAdvance ?? 0; ?>
<?php if (!function_exists('fe2')) {
    function fe2(string $f, array $e): string { $msg = $e[$f] ?? ''; return $msg ? '<div class="field-error"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($msg) . '</div>' : ''; }
}
if (!function_exists('hasErr2')) {
    function hasErr2(string $f, array $e): string { return !empty($e[$f]) ? ' is-invalid' : ''; }
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
                    <div class="fs-5 fw-bold" style="color:#059669;"><?= $roomRent > 0 ? formatCurrency($roomRent + $roomAdvance) : 'N/A' ?></div>
                    <div class="small text-muted">Rent + Advance</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($bills)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
        <h5 class="fw-bold text-muted mb-1">No Outstanding Bills</h5>
        <p class="text-muted mb-3">This tenant has no pending, overdue, or partially paid payments.</p>
    <a href="<?= url($baseUrl . '/students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Students</a>
    </div>
</div>
<?php else: ?>

<form action="<?= url($baseUrl . '/student/walk-in-payment/' . (int)$student['id']) ?>" method="POST" id="walkInPaymentForm" novalidate>
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-file-invoice me-2 text-primary"></i>Outstanding Bills</div>
        <div class="card-body p-0">
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
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-hand-holding-usd me-2 text-success"></i>Payment Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select<?= hasErr2('payment_method',$errors) ?>">
                        <option value="cash" <?= ($old['payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="gcash" <?= ($old['payment_method'] ?? '') === 'gcash' ? 'selected' : '' ?>>GCash</option>
                    </select>
                    <?= fe2('payment_method',$errors) ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Reference / OR Number</label>
                    <input type="text" name="payment_reference" class="form-control" placeholder="Official receipt or GCash reference no." value="<?= e($old['payment_reference'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="payment_notes" class="form-control" placeholder="Optional notes" value="<?= e($old['payment_notes'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 d-flex flex-wrap align-items-center gap-3">
                        <div class="me-auto small text-muted">Total of selected bills</div>
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

<style>
.field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;font-weight:500;}
.form-control.is-invalid,.form-select.is-invalid{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.15);}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const billChecks = document.querySelectorAll('.bill-check');
    const totalEl = document.getElementById('selectedTotal');
    const currencySymbol = <?= json_encode(getCurrencySymbol()) ?>;
    let lastSelectedTotal = 0;

    function fmt(n) {
        return currencySymbol + Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function updateSelectedTotal() {
        let total = 0;
        billChecks.forEach(function(cb) {
            if (cb.checked) total += parseFloat(cb.dataset.due || 0);
        });
        lastSelectedTotal = Math.round(total);
        totalEl.textContent = fmt(lastSelectedTotal);
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

    updateSelectedTotal();
});
</script>
<?php endif; ?>
