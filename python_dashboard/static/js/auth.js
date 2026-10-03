/**
 * Student Boarding House Dashboard - Auth JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // OTP Input Handling
    // ========================================

    function initOTPInputs() {
        const otpInputs = document.querySelectorAll('.otp-digit');
        const fullCodeInput = document.getElementById('fullCode');
        
        if (!otpInputs.length) return;

        otpInputs.forEach((input, index, inputs) => {
            // Handle input
            input.addEventListener('input', function() {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Combine all digits
                const code = Array.from(inputs).map(i => i.value).join('');
                if (fullCodeInput) fullCodeInput.value = code;
                
                // Visual feedback
                if (this.value) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }
                
                // Auto-focus next
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Auto-submit when all filled
                if (code.length === inputs.length) {
                    const form = input.closest('form');
                    if (form) {
                        setTimeout(() => form.submit(), 300);
                    }
                }
            });

            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                    inputs[index - 1].dispatchEvent(new Event('input'));
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                const digits = pasteData.split('');
                
                inputs.forEach((inp, i) => {
                    if (digits[i]) {
                        inp.value = digits[i];
                        inp.classList.add('filled');
                    } else {
                        inp.value = '';
                        inp.classList.remove('filled');
                    }
                });
                
                // Update hidden field
                const code = Array.from(inputs).map(i => i.value).join('');
                if (fullCodeInput) fullCodeInput.value = code;
                
                // Focus last filled or next empty
                const lastFilled = digits.length < inputs.length ? digits.length : inputs.length - 1;
                inputs[lastFilled].focus();
            });
        });

        // Focus first input on load
        otpInputs[0].focus();
    }

    // ========================================
    // Password Toggle
    // ========================================

    function initPasswordToggle() {
        document.querySelectorAll('#togglePassword').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input[type="password"], input[type="text"]');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    }

    // ========================================
    // Resend Code Timer
    // ========================================

    function initResendTimer() {
        const resendBtn = document.getElementById('resendBtn');
        const cooldownTimer = document.getElementById('cooldownTimer');
        const expiryTimer = document.getElementById('expiryTimer');
        
        if (!resendBtn) return;

        let cooldown = parseInt(resendBtn.dataset.cooldown) || 0;
        let expiry = parseInt(expiryTimer?.textContent) || 0;

        const updateTimers = () => {
            if (cooldown > 0) {
                cooldown--;
                if (cooldownTimer) cooldownTimer.textContent = cooldown;
                resendBtn.disabled = true;
            } else {
                resendBtn.disabled = false;
            }

            if (expiry > 0) {
                expiry--;
                if (expiryTimer) expiryTimer.textContent = expiry;
            }
        };

        // Initial update
        updateTimers();

        // Countdown interval
        const interval = setInterval(() => {
            updateTimers();
            if (cooldown <= 0 && expiry <= 0) {
                clearInterval(interval);
            }
        }, 1000);

        // Resend button click
        resendBtn.addEventListener('click', function() {
            if (this.disabled) return;
            
            const form = this.closest('form') || document.querySelector('form');
            if (form) {
                // Add a hidden field to indicate resend
                const resendInput = document.createElement('input');
                resendInput.type = 'hidden';
                resendInput.name = 'resend';
                resendInput.value = '1';
                form.appendChild(resendInput);
                form.submit();
            }
        });
    }

    // ========================================
    // Form Validation
    // ========================================

    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            });
        });
    }

    // ========================================
    // Initialize All
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        initOTPInputs();
        initPasswordToggle();
        initResendTimer();
        initFormValidation();
    });

})();