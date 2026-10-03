(function() {
    'use strict';

    function showToast(type, title, message) {
        var icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        var toast = document.createElement('div');
        toast.className = 'toast-custom toast-' + type;
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:12px;color:#fff;font-family:inherit;font-size:.9rem;box-shadow:0 8px 32px rgba(0,0,0,.15);animation:slideInRight .3s ease;max-width:400px;';
        var bgColors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
        toast.style.background = bgColors[type] || bgColors.info;
        toast.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '" style="font-size:1.2rem"></i><div><strong>' + title + '</strong>' + (message ? '<br><span style="font-weight:400;opacity:.9">' + message + '</span>' : '') + '</div>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.animation = 'slideOutRight .3s ease forwards'; setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300); }, 4000);
    }
    if (!window.showToast) window.showToast = showToast;

    document.addEventListener('DOMContentLoaded', function() {
        initPasswordValidation();
        initPasswordFormBlocking();
        initPasswordToggles();
        initPhoneInputs();
    });
    function initPasswordValidation() {
        document.querySelectorAll('[data-pw-validate]').forEach(function(input) {
            var container = input.closest('.pw-field-wrap') || input.closest('.auth-input-wrap');
            if (!container) return;
            var msgs = container.querySelectorAll('.pw-validation');
            var strengthFill = container.querySelector('.pw-strength-fill');
            if (msgs.length === 0) msgs = container.parentElement.querySelectorAll('.pw-validation');
            if (!strengthFill) strengthFill = container.parentElement.querySelector('.pw-strength-fill');
            function validate() {
                if (input._pwToggling) return;
                var val = input.value;
                msgs.forEach(function(m) {
                    var rule = m.getAttribute('data-pw-rule');
                    var pass = false;
                    if (rule === 'length') pass = val.length >= 8;
                    else if (rule === 'upper') pass = /[A-Z]/.test(val);
                    else if (rule === 'lower') pass = /[a-z]/.test(val);
                    else if (rule === 'number') pass = /[0-9]/.test(val);
                    else if (rule === 'special') pass = /[^A-Za-z0-9]/.test(val);
                    m.classList.remove('pass', 'fail');
                    m.classList.add(val.length === 0 ? '' : (pass ? 'pass' : 'fail'));
                    m.querySelector('i').className = val.length === 0 ? 'bi bi-circle me-1' : (pass ? 'bi bi-check-circle-fill me-1' : 'bi bi-x-circle-fill me-1');
                });
                if (strengthFill) {
                    var score = 0;
                    if (val.length >= 8) score++;
                    if (val.length >= 12) score++;
                    if (/[A-Z]/.test(val)) score++;
                    if (/[0-9]/.test(val)) score++;
                    if (/[^A-Za-z0-9]/.test(val)) score++;
                    var pct = val.length === 0 ? 0 : (score / 5) * 100;
                    var color = score <= 1 ? '#ef4444' : score <= 2 ? '#f59e0b' : score <= 3 ? '#eab308' : score <= 4 ? '#22c55e' : '#16a34a';
                    strengthFill.style.width = pct + '%';
                    strengthFill.style.backgroundColor = color;
                }
            }
            input.addEventListener('input', validate);
            validate();
        });
    }
    function initPasswordFormBlocking() {
        document.querySelectorAll('form').forEach(function(form) {
            var pwInput = form.querySelector('[data-pw-validate]');
            var confirmInput = form.querySelector('[data-pw-match]');
            if (!pwInput && !confirmInput) return;
            
            form.addEventListener('submit', function(e) {
                var val = pwInput ? pwInput.value : '';
                var valid = val.length >= 8 && /[A-Z]/.test(val) && /[a-z]/.test(val) && /[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val);
                
                if (pwInput && val.length > 0 && !valid) {
                    e.preventDefault();
                    e.stopPropagation();
                    showToast('error', 'Password Requirements', 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
                    pwInput.focus();
                    return;
                }
                
                if (confirmInput && pwInput && confirmInput.value !== pwInput.value) {
                    e.preventDefault();
                    e.stopPropagation();
                    showToast('error', 'Password Mismatch', 'Password and Confirm Password do not match.');
                    confirmInput.focus();
                    return;
                }
            });
        });
    }
    function initPasswordToggles() {
        document.addEventListener('change', function(e) {
            var cb = e.target.closest('.pw-show-cb input[type="checkbox"]');
            if (!cb) return;
            var wrap = cb.closest('.pw-field-wrap') || cb.closest('.auth-input-wrap');
            if (!wrap) return;
            var input = wrap.querySelector('input[type="password"], input[type="text"]');
            if (!input || input === cb) return;
            var inputGroup = wrap.closest('.pw-input-group') || wrap.querySelector('.pw-input-group');
            var validations = wrap.querySelectorAll('.pw-validation');
            var strengthBar = wrap.querySelector('.pw-strength-bar');
            input._pwToggling = true;
            if (cb.checked) {
                input.type = 'text';
                if (inputGroup) inputGroup.classList.add('pw-visible');
                validations.forEach(function(v) { v.style.display = 'none'; });
                if (strengthBar) strengthBar.style.display = 'none';
            } else {
                input.type = 'password';
                if (inputGroup) inputGroup.classList.remove('pw-visible');
                validations.forEach(function(v) { v.style.display = ''; });
                if (strengthBar) strengthBar.style.display = '';
            }
            setTimeout(function() { input._pwToggling = false; }, 50);
        });
    }

    function initPhoneInputs() {
        document.querySelectorAll('input[type="tel"]').forEach(function (input) {
            input.setAttribute('maxlength', '11');
            input.setAttribute('inputmode', 'numeric');
            function normalize() {
                var digits = this.value.replace(/\D/g, '');
                if (digits.length > 11) digits = digits.substring(0, 11);
                if (this.value !== digits) this.value = digits;
            }
            input.addEventListener('input', normalize);
            input.addEventListener('blur', normalize);
        });
    }
})();
