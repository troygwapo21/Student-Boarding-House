<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'Student Dashboard') ?> - <?= e(getSiteName()) ?></title>
    <script>(function(){try{var t=localStorage.getItem('sbh_theme');if(!t){var m=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)');t=(m&&m.matches)?'dark':'light';}if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="<?= asset('css/student.css') ?>" rel="stylesheet">
    <script>window.APP_CURRENCY_SYMBOL = <?= json_encode(getCurrencySymbol()) ?>;</script>
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --primary: #8fa61b;
            --primary-light: #e4ecc6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; background-attachment: fixed; overflow-x: hidden; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; height: 100dvh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff; z-index: 1040; transition: width 0.3s ease, transform 0.3s ease; overflow: hidden;
            display: flex; flex-direction: column;
        }
        .sidebar-close {
            display: none; margin-left: auto; background: none; border: none; color: #94a3b8;
            font-size: 18px; cursor: pointer; padding: 6px; border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar-close:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-backdrop {
            position: fixed; inset: 0; z-index: 1035; background: rgba(15, 23, 42, 0.55);
            opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease;
            -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px);
        }
        body.sidebar-mobile-open .sidebar-backdrop { opacity: 1; visibility: visible; }
        .sidebar .sidebar-brand {
            padding: 20px 16px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1); min-height: var(--topbar-height);
        }
        .sidebar .sidebar-brand i { font-size: 24px; color: var(--primary-light); min-width: 32px; text-align: center; }
        .sidebar .sidebar-brand span { font-size: 16px; font-weight: 600; white-space: nowrap; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-nav .nav-section { padding: 8px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 8px; white-space: nowrap; }
        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: 12px; padding: 10px 16px; margin: 2px 8px;
            color: #cbd5e1; text-decoration: none; border-radius: 8px; transition: all 0.2s;
            white-space: nowrap; font-size: 14px;
        }
        .sidebar-nav .nav-link i { width: 20px; text-align: center; font-size: 16px; min-width: 20px; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-nav .nav-link.active { background: var(--primary); color: #fff; font-weight: 500; }
        .sidebar-nav .nav-link .badge { margin-left: auto; font-size: 10px; padding: 3px 6px; transition: all .3s ease; transform-origin: center; }
        .sidebar-nav .nav-link .badge.badge-pulse { animation: badgePulse .4s ease; }
        @keyframes badgePulse { 0% { transform: scale(1); } 50% { transform: scale(1.3); } 100% { transform: scale(1); } }

        /* Collapsed sidebar */
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        .sidebar.collapsed .sidebar-brand span,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-link .badge { display: none; }
        .sidebar.collapsed .sidebar-brand { justify-content: center; padding: 20px 8px; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 10px; margin: 2px 6px; }
        .sidebar.collapsed .nav-link i { margin: 0; }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left 0.3s ease;
        }
        body.sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }

        /* Topbar */
        .topbar {
            position: sticky; top: 0; z-index: 1030; background: #fff;
            height: var(--topbar-height); display: flex; align-items: center;
            padding: 0 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .topbar .hamburger { cursor: pointer; font-size: 20px; color: #475569; margin-right: 16px; border: none; background: none; }
        .topbar .search-bar { flex: 1; max-width: 400px; margin-left: 8px; }
        .topbar .search-bar input {
            border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 14px 8px 36px;
            width: 100%; font-size: 14px; background: #f8fafc; transition: all 0.2s;
        }
        .topbar .search-bar input:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 3px rgba(143,166,27,0.15); }
        .topbar .search-bar { position: relative; }
        .topbar .search-bar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        .topbar .topbar-actions { display: flex; align-items: center; gap: 16px; margin-left: auto; }
        .topbar .notif-btn {
            position: relative; background: none; border: none; font-size: 20px;
            color: #475569; cursor: pointer; padding: 6px; border-radius: 8px; transition: background 0.2s;
        }
        .topbar .notif-btn:hover { background: #f1f5f9; }
        .topbar .notif-btn .badge {
            position: absolute; top: 0; right: 0; background: #ef4444; color: #fff;
            font-size: 10px; padding: 2px 5px; border-radius: 10px; min-width: 18px; text-align: center;
        }
        .notification-dropdown-wrap .dropdown-toggle::after { display: none; }
        .notification-panel { width: 340px; max-width: calc(100vw - 24px); padding: 0; border: none; border-radius: 14px; box-shadow: 0 18px 40px rgba(15,23,42,.18); overflow: hidden; }
        .notification-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #f1f5f9; background: #fff; }
        .notification-panel-head h6 { margin: 0; font-weight: 700; font-size: 14px; color: #0f172a; }
        .notification-mark-all { background: none; border: none; color: var(--primary); font-size: 11.5px; font-weight: 600; cursor: pointer; padding: 0; }
        .notification-mark-all:hover { text-decoration: underline; opacity: .85; }
        .notification-list { max-height: 360px; overflow-y: auto; }
        .notification-item { display: flex; align-items: flex-start; gap: 11px; padding: 12px 16px; border-bottom: 1px solid #f5f7fa; cursor: pointer; transition: background .15s; }
        .notification-item:hover { background: #f8fafc; }
        .notification-item.unread { background: #f6f8ff; }
        .notification-item.unread:hover { background: #eef2ff; }
        .notification-item-ico { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; min-width: 36px; }
        .notification-item-body { flex: 1; min-width: 0; }
        .notification-item-title { font-size: 12.5px; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .notification-item-msg { font-size: 11.5px; color: #64748b; margin-top: 2px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .notification-item-time { font-size: 10.5px; color: #94a3b8; margin-top: 3px; }
        .notification-empty { text-align: center; padding: 28px 16px; color: #94a3b8; font-size: 13px; }
        .notification-empty i { font-size: 28px; display: block; margin-bottom: 8px; opacity: .5; }
        .notification-panel-foot { display: block; text-align: center; padding: 11px 16px; font-size: 12px; font-weight: 600; color: var(--primary); border-top: 1px solid #f1f5f9; text-decoration: none; background: #fff; }
        .notification-panel-foot:hover { background: #f8fafc; opacity: .85; }
        html[data-theme="dark"] .notification-panel { background: #1e293b; box-shadow: 0 18px 40px rgba(0,0,0,.5); }
        html[data-theme="dark"] .notification-panel-head { background: #1e293b; border-bottom-color: rgba(255,255,255,.07); }
        html[data-theme="dark"] .notification-panel-head h6 { color: #f1f5f9; }
        html[data-theme="dark"] .notification-mark-all { color: #a5b4fc; }
        html[data-theme="dark"] .notification-item { border-bottom-color: rgba(255,255,255,.06); }
        html[data-theme="dark"] .notification-item:hover { background: rgba(255,255,255,.04); }
        html[data-theme="dark"] .notification-item.unread { background: rgba(99,102,241,.12); }
        html[data-theme="dark"] .notification-item.unread:hover { background: rgba(99,102,241,.18); }
        html[data-theme="dark"] .notification-item-title { color: #f1f5f9; }
        html[data-theme="dark"] .notification-item-msg { color: #94a3b8; }
        html[data-theme="dark"] .notification-item-time { color: #64748b; }
        html[data-theme="dark"] .notification-empty { color: #64748b; }
        html[data-theme="dark"] .notification-panel-foot { border-top-color: rgba(255,255,255,.07); color: #a5b4fc; background: #1e293b; }
        html[data-theme="dark"] .notification-panel-foot:hover { background: rgba(255,255,255,.05); }
        .topbar .user-dropdown .dropdown-toggle {
            display: flex; align-items: center; gap: 10px; background: none; border: none;
            cursor: pointer; padding: 4px 8px; border-radius: 8px; transition: background 0.2s;
        }
        .topbar .user-dropdown .dropdown-toggle:hover { background: #f1f5f9; }
        .topbar .user-dropdown .dropdown-toggle .avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--primary);
            display: flex; align-items: center; justify-content: center; color: #fff;
            font-weight: 600; font-size: 14px;
        }
        .topbar .user-dropdown .dropdown-toggle .user-info { text-align: left; }
        .topbar .user-dropdown .dropdown-toggle .user-name { font-size: 14px; font-weight: 500; color: #1e293b; display: block; }
        .topbar .user-dropdown .dropdown-toggle .user-role { font-size: 12px; color: #94a3b8; display: block; }
        .topbar .user-dropdown .dropdown-menu { border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 8px; min-width: 180px; }
        .topbar .user-dropdown .dropdown-menu .dropdown-item { border-radius: 6px; padding: 8px 12px; font-size: 14px; color: #475569; }
        .topbar .user-dropdown .dropdown-menu .dropdown-item:hover { background: #f1f5f9; color: #1e293b; }
        .topbar .user-dropdown .dropdown-menu .dropdown-item i { width: 18px; margin-right: 8px; color: #94a3b8; }
        .topbar .user-dropdown .dropdown-menu .dropdown-item.text-danger i { color: #ef4444; }
        .topbar .user-dropdown .dropdown-menu .dropdown-divider { margin: 4px 0; }

        /* Content */
        .content-wrapper { padding: 20px; }
        .page-header { margin-bottom: 16px; }
        .page-header h4 { font-weight: 700; color: #1e293b; margin: 0; }

        /* Cards */
        .stat-card {
            background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 12px; display: flex;
            align-items: center; justify-content: center; font-size: 20px;
        }
        .stat-card .stat-value { font-size: 24px; font-weight: 700; color: #1e293b; }
        .stat-card .stat-label { font-size: 13px; color: #64748b; margin-top: 2px; }

        /* Tables */
        .table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .table-card .table { margin: 0; }
        .table-card .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-weight: 600; font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; }
        .table-card .table td { padding: 12px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .table-card .table tr:hover td { background: #f8fafc; }

        /* Card */
        .content-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; }

        /* Dashboard welcome banner */
        .dash-welcome {
            background: linear-gradient(135deg, #8fa61b 0%, #a8bd33 50%, #6d7f0f 100%);
            border-radius: 14px; padding: 20px 24px; color: #fff; margin-bottom: 20px;
            position: relative; overflow: hidden;
        }
        .dash-welcome::before {
            content: ''; position: absolute; top: -40px; right: -40px;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .dash-welcome::after {
            content: ''; position: absolute; bottom: -60px; right: 60px;
            width: 100px; height: 100px; border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .dash-welcome h3 { font-weight: 700; margin-bottom: 2px; font-size: 1.35rem; }
        .dash-welcome p { opacity: 0.85; margin: 0; font-size: 13px; }

        /* Dashboard stat cards */
        .dash-stat {
            background: #fff; border-radius: 12px; padding: 14px 16px;
            border: 1px solid #e2e8f0; position: relative; overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dash-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        .dash-stat .ds-icon {
            width: 40px; height: 40px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; font-size: 16px; font-weight: 600;
        }
        .dash-stat .ds-value { font-size: 22px; font-weight: 800; color: #1e293b; line-height: 1.1; }
        .dash-stat .ds-label { font-size: 12px; color: #64748b; margin-top: 2px; font-weight: 500; }
        .dash-stat .ds-accent {
            position: absolute; top: 0; right: 0; width: 60px; height: 100%;
            opacity: 0.04; border-radius: 0 12px 12px 0;
        }

        /* Dashboard section cards */
        .dash-section {
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
            overflow: hidden; margin-bottom: 16px;
        }
        .dash-section .ds-header {
            padding: 13px 18px; border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .dash-section .ds-header h6 { margin: 0; font-weight: 700; font-size: 14px; color: #1e293b; }
        .dash-section .ds-body { padding: 14px 18px; }
        .dash-section .ds-body-plain { padding: 0; }

        /* Dashboard reservation highlight */
        .dash-reservation {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px;
        }
        .dash-reservation .dr-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #16a34a; font-weight: 600; }
        .dash-reservation .dr-room { font-size: 17px; font-weight: 700; color: #166534; margin: 4px 0 10px; }
        .dash-reservation .dr-detail { font-size: 12px; color: #4b5563; margin-bottom: 3px; }
        .dash-reservation .dr-detail strong { color: #1e293b; }

        /* Dashboard empty state */
        .dash-empty {
            text-align: center; padding: 24px 16px; color: #94a3b8;
        }
        .dash-empty i { font-size: 32px; margin-bottom: 8px; display: block; opacity: 0.5; }
        .dash-empty p { margin: 0 0 10px; font-size: 13px; }

        /* Dashboard quick actions */
        .dash-quick { display: flex; gap: 8px; flex-wrap: wrap; }
        .dash-quick a {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 14px; border-radius: 9px; font-size: 12px; font-weight: 600;
            text-decoration: none; transition: all 0.2s; border: 1px solid #e2e8f0;
            color: #475569; background: #fff;
        }
        .dash-quick a:hover { background: #4f46e5; color: #fff; border-color: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.25); }
        .dash-quick a i { font-size: 13px; }

        /* Flash messages */
        .flash-container { position: fixed; top: 76px; right: 24px; z-index: 9999; max-width: 400px; }

        @media (max-width: 768px) {
            /* Off-canvas drawer: hidden by default, slides in as an overlay */
            .sidebar {
                width: var(--sidebar-width);
                transform: translateX(-100%);
                box-shadow: 4px 0 24px rgba(0,0,0,0.25);
            }
            body.sidebar-mobile-open .sidebar { transform: translateX(0); }
            /* Never show the mini/collapsed state on small screens */
            .sidebar.collapsed { width: var(--sidebar-width); }
            .sidebar.collapsed .sidebar-brand span,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .nav-section,
            .sidebar.collapsed .nav-link .badge { display: block; }
            .sidebar.collapsed .sidebar-brand { justify-content: flex-start; padding: 20px 16px; }
            .sidebar.collapsed .nav-link { justify-content: flex-start; padding: 10px 16px; margin: 2px 8px; }
            .sidebar.collapsed .nav-link i { margin: 0; }
            .main-content { margin-left: 0; }
            body.sidebar-collapsed .main-content { margin-left: 0; }
            .topbar { padding: 0 12px; }
            .topbar .search-bar { display: none; }
            .topbar .user-dropdown .user-info { display: none; }
            .topbar .user-dropdown .dropdown-toggle .avatar { width: 32px; height: 32px; font-size: 13px; }
            .content-wrapper { padding: 14px; }
        }
        .pw-input-group{position:relative}
        .pw-input-group .form-control{display:block;width:100%;border-radius:16px;background:#f1f5f9;border:1.5px solid #dee2e6;padding:.8rem 3rem .8rem 1rem;font-family:inherit;font-size:.9rem;color:#212529;transition:all .2s ease;outline:none}
        .pw-input-group .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12);background:#fff}
        .pw-input-group .form-control::placeholder{color:#94a3b8}
        .pw-field-wrap:has(.pw-show-cb input:checked) .pw-validation,
        .pw-field-wrap:has(.pw-show-cb input:checked) .pw-strength-bar{display:none!important}
        .pw-input-group.pw-visible .form-control{border-color:rgba(37,99,235,.4);background:#f8faff;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
        .pw-show-cb{display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#64748b;cursor:pointer;user-select:none;-webkit-user-select:none;white-space:nowrap;padding:.25rem 0;transition:color .2s ease}
        .pw-show-cb:hover{color:#2563eb}
        .pw-show-cb input[type="checkbox"]{width:15px;height:15px;accent-color:#2563eb;cursor:pointer;margin:0}
        .pw-helper{font-size:.8rem;color:#64748b;margin-top:.35rem}
        .pw-validation{font-size:.8rem;margin-top:.2rem;display:none;line-height:1.6;transition:color .2s ease}
        .pw-validation.show{display:block}
        .pw-validation.pass{color:#16a34a;display:block}
        .pw-validation.fail{color:#dc2626;display:block}
        .pw-validation i{width:14px;text-align:center;font-size:.7rem}
        .pw-strength-bar{height:4px;border-radius:2px;background:#e2e8f0;margin-top:.45rem;overflow:hidden}
        .pw-strength-fill{height:100%;border-radius:2px;width:0%;transition:width .3s ease,background .3s ease}

        /* ===== Dark Mode ===== */
        .theme-toggle{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .2s;font-size:15px;line-height:1;flex-shrink:0}
        .theme-toggle:hover{color:#4f46e5;border-color:#6366f1;transform:translateY(-1px);box-shadow:0 4px 12px rgba(99,102,241,.18)}
        html[data-theme="dark"]{color-scheme:dark}
        html[data-theme="dark"] body{background:#f8fafc;background-attachment:fixed;color:#334155}
        html[data-theme="dark"] .topbar{background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
        html[data-theme="dark"] .topbar .hamburger{color:#334155}
        html[data-theme="dark"] .topbar .search-bar input{background:#f1f5f9;border-color:#e2e8f0;color:#334155}
        html[data-theme="dark"] .topbar .search-bar input:focus{background:#ffffff;border-color:#8fa61b;box-shadow:0 0 0 3px rgba(143,166,27,.15)}
        html[data-theme="dark"] .topbar .search-bar input::placeholder{color:#94a3b8}
        html[data-theme="dark"] .topbar .notif-btn{color:#475569}
        html[data-theme="dark"] .topbar .notif-btn:hover{background:rgba(15,23,42,.05)}
        html[data-theme="dark"] .topbar .user-dropdown .dropdown-toggle:hover{background:rgba(15,23,42,.05)}
        html[data-theme="dark"] .topbar .user-dropdown .user-name{color:#1e293b}
        html[data-theme="dark"] .topbar .user-dropdown .user-role{color:#64748b}
        html[data-theme="dark"] .theme-toggle{background:#ffffff;border-color:#e2e8f0;color:#475569}
        html[data-theme="dark"] .theme-toggle:hover{color:#4f46e5;border-color:#6366f1}
        html[data-theme="dark"] #bell-badge{border-color:#ffffff!important}
        html[data-theme="dark"] .text-dark{color:#1e293b!important}
        html[data-theme="dark"] .text-muted{color:#64748b!important}
        html[data-theme="dark"] .bg-white{background:#ffffff!important}
        html[data-theme="dark"] .bg-light{background:#f8fafc!important}
        html[data-theme="dark"] .border,html[data-theme="dark"] .border-top,html[data-theme="dark"] .border-bottom,html[data-theme="dark"] .border-end,html[data-theme="dark"] .border-start{border-color:#e2e8f0!important}
        html[data-theme="dark"] .dropdown-menu{background:#ffffff;border:1px solid #e2e8f0}
        html[data-theme="dark"] .dropdown-item{color:#334155}
        html[data-theme="dark"] .dropdown-item:hover,html[data-theme="dark"] .dropdown-item:focus{background:#f1f5f9;color:#0f172a}
        html[data-theme="dark"] .dropdown-divider{border-color:#e2e8f0}
        html[data-theme="dark"] .page-header h4{color:#1e293b}
        html[data-theme="dark"] .stat-card,.table-card,.content-card{background:#ffffff;border-color:#e2e8f0}
        html[data-theme="dark"] .stat-card .stat-value{color:#1e293b}
        html[data-theme="dark"] .stat-card .stat-label{color:#64748b}
        html[data-theme="dark"] .table-card .table th{background:#f8fafc;border-bottom:2px solid #e2e8f0;color:#64748b}
        html[data-theme="dark"] .table-card .table td{border-color:#eef2f7}
        html[data-theme="dark"] .table-card .table tr:hover td{background:rgba(15,23,42,.03)}
        html[data-theme="dark"] .dash-stat,.dash-section{background:#ffffff;border-color:#e2e8f0}
        html[data-theme="dark"] .dash-stat .ds-value,.dash-section .ds-header h6{color:#1e293b}
        html[data-theme="dark"] .dash-stat .ds-label{color:#64748b}
        html[data-theme="dark"] .dash-section .ds-header{border-bottom-color:#eef2f7}
        html[data-theme="dark"] .dash-quick a{background:#f8fafc;border-color:#e2e8f0;color:#334155}
        html[data-theme="dark"] .dash-empty{color:#94a3b8}
        html[data-theme="dark"] .dash-reservation{background:rgba(16,185,129,.10);border-color:rgba(16,185,129,.25)}
        html[data-theme="dark"] .dash-reservation .dr-label{color:#059669}
        html[data-theme="dark"] .dash-reservation .dr-room{color:#0f9d6e}
        html[data-theme="dark"] .dash-reservation .dr-detail{color:#475569}
        html[data-theme="dark"] .dash-reservation .dr-detail strong{color:#1e293b}
        html[data-theme="dark"] .table{--bs-table-bg:transparent;--bs-table-color:#334155;--bs-table-striped-bg:rgba(15,23,42,.02);--bs-table-hover-color:#1e293b;--bs-table-hover-bg:rgba(15,23,42,.03);color:#334155}
        html[data-theme="dark"] .table thead th{border-color:#e2e8f0}
        html[data-theme="dark"] .table td{border-color:#eef2f7}
        html[data-theme="dark"] .table-light,html[data-theme="dark"] .table-light>th,html[data-theme="dark"] .table-light>td{background:#f8fafc;color:#334155}
        html[data-theme="dark"] .table-light{--bs-table-bg:#f8fafc}
        html[data-theme="dark"] .form-control,html[data-theme="dark"] .form-select{background-color:#ffffff;border-color:#e2e8f0;color:#334155}
        html[data-theme="dark"] .form-control:focus,html[data-theme="dark"] .form-select:focus{background-color:#ffffff;color:#0f172a;border-color:#8fa61b;box-shadow:0 0 0 .2rem rgba(143,166,27,.15)}
        html[data-theme="dark"] .form-control::placeholder{color:#94a3b8}
        html[data-theme="dark"] .form-check-input{background-color:#ffffff;border-color:#cbd5e1}
        html[data-theme="dark"] .form-check-input:checked{background-color:#8fa61b;border-color:#8fa61b}
        html[data-theme="dark"] .list-group{--bs-list-group-bg:none;--bs-list-group-color:#334155;--bs-list-group-border-color:#eef2f7;--bs-list-group-hover-bg:rgba(15,23,42,.03);--bs-list-group-hover-color:#0f172a}
        html[data-theme="dark"] .list-group-item{background:#ffffff}
        html[data-theme="dark"] .btn-light{background:#ffffff;border-color:#e2e8f0;color:#334155}
        html[data-theme="dark"] .btn-light:hover{background:#f1f5f9;color:#0f172a}
        html[data-theme="dark"] .btn-outline-secondary{color:#475569;border-color:#cbd5e1}
        html[data-theme="dark"] .alert{background:#ffffff;border-color:#e2e8f0;color:#334155}
        html[data-theme="dark"] .alert-success{color:#15803d;border-color:rgba(34,197,94,.35)}
        html[data-theme="dark"] .alert-danger{color:#b91c1c;border-color:rgba(239,68,68,.35)}
        html[data-theme="dark"] .alert-warning{color:#b45309;border-color:rgba(245,158,11,.35)}
        html[data-theme="dark"] .alert-info{color:#0369a1;border-color:rgba(14,165,233,.35)}
        html[data-theme="dark"] .card{--bs-card-bg:#ffffff;--bs-card-border-color:#e2e8f0;--bs-card-color:#334155}

        /* Modals */
        html[data-theme="dark"] .modal-content{background:#ffffff;border:1px solid #e2e8f0;color:#334155}
        html[data-theme="dark"] .modal-header,html[data-theme="dark"] .modal-footer{border-color:#eef2f7}
        html[data-theme="dark"] .modal-title{color:#1e293b}
        html[data-theme="dark"] .modal-body{color:#334155}
        html[data-theme="dark"] .modal .form-label{color:#334155}
        html[data-theme="dark"] .modal-header .btn-close{filter:none}

        /* Dashboard view classes (defined in view <style>) */
        html[data-theme="dark"] .dash-stat-view{background:#eef2f7!important;color:#4f46e5!important}
        html[data-theme="dark"] .at-text{color:#334155!important}
        html[data-theme="dark"] .activity-timeline::before{background:rgba(15,23,42,.12)!important}

        /* Inline pastel backgrounds → light tints */
        html[data-theme="dark"] [style*="background:#f8fafc"]{background:#f1f5f9!important}
        html[data-theme="dark"] [style*="background:#f1f5f9"]{background:#eef2f7!important}
        html[data-theme="dark"] [style*="background:#eef2f7"]{background:#eef2f7!important}
        html[data-theme="dark"] [style*="background:#e2e8f0"]{background:#e2e8f0!important}
        html[data-theme="dark"] [style*="background:#fef2f2"]{background:rgba(239,68,68,.15)!important}
        html[data-theme="dark"] [style*="background:#fee2e2"]{background:rgba(239,68,68,.18)!important}
        html[data-theme="dark"] [style*="background:#fde8e8"]{background:rgba(239,68,68,.18)!important}
        html[data-theme="dark"] [style*="background:#fffbeb"]{background:rgba(245,158,11,.14)!important}
        html[data-theme="dark"] [style*="background:#fef3c7"]{background:rgba(245,158,11,.16)!important}
        html[data-theme="dark"] [style*="background:#fff7ed"]{background:rgba(249,115,22,.15)!important}
        html[data-theme="dark"] [style*="border-color:#fed7aa"]{border-color:rgba(249,115,22,.3)!important}
        html[data-theme="dark"] [style*="border-color:#fde68a"]{border-color:rgba(251,191,36,.3)!important}
        html[data-theme="dark"] [style*="background:#fefce8"]{background:rgba(202,138,4,.14)!important}
        html[data-theme="dark"] [style*="background:#ecfdf5"]{background:rgba(16,185,129,.14)!important}
        html[data-theme="dark"] [style*="background:#f0fdf4"]{background:rgba(16,185,129,.15)!important}
        html[data-theme="dark"] [style*="background:#dcfce7"]{background:rgba(16,185,129,.18)!important}
        html[data-theme="dark"] [style*="background:#e7f8f1"]{background:rgba(16,185,129,.16)!important}
        html[data-theme="dark"] [style*="background:#f3eefe"]{background:rgba(139,92,246,.16)!important}
        html[data-theme="dark"] [style*="background:#fdf2f8"]{background:rgba(219,39,119,.14)!important}
        html[data-theme="dark"] [style*="background:#fce7f3"]{background:rgba(219,39,119,.18)!important}
        html[data-theme="dark"] [style*="background:#e0e7ff"]{background:rgba(99,102,241,.18)!important}
        html[data-theme="dark"] [style*="background:#eef2ff"]{background:rgba(99,102,241,.16)!important}
        html[data-theme="dark"] [style*="background:#eff6ff"]{background:rgba(59,130,246,.15)!important}
        html[data-theme="dark"] [style*="background:#dbeafe"]{background:rgba(37,99,235,.2)!important}
        html[data-theme="dark"] [style*="background:#e0f2fe"]{background:rgba(14,165,233,.18)!important}
        html[data-theme="dark"] [style*="background:#ecfeff"]{background:rgba(6,182,212,.16)!important}
        html[data-theme="dark"] [style*="background:#f8faff"]{background:#1e293b!important}
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= getSiteLogo('32') ?>" alt="<?= e(getSiteName()) ?>" style="height:32px;width:32px;object-fit:contain;border-radius:6px;">
        <span><?= e(getSiteName()) ?></span>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <?php
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $currentPath = parse_url($currentUrl, PHP_URL_PATH) ?? $currentUrl;
        $isActive = function (string $route) use ($currentPath): string {
            return strpos($currentPath, $route) !== false ? 'active' : '';
        };
        ?>
        <div class="nav-section">Main</div>
        <a href="<?= url('/student/dashboard') ?>" class="nav-link <?= $isActive('/student/dashboard') ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section">Personal</div>
        <a href="<?= url('/student/profile') ?>" class="nav-link <?= $isActive('/student/profile') ?>">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
        <a href="<?= url('/student/reservations') ?>" class="nav-link <?= $isActive('/student/reservation') ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Reservations</span>
        </a>

        <div class="nav-section">Finance</div>
        <a href="<?= url('/student/payments') ?>" class="nav-link <?= $isActive('/student/payment') ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Payments</span>
            <span class="badge bg-warning text-dark rounded-pill" data-module-badge="payments" style="<?= empty($sidebarCounts['pending_payments']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_payments'] ?></span>
            <span class="badge bg-danger rounded-pill" data-module-badge="payments_unpaid" style="<?= (!empty($sidebarCounts['pending_payments']) || empty($sidebarCounts['unpaid_payments'])) ? 'display:none' : '' ?>"><?= $sidebarCounts['unpaid_payments'] ?></span>
        </a>
        <a href="<?= url('/student/receipts') ?>" class="nav-link <?= $isActive('/student/receipt') ?>">
            <i class="fas fa-receipt"></i>
            <span>Receipts</span>
        </a>

        <div class="nav-section">Information</div>
        <a href="<?= url('/student/announcements') ?>" class="nav-link <?= $isActive('/student/announcement') ?>">
            <i class="fas fa-bullhorn"></i>
            <span>Announcements</span>
            <span class="badge bg-info rounded-pill" data-module-badge="announcements" style="<?= empty($sidebarCounts['new_announcements']) ? 'display:none' : '' ?>"><?= $sidebarCounts['new_announcements'] ?></span>
        </a>

        <div class="nav-section">Services</div>
        <a href="<?= url('/student/maintenance') ?>" class="nav-link <?= $isActive('/student/maintenance') ?>">
            <i class="fas fa-tools"></i>
            <span>Maintenance</span>
            <span class="badge bg-danger rounded-pill" data-module-badge="maintenance" style="<?= empty($sidebarCounts['open_maintenance']) ? 'display:none' : '' ?>"><?= $sidebarCounts['open_maintenance'] ?></span>
        </a>
        <a href="<?= url('/student/complaints') ?>" class="nav-link <?= $isActive('/student/complaint') ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Complaints</span>
            <span class="badge bg-danger rounded-pill" data-module-badge="complaints" style="<?= empty($sidebarCounts['open_complaints']) ? 'display:none' : '' ?>"><?= $sidebarCounts['open_complaints'] ?></span>
        </a>
        <a href="<?= url('/student/feedback') ?>" class="nav-link <?= $isActive('/student/feedback') ?>">
            <i class="fas fa-comment-dots"></i>
            <span>Feedback</span>
            <span class="badge bg-warning text-dark rounded-pill" data-module-badge="feedback" style="<?= empty($sidebarCounts['pending_feedback']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_feedback'] ?></span>
        </a>
        <?php if (!empty($isTenant)): ?>
        <a href="<?= url('/student/refund-requests') ?>" class="nav-link <?= $isActive('/student/refund') ?>">
            <i class="fas fa-hand-holding-usd"></i>
            <span>Refund Request</span>
            <span class="badge bg-warning text-dark rounded-pill" data-module-badge="refunds" style="<?= empty($sidebarCounts['pending_refunds']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_refunds'] ?></span>
        </a>
        <?php endif; ?>

        <div class="nav-section">Account</div>
        <a href="<?= url('/student/notifications') ?>" class="nav-link <?= $isActive('/student/notification') ?>">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
            <span class="badge bg-danger rounded-pill sidebar-badge" data-module-badge="notifications" style="<?= empty($unreadNotifications) || $unreadNotifications <= 0 ? 'display:none' : '' ?>"><?= $unreadNotifications ?></span>
        </a>
        <a href="<?= url('/student/settings') ?>" class="nav-link <?= $isActive('/student/setting') || $isActive('/student/change-password') ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="<?= url('/logout') ?>" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <header class="topbar">
        <button class="hamburger" id="sidebarToggle" title="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search..." id="globalSearch">
        </div>
        <div class="topbar-actions">
            <div class="dropdown notification-dropdown-wrap">
                <button class="notif-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Notifications" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge" id="bell-badge" style="position:absolute;top:-2px;right:-4px;min-width:16px;height:16px;border-radius:8px;background:#e94560;color:#fff;font-size:.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;border:2px solid #fff;line-height:1;<?= empty($unreadNotifications) || $unreadNotifications <= 0 ? 'display:none' : '' ?>"><?= $unreadNotifications > 99 ? '99+' : $unreadNotifications ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-panel" aria-labelledby="dropdownMenu">
                    <div class="notification-panel-head">
                        <h6>Notifications</h6>
                        <button type="button" class="notification-mark-all" data-notif-mark-all>Mark all read</button>
                    </div>
                    <div class="notification-list" id="notification-list"><div class="notification-empty">Loading…</div></div>
                    <a href="<?= url('/student/notifications') ?>" class="notification-panel-foot">View all notifications</a>
                </div>
            </div>
            <div class="user-dropdown dropdown">
                <button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar">
                        <?php
                        $initials = '';
                        if (!empty($student['first_name'])) $initials .= strtoupper(substr($student['first_name'], 0, 1));
                        if (!empty($student['last_name'])) $initials .= strtoupper(substr($student['last_name'], 0, 1));
                        echo $initials ?: 'S';
                        ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? 'Student')) ?></span>
                        <span class="user-role">Student</span>
                    </div>
                    <i class="fas fa-chevron-down ms-1" style="font-size: 12px; color: #94a3b8;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= url('/student/profile') ?>"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="<?= url('/student/settings') ?>"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (!empty($flashMessages)): ?>
    <div class="flash-container">
        <?php foreach ($flashMessages as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : 'info-circle') ?> me-2"></i>
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Page Content -->
    <div class="content-wrapper">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Sidebar toggle
var sidebarEl = document.getElementById('sidebar');
var sidebarToggle = document.getElementById('sidebarToggle');
var sidebarClose = document.getElementById('sidebarClose');
var sidebarBackdrop = document.getElementById('sidebarBackdrop');
var isMobileView = function() { return window.innerWidth <= 768; };

function openMobileSidebar() {
    sidebarEl.classList.remove('collapsed');
    document.body.classList.remove('sidebar-collapsed');
    document.body.classList.add('sidebar-mobile-open');
}
function closeMobileSidebar() {
    document.body.classList.remove('sidebar-mobile-open');
}

sidebarToggle.addEventListener('click', function() {
    if (isMobileView()) {
        openMobileSidebar();
    } else {
        sidebarEl.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
    }
});
if (sidebarClose) sidebarClose.addEventListener('click', closeMobileSidebar);
if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);

document.addEventListener('click', function(e) {
    if (isMobileView() && e.target.closest('.sidebar-nav .nav-link')) {
        closeMobileSidebar();
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMobileSidebar();
});
window.addEventListener('resize', function() {
    if (!isMobileView()) {
        document.body.classList.remove('sidebar-mobile-open');
    }
});

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert.alert-dismissible, .alert.message-autodismiss').forEach(function(el) {
            var alert = bootstrap.Alert.getOrCreateInstance(el);
            alert.close();
        });
    }, 5000);
});

// SweetAlert confirmations
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            var form = this.closest('form');
            var confirmMsg = this.dataset.confirm || 'This action cannot be undone.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e94560',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, proceed!'
                }).then(function(result) {
                    if (result.isConfirmed && form) form.submit();
                });
            } else {
                if (confirm(confirmMsg)) {
                    if (form) form.submit();
                }
            }
        });
    });
});

function confirmDelete(url, csrfToken) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '<input type="hidden" name="csrf_token" value="' + csrfToken + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmAction(message, url, csrfToken) {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed!'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '<input type="hidden" name="csrf_token" value="' + csrfToken + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}


</script>
<script src="<?= asset('js/money.js') ?>"></script>
<script src="<?= asset('js/student.js') ?>"></script>
<script src="<?= asset('js/badges.js') ?>"></script>
</body>
</html>
