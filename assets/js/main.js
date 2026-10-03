/**
 * ============================================================
 * STUDENT BOARDING HOUSE MANAGEMENT SYSTEM
 * Main JavaScript File
 * Version: 2.0.0 — Premium UX
 * ============================================================
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initNavbarScroll();
        initSmoothScroll();
        initFlashMessages();
        initDeleteConfirmations();
        initDataTable();
        initStarRating();
        initBackToTop();
        initImagePreview();
        initDynamicFormFields();
        initSidebarToggle();
        initNotificationDropdown();
        initAjaxStatusUpdates();
        initChartHelpers();
        initSidebarMobileClose();
        initCsrfToken();
        initFormValidation();
        initScrollAnimations();
        initLoadingStates();
        initCounterAnimations();
        initHeroCountUp();
        initPasswordValidation();
        initPasswordFormBlocking();
        initPasswordToggles();
        initRegisterAgeCompute();
        initRegisterInlineValidation();
        initPhoneInputs();
        initGmailValidation();
        initAutoMarkRead();
    });

    /* ============================================================
       CSRF TOKEN
       ============================================================ */
    function initCsrfToken() {
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) window.csrfToken = token.getAttribute('content');

        if (window.csrfToken) {
            var originalFetch = window.fetch;
            window.fetch = function (url, options) {
                options = options || {};
                if (typeof url === 'string' && (url.indexOf('/') === 0 || url.indexOf(window.location.origin) === 0)) {
                    options.headers = options.headers || {};
                    options.headers['X-CSRF-TOKEN'] = window.csrfToken;
                    options.headers['X-Requested-With'] = 'XMLHttpRequest';
                }
                return originalFetch.call(this, url, options);
            };
        }
    }

    /* ============================================================
       NAVBAR SCROLL — Premium glassmorphism
       ============================================================ */
    function initNavbarScroll() {
        var navbar = document.getElementById('mainNav') || document.querySelector('.navbar');
        if (!navbar) return;

        function handleScroll() {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ============================================================
       SMOOTH SCROLL
       ============================================================ */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');
                if (targetId === '#' || targetId === '#0') return;

                var target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    var navHeight = document.querySelector('.navbar') ? document.querySelector('.navbar').offsetHeight : 0;
                    window.scrollTo({
                        top: target.offsetTop - navHeight - 20,
                        behavior: 'smooth'
                    });
                    var navbarCollapse = document.querySelector('.navbar-collapse.show');
                    if (navbarCollapse) {
                        var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) bsCollapse.hide();
                    }
                }
            });
        });
    }

    /* ============================================================
       FLASH MESSAGES
       ============================================================ */
    function initFlashMessages() {
        var flashEl = document.querySelector('[data-flash]');
        if (flashEl) {
            var type = flashEl.dataset.flashType || 'success';
            var title = flashEl.dataset.flashTitle || 'Success';
            var message = flashEl.dataset.flashMessage || '';
            showToast(type, title, message);
            flashEl.remove();
        }
    }

    function showToast(type, title, message, duration) {
        duration = duration || 4000;
        var icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };

        var toast = document.createElement('div');
        toast.className = 'toast-custom toast-' + type;
        toast.innerHTML =
            '<i class="bi ' + (icons[type] || icons.info) + ' toast-icon"></i>' +
            '<div class="toast-content">' +
            '<div class="toast-title">' + escapeHtml(title) + '</div>' +
            (message ? '<p class="toast-message">' + escapeHtml(message) + '</p>' : '') +
            '</div>' +
            '<button type="button" class="toast-close" aria-label="Close">&times;</button>';

        document.body.appendChild(toast);
        toast.querySelector('.toast-close').addEventListener('click', function () { removeToast(toast); });
        setTimeout(function () { removeToast(toast); }, duration);
    }

    function removeToast(toast) {
        if (!toast || !toast.parentNode) return;
        toast.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.showToast = showToast;

    /* ============================================================
       FORM VALIDATION
       ============================================================ */
    function initFormValidation() {
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.forEach.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    var firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
                form.classList.add('was-validated');
            }, false);
        });

        document.querySelectorAll('.form-control, .form-select').forEach(function (input) {
            input.addEventListener('blur', function () { validateField(this); });
            input.addEventListener('input', function () {
                if (this.classList.contains('is-invalid') || this.classList.contains('is-valid')) validateField(this);
            });
        });
    }

    function validateField(field) {
        if (!field.checkValidity()) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    }

    window.validateEmail = function (email) { return /^[A-Za-z0-9._%+-]+@gmail\.com$/.test(String(email).trim()); };
    window.validatePhone = function (phone) { return /^09\d{9}$/.test(String(phone)); };

    /* ============================================================
       GMAIL-ONLY EMAIL VALIDATION (all forms, all email inputs)
       ============================================================ */
    var GMAIL_ERROR = 'Please enter a valid Gmail address ending with @gmail.com.';

    function fieldDisplayName(input) {
        var form = input.form || input.closest('form');
        if (form && input.id) {
            var lbl = form.querySelector('label[for="' + input.id + '"]');
            if (lbl && lbl.textContent.trim()) return lbl.textContent.replace(/\s*\*\s*$/, '').trim();
        }
        var wrap = input.closest('.col-md-6, .col-md-8, .col-12, .mb-3, .auth-field') || input.parentElement;
        var near = wrap ? wrap.querySelector('label.form-label, label.auth-label, label') : null;
        if (near && near.textContent.trim() && !near.classList.contains('pw-show-cb')) {
            return near.textContent.replace(/\s*\*\s*$/, '').trim();
        }
        return (input.name || 'Email').replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function gmailFieldError(input) {
        var val = input.value.trim();
        var name = fieldDisplayName(input);
        if (val === '') return name + ' is required. ' + GMAIL_ERROR;
        return name + ' "' + val + '" is not a valid Gmail address. ' + GMAIL_ERROR;
    }

    function initGmailValidation() {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var emailInputs = form.querySelectorAll('input[type="email"]');
                if (!emailInputs.length) return;
                var firstInvalid = null;
                Array.prototype.forEach.call(emailInputs, function (input) {
                    var val = input.value.trim();
                    if (val !== '' && !window.validateEmail(val)) {
                        input.classList.add('is-invalid');
                        input.setCustomValidity(gmailFieldError(input));
                        if (!firstInvalid) firstInvalid = input;
                    } else {
                        input.classList.remove('is-invalid');
                        input.setCustomValidity('');
                    }
                });
                if (firstInvalid) {
                    e.preventDefault();
                    e.stopPropagation();
                    var msg = gmailFieldError(firstInvalid);
                    if (typeof showToast === 'function') showToast('error', 'Invalid Email', msg);
                    if (firstInvalid.reportValidity) firstInvalid.reportValidity();
                    firstInvalid.focus();
                }
            });
        });

        // Clear the custom error as soon as the user fixes the field
        document.addEventListener('input', function (e) {
            if (e.target && e.target.type === 'email' && e.target.classList.contains('is-invalid')) {
                var val = e.target.value.trim();
                if (val === '' || window.validateEmail(val)) {
                    e.target.classList.remove('is-invalid');
                    e.target.setCustomValidity('');
                }
            }
        });
    }

    /* ============================================================
       DELETE CONFIRMATIONS
       ============================================================ */
    function initDeleteConfirmations() {
        document.querySelectorAll('[data-confirm-delete]').forEach(function (element) {
            element.addEventListener('click', function (e) {
                e.preventDefault();
                var form = this.closest('form');
                var name = this.dataset.confirmName || 'this item';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to delete ' + name + '. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed && form) form.submit();
                    });
                } else {
                    if (confirm('Are you sure you want to delete ' + name + '?')) {
                        if (form) form.submit();
                    }
                }
            });
        });
    }

    window.confirmAction = function (title, text, onConfirm) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title, text: text, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#2563eb', cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, proceed!', cancelButtonText: 'Cancel', reverseButtons: true
            }).then(function (result) { if (result.isConfirmed) onConfirm(); });
        } else {
            if (confirm(text)) onConfirm();
        }
    };

    /* ============================================================
       DATATABLE
       ============================================================ */
    function initDataTable() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.datatable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                language: {
                    search: '<i class="bi bi-search"></i>',
                    searchPlaceholder: 'Search records...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries available',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    zeroRecords: 'No matching records found',
                    paginate: { first: '<i class="bi bi-chevron-double-left"></i>', last: '<i class="bi bi-chevron-double-right"></i>', next: '<i class="bi bi-chevron-right"></i>', previous: '<i class="bi bi-chevron-left"></i>' }
                },
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-5"i><"col-sm-7"p>>'
            });
        }
    }

    /* ============================================================
       PRINT
       ============================================================ */
    window.printArea = function (areaId) {
        var printContent = document.getElementById(areaId);
        if (!printContent) return;
        var printWindow = window.open('', '_blank', 'width=800,height=600');
        if (!printWindow) { showToast('error', 'Error', 'Please allow popups to print.'); return; }
        var styles = '';
        document.querySelectorAll('link[rel="stylesheet"], style').forEach(function (el) { styles += el.outerHTML; });
        printWindow.document.write('<!DOCTYPE html><html><head><title>Print</title>' + styles + '<style>@media print{body{padding:20px;}}</style></head><body><div class="print-area">' + printContent.innerHTML + '</div></body></html>');
        printWindow.document.close();
        printWindow.onload = function () { printWindow.focus(); printWindow.print(); printWindow.close(); };
    };

    /* ============================================================
       STAR RATING
       ============================================================ */
    function initStarRating() {
        document.querySelectorAll('.star-rating').forEach(function (container) {
            container.querySelectorAll('label').forEach(function (label) {
                label.addEventListener('click', function () {
                    var value = this.getAttribute('for').replace('star', '');
                    var displayValue = container.parentElement.querySelector('.rating-value');
                    if (displayValue) displayValue.textContent = value + ' / 5';
                });
            });
        });
    }

    window.getStarRating = function (containerSelector) {
        var container = document.querySelector(containerSelector);
        if (!container) return 0;
        var checked = container.querySelector('input:checked');
        return checked ? parseInt(checked.value) : 0;
    };

    /* ============================================================
       BACK TO TOP
       ============================================================ */
    function initBackToTop() {
        var btn = document.querySelector('.back-to-top');
        if (!btn) return;
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) btn.classList.add('visible');
            else btn.classList.remove('visible');
        }, { passive: true });
        btn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }

    /* ============================================================
       IMAGE PREVIEW
       ============================================================ */
    function initImagePreview() {
        document.querySelectorAll('.image-upload-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var preview = document.getElementById(this.dataset.preview);
                if (!preview) return;
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) { preview.src = e.target.result; preview.classList.add('active'); };
                    reader.readAsDataURL(this.files[0]);
                } else { preview.src = ''; preview.classList.remove('active'); }
            });
        });

        document.querySelectorAll('.file-upload-area').forEach(function (area) {
            var input = area.querySelector('.file-upload-input');
            if (!input) return;
            ['dragenter', 'dragover'].forEach(function (eventName) {
                area.addEventListener(eventName, function (e) { e.preventDefault(); e.stopPropagation(); area.classList.add('dragover'); }, false);
            });
            ['dragleave', 'drop'].forEach(function (eventName) {
                area.addEventListener(eventName, function (e) { e.preventDefault(); e.stopPropagation(); area.classList.remove('dragover'); }, false);
            });
            area.addEventListener('drop', function (e) {
                if (input && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
            }, false);
        });
    }

    window.previewImage = function (input, previewSelector) {
        var preview = document.querySelector(previewSelector);
        if (!preview || !input.files || !input.files[0]) return;
        var reader = new FileReader();
        reader.onload = function (e) { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    };

    /* ============================================================
       DYNAMIC FORM FIELDS
       ============================================================ */
    function initDynamicFormFields() {
        document.querySelectorAll('[data-toggle-field]').forEach(function (trigger) {
            trigger.addEventListener('change', function () {
                var target = document.getElementById(this.dataset.toggleField);
                var showValues = (this.dataset.toggleValues || '').split(',');
                if (!target) return;
                var shouldShow = showValues.includes(this.value);
                target.style.display = shouldShow ? '' : 'none';
                target.querySelectorAll('input, select, textarea').forEach(function (field) { field.disabled = !shouldShow; });
            });
            trigger.dispatchEvent(new Event('change'));
        });

        document.querySelectorAll('[data-show-if]').forEach(function (element) {
            var parts = element.dataset.showIf.split('=');
            if (parts.length !== 2) return;
            var field = document.querySelector('[name="' + parts[0] + '"]');
            if (field) {
                function check() { element.style.display = field.value === parts[1] ? '' : 'none'; }
                field.addEventListener('change', check);
                check();
            }
        });
    }

    /* ============================================================
       SIDEBAR TOGGLE
       ============================================================ */
    function initSidebarToggle() {
        var toggleBtn = document.querySelector('.sidebar-toggle');
        var sidebar = document.querySelector('.sidebar');
        var overlay = document.querySelector('.sidebar-overlay');
        if (!toggleBtn || !sidebar) return;
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        });
        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('show'); overlay.classList.remove('show'); document.body.style.overflow = '';
            });
        }
    }

    /* ============================================================
       NOTIFICATION DROPDOWN
       ============================================================ */
    function initNotificationDropdown() {
        var notifBtn = document.querySelector('.notification-btn');
        var dropdown = document.querySelector('.notification-dropdown');
        if (!notifBtn || !dropdown) return;

        notifBtn.addEventListener('click', function (e) { e.stopPropagation(); dropdown.classList.toggle('show'); });
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !notifBtn.contains(e.target)) dropdown.classList.remove('show');
        });

        dropdown.querySelectorAll('.notification-item.unread').forEach(function (item) {
            item.addEventListener('click', function () {
                this.classList.remove('unread');
                var id = this.dataset.notificationId;
                if (id) markNotificationRead(id);
            });
        });

        var markAllBtn = dropdown.querySelector('.mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                dropdown.querySelectorAll('.notification-item.unread').forEach(function (item) { item.classList.remove('unread'); });
                var badge = notifBtn.querySelector('.badge');
                if (badge) badge.style.display = 'none';
                fetch('/api/notifications/mark-all-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } });
            });
        }
    }

    function markNotificationRead(id) {
        fetch('/api/notifications/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } });
    }

    /* ============================================================
       AJAX STATUS UPDATES
       ============================================================ */
    function initAjaxStatusUpdates() {
        document.querySelectorAll('[data-ajax-status]').forEach(function (element) {
            element.addEventListener('change', function () {
                var url = this.dataset.ajaxUrl || this.closest('form').action;
                var field = this.dataset.ajaxField || this.name;
                var value = this.value || (this.checked ? 1 : 0);
                var formData = new FormData();
                formData.append(field, value);
                formData.append('_token', window.csrfToken || '');
                fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) showToast('success', 'Updated', data.message || 'Status updated successfully.');
                        else showToast('error', 'Error', data.message || 'Failed to update status.');
                    })
                    .catch(function () { showToast('error', 'Error', 'An error occurred while updating.'); });
            });
        });

        document.querySelectorAll('[data-status-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = this.dataset.statusAction;
                var url = this.dataset.statusUrl;
                var id = this.dataset.statusId;
                if (!url) return;
                var btnEl = this;
                var originalText = btnEl.innerHTML;
                btnEl.disabled = true;
                btnEl.innerHTML = '<span class="loading-spinner loading-spinner-sm"></span>';
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: action, id: id })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        btnEl.disabled = false; btnEl.innerHTML = originalText;
                        if (data.success) { showToast('success', 'Updated', data.message || 'Status updated.'); if (data.reload) location.reload(); }
                        else showToast('error', 'Error', data.message || 'Update failed.');
                    })
                    .catch(function () { btnEl.disabled = false; btnEl.innerHTML = originalText; showToast('error', 'Error', 'An error occurred.'); });
            });
        });
    }

    /* ============================================================
       CHARTS
       ============================================================ */
    function initChartHelpers() {
        document.querySelectorAll('[data-chart]').forEach(function (canvas) {
            var type = canvas.dataset.chartType || 'bar';
            var labels = tryParseJSON(canvas.dataset.chartLabels);
            var values = tryParseJSON(canvas.dataset.chartValues);
            var title = canvas.dataset.chartTitle || '';
            if (!labels || !values) return;
            var colors = ['rgba(37,99,235,0.8)', 'rgba(245,158,11,0.8)', 'rgba(16,185,129,0.8)', 'rgba(239,68,68,0.8)', 'rgba(139,92,246,0.8)', 'rgba(6,182,212,0.8)', 'rgba(236,72,153,0.8)'];
            var bgColors = values.map(function (_, i) { return colors[i % colors.length]; });
            new Chart(canvas.getContext('2d'), {
                type: type,
                data: { labels: labels, datasets: [{ label: title, data: values, backgroundColor: type === 'line' ? 'rgba(37,99,235,0.1)' : bgColors, borderColor: type === 'line' ? 'rgba(37,99,235,1)' : bgColors.map(function (c) { return c.replace('0.8', '1'); }), borderWidth: type === 'line' ? 2 : 1, tension: 0.4, fill: type === 'line', pointBackgroundColor: 'rgba(37,99,235,1)', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: type === 'line' ? 4 : 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,0.9)', titleFont: { family: 'Poppins', weight: '600' }, bodyFont: { family: 'Poppins' }, padding: 12, cornerRadius: 8 } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: 'Poppins', size: 12 } } }, x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 12 } } } } }
            });
        });
    }

    function tryParseJSON(str) { try { return JSON.parse(str); } catch (e) { return null; } }

    window.createChart = function (canvasId, config) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') return null;
        return new Chart(canvas.getContext('2d'), config);
    };

    /* ============================================================
       SIDEBAR MOBILE CLOSE
       ============================================================ */
    function initSidebarMobileClose() {
        var sidebar = document.querySelector('.sidebar');
        var overlay = document.querySelector('.sidebar-overlay');
        if (!sidebar) return;
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        sidebar.querySelectorAll('.sidebar-menu a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    /* ============================================================
       SCROLL ANIMATIONS (Intersection Observer)
       ============================================================ */
    function initScrollAnimations() {
        var elements = document.querySelectorAll('.animate-on-scroll');
        if (!elements.length) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            elements.forEach(function (el) { observer.observe(el); });
        } else {
            elements.forEach(function (el) { el.classList.add('animated'); });
        }
    }

    /* ============================================================
       COUNTER ANIMATIONS (scroll-triggered)
       ============================================================ */
    function initCounterAnimations() {
        var counters = document.querySelectorAll('.counter');
        if (!counters.length) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            counters.forEach(function (el) { observer.observe(el); });
        } else {
            counters.forEach(function (el) { el.textContent = el.dataset.target; });
        }
    }

    function animateCounter(element) {
        var target = parseInt(element.dataset.target) || 0;
        var duration = 1500;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = Math.floor(eased * target).toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString();
            }
        }
        requestAnimationFrame(step);
    }

    /* ============================================================
       HERO COUNT-UP (hero stat numbers)
       ============================================================ */
    function initHeroCountUp() {
        var heroNumbers = document.querySelectorAll('.hero-stat-number[data-count]');
        if (!heroNumbers.length) return;

        function runCount(el) {
            var target = parseInt(el.dataset.count, 10) || 0;
            var duration = 2000;
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = progress === 1 ? 1 : (1 - Math.pow(2, -10 * progress));
                el.textContent = Math.floor(eased * target).toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target.toLocaleString();
                    setTimeout(function () { el.classList.add('counted'); }, 40);
                }
            }

            el.classList.add('counting');
            requestAnimationFrame(step);
        }

        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if ('IntersectionObserver' in window && !reduced) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        runCount(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.4 });
            heroNumbers.forEach(function (el) { observer.observe(el); });
        } else {
            heroNumbers.forEach(function (el) {
                el.textContent = (parseInt(el.dataset.count, 10) || 0).toLocaleString();
            });
        }
    }

    /* ============================================================
       LOADING STATES
       ============================================================ */
    function initLoadingStates() {
        document.querySelectorAll('form[data-loading]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = this.querySelector('button[type="submit"]');
                if (btn) { btn.disabled = true; btn.dataset.originalText = btn.innerHTML; btn.innerHTML = '<span class="loading-spinner loading-spinner-sm me-2"></span> Processing...'; }
            });
        });

        document.querySelectorAll('form[data-ajax]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var formEl = this;
                var url = formEl.action;
                var method = formEl.method || 'POST';
                var btn = formEl.querySelector('button[type="submit"]');
                var originalText = btn ? btn.innerHTML : '';
                if (btn) { btn.disabled = true; btn.innerHTML = '<span class="loading-spinner loading-spinner-sm me-2"></span> Processing...'; }

                var formData = new FormData(formEl);
                formData.append('_token', window.csrfToken || '');

                fetch(url, { method: method, body: formData, headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                        if (data.success) {
                            showToast('success', 'Success', data.message || 'Operation completed successfully.');
                            if (data.redirect) window.location.href = data.redirect;
                            else if (data.reload) location.reload();
                            if (data.resetForm) formEl.reset();
                        } else {
                            showToast('error', 'Error', data.message || 'Something went wrong.');
                            if (data.errors) {
                                Object.keys(data.errors).forEach(function (field) {
                                    var input = formEl.querySelector('[name="' + field + '"]');
                                    if (input) { input.classList.add('is-invalid'); var feedback = input.parentElement.querySelector('.invalid-feedback'); if (feedback) feedback.textContent = data.errors[field][0]; }
                                });
                            }
                        }
                    })
                    .catch(function () {
                        if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                        showToast('error', 'Error', 'An unexpected error occurred. Please try again.');
                    });
            });
        });
    }

    /* ============================================================
       PASSWORD VALIDATION (8+ chars, upper, lower, number, special)
       ============================================================ */
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

    /* ============================================================
       PASSWORD FORM SUBMISSION BLOCKING
       ============================================================ */
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

    /* ============================================================
       PASSWORD TOGGLE (SHOW/HIDE) CHECKBOX
       ============================================================ */
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

    /* ============================================================
       AUTO-COMPUTE AGE FROM DATE OF BIRTH
       ============================================================ */
    function initRegisterAgeCompute() {
        var dobInput = document.getElementById('regDob');
        var ageInput = document.getElementById('regAge');
        if (!dobInput) return;

        // Keep the picker bounded to ages 18-100 (server-rendered attrs are primary).
        var today = new Date();
        function toISO(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        dobInput.setAttribute('min', toISO(new Date(today.getFullYear() - 100, today.getMonth(), today.getDate())));
        dobInput.setAttribute('max', toISO(new Date(today.getFullYear() - 18, today.getMonth(), today.getDate())));

        if (!ageInput) return;
        function computeAge() {
            var val = dobInput.value;
            if (!val) { ageInput.value = ''; return; }
            var dob = new Date(val);
            var now = new Date();
            var age = now.getFullYear() - dob.getFullYear();
            var m = now.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
            ageInput.value = age >= 0 ? age : '';
        }
        dobInput.addEventListener('change', computeAge);
        dobInput.addEventListener('input', computeAge);
        computeAge();
    }

    /* ============================================================
       REGISTER FORM — CLIENT-SIDE INLINE VALIDATION
       ============================================================ */
    function initRegisterInlineValidation() {
        var form = document.getElementById('regForm');
        if (!form) return;

        var rules = {
            first_name:            { required: true, label: 'First name' },
            last_name:             { required: true, label: 'Last name' },
            gender:                { required: true, label: 'Gender' },
            date_of_birth:         { required: true, minAge: 18, maxAge: 100, label: 'Date of birth' },
            civil_status:          { required: true, label: 'Civil status' },
            nationality:           { required: true, label: 'Nationality' },
            school_university:     { required: true, label: 'School/College' },
            course_program:        { required: true, label: 'Course/Program' },
            year_level:            { required: true, label: 'Year level' },
            email:                 { required: true, email: true, label: 'Email' },
            phone:                 { required: true, phone: true, label: 'Mobile number' },
            barangay:              { required: true, label: 'Barangay' },
            municipality_city:     { required: true, label: 'Municipality/City' },
            province:              { required: true, label: 'Province' },
            zip_code:              { required: true, digits: 4, label: 'ZIP code' },
            username:              { required: true, minLength: 4, label: 'Username' },
            password:              { required: true, minLength: 8, label: 'Password' },
            password_confirmation: { required: true, match: 'password', label: 'Confirm Password' },
            guardian_first_name:   { required: true, label: 'Guardian first name' },
            guardian_last_name:    { required: true, label: 'Guardian last name' },
            guardian_relationship: { required: true, label: 'Guardian relationship' },
            guardian_mobile:       { required: true, phone: true, label: 'Guardian mobile number' },
            guardian_alt_contact:  { phone: true, label: 'Alternative contact' },
            guardian_email:        { email: true, label: 'Guardian email' },
            guardian_barangay:     { required: true, label: 'Guardian barangay' },
            guardian_municipality_city: { required: true, label: 'Guardian municipality/city' },
            guardian_province:     { required: true, label: 'Guardian province' },
            guardian_zip_code:     { required: true, digits: 4, label: 'Guardian ZIP code' },
            terms:                 { checked: true, label: 'Terms & Conditions agreement' }
        };

        function showError(name, msg) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            input.classList.add('is-invalid');
            var wrap = input.closest('.pw-field-wrap') || input.parentElement;
            if (input.type === 'checkbox') wrap = input.closest('.form-check') || wrap;
            var existing = wrap.querySelector('.field-error');
            if (existing) existing.remove();
            var div = document.createElement('div');
            div.className = 'field-error';
            div.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + msg;
            wrap.appendChild(div);
        }

        function clearError(name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            input.classList.remove('is-invalid');
            var wrap = input.closest('.pw-field-wrap') || input.parentElement;
            if (input.type === 'checkbox') wrap = input.closest('.form-check') || wrap;
            var err = wrap.querySelector('.field-error');
            if (err) err.remove();
        }

        function validateField(name) {
            var r = rules[name];
            if (!r) return true;
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) return true;
            var val = input.value.trim();
            clearError(name);

            if (r.required && val === '') {
                showError(name, r.label + ' is required.');
                return false;
            }
            if (r.email && val !== '' && !window.validateEmail(val)) {
                showError(name, r.label + ' "' + escapeHtml(val) + '" is not a valid Gmail address. ' + GMAIL_ERROR);
                return false;
            }
            if (r.phone && val !== '' && !/^09\d{9}$/.test(val.replace(/\D/g, ''))) {
                showError(name, 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                return false;
            }
            if ((r.minAge || r.maxAge) && val !== '') {
                var dobDate = new Date(val + 'T00:00:00');
                if (isNaN(dobDate.getTime())) {
                    showError(name, r.label + ' is invalid.');
                    return false;
                }
                var now = new Date();
                var age = now.getFullYear() - dobDate.getFullYear();
                var mDiff = now.getMonth() - dobDate.getMonth();
                if (mDiff < 0 || (mDiff === 0 && now.getDate() < dobDate.getDate())) age--;
                if (age < r.minAge) {
                    showError(name, 'You must be at least ' + r.minAge + ' years old to register.');
                    return false;
                }
                if (age > r.maxAge) {
                    showError(name, 'You must be ' + r.maxAge + ' years old or below to register.');
                    return false;
                }
            }
            if (r.minLength && val !== '' && val.length < r.minLength) {
                showError(name, r.label + ' must be at least ' + r.minLength + ' characters.');
                return false;
            }
            if (r.digits && val !== '' && val.length !== r.digits) {
                showError(name, r.label + ' must be exactly ' + r.digits + ' digits.');
                return false;
            }
            if (r.match) {
                var other = form.querySelector('[name="' + r.match + '"]');
                if (other && val !== other.value) {
                    showError(name, 'Passwords do not match.');
                    return false;
                }
            }
            if (r.checked && !input.checked) {
                showError(name, 'You must agree to the ' + r.label + '.');
                return false;
            }
            return true;
        }

        // Validate on blur / change
        Object.keys(rules).forEach(function(name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            if (input.type === 'checkbox') {
                input.addEventListener('change', function() {
                    validateField(name);
                });
            } else {
                input.addEventListener('blur', function() {
                    if (input.value.trim() !== '') validateField(name);
                });
                input.addEventListener('input', function() {
                    if (input.classList.contains('is-invalid')) validateField(name);
                });
            }
        });

        // Validate all on submit
        form.addEventListener('submit', function(e) {
            var firstErr = null;
            Object.keys(rules).forEach(function(name) {
                if (!validateField(name) && !firstErr) {
                    firstErr = form.querySelector('[name="' + name + '"]');
                }
            });
            if (firstErr) {
                e.preventDefault();
                e.stopPropagation();
                firstErr.focus();
                var section = firstErr.closest('.reg-section');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    /* ============================================================
       MOBILE NUMBER INPUT — digits only, max 11
       ============================================================ */
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

    /* ============================================================
       UTILITIES
       ============================================================ */
    window.debounce = function (func, wait) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () { func.apply(context, args); }, wait);
        };
    };

    window.formatCurrency = function (amount) {
        var sym = window.APP_CURRENCY_SYMBOL || '₱';
        var num = Number(amount) || 0;
        if (num === Math.round(num)) return sym + num.toLocaleString();
        return sym + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    window.formatDate = function (dateStr, options) {
        options = options || { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    };

    window.copyToClipboard = function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () { showToast('success', 'Copied', 'Text copied to clipboard.'); });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = text; textarea.style.position = 'fixed'; textarea.style.left = '-9999px';
            document.body.appendChild(textarea); textarea.select(); document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('success', 'Copied', 'Text copied to clipboard.');
        }
    };

    /* ============================================================
       AUTO MARK MODULE AS READ (Notification Badge Removal)
       ============================================================ */
    function initAutoMarkRead() {
        var path = window.location.pathname;
        var moduleMap = {
            '/admin/rooms': 'rooms', '/manager/rooms': 'rooms',
            '/admin/reservations': 'reservations', '/manager/reservations': 'reservations',
            '/admin/students': 'students', '/manager/students': 'students',
            '/admin/payments': 'payments', '/manager/payments': 'payments', '/student/payments': 'payments',
            '/admin/billing': 'payments', '/manager/billing': 'payments',
            '/admin/announcements': 'announcements', '/manager/announcements': 'announcements', '/student/announcements': 'announcements',
            '/admin/maintenance': 'maintenance', '/manager/maintenance': 'maintenance', '/student/maintenance': 'maintenance',
            '/admin/complaints': 'complaints', '/manager/complaints': 'complaints', '/student/complaints': 'complaints',
            '/admin/feedback': 'feedback', '/manager/feedback': 'feedback', '/student/feedback': 'feedback',
            '/admin/contact-messages': 'contact_messages', '/manager/contact-messages': 'contact_messages',
            '/admin/notifications': 'notifications', '/manager/notifications': 'notifications',
            '/student/notifications': 'notifications'
        };

        var detectedModule = null;
        for (var key in moduleMap) {
            if (path.indexOf(key) === 0) {
                detectedModule = moduleMap[key];
                break;
            }
        }
        if (!detectedModule) return;

        var formData = new FormData();
        formData.append('module', detectedModule);
        if (window.csrfToken) formData.append('csrf_token', window.csrfToken);

        fetch('/api/module-read', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data.success || !data.counts) return;
            updateBadges(data.counts);
        })
        .catch(function(){});
    }

    function updateBadges(counts) {
        var badgeMap = {
            'rooms': 'rooms_available',
            'reservations': 'pending_reservations',
            'students': 'total_students',
            'payments': 'pending_payments',
            'announcements': 'total_announcements',
            'maintenance': 'open_maintenance',
            'complaints': 'open_complaints',
            'feedback': 'new_feedback',
            'contact_messages': 'new_messages',
            'notifications': 'unread_notifications'
        };
        for (var module in badgeMap) {
            var count = counts[badgeMap[module]] || 0;
            var badges = document.querySelectorAll('[data-module-badge="' + module + '"]');
            badges.forEach(function(badge) {
                var prev = parseInt(badge.textContent) || 0;
                if (count > 0) {
                    if (prev !== count) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = '';
                        badge.classList.remove('badge-pulse');
                        void badge.offsetWidth;
                        badge.classList.add('badge-pulse');
                    }
                } else {
                    badge.style.display = 'none';
                    badge.classList.remove('badge-pulse');
                }
            });
        }

        var bellBadge = document.getElementById('bell-badge');
        if (bellBadge) {
            var unreadNotifs = counts['unread_notifications'] || 0;
            var prevBell = parseInt(bellBadge.textContent) || 0;
            if (unreadNotifs > 0) {
                if (prevBell !== unreadNotifs) {
                    bellBadge.textContent = unreadNotifs > 99 ? '99+' : unreadNotifs;
                    bellBadge.style.display = '';
                    bellBadge.classList.remove('badge-pulse');
                    void bellBadge.offsetWidth;
                    bellBadge.classList.add('badge-pulse');
                }
            } else {
                bellBadge.style.display = 'none';
                bellBadge.classList.remove('badge-pulse');
            }
        }
    }

    /* ============================================================
       AUTO POLLING — Refresh sidebar badges + dashboard stats
       ============================================================ */
    function initAutoPolling() {
        var path = window.location.pathname;
        var isAdminOrManager = path.indexOf('/admin/') === 0 || path.indexOf('/manager/') === 0;
        var isStudent = path.indexOf('/student/') === 0;
        if (!isAdminOrManager && !isStudent) return;

        setInterval(function() {
            fetch('/api/dashboard-stats', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (!data.success || !data.stats) return;
                var stats = data.stats;
                var sb = stats.sidebar || {};
                updateBadges(sb);

                var dashMap = {
                    'stat-total-rooms': stats.totalRooms,
                    'stat-available-rooms': stats.availableRooms,
                    'stat-occupied-rooms': stats.occupiedRooms,
                    'stat-total-students': stats.totalStudents,
                    'stat-pending-reservations': stats.pendingReservations,
                    'stat-open-maintenance': stats.openMaintenance,
                    'stat-open-complaints': stats.openComplaints,
                    'stat-pending-payments': stats.pendingPayments,
                    'stat-open-maintenance-student': stats.openMaintenance,
                    'stat-active-reservation': stats.activeReservation
                };
                if (stats.totalRevenue !== undefined) {
                    var csym = window.APP_CURRENCY_SYMBOL || '₱';
                    var rev = Number(stats.totalRevenue) || 0;
                    dashMap['stat-total-revenue'] = rev === Math.round(rev) ? csym + rev.toLocaleString() : csym + rev.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                if (stats.totalPaid !== undefined) {
                    var csym = window.APP_CURRENCY_SYMBOL || '₱';
                    var paid = Number(stats.totalPaid) || 0;
                    dashMap['stat-total-paid'] = paid === Math.round(paid) ? csym + paid.toLocaleString() : csym + paid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                for (var id in dashMap) {
                    var el = document.getElementById(id);
                    if (el && dashMap[id] !== undefined) {
                        el.textContent = dashMap[id];
                    }
                }
            })
            .catch(function(){});
        }, 30000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initAutoPolling();
    });

})();
