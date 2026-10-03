/**
 * Student Boarding House Dashboard - Main JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // Utility Functions
    // ========================================

    const utils = {
        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            }).format(amount || 0);
        },

        // Format date
        formatDate: function(dateStr, options = {}) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                ...options
            }).format(date);
        },

        // Time ago
        timeAgo: function(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diff = now - date;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (days > 0) return `${days}d ago`;
            if (hours > 0) return `${hours}h ago`;
            if (minutes > 0) return `${minutes}m ago`;
            return 'Just now';
        },

        // Show toast notification
        toast: function(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        },

        createToastContainer: function() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        },

        // Show loading state on button
        setButtonLoading: function(btn, loading = true) {
            if (loading) {
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                btn.disabled = true;
            } else {
                btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
                btn.disabled = false;
            }
        },

        // Confirm dialog
        confirm: function(message, callback) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Confirm',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#6366f1',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed && callback) callback();
                });
            } else if (confirm(message)) {
                if (callback) callback();
            }
        },

        // AJAX request helper
        ajax: function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRFToken': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            };
            
            const config = { ...defaults, ...options };
            
            if (config.data && typeof config.data === 'object') {
                config.body = JSON.stringify(config.data);
            }
            
            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                });
        }
    };

    // ========================================
    // Sidebar Toggle (Mobile)
    // ========================================

    function initSidebarToggle() {
        const sidebar = document.getElementById('sidebarMenu');
        const toggleBtn = document.querySelector('[data-bs-target="#sidebarMenu"]');
        
        if (sidebar && toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('show') && 
                    !sidebar.contains(e.target) && 
                    !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }

    // ========================================
    // Auto-hide Flash Messages
    // ========================================

    function initFlashMessages() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });
    }

    // ========================================
    // Form Enhancements
    // ========================================

    function initFormEnhancements() {
        // Auto-focus first input
        const firstInput = document.querySelector('form input:not([type="hidden"]):not([disabled]), form select:not([disabled]), form textarea:not([disabled])');
        if (firstInput && !firstInput.value) {
            firstInput.focus();
        }

        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    utils.setButtonLoading(submitBtn, true);
                }
            });
        });

        // OTP input auto-focus
        document.querySelectorAll('.otp-digit').forEach((input, index, inputs) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                // Combine OTP digits
                const fullCode = Array.from(inputs).map(i => i.value).join('');
                document.getElementById('fullCode').value = fullCode;
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    }

    // ========================================
    // Table Enhancements
    // ========================================

    function initTableEnhancements() {
        // Row click navigation
        document.querySelectorAll('table tbody tr[data-href]').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && !e.target.closest('a, button')) {
                    window.location.href = row.dataset.href;
                }
            });
        });

        // Sortable tables
        document.querySelectorAll('table[data-sortable]').forEach(table => {
            const headers = table.querySelectorAll('th[data-sort]');
            headers.forEach(th => {
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => sortTable(table, th));
            });
        });
    }

    function sortTable(table, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const column = header.dataset.sort;
        const isAsc = header.classList.contains('sort-asc');
        
        rows.sort((a, b) => {
            const aVal = a.querySelector(`[data-${column}]`)?.dataset[column] || a.cells[header.cellIndex]?.textContent || '';
            const bVal = b.querySelector(`[data-${column}]`)?.dataset[column] || b.cells[header.cellIndex]?.textContent || '';
            
            if (!isNaN(aVal) && !isNaN(bVal)) {
                return isAsc ? aVal - bVal : bVal - aVal;
            }
            return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        
        rows.forEach(row => tbody.appendChild(row));
        
        // Update header indicators
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
    }

    // ========================================
    // Chart Defaults
    // ========================================

    function initChartDefaults() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.padding = 20;
            Chart.defaults.elements.bar.borderRadius = 4;
        }
    }

    // ========================================
    // Initialize All
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        initSidebarToggle();
        initFlashMessages();
        initFormEnhancements();
        initTableEnhancements();
        initChartDefaults();
        
        // Make utils globally available
        window.dashboardUtils = utils;
    });

    // Handle page visibility change for real-time updates
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Page became visible, could refresh data here
            console.log('Page visible - data refresh could be triggered');
        }
    });

})();