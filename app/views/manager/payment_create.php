<style>
.payment-methods-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:12px;margin-top:8px}
.payment-method-card{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 12px;border:2px solid #e0e0e0;border-radius:14px;cursor:pointer;transition:all .2s;background:#fff}
.payment-method-card:hover{border-color:#4e73df80;background:#f8f9fc}
.payment-method-card.active{border-color:#4e73df;background:#eef0fb;box-shadow:0 4px 12px rgba(78,115,223,.15)}
.payment-method-card .pm-icon{height:40px;display:flex;align-items:center;justify-content:center}
.payment-method-card span{font-size:.8rem;font-weight:600;color:#333}
</style>

<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Record Payment</h4>
    <a href="<?= url('/manager/tenants') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Tenants
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (empty($activeReservation)): ?>
            <div class="content-card">
                <div class="text-center py-4">
                    <i class="fas fa-home fa-3x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">No Active Reservation</h5>
                    <p class="text-muted mb-3">This tenant has no approved reservation.</p>
                    <a href="<?= url('/manager/tenants') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Tenants
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Reservation Info Card -->
            <div class="content-card mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Tenant & Reservation Details</h6>
                <div class="p-3" style="background: #f0fdf4; border-radius: 10px; border: 1px solid #bbf7d0;">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Tenant</small>
                            <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">ID Number</small>
                            <strong><?= e($student['student_id_number'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Room</small>
                            <strong><?= e($activeReservation['room_number'] ?? '') ?> - <?= e($activeReservation['room_name'] ?? '') ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Monthly Rent</small>
                            <strong class="text-success"><?= formatCurrency((float)($activeReservation['monthly_rent'] ?? 0)) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="content-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2 text-success"></i>Payment Information</h6>
                <form method="POST" action="<?= url('/manager/payment/create') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">
                    <?php if (!empty($prefillDueDate)): ?>
                        <input type="hidden" name="due_date" value="<?= e($prefillDueDate) ?>">
                    <?php endif; ?>
                    <?php if (!empty($targetPayment)): ?>
                        <input type="hidden" name="payment_id" value="<?= (int)$targetPayment['id'] ?>">
                        <input type="hidden" name="payment_type" value="<?= e($targetPayment['payment_type']) ?>">
                    <?php endif; ?>

                    <!-- Outstanding Bills (when no target payment) -->
                    <?php if (empty($targetPayment) && !empty($outstandingBills)): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-file-invoice me-2 text-primary"></i>Outstanding Bills</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px"><input type="checkbox" class="form-check-input" id="checkAllBills"></th>
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
                                        <?php foreach ($outstandingBills as $bill): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input bill-check" name="payment_ids[]" value="<?= (int)$bill['id'] ?>"
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
                    <?php endif; ?>

                    <!-- Custom Payment (for bills not auto-generated) -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-receipt me-2 text-success"></i>Custom Payment <span class="text-muted fw-normal small">(for bills not auto-generated)</span></div>
                        <div class="card-body">
                            <?php if ($activeReservation && !empty($activeReservation['move_in_date'])): ?>
                            <div class="bg-info bg-opacity-10 rounded-3 p-3 mb-3">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="small text-muted">Move-In Date</div>
                                        <div class="fw-bold"><?= formatDate($activeReservation['move_in_date']) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Monthly Rent</div>
                                        <div class="fw-bold"><?= formatCurrency((float)($activeReservation['monthly_rent'] ?? 0)) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Advance Payment</div>
                                        <div class="fw-bold"><?= formatCurrency((float)($activeReservation['advance_payment'] ?? 0)) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Last Due Date</div>
                                        <div class="fw-bold"><?= !empty($lastDueDate) ? formatDate($lastDueDate) : '—' ?></div>
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
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="1" min="1" max="24" name="custom_duration" id="customDuration" class="form-control" placeholder="1" value="<?= e($old['custom_duration'] ?? '1') ?>">
                                        <span class="input-group-text">mo.</span>
                                    </div>
                                    <div class="form-text">Number of months to pay.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Custom Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?= e(getCurrencySymbol()) ?></span>
                                        <input type="number" step="1" min="1" name="custom_amount" id="customAmount" class="form-control" placeholder="0" value="<?= e($old['custom_amount'] ?? '') ?>" readonly style="background:#f1f5f9;">
                                    </div>
                                    <div class="form-text" id="customAmountHint">Auto-filled based on room rate × duration.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Due Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="custom_due_date" id="customDueDate" class="form-control" value="<?= e($old['custom_due_date'] ?? ($prefillDueDate ?? '')) ?>" readonly style="background:#f1f5f9;">
                                    </div>
                                    <div class="form-text">Auto-calculated from last due date + duration.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3"><i class="fas fa-hand-holding-usd me-2 text-success"></i>Payment Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-select" id="paymentMethodSelect">
                                        <option value="cash" <?= ($old['payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' ?>>Cash</option>
                                        <option value="gcash" <?= ($old['payment_method'] ?? '') === 'gcash' ? 'selected' : '' ?>>GCash</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Reference / OR Number <span class="text-danger d-none" id="refRequired">*</span></label>
                                    <input type="text" name="payment_reference" id="paymentReference" class="form-control" placeholder="Official receipt or GCash reference no." value="<?= e($old['payment_reference'] ?? '') ?>">
                                    <div class="form-text d-none" id="refHint">Required for GCash payments.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <input type="text" name="payment_notes" class="form-control" placeholder="Optional notes" value="<?= e($old['payment_notes'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Proof of Payment <span class="text-danger" id="proofRequiredStar">*</span></label>
                                    <input type="file" class="form-control" name="proof_of_payment" id="proofOfPayment" accept="image/*,.pdf">
                                    <small class="text-muted">Upload receipt screenshot (JPG, PNG, PDF. Max 5MB). Required for GCash.</small>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light rounded-3 p-3 d-flex flex-wrap align-items-center gap-3">
                                        <div class="me-auto small text-muted">Selected bills + custom amount total</div>
                                        <div class="fs-5 fw-bold" id="selectedTotal"><?= formatCurrency(0) ?></div>
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="fas fa-money-bill-wave me-1"></i> Record Payment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="<?= url('/manager/tenants') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment App Modal -->
<div class="modal fade" id="paymentAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
            <div class="modal-body text-center p-4">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="modalAppIcon" class="mb-3 mt-3"></div>
                <h5 class="fw-bold mb-1" id="modalAppTitle">GCash</h5>
                <p class="text-muted small mb-4" id="modalAppDesc">Pay using your GCash app</p>
                <div class="mb-3 d-flex justify-content-center">
                    <div id="qrCodeContainer" style="background:#fff;border:2px dashed #dee2e6;border-radius:16px;padding:16px;width:220px;text-align:center">
                        <img src="<?= url('/public/images/gcash_qr.jpg') ?>" alt="QR Code" style="width:180px;height:auto;border-radius:8px">
                        <p class="text-muted small mt-2 mb-0">Scan to pay</p>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="#" id="openAppBtn" target="_blank" class="btn btn-lg fw-semibold text-white" style="border-radius:12px">
                        <i class="fas fa-external-link-alt me-2"></i> Open App
                    </a>
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

<script>
var CSYM = <?= json_encode(getCurrencySymbol()) ?>;
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('paymentAppModal'));
    var appModalEl = document.getElementById('paymentAppModal');
    appModalEl.addEventListener('hidden.bs.modal', function() {
        var firstInput = document.getElementById('customAmount') || document.getElementById('paymentAmount');
        if (firstInput) firstInput.focus();
    });

    // Proof of payment required for GCash
    var proofInput = document.getElementById('proofOfPayment');
    var proofStar = document.getElementById('proofRequiredStar');
    function syncProofRequired() {
        var payMethod = document.getElementById('paymentMethodSelect');
        var isGcash = payMethod && payMethod.value === 'gcash';
        if (proofInput) {
            if (isGcash) {
                proofInput.setAttribute('required', 'required');
                if (proofStar) proofStar.style.display = '';
            } else {
                proofInput.removeAttribute('required');
                proofInput.value = '';
                if (proofStar) proofStar.style.display = 'none';
            }
        }
    }
    var payMethodSelect = document.getElementById('paymentMethodSelect');
    if (payMethodSelect) payMethodSelect.addEventListener('change', syncProofRequired);
    syncProofRequired();

    // Custom payment logic
    var customType = document.getElementById('customType');
    var customDuration = document.getElementById('customDuration');
    var customAmount = document.getElementById('customAmount');
    var customDueDate = document.getElementById('customDueDate');
    var totalEl = document.getElementById('selectedTotal');
    var checkAllBills = document.getElementById('checkAllBills');
    var billChecks = document.querySelectorAll('.bill-check');
    var monthlyRentAmount = <?= json_encode((float)($activeReservation['monthly_rent'] ?? 0)) ?>;
    var advancePaymentAmount = <?= json_encode((float)($activeReservation['advance_payment'] ?? 0)) ?>;
    var lastDueDate = <?= json_encode(!empty($lastDueDate) ? $lastDueDate : '') ?>;
    var currencySymbol = <?= json_encode(getCurrencySymbol()) ?>;
    let lastTotal = 0;

    function fmt(n) {
        return currencySymbol + Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function getDuration() {
        if (!customDuration) return 1;
        var v = parseInt(customDuration.value, 10);
        return (isNaN(v) || v < 1) ? 1 : v;
    }

    function calcDueDate(months) {
        var baseDate;
        if (lastDueDate) {
            baseDate = new Date(lastDueDate + 'T00:00:00');
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
        if (checkAllBills) {
            checkAllBills.checked = billChecks.length > 0 && [...billChecks].every(function(cb) { return cb.checked; });
        }
    }

    if (checkAllBills) {
        checkAllBills.addEventListener('change', function() {
            billChecks.forEach(function(cb) { cb.checked = checkAllBills.checked; });
            updateSelectedTotal();
        });
    }
    billChecks.forEach(function(cb) { cb.addEventListener('change', updateSelectedTotal); });
    if (customType) customType.addEventListener('change', applyAutoAmount);
    if (customDuration) customDuration.addEventListener('input', applyAutoAmount);
    if (customAmount) {
        customAmount.addEventListener('input', function() {
            updateSelectedTotal();
        });
    }

    // Reference required for GCash
    var refInput = document.getElementById('paymentReference');
    var refReq = document.getElementById('refRequired');
    var refHint = document.getElementById('refHint');
    function toggleRefRequired() {
        var isGcash = payMethodSelect && payMethodSelect.value === 'gcash';
        if (refInput) refInput.required = isGcash;
        if (refReq) refReq.classList.toggle('d-none', !isGcash);
        if (refHint) refHint.classList.toggle('d-none', !isGcash);
    }
    if (payMethodSelect) payMethodSelect.addEventListener('change', toggleRefRequired);
    toggleRefRequired();

    applyAutoAmount();
    updateSelectedTotal();
});
</script>