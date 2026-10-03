<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $email = $email ?? ''; $remainingSeconds = (int)($remainingSeconds ?? 0); $resendCooldown = (int)($resendCooldown ?? 0); ?>

<section class="d-flex align-items-center justify-content-center" style="min-height:80vh;background:linear-gradient(135deg,#f8fafc,#e2e8f0);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg" style="border-radius:16px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <a href="<?= url('/') ?>" class="text-decoration-none">
                                <i class="fas fa-home fa-2x mb-2" style="color:#8fa61b;"></i>
                            </a>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:linear-gradient(135deg,#8fa61b20,#8fa61b0a);color:#8fa61b;">
                                <i class="fas fa-key fa-2x"></i>
                            </div>
                            <h3 class="fw-bold" style="color:#1e293b;">Password Change Verification</h3>
                            <p class="text-muted mb-1">We sent a 6-digit verification code to your email.</p>
                            <p class="text-muted mb-0">
                                Enter the code below to confirm your password change.
                                <?php if ($email !== ''): ?>
                                    <br><span class="fw-semibold" style="color:#475569;"><?= e($email) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <form action="<?= url('/verify-password-change') ?>" method="POST" id="verifyForm" autocomplete="off">
                            <?= csrf_field() ?>
                            <div class="mb-4">
                                <label class="form-label fw-medium">6-Digit Verification Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag text-muted"></i></span>
                                    <input type="text" name="code" id="codeInput" class="form-control form-control-lg border-start-0" placeholder="123456" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" title="Enter the exact 6-digit code from your email." autofocus style="letter-spacing:6px;font-size:1.35rem;text-align:center;">
                                </div>
                            </div>

                            <div class="mb-4 text-center">
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background:#f1f5f9;color:#475569;font-weight:600;">
                                    <i class="fas fa-clock text-muted"></i>
                                    <span>Code expires in</span>
                                    <span id="countdown" class="font-monospace" style="color:#8fa61b;"><?= gmdate('i:s', max(0, $remainingSeconds)) ?></span>
                                </div>
                                <?php $remainingAttempts = (int)($remainingAttempts ?? 3); ?>
                                <div class="small text-muted mt-2">
                                    Code expires in <strong>5 minutes</strong>. You have
                                    <strong id="attemptsLeft" style="color:#8fa61b;"><?= $remainingAttempts ?></strong>
                                    attempt<?= $remainingAttempts === 1 ? '' : 's' ?> left. After 3 incorrect attempts you will be returned to the password change page.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-semibold" style="background:#8fa61b;border-color:#8fa61b;">
                                <i class="fas fa-unlock-alt me-2"></i> Verify &amp; Change Password
                            </button>
                        </form>

                        <form action="<?= url('/verify-password-change/resend') ?>" method="POST" id="resendForm" class="mt-3">
                            <?= csrf_field() ?>
                            <button type="submit" id="resendBtn" class="btn btn-outline-secondary btn-lg w-100 py-2 fw-semibold">
                                <i class="fas fa-redo-alt me-2"></i> Resend Code
                            </button>
                            <div class="text-center mt-2 small text-muted d-none" id="resendHint">
                                You can request a new code in <span class="font-monospace" id="resendCount"><?= gmdate('i:s', max(0, $resendCooldown)) ?></span>
                            </div>
                        </form>

                        <div class="alert alert-info small py-2 px-3 mt-4 mb-0" role="alert" style="border-radius:10px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Check the spam/junk folder if you do not see the email, then click <strong>Resend Code</strong>.
                        </div>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Changed your mind? <a href="<?= url('/forgot-password') ?>" class="text-primary fw-semibold text-decoration-none">Back to Change Password</a></p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= url('/') ?>" class="text-muted text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var remaining = Math.max(0, <?= (int)$remainingSeconds ?>);
    var cooldown = Math.max(0, <?= (int)$resendCooldown ?>);
    var countdownEl = document.getElementById('countdown');
    var resendBtn = document.getElementById('resendBtn');
    var resendHint = document.getElementById('resendHint');
    var resendCount = document.getElementById('resendCount');

    function fmt(sec) {
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        if (remaining > 0) {
            remaining--;
        }
        if (remaining <= 0) {
            countdownEl.textContent = '00:00';
            clearInterval(timer);
        } else {
            countdownEl.textContent = fmt(remaining);
        }

        if (cooldown > 0) {
            cooldown--;
            resendCount.textContent = fmt(cooldown);
        }
        if (cooldown <= 0) {
            resendHint.classList.add('d-none');
            resendBtn.removeAttribute('disabled');
            resendBtn.classList.remove('disabled');
        } else {
            resendHint.classList.remove('d-none');
            resendBtn.setAttribute('disabled', 'disabled');
            resendBtn.classList.add('disabled');
        }
    }

    var timer = setInterval(tick, 1000);
    tick();

    var codeInput = document.getElementById('codeInput');
    codeInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
})();
</script>