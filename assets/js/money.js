/**
 * Monetary Input Validation (Whole Numbers Only)
 * Accepts digits only (0-9). No decimals, no letters, no special chars.
 * Internally stores/converts as decimal for DB (e.g., 100 -> 100.00).
 * Usage: <input type="text" data-money [required]>
 *        <input type="text" data-money-optional>
 *        <input type="text" data-money-min="1">
 */
(function () {
    'use strict';

    function sanitizeRaw(v) {
        return v.replace(/[^0-9]/g, '');
    }

    function isValidMoney(v) {
        if (v === '' || v === null || v === undefined) return false;
        if (/[^0-9]/.test(v)) return false;
        var n = parseInt(v, 10);
        return !isNaN(n) && n >= 0;
    }

    function toDecimal(v) {
        var n = parseInt(v, 10);
        if (isNaN(n) || n < 0) return '';
        return n.toFixed(2);
    }

    function toInt(v) {
        var n = parseInt(v, 10);
        if (isNaN(n) || n < 0) return '';
        return String(n);
    }

    function getMinMax(el) {
        var min = parseFloat(el.getAttribute('data-money-min'));
        var max = parseFloat(el.getAttribute('data-money-max'));
        return { min: isNaN(min) ? null : min, max: isNaN(max) ? null : max };
    }

    function showError(el, msg) {
        clearError(el);
        el.style.borderColor = '#dc3545';
        var div = document.createElement('div');
        div.className = 'money-validation-error';
        div.style.cssText = 'color:#dc3545;font-size:.75rem;margin-top:4px;font-weight:500;';
        div.textContent = msg;
        el.parentNode.appendChild(div);
    }

    function clearError(el) {
        el.style.borderColor = '';
        var parent = el.parentNode;
        var err = parent.querySelector('.money-validation-error');
        if (err) err.remove();
    }

    function validateAndFormat(el) {
        var raw = sanitizeRaw(el.value.trim());

        if (raw === '') {
            if (el.hasAttribute('data-money') && el.required) {
                el.value = '';
                showError(el, 'This field is required.');
                return false;
            }
            el.value = '';
            clearError(el);
            return true;
        }

        if (!isValidMoney(raw)) {
            showError(el, 'Please enter a valid whole number only (e.g., 100).');
            el.value = raw;
            return false;
        }

        var display = toInt(raw);
        var num = parseInt(display, 10);
        var range = getMinMax(el);

        if (range.min !== null && num < range.min) {
            showError(el, 'Minimum amount is ' + toInt(String(range.min)) + '.');
            el.value = display;
            return false;
        }
        if (range.max !== null && num > range.max) {
            showError(el, 'Maximum amount is ' + toInt(String(range.max)) + '.');
            el.value = display;
            return false;
        }

        el.value = display;
        clearError(el);
        return true;
    }

    function getHiddenDecimalValue(el) {
        var raw = sanitizeRaw(el.value.trim());
        if (raw === '') return '';
        return toDecimal(raw);
    }

    function bindMoneyInput(el) {
        el.setAttribute('inputmode', 'numeric');
        el.setAttribute('autocomplete', 'off');

        el.addEventListener('input', function () {
            var pos = this.selectionStart;
            var oldLen = this.value.length;
            this.value = sanitizeRaw(this.value);
            var diff = this.value.length - oldLen;
            this.setSelectionRange(pos + diff, pos + diff);
        });

        el.addEventListener('blur', function () {
            validateAndFormat(this);
        });

        el.addEventListener('focus', function () {
            clearError(this);
        });

        el.closest('form') && el.closest('form').addEventListener('submit', function (e) {
            if (!validateAndFormat(el)) {
                e.preventDefault();
                el.focus();
                return;
            }
            var hidden = el.getAttribute('data-money-hidden');
            if (hidden) {
                var h = document.getElementById(hidden);
                if (h) h.value = getHiddenDecimalValue(el);
            }
        });
    }

    function init() {
        document.querySelectorAll('input[data-money], input[data-money-optional]').forEach(bindMoneyInput);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.MoneyValidator = {
        validate: validateAndFormat,
        toDecimal: toDecimal,
        toInt: toInt,
        isValid: isValidMoney,
        init: init
    };
})();
