<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $siteKey = $siteKey ?? ''; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
.lock-countdown{max-width:340px;margin:0 auto 20px;padding:22px 18px;border:1.5px dashed rgba(220,53,69,.35);border-radius:16px;background:linear-gradient(135deg,#fff5f5,#ffffff);text-align:center}
.lock-ring{position:relative;width:110px;height:110px;margin:0 auto 12px}
.lock-ring svg{transform:rotate(-90deg)}
.lock-ring-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:44px;font-weight:800;color:#dc3545;line-height:1}
.lock-ring-num.lock-pop{animation:lockPop .9s ease}
@keyframes lockPop{0%{transform:scale(1.35);opacity:.4}100%{transform:scale(1);opacity:1}}
.lock-txt{font-weight:700;color:#b91c1c;margin-bottom:4px;font-size:14px}
.lock-sub{font-size:12px;color:#9ca3af;margin:0}
</style>

<section class="auth-section" style="min-height:calc(100vh - 72px);display:flex;align-items:center;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);padding:2rem 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="auth-card">
                    <div class="row g-0">
                        <!-- Left Panel — Branding -->
                        <div class="col-lg-5 d-none d-lg-flex">
                            <div class="auth-visual">
                                <div class="auth-visual-shapes" aria-hidden="true">
                                    <div class="auth-shape auth-shape-1"></div>
                                    <div class="auth-shape auth-shape-2"></div>
                                    <div class="auth-shape auth-shape-3"></div>
                                </div>
                                <div class="auth-visual-content">
                                    <div class="auth-visual-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h2>Welcome Back</h2>
                                    <p>Sign in to manage your boarding house account, reservations, and more.</p>
                                    <div class="auth-visual-features">
                                        <div class="auth-feature">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Manage Reservations</span>
                                        </div>
                                        <div class="auth-feature">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Track Payments</span>
                                        </div>
                                        <div class="auth-feature">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Submit Maintenance Requests</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel — Form -->
                        <div class="col-lg-7">
                            <div class="auth-form-panel">
                                <div class="auth-form-header">
                                    <a href="<?= url('/') ?>" class="auth-form-brand">
                                        <span class="auth-form-brand-icon"><i class="fas fa-building"></i></span>
                                        <span><?= e(getSiteName()) ?></span>
                                    </a>
                                    <a href="<?= url('/') ?>" class="auth-close-btn" title="Close" aria-label="Close">&times;</a>
                                </div>

                                <div class="auth-form-body">
                                    <h3 class="auth-form-title">Sign In</h3>
                                    <p class="auth-form-subtitle">Enter your credentials to access your account</p>
                                    

                                    <?php if (!empty($lockSeconds)): ?>
                                        <div class="lock-countdown" id="lockCountdown">
                                            <div class="lock-ring">
                                                <svg width="110" height="110" viewBox="0 0 110 110">
                                                    <circle cx="55" cy="55" r="50" fill="none" stroke="#fecaca" stroke-width="8"></circle>
                                                    <circle id="lockRingBar" cx="55" cy="55" r="50" fill="none" stroke="#dc3545" stroke-width="8" stroke-linecap="round" stroke-dasharray="314.16" stroke-dashoffset="0"></circle>
                                                </svg>
                                                <div class="lock-ring-num" id="lockNumber"><?= (int)$lockSeconds ?></div>
                                            </div>
                                            <p class="lock-txt"><i class="fas fa-lock me-1"></i><?= e($lockMessage ?: 'Account locked. Too many failed attempts.') ?></p>
                                            <p class="lock-sub">You can try signing in again after the countdown ends.</p>
                                            <span id="lockSecondsData" data-seconds="<?= (int)$lockSeconds ?>" hidden></span>
                                        </div>
                                    <?php endif; ?>

                                    <form action="<?= url('/login') ?>" method="POST" id="loginForm">
                                        <?= csrf_field() ?>
                                        <div class="auth-field">
                                            <label class="auth-label">Email Address</label>
                                            <div class="auth-input-wrap">
                                                <i class="fas fa-envelope"></i>
                                                <input type="email" name="email" class="auth-input" placeholder="example@gmail.com" required autofocus pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                                            </div>
                                        </div>
                                        <div class="auth-field">
                                            <label class="auth-label">Password</label>
                                            <div class="auth-input-wrap">
                                                <i class="fas fa-lock"></i>
                                                <input type="password" name="password" class="auth-input" placeholder="Enter your password" required>
                                                <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="remember" name="remember" style="border-radius:4px;">
                                                <label class="form-check-label small" for="remember" style="color:var(--gray-600);font-weight:500;">Remember Me</label>
                                            </div>
                                            <a href="<?= url('/forgot-password') ?>" class="auth-link-sm">Forgot Password?</a>
                                        </div>
                                        <div class="mb-4 d-flex justify-content-center">
                                            <div class="g-recaptcha" data-sitekey="<?= e($siteKey ?? '') ?>"></div>
                                        </div>
                                        <button type="submit" class="auth-submit-btn">
                                            <i class="fas fa-sign-in-alt"></i>
                                            <span>Sign In</span>
                                        </button>
                                    </form>

                                    <div class="auth-divider">
                                        <span>New to <?= e(getSiteName()) ?>?</span>
                                    </div>

                                    <a href="<?= url('/register') ?>" class="auth-alt-btn">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Create an Account</span>
                                    </a>
                                </div>

                                <div class="auth-form-footer">
                                    <a href="<?= url('/') ?>"><i class="fas fa-arrow-left"></i> Back to Home</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($lockSeconds)): ?>
<script>
(function() {
    var data = document.getElementById('lockSecondsData');
    var total = parseInt(data.getAttribute('data-seconds'), 10) || 3;
    var remaining = total;
    var form = document.getElementById('loginForm');
    var numEl = document.getElementById('lockNumber');
    var ringEl = document.getElementById('lockRingBar');
    var countdownEl = document.getElementById('lockCountdown');
    var circumference = 2 * Math.PI * 50;
    var finished = false;

    function setFields(locked) {
        if (!form) return;
        Array.prototype.forEach.call(form.querySelectorAll('input,select,textarea,button'), function(field) {
            field.disabled = locked;
        });
    }

    function render() {
        if (finished) return;
        numEl.textContent = remaining;
        ringEl.style.strokeDashoffset = circumference * (1 - remaining / total);
        numEl.classList.remove('lock-pop');
        void numEl.offsetWidth;
        numEl.classList.add('lock-pop');
        if (remaining <= 0) {
            finished = true;
            clearInterval(timer);
            setFields(false);
            countdownEl.innerHTML =
                '<p class="lock-txt" style="color:#16a34a;margin:0;"><i class="fas fa-check-circle me-1"></i>Countdown finished. You can try signing in again.</p>';
            ringEl.style.stroke = '#22c55e';
            setTimeout(function() {
                countdownEl.style.transition = 'opacity .4s ease';
                countdownEl.style.opacity = '0';
                setTimeout(function() { countdownEl.remove(); }, 400);
            }, 2000);
        }
    }

    setFields(true);
    render();
    var timer = setInterval(function() {
        remaining = remaining - 1;
        render();
    }, 1000);
})();
</script>
<?php endif; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
