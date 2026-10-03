<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $siteKey = $siteKey ?? ''; ?>

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
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h3 class="fw-bold" style="color:#1e293b;">Security Check</h3>
                            <p class="text-muted mb-0">Your email is verified. Complete the reCAPTCHA to finish creating your account.</p>
                        </div>

                        <form action="<?= url('/verify-email-captcha') ?>" method="POST" id="captchaForm" autocomplete="off">
                            <?= csrf_field() ?>

                            <div class="mb-4 d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="<?= e($siteKey) ?>"></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-semibold" style="background:#8fa61b;border-color:#8fa61b;">
                                <i class="fas fa-check-circle me-2"></i> Finish & Continue
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Want to login now? <a href="<?= url('/login') ?>" class="text-primary fw-semibold text-decoration-none">Go to Login</a></p>
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

<script src="https://www.google.com/recaptcha/api.js" async defer></script>