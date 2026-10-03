(function() {
    'use strict';

    var CSRF_META_SELECTOR = 'meta[name="csrf-token"]';
    var POLL_INTERVAL = 30000;

    function getCsrfToken() {
        var meta = document.querySelector(CSRF_META_SELECTOR);
        return meta ? meta.getAttribute('content') : '';
    }

    function pickCount(counts, keys) {
        for (var i = 0; i < keys.length; i++) {
            if (counts && keys[i] in counts) {
                var c = parseInt(counts[keys[i]], 10);
                return isNaN(c) ? 0 : c;
            }
        }
        return 0;
    }

    function setBadges(module, count) {
        var badges = document.querySelectorAll('[data-module-badge="' + module + '"]');
        for (var i = 0; i < badges.length; i++) {
            if (count > 0) {
                var prev = parseInt(badges[i].textContent, 10) || 0;
                if (prev !== count) {
                    badges[i].textContent = count > 99 ? '99+' : count;
                }
                badges[i].style.display = '';
            } else {
                badges[i].style.display = 'none';
            }
        }
    }

    function updateBadges(counts) {
        if (!counts) return;
        var badgeKeys = {
            'rooms': ['rooms_available'],
            'reservations': ['pending_reservations'],
            'students': ['total_students'],
            'tenants': ['total_tenants'],
            'announcements': ['total_announcements', 'new_announcements'],
            'maintenance': ['open_maintenance'],
            'complaints': ['open_complaints'],
            'feedback': ['new_feedback', 'pending_feedback'],
            'contact_messages': ['new_messages'],
            'refunds': ['pending_refunds'],
            'notifications': ['unread_notifications']
        };

        for (var module in badgeKeys) {
            setBadges(module, pickCount(counts, badgeKeys[module]));
        }

        var pendingPayments = pickCount(counts, ['pending_payments']);
        setBadges('payments', pendingPayments);
        setBadges('payments_unpaid', pendingPayments > 0 ? 0 : pickCount(counts, ['unpaid_payments']));

        var bellBadge = document.getElementById('bell-badge');
        if (bellBadge) {
            var unread = pickCount(counts, ['unread_notifications']);
            if (unread > 0) {
                bellBadge.textContent = unread > 99 ? '99+' : unread;
                bellBadge.style.display = '';
            } else {
                bellBadge.style.display = 'none';
            }
        }

        var markAllButtons = document.querySelectorAll('[data-mark-all-read]');
        for (var b = 0; b < markAllButtons.length; b++) {
            var mod = markAllButtons[b].getAttribute('data-mark-all-read');
            var keys = badgeKeys[mod];
            if (mod && keys && pickCount(counts, keys) > 0) {
                markAllButtons[b].removeAttribute('data-busy');
                markAllButtons[b].disabled = false;
                if (markAllButtons[b].getAttribute('data-original')) {
                    markAllButtons[b].innerHTML = markAllButtons[b].getAttribute('data-original');
                }
            }
        }
    }

    function detectModule() {
        var path = window.location.pathname;
        var map = [
            { module: 'rooms', paths: ['/admin/rooms', '/manager/rooms'] },
            { module: 'reservations', paths: ['/admin/reservations', '/manager/reservations'] },
            { module: 'students', paths: ['/admin/students', '/manager/students', '/admin/tenants', '/manager/tenants'] },
            { module: 'payments', paths: ['/admin/payments', '/manager/payments', '/student/payments', '/admin/billing', '/manager/billing'] },
            { module: 'announcements', paths: ['/admin/announcements', '/manager/announcements', '/student/announcements'] },
            { module: 'maintenance', paths: ['/admin/maintenance', '/manager/maintenance', '/student/maintenance'] },
            { module: 'complaints', paths: ['/admin/complaints', '/manager/complaints', '/student/complaints'] },
            { module: 'feedback', paths: ['/admin/feedback', '/manager/feedback', '/student/feedback'] },
            { module: 'contact_messages', paths: ['/admin/contact-messages', '/manager/contact-messages'] },
            { module: 'refunds', paths: ['/student/refund-requests', '/student/refund-request'] },
            { module: 'notifications', paths: ['/admin/notifications', '/manager/notifications', '/student/notifications'] }
        ];
        for (var i = 0; i < map.length; i++) {
            for (var j = 0; j < map[i].paths.length; j++) {
                if (path.indexOf(map[i].paths[j]) === 0) {
                    return map[i].module;
                }
            }
        }
        return null;
    }

    function markModuleReadApi(module) {
        var csrf = getCsrfToken();
        var fd = new FormData();
        fd.append('module', module);
        if (csrf) fd.append('csrf_token', csrf);

        return fetch('/api/module-read', {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf || ''
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success && data.counts) {
                updateBadges(data.counts);
            }
            return data;
        })
        .catch(function() {});
    }

    var NO_AUTO_MARK_PATHS = ['/admin/maintenance', '/manager/maintenance'];

    function markCurrentModuleRead() {
        var module = detectModule();
        if (!module) return;
        var path = window.location.pathname;
        for (var i = 0; i < NO_AUTO_MARK_PATHS.length; i++) {
            if (path.indexOf(NO_AUTO_MARK_PATHS[i]) === 0) return;
        }
        markModuleReadApi(module);
    }

    /* ============================================================
     * Live notification dropdown (dashboard bell)
     * ============================================================ */
    var NOTIF_POLL_INTERVAL = 15000;
    var notifTypeMeta = {
        'payment':      { icon: 'fa-money-bill',  color: '#16a34a', bg: '#dcfce7' },
        'reservation':  { icon: 'fa-calendar-check', color: '#7c3aed', bg: '#ede9fe' },
        'announcement': { icon: 'fa-bullhorn',    color: '#d97706', bg: '#fef3c7' },
        'maintenance':  { icon: 'fa-tools',       color: '#2563eb', bg: '#dbeafe' },
        'complaint':    { icon: 'fa-exclamation-triangle', color: '#dc2626', bg: '#fee2e2' },
        'refund':       { icon: 'fa-rotate-left', color: '#0d9488', bg: '#ccfbf1' },
        'system':       { icon: 'fa-bell',        color: '#64748b', bg: '#f1f5f9' }
    };

    function notifMeta(type) {
        return notifTypeMeta[type] || notifTypeMeta['system'];
    }

    function buildNotifUrl(n) {
        var role = (window.location.pathname.match(/^\/(admin|manager|student)\b/) || [])[1] || '';
        var base = '/' + role;
        var refType = n.reference_type || n.type || 'system';
        var id = n.reference_id || '';
        var map = {
            'payment': base + '/payments',
            'reservation': base + '/reservations',
            'announcement': base + '/announcements',
            'maintenance': base + '/maintenance',
            'complaint': base + '/complaints',
            'room': base + '/rooms',
            'student': base + '/students'
        };
        var target = map[refType] || (role ? base + '/notifications' : '/');
        return id ? target + (target.indexOf('?') > -1 ? '&' : '?') + 'highlight=' + id : target;
    }

    function timeAgoShort(dt) {
        if (!dt) return '';
        var then = new Date((dt || '').replace(' ', 'T').replace(/\.\d+$/, '') + 'Z');
        var diff = (Date.now() - then.getTime()) / 1000;
        if (isNaN(diff) || diff < 0) return '';
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        return then.getUTCMonth() + 1 + '/' + then.getUTCDate();
    }

    function renderNotifications(list, unread) {
        var container = document.getElementById('notification-list');
        if (!container) return;
        var bellBadge = document.getElementById('bell-badge');

        if (bellBadge) {
            if (unread > 0) {
                bellBadge.textContent = unread > 99 ? '99+' : unread;
                bellBadge.style.display = 'flex';
            } else {
                bellBadge.style.display = 'none';
            }
        }

        if (!list || !list.length) {
            container.innerHTML = '<div class="notification-empty"><i class="fas fa-bell-slash"></i>No notifications yet</div>';
            return;
        }

        var html = '';
        for (var i = 0; i < list.length; i++) {
            var n = list[i];
            var meta = notifMeta(n.type);
            var unreadCls = n.is_read ? '' : ' unread';
            var dot = n.is_read ? '' : '<span style="width:7px;height:7px;border-radius:50%;background:' + meta.color + ';display:inline-block;flex-shrink:0;margin-top:5px;"></span>';
            html += '<div class="notification-item' + unreadCls + '" data-notif-id="' + (n.id || '') + '" data-notif-url="' + buildNotifUrl(n).replace(/"/g, '&quot;') + '">'
                + '<div class="notification-item-ico" style="background:' + meta.bg + ';color:' + meta.color + ';"><i class="fas ' + meta.icon + '"></i></div>'
                + '<div class="notification-item-body">'
                + '<div class="notification-item-title">' + escapeHtml(n.title || 'Notification') + '</div>'
                + '<div class="notification-item-msg">' + escapeHtml(n.message || '') + '</div>'
                + '<div class="notification-item-time"><i class="fas fa-clock me-1"></i>' + timeAgoShort(n.created_at) + '</div>'
                + '</div>' + dot + '</div>';
        }
        container.innerHTML = html;
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function(c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    function fetchNotifications(silent) {
        var container = document.getElementById('notification-list');
        if (!container) return;
        if (!silent) {
            container.innerHTML = '<div class="notification-empty">Loading…</div>';
        }
        fetch('/api/notifications?limit=10', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() || '' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success) {
                renderNotifications(data.notifications, data.unread);
            }
        })
        .catch(function() {
            if (container && !silent) {
                container.innerHTML = '<div class="notification-empty">Could not load notifications.</div>';
            }
        });
    }

    function markNotificationRead(id, el) {
        var csrf = getCsrfToken();
        return fetch('/api/notifications/read', {
            method: 'POST',
            body: JSON.stringify({ notification_id: id, csrf_token: csrf }),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf || ''
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (el) { el.classList.remove('unread'); }
            if (data && data.success) {
                var bellBadge = document.getElementById('bell-badge');
                var val = data.unread || 0;
                if (bellBadge) {
                    if (val > 0) { bellBadge.textContent = val > 99 ? '99+' : val; bellBadge.style.display = 'flex'; }
                    else { bellBadge.style.display = 'none'; }
                }
            }
            return data;
        })
        .catch(function() {});
    }

    function startNotificationPolling() {
        fetchNotifications(true);
        setInterval(function() { fetchNotifications(true); }, NOTIF_POLL_INTERVAL);

        document.addEventListener('show.bs.dropdown', function(e) {
            if (e.target.closest && e.target.closest('.notification-dropdown-wrap')) {
                fetchNotifications(false);
            }
        });

        document.addEventListener('click', function(e) {
            var item = e.target.closest('.notification-item');
            if (item) {
                var url = item.getAttribute('data-notif-url');
                var id = item.getAttribute('data-notif-id');
                if (id) markNotificationRead(id, item);
                if (url && url.indexOf('#') !== 0) {
                    window.location.href = url;
                }
                return;
            }
            var markAll = e.target.closest('[data-notif-mark-all]');
            if (markAll) {
                e.preventDefault();
                markAll.disabled = true;
                markNotificationRead(0).then(function() {
                    renderNotifications([], 0);
                    var list = document.getElementById('notification-list');
                    if (list) list.innerHTML = '<div class="notification-empty"><i class="fas fa-check-circle"></i>All caught up</div>';
                    markAll.disabled = false;
                });
            }
        });

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                fetchNotifications(true);
            }
        });
    }

    function startPolling() {
        setInterval(function() {
            fetch('/api/dashboard-stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken() || ''
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.success && data.stats && data.stats.sidebar) {
                    updateBadges(data.stats.sidebar);
                }
            })
            .catch(function() {});
        }, POLL_INTERVAL);
    }

    document.addEventListener('DOMContentLoaded', function() {
        markCurrentModuleRead();
        startPolling();
        startNotificationPolling();
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-mark-all-read]');
        if (!btn) return;
        e.preventDefault();
        var module = btn.getAttribute('data-mark-all-read');
        if (!module || btn.getAttribute('data-busy') === '1') return;
        if (!btn.getAttribute('data-original')) {
            btn.setAttribute('data-original', btn.innerHTML);
        }
        btn.setAttribute('data-busy', '1');
        btn.disabled = true;
        markModuleReadApi(module).then(function(data) {
            if (data && data.success) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Marked Read';
            } else {
                btn.disabled = false;
                btn.removeAttribute('data-busy');
                if (btn.getAttribute('data-original')) {
                    btn.innerHTML = btn.getAttribute('data-original');
                }
            }
        });
    });
})();
