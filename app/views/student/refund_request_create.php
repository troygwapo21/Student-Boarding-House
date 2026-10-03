<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Submit Refund Request</h4>
    <a href="<?= url('/student/refund-requests') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<?php
$defaultType = $monthlyAvailable >= 1 ? 'monthly' : ($advanceAvailable >= 1 ? 'advance' : ($allAvailable >= 1 ? 'all' : ''));
$hasAny = $monthlyAvailable >= 1 || $advanceAvailable >= 1 || $allAvailable >= 1;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <?php if (!empty($activeReservation)): ?>
            <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-door-open"></i>
                <span>Requesting for room <strong><?= e($activeReservation['room_name'] . ' ' . $activeReservation['room_number']) ?></strong>
                    (Reservation: <?= e($activeReservation['reservation_code']) ?>).</span>
            </div>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="font-size:.85rem;">
                <i class="fas fa-info-circle"></i>
                <span><strong>Monthly Rent Refund Rule:</strong> <strong>Monthly Refund</strong> and <strong>All Payments Refund</strong> are only available to tenants who have paid <strong>more than 2 months</strong> of rent. A <strong>1-month penalty</strong> is deducted from the monthly portion.</span>
            </div>
            <?php endif; ?>

            <?php if (!$hasAny): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span>You currently have no eligible refundable amounts. Refunds can only be requested for monthly rent or advance payments you have made that have not already been refunded.</span>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/student/refund-request/create') ?>" id="refundForm">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Refund Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="refund_type" id="refundType" <?= $hasAny ? '' : 'disabled' ?>>
                        <option value="monthly" <?= $monthlyAvailable < 1 ? 'disabled' : '' ?> <?= $defaultType === 'monthly' ? 'selected' : '' ?>>Monthly Refund</option>
                        <option value="advance" <?= $advanceAvailable < 1 ? 'disabled' : '' ?> <?= $defaultType === 'advance' ? 'selected' : '' ?>>Advance Refund</option>
                        <option value="all" <?= $allAvailable < 1 ? 'disabled' : '' ?> <?= $defaultType === 'all' ? 'selected' : '' ?>>All Payments Refund</option>
                    </select>
                    <div class="form-text" id="refundTypeHelp"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Refund Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><?= e(getCurrencySymbol()) ?></span>
                        <input type="number" class="form-control" name="amount" id="refundAmount" min="1" step="1" placeholder="0" required readonly tabindex="-1" onkeydown="event.preventDefault();" onpaste="return false;">
                        <span class="input-group-text bg-white" id="amountSuffix"></span>
                    </div>
                    <div class="form-text" id="amountHelp"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-mobile-screen me-1 text-success"></i> GCash Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" name="gcash_number" id="gcashNumber" placeholder="09*********" required inputmode="tel" maxlength="11" pattern="09[0-9]{9}" autocomplete="tel" title="Please enter a valid 11-digit Philippine mobile number starting with 09.">
                    </div>
                    <div class="form-text">Your GCash mobile number where the refund will be sent.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" rows="6" placeholder="Explain why you are requesting this refund (e.g. security deposit refund after move-out, advance overpayment)..." required maxlength="1000"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/student/refund-requests') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" <?= $hasAny ? '' : 'disabled' ?>><i class="fas fa-paper-plane me-1"></i> Submit Refund Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var monthlyRent = <?= json_encode((float)$monthlyRent) ?>;
    var monthlyPaidNet = <?= json_encode((float)$monthlyPaidNet) ?>;
    var monthlyAvailable = <?= json_encode((float)$monthlyAvailable) ?>;
    var advancePaidNet = <?= json_encode((float)$advanceTotal) ?>;
    var advanceAvailable = <?= json_encode((float)$advanceAvailable) ?>;
    var allAvailable = <?= json_encode((float)$allAvailable) ?>;
    var pendingMonthly = <?= json_encode((float)$pendingMonthly) ?>;
    var pendingAdvance = <?= json_encode((float)$pendingAdvance) ?>;
    var pendingAll = <?= json_encode((float)$pendingAll) ?>;
    var monthsPaid = <?= json_encode((int)$monthsPaid) ?>;

    var typeEl = document.getElementById('refundType');
    var amountEl = document.getElementById('refundAmount');
    var suffixEl = document.getElementById('amountSuffix');
    var helpEl = document.getElementById('refundTypeHelp');
    var amountHelpEl = document.getElementById('amountHelp');
    var submitBtn = document.getElementById('submitBtn');

    function money(n) {
        var v = Number(n) || 0;
        var sym = <?= json_encode(e(getCurrencySymbol())) ?>;
        if (Math.abs(v - Math.round(v)) < 0.005) {
            return sym + Math.round(v).toLocaleString('en-PH');
        }
        return sym + v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function dataFor(type) {
        if (type === 'advance') return { available: advanceAvailable, pending: pendingAdvance };
        if (type === 'all') return { available: allAvailable, pending: pendingAll };
        return { available: monthlyAvailable, pending: pendingMonthly };
    }

    function update() {
        var type = typeEl.value;
        var d = dataFor(type);
        var avail = d.available;

        amountEl.setAttribute('min', '1');
        if (avail >= 1) {
            amountEl.setAttribute('max', String(Math.round(avail)));
        } else {
            amountEl.removeAttribute('max');
        }

        if (avail >= 1) {
            amountEl.value = String(Math.round(avail));
            submitBtn.disabled = false;
        } else {
            amountEl.value = '';
            submitBtn.disabled = true;
        }

        amountEl.setCustomValidity('');
        suffixEl.textContent = money(Math.max(0, avail));

        if (type === 'advance') {
            amountHelpEl.textContent = 'Maximum refundable: ' + money(avail) + '. This amount is auto-filled and cannot be changed.';
            helpEl.innerHTML = 'Advance refund = the advance you have paid that has not yet been refunded (<strong>' + money(advancePaidNet) + '</strong>).'
                + (d.pending > 0 ? ' Pending requests already cover <strong>' + money(d.pending) + '</strong>.' : '');
        } else if (type === 'all') {
            var eligible = monthsPaid > 2;
            if (eligible) {
                var totalPaid = monthlyPaidNet;
                var penalty = monthlyRent;
                var monthlyNet = monthlyAvailable;
                var advNet = advanceAvailable;
                var totalPending = pendingAll + pendingMonthly + pendingAdvance;
                amountHelpEl.textContent = 'Monthly: (' + monthsPaid + ' months × ' + money(monthlyRent) + ' = ' + money(totalPaid) + ') − 1-month penalty (' + money(penalty) + ') = ' + money(monthlyPaidNet - monthlyRent) + '. Advance: ' + money(advancePaidNet) + '. Total before pending: ' + money(monthlyPaidNet - monthlyRent + advancePaidNet) + (totalPending > 0 ? ' − pending (' + money(totalPending) + ')' : '') + ' = ' + money(avail) + '. This amount is auto-filled and cannot be changed.';
                if (totalPending > 0) {
                    helpEl.innerHTML = '<strong>Eligible.</strong><br>'
                        + '<strong>Monthly portion:</strong> ' + monthsPaid + ' months (<strong>' + money(totalPaid) + '</strong>) − 1-month penalty (<strong>' + money(penalty) + '</strong>) = <strong>' + money(monthlyPaidNet - monthlyRent) + '</strong>. '
                        + 'Advance portion: <strong>' + money(advancePaidNet) + '</strong>.<br>'
                        + 'Total before pending: <strong>' + money(monthlyPaidNet - monthlyRent + advancePaidNet) + '</strong> '
                        + '− pending requests (<strong>' + money(totalPending) + '</strong>) = <strong>' + money(avail) + '</strong>.';
                } else {
                    helpEl.innerHTML = '<strong>Eligible.</strong><br>'
                        + '<strong>Monthly portion:</strong> ' + monthsPaid + ' months (<strong>' + money(totalPaid) + '</strong>) − 1-month penalty (<strong>' + money(penalty) + '</strong>) = <strong>' + money(monthlyPaidNet - monthlyRent) + '</strong>. '
                        + 'Advance portion: <strong>' + money(advancePaidNet) + '</strong>.<br>'
                        + '<strong>Total refund: ' + money(avail) + '</strong>.';
                }
            } else {
                amountHelpEl.textContent = 'All Payments Refund requires more than 2 months of paid rent.';
                helpEl.innerHTML = '<strong>Not eligible.</strong> All Payments Refund requires more than 2 months of paid rent. You have paid <strong>' + monthsPaid + ' month' + (monthsPaid !== 1 ? 's' : '') + '</strong> of rent (<strong>' + money(monthlyPaidNet) + '</strong>). '
                    + 'You may still request an <strong>Advance Refund</strong> separately if you have paid an advance payment.';
            }
        } else {
            var eligible = monthsPaid > 2;
            if (eligible) {
                var totalPaid = monthlyPaidNet;
                var penalty = monthlyRent;
                var totalPending = pendingMonthly + pendingAll;
                if (totalPending > 0) {
                    amountHelpEl.textContent = 'Calculation: ' + monthsPaid + ' months × ' + money(monthlyRent) + ' = ' + money(totalPaid) + ' − 1-month penalty (' + money(penalty) + ') − pending (' + money(totalPending) + ') = ' + money(avail) + '. This amount is auto-filled and cannot be changed.';
                    helpEl.innerHTML = '<strong>Eligible.</strong><br>'
                        + 'Total rent paid: <strong>' + money(totalPaid) + '</strong> (' + monthsPaid + ' months). '
                        + '1-month penalty deducted: <strong>' + money(penalty) + '</strong>.<br>'
                        + 'Pending requests: <strong>' + money(totalPending) + '</strong>. '
                        + 'Refund amount: <strong>' + money(avail) + '</strong>.';
                } else {
                    amountHelpEl.textContent = 'Calculation: ' + monthsPaid + ' months × ' + money(monthlyRent) + ' = ' + money(totalPaid) + ' − 1-month penalty (' + money(penalty) + ') = ' + money(avail) + '. This amount is auto-filled and cannot be changed.';
                    helpEl.innerHTML = '<strong>Eligible.</strong><br>'
                        + 'Total rent paid: <strong>' + money(totalPaid) + '</strong> (' + monthsPaid + ' months). '
                        + '1-month penalty deducted: <strong>' + money(penalty) + '</strong>.<br>'
                        + 'Refund amount: <strong>' + money(avail) + '</strong>.';
                }
            } else {
                amountHelpEl.textContent = 'You are not yet eligible for a monthly rent refund.';
                helpEl.innerHTML = '<strong>Not eligible.</strong> You have paid <strong>' + monthsPaid + ' month' + (monthsPaid !== 1 ? 's' : '') + '</strong> of rent (<strong>' + money(monthlyPaidNet) + '</strong>). '
                    + 'Monthly rent refund requires <strong>more than 2 months</strong> of paid rent (at least 3 months). A 1-month penalty is deducted from the refundable amount.';
            }
        }
    }

    amountEl.addEventListener('input', function() {
        var avail = dataFor(typeEl.value).available;
        var v = parseInt(amountEl.value, 10);
        if (!isNaN(v) && avail >= 1) {
            amountEl.setCustomValidity(v > Math.round(avail) ? 'Amount exceeds the maximum refundable amount of ' + money(avail) + '.' : '');
        } else {
            amountEl.setCustomValidity('');
        }
    });

    var gcashEl = document.getElementById('gcashNumber');
    if (gcashEl) {
        gcashEl.addEventListener('input', function() {
            var digits = this.value.replace(/\D/g, '').slice(0, 11);
            if (digits !== this.value) this.value = digits;
            if (this.value.length > 0 && !/^09\d{9}$/.test(this.value)) {
                this.setCustomValidity('Please enter a valid 11-digit Philippine mobile number starting with 09.');
            } else {
                this.setCustomValidity('');
            }
        });
        gcashEl.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 11);
            this.value = text;
            this.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    typeEl.addEventListener('change', update);
    update();
})();
</script>
