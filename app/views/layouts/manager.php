<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'Manager Panel') ?> - <?= e(getSiteName()) ?></title>
    <script>(function(){try{var t=localStorage.getItem('sbh_theme');if(!t){var m=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)');t=(m&&m.matches)?'dark':'light';}if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="<?= asset('css/manager.css') ?>" rel="stylesheet">
    <script>window.APP_CURRENCY_SYMBOL = <?= json_encode(getCurrencySymbol()) ?>;</script>
    <style>
        :root{--sidebar-width:260px;--topbar-height:60px}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:radial-gradient(1100px 520px at 88% -8%,rgba(143,166,27,.12),transparent 60%),radial-gradient(900px 460px at -12% 30%,rgba(143,166,27,.08),transparent 55%),linear-gradient(160deg,#fbfcf8 0%,#f1f5e8 48%,#f7f8f3 100%);background-attachment:fixed;overflow-x:hidden}
        .sidebar{position:fixed;top:0;left:0;width:var(--sidebar-width);height:100vh;height:100dvh;background:linear-gradient(180deg,#7c8f13 0%,#4a5a0c 100%);color:#fff;z-index:1000;transition:width .3s,transform .3s;overflow-y:auto;overflow-x:hidden}
        .brand-close{display:none;margin-left:auto;background:none;border:none;color:rgba(255,255,255,.6);font-size:18px;cursor:pointer;padding:6px;border-radius:8px;transition:all .2s}
        .brand-close:hover{background:rgba(255,255,255,.12);color:#fff}
        .sidebar-backdrop{position:fixed;inset:0;z-index:950;background:rgba(10,15,30,.55);opacity:0;visibility:hidden;transition:opacity .3s ease,visibility .3s ease;-webkit-backdrop-filter:blur(2px);backdrop-filter:blur(2px)}
        body.sidebar-mobile-open .sidebar-backdrop{opacity:1;visibility:visible}
        .sidebar .brand{padding:1.25rem 1rem;display:flex;align-items:center;gap:.75rem;border-bottom:1px solid rgba(255,255,255,.15)}
        .sidebar .brand i{font-size:1.5rem}
        .sidebar .brand span{font-size:1.05rem;font-weight:700;white-space:nowrap}
        .sidebar .nav-link{display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;color:rgba(255,255,255,.75);text-decoration:none;font-size:.9rem;border-radius:.375rem;margin:.15rem .6rem;transition:all .2s;white-space:nowrap}
        .sidebar .nav-link:hover{background:rgba(255,255,255,.12);color:#fff}
        .sidebar .nav-link.active{background:rgba(255,255,255,.2);color:#fff;font-weight:600}
        .sidebar .nav-link i{width:20px;text-align:center;font-size:.95rem}
        .sidebar .nav-link{position:relative}
        .sidebar-badge{margin-left:auto;font-size:.65rem;font-weight:700;padding:.15rem .45rem;border-radius:.75rem;min-width:20px;text-align:center;line-height:1.2;flex-shrink:0;transition:all .3s ease;transform-origin:center}
        .sidebar-badge.badge-pulse{animation:badgePulse .4s ease}
        .sidebar.collapsed .sidebar-badge{display:none}
        @keyframes badgePulse{0%{transform:scale(1)}50%{transform:scale(1.3)}100%{transform:scale(1)}}
        .badge-available{background:rgba(34,197,94,.25);color:#4ade80}
        .badge-pending{background:rgba(251,191,36,.25);color:#fde047}
        .badge-warning{background:rgba(249,115,22,.25);color:#fb923c}
        .badge-danger{background:rgba(239,68,68,.25);color:#f87171}
        .badge-info{background:rgba(96,165,250,.25);color:#93c5fd}
        .sidebar .nav-section{padding:.5rem 1rem .3rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.45);margin-top:.5rem}
        .main-content{margin-left:var(--sidebar-width);min-height:100vh;transition:margin .3s}
        .topbar{height:var(--topbar-height);background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;position:sticky;top:0;z-index:900}
        .topbar .sidebar-toggle{cursor:pointer;font-size:1.2rem;color:#555;background:none;border:none}
        .topbar .topbar-right{display:flex;align-items:center;gap:1rem}
        .topbar .notification-badge{position:relative}
        .topbar .notification-badge .badge{position:absolute;top:-5px;right:-5px;font-size:.65rem;padding:.2em .45em}
        .notification-dropdown-wrap .dropdown-toggle::after{display:none}
        .notification-panel{width:340px;max-width:calc(100vw - 24px);padding:0;border:none;border-radius:14px;box-shadow:0 18px 40px rgba(15,23,42,.18);overflow:hidden}
        .notification-panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid #f1f5f9;background:#fff}
        .notification-panel-head h6{margin:0;font-weight:700;font-size:14px;color:#0f172a}
        .notification-mark-all{background:none;border:none;color:#6366f1;font-size:11.5px;font-weight:600;cursor:pointer;padding:0}
        .notification-mark-all:hover{color:#4f46e5;text-decoration:underline}
        .notification-list{max-height:360px;overflow-y:auto}
        .notification-item{display:flex;align-items:flex-start;gap:11px;padding:12px 16px;border-bottom:1px solid #f5f7fa;cursor:pointer;transition:background .15s}
        .notification-item:hover{background:#f8fafc}
        .notification-item.unread{background:#f6f8ff}
        .notification-item.unread:hover{background:#eef2ff}
        .notification-item-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;min-width:36px}
        .notification-item-body{flex:1;min-width:0}
        .notification-item-title{font-size:12.5px;font-weight:600;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .notification-item-msg{font-size:11.5px;color:#64748b;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .notification-item-time{font-size:10.5px;color:#94a3b8;margin-top:3px}
        .notification-empty{text-align:center;padding:28px 16px;color:#94a3b8;font-size:13px}
        .notification-empty i{font-size:28px;display:block;margin-bottom:8px;opacity:.5}
        .notification-panel-foot{display:block;text-align:center;padding:11px 16px;font-size:12px;font-weight:600;color:#6366f1;border-top:1px solid #f1f5f9;text-decoration:none;background:#fff}
        .notification-panel-foot:hover{background:#f8fafc;color:#4f46e5}
        html[data-theme="dark"] .notification-panel{background:#1e293b;box-shadow:0 18px 40px rgba(0,0,0,.5)}
        html[data-theme="dark"] .notification-panel-head{background:#1e293b;border-bottom-color:rgba(255,255,255,.07)}
        html[data-theme="dark"] .notification-panel-head h6{color:#f1f5f9}
        html[data-theme="dark"] .notification-mark-all{color:#a5b4fc}
        html[data-theme="dark"] .notification-item{border-bottom-color:rgba(255,255,255,.06)}
        html[data-theme="dark"] .notification-item:hover{background:rgba(255,255,255,.04)}
        html[data-theme="dark"] .notification-item.unread{background:rgba(99,102,241,.12)}
        html[data-theme="dark"] .notification-item.unread:hover{background:rgba(99,102,241,.18)}
        html[data-theme="dark"] .notification-item-title{color:#f1f5f9}
        html[data-theme="dark"] .notification-item-msg{color:#94a3b8}
        html[data-theme="dark"] .notification-item-time{color:#64748b}
        html[data-theme="dark"] .notification-empty{color:#64748b}
        html[data-theme="dark"] .notification-panel-foot{border-top-color:rgba(255,255,255,.07);color:#a5b4fc;background:#1e293b}
        html[data-theme="dark"] .notification-panel-foot:hover{background:rgba(255,255,255,.05)}
        .content-wrapper{padding:1.5rem}
        .sidebar.collapsed{width:70px}
        .sidebar.collapsed .brand span,.sidebar.collapsed .nav-link span,.sidebar.collapsed .nav-section{display:none}
        .sidebar.collapsed .nav-link{justify-content:center;padding:.7rem}
        .main-content.sidebar-collapsed{margin-left:70px}

        /* Modals */
        .modal-content{border:none;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.4);background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);color:#fff}
        .modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.08)}
        .modal-header .modal-title{font-size:1.1rem;color:#fff}
        .modal-header .btn-close-white{filter:brightness(0) invert(1)}
        .modal-body{padding:1.5rem;color:rgba(255,255,255,0.85)}
        .modal-body p{color:rgba(255,255,255,0.8)}
        .modal-body .form-label{color:rgba(255,255,255,0.7)}
        .modal-body .form-control{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff}
        .modal-body .form-control:focus{background:rgba(255,255,255,0.1);border-color:rgba(96,165,250,0.4);color:#fff;box-shadow:0 0 0 .2rem rgba(96,165,250,0.15)}
        .modal-body .form-control::placeholder{color:rgba(255,255,255,0.35)}
        .modal-footer{padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,0.08)}
        .modal .info-section{background:rgba(255,255,255,0.06)!important;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:1.25rem;transition:all 0.3s ease}
        .modal .info-section:hover{background:rgba(255,255,255,0.1)!important;border-color:rgba(96,165,250,0.3);box-shadow:0 0 20px rgba(96,165,250,0.1)}
        .modal .info-section h6{font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.75rem;color:#60a5fa;font-weight:600}
        .modal .info-section .table{margin-bottom:0;background:transparent!important}
        .modal .info-section .table td{padding:.4rem 0;font-size:.875rem;border:none!important;color:rgba(255,255,255,0.7);background:transparent!important}
        .modal .info-section .table td:first-child{color:rgba(255,255,255,0.4);width:130px}
        .modal .info-section .table td.fw-bold{color:#fff}
        .modal .info-section .table td .badge{font-size:.7rem}
        .modal .info-section .table tr{background:transparent!important;border:none!important}
        .modal .nasa-glass-box{background:rgba(255,255,255,0.06)!important;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:1.25rem;transition:all 0.3s ease}
        .modal .nasa-glass-box:hover{background:rgba(255,255,255,0.1)!important;border-color:rgba(96,165,250,0.3)}
        @media(max-width:992px){
            /* Off-canvas drawer: hidden by default, slides in as an overlay */
            .sidebar{width:var(--sidebar-width);transform:translateX(-100%);box-shadow:4px 0 24px rgba(0,0,0,.25)}
            body.sidebar-mobile-open .sidebar{transform:translateX(0)}
            /* Never show the mini/collapsed state on small screens */
            .sidebar.collapsed{width:var(--sidebar-width)}
            .sidebar.collapsed .brand span,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .nav-section,
            .sidebar.collapsed .sidebar-badge{display:block}
            .sidebar.collapsed .nav-link{justify-content:flex-start;padding:.7rem 1rem;margin:.15rem .6rem}
            .sidebar.collapsed .nav-link i{margin:0}
            .main-content{margin-left:0}
            .main-content.sidebar-collapsed{margin-left:0}
            .topbar{padding:0 .75rem}
            .content-wrapper{padding:.75rem}
        }
        @media(max-width:768px){.modal-dialog{margin:.5rem;max-width:calc(100vw - 1rem)}}
        .modal-backdrop.show{background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)}
        .btn-close-white{filter:brightness(0) invert(1)}
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

        /* Dashboard */
        .dash-welcome{background:linear-gradient(135deg,#8fa61b 0%,#a8bd33 55%,#6d7f0f 100%);border-radius:16px;padding:28px 32px;color:#fff;margin-bottom:1.25rem}
        .dash-welcome::before{content:'';position:absolute;top:-40px;right:-40px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.08)}
        .dash-welcome::after{content:'';position:absolute;bottom:-60px;right:40px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.05)}
        .dash-welcome{position:relative;overflow:hidden}
        .dash-welcome h3{font-weight:700;margin-bottom:4px}
        .dash-welcome p{opacity:.85;margin:0;font-size:14px}
        .dash-stat{background:#fff;border-radius:14px;padding:20px 24px;box-shadow:0 2px 12px rgba(0,0,0,.06);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
        .dash-stat:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.07)}
        .dash-stat .ds-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
        .dash-stat .ds-value{font-size:28px;font-weight:800;color:#1e293b;line-height:1.1}
        .dash-stat .ds-label{font-size:13px;color:#64748b;margin-top:4px;font-weight:500}
        .dash-stat .ds-accent{position:absolute;bottom:0;left:0;right:0;height:4px;border-radius:0 0 14px 14px}
        .dash-section{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.25rem}
        .dash-section .ds-header{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid #f0f0f0}
        .dash-section .ds-header h6{margin:0;font-weight:700;font-size:15px;color:#1e293b}
        .dash-section .ds-body{padding:20px 24px}
        .dash-section .ds-body-plain{padding:0}
        .dash-empty{text-align:center;padding:30px 20px;color:#94a3b8}
        .dash-empty i{font-size:40px;margin-bottom:12px;display:block;opacity:.5}
        .dash-empty p{margin:0 0 12px;font-size:14px}
        .dash-quick{display:flex;gap:10px;flex-wrap:wrap}
        .dash-quick a{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:#fff;border:1.5px solid #e2e8f0;font-size:.85rem;font-weight:600;color:#475569;text-decoration:none;transition:all .2s}
        .dash-quick a:hover{background:#4f46e5;color:#fff;border-color:#4f46e5;transform:translateY(-1px);box-shadow:0 4px 12px rgba(79,70,229,.25)}
        .dash-quick a i{font-size:14px}

        /* ===== Dark Mode ===== */
        .theme-toggle{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .2s;font-size:15px;line-height:1}
        .theme-toggle:hover{color:#4f46e5;border-color:#6366f1;transform:translateY(-1px);box-shadow:0 4px 12px rgba(99,102,241,.18)}
        html[data-theme="dark"]{color-scheme:dark}
        html[data-theme="dark"] body{background:radial-gradient(1100px 520px at 88% -8%,rgba(163,190,31,.16),transparent 60%),radial-gradient(900px 460px at -12% 30%,rgba(163,190,31,.09),transparent 55%),linear-gradient(160deg,#0a101d 0%,#0e140d 45%,#151b0b 100%);background-attachment:fixed;color:#cbd5e1}
        html[data-theme="dark"] .topbar{background:#1e293b;box-shadow:0 2px 8px rgba(0,0,0,.4)}
        html[data-theme="dark"] .topbar .sidebar-toggle{color:#cbd5e1}
        html[data-theme="dark"] .theme-toggle{background:#1e293b;border-color:#334155;color:#cbd5e1}
        html[data-theme="dark"] .theme-toggle:hover{color:#a5b4fc;border-color:#6366f1}
        html[data-theme="dark"] #bell-badge{border-color:#1e293b!important}
        html[data-theme="dark"] .text-dark{color:#e2e8f0!important}
        html[data-theme="dark"] .text-muted{color:#94a3b8!important}
        html[data-theme="dark"] .bg-white{background:#1e293b!important}
        html[data-theme="dark"] .bg-light{background:rgba(255,255,255,.05)!important}
        html[data-theme="dark"] .border,html[data-theme="dark"] .border-top,html[data-theme="dark"] .border-bottom,html[data-theme="dark"] .border-end,html[data-theme="dark"] .border-start{border-color:#334155!important}
        html[data-theme="dark"] .dropdown-menu{background:#1e293b;border:1px solid #334155}
        html[data-theme="dark"] .dropdown-item{color:#cbd5e1}
        html[data-theme="dark"] .dropdown-item:hover,html[data-theme="dark"] .dropdown-item:focus{background:rgba(255,255,255,.06);color:#fff}
        html[data-theme="dark"] .dropdown-divider{border-color:#334155}
        html[data-theme="dark"] .dash-stat,html[data-theme="dark"] .dash-section{background:#1e293b}
        html[data-theme="dark"] .dash-stat .ds-value,html[data-theme="dark"] .dash-section .ds-header h6{color:#f1f5f9}
        html[data-theme="dark"] .dash-stat .ds-label{color:#94a3b8}
        html[data-theme="dark"] .dash-section .ds-header{border-color:rgba(255,255,255,.07)}
        html[data-theme="dark"] .dash-quick a{background:#1e293b;border-color:#334155;color:#cbd5e1}
        html[data-theme="dark"] .dash-empty{color:#64748b}
        html[data-theme="dark"] .content-card,html[data-theme="dark"] .table-card{background:#1e293b;border-color:#334155}
        html[data-theme="dark"] .table{--bs-table-bg:transparent;--bs-table-color:#cbd5e1;--bs-table-striped-bg:rgba(255,255,255,.03);--bs-table-hover-color:#e2e8f0;--bs-table-hover-bg:rgba(255,255,255,.04);color:#cbd5e1}
        html[data-theme="dark"] .table thead th{border-color:#334155}
        html[data-theme="dark"] .table td{border-color:rgba(255,255,255,.07)}
        html[data-theme="dark"] .table-light,html[data-theme="dark"] .table-light>th,html[data-theme="dark"] .table-light>td{background:#1e293b;color:#cbd5e1}
        html[data-theme="dark"] .table-light{--bs-table-bg:#1e293b}
        html[data-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>*{background:rgba(255,255,255,.02)}
        html[data-theme="dark"] .form-control,html[data-theme="dark"] .form-select{background-color:#0f172a;border-color:#334155;color:#e2e8f0}
        html[data-theme="dark"] .form-control:focus,html[data-theme="dark"] .form-select:focus{background-color:#0f172a;color:#fff;border-color:#6366f1;box-shadow:0 0 0 .2rem rgba(99,102,241,.15)}
        html[data-theme="dark"] .form-control::placeholder{color:#64748b}
        html[data-theme="dark"] .form-check-input{background-color:#0f172a;border-color:#475569}
        html[data-theme="dark"] .form-check-input:checked{background-color:#6366f1;border-color:#6366f1}
        html[data-theme="dark"] .list-group{--bs-list-group-bg:#1e293b;--bs-list-group-color:#e2e8f0;--bs-list-group-border-color:rgba(255,255,255,.08);--bs-list-group-hover-bg:rgba(255,255,255,.05);--bs-list-group-hover-color:#fff}
        html[data-theme="dark"] .list-group-item.bg-light{background:rgba(255,255,255,.07)!important}
        html[data-theme="dark"] .btn-light{background:#1e293b;border-color:#334155;color:#e2e8f0}
        html[data-theme="dark"] .btn-light:hover{background:#334155;color:#fff}
        html[data-theme="dark"] .btn-outline-secondary{color:#cbd5e1;border-color:#475569}
        html[data-theme="dark"] .alert{background:#1e293b;border-color:#334155;color:#cbd5e1}
        html[data-theme="dark"] .alert-success{color:#4ade80;border-color:rgba(74,222,128,.25)}
        html[data-theme="dark"] .alert-danger{color:#f87171;border-color:rgba(248,113,113,.25)}
        html[data-theme="dark"] .alert-warning{color:#fbbf24;border-color:rgba(251,191,36,.25)}
        html[data-theme="dark"] .alert-info{color:#38bdf8;border-color:rgba(56,189,248,.25)}
        html[data-theme="dark"] .card{--bs-card-bg:#1e293b;--bs-card-border-color:#334155;--bs-card-color:#e2e8f0}

        /* Dashboard (s-*) overrides — these live in view <style>, so force here */
        html[data-theme="dark"] .s-card{background:#1e293b!important;border-color:#334155!important}
        html[data-theme="dark"] .s-card:hover{box-shadow:0 1px 3px rgba(0,0,0,.3),0 14px 34px rgba(0,0,0,.45)!important}
        html[data-theme="dark"] .s-card-h{border-bottom-color:rgba(255,255,255,.07)!important}
        html[data-theme="dark"] .s-card-h h6{color:#f1f5f9!important}
        html[data-theme="dark"] .s-card-h a{color:#a5b4fc!important}
        html[data-theme="dark"] .s-head h2{color:#f1f5f9!important}
        html[data-theme="dark"] .s-head p{color:#94a3b8!important}
        html[data-theme="dark"] .s-kpi{background:#1e293b!important;border-color:#334155!important}
        html[data-theme="dark"] .s-kpi:hover{box-shadow:0 12px 28px rgba(0,0,0,.45)!important}
        html[data-theme="dark"] .s-kpi-val{color:#f1f5f9!important}
        html[data-theme="dark"] .s-kpi-label{color:#94a3b8!important}
        html[data-theme="dark"] .s-kpi-foot{border-top-color:rgba(255,255,255,.07)!important;color:#64748b!important}
        html[data-theme="dark"] .s-kpi-badge.up{background:rgba(16,185,129,.16)!important;color:#6ee7b7!important}
        html[data-theme="dark"] .s-kpi-badge.down{background:rgba(239,68,68,.16)!important;color:#fca5a5!important}
        html[data-theme="dark"] .s-kpi-badge.flat{background:rgba(148,163,184,.16)!important;color:#cbd5e1!important}
        html[data-theme="dark"] .s-pill{background:rgba(245,158,11,.14)!important;color:#fbbf24!important;border-color:rgba(251,191,36,.3)!important}
        html[data-theme="dark"] .s-pill.pink{background:rgba(219,39,119,.14)!important;color:#f9a8d4!important;border-color:rgba(219,39,119,.3)!important}
        html[data-theme="dark"] .s-seg{background:#0f172a!important}
        html[data-theme="dark"] .s-seg span{color:#94a3b8!important}
        html[data-theme="dark"] .s-seg span.active{background:#334155!important;color:#e2e8f0!important;box-shadow:0 1px 3px rgba(0,0,0,.3)!important}
        html[data-theme="dark"] .s-btn{background:#1e293b!important;border-color:#334155!important;color:#cbd5e1!important}
        html[data-theme="dark"] .s-btn:hover{border-color:#6366f1!important;color:#a5b4fc!important}
        html[data-theme="dark"] .s-qa{background:#1e293b!important;border-color:#334155!important}
        html[data-theme="dark"] .s-qa-title{color:#94a3b8!important}
        html[data-theme="dark"] .s-qa-btn{background:rgba(99,102,241,.16)!important;border-color:rgba(99,102,241,.3)!important;color:#a5b4fc!important}
        html[data-theme="dark"] .s-qa-btn:hover{background:#4f46e5!important;color:#fff!important}
        html[data-theme="dark"] .s-qa-btn.tenant{background:rgba(245,158,11,.15)!important;border-color:rgba(245,158,11,.3)!important;color:#fbbf24!important}
        html[data-theme="dark"] .s-qa-btn.tenant:hover{background:#f59e0b!important;color:#fff!important}
        html[data-theme="dark"] .s-qa-btn.purple{background:rgba(139,92,246,.15)!important;border-color:rgba(139,92,246,.3)!important;color:#c4b5fd!important}
        html[data-theme="dark"] .s-qa-btn.purple:hover{background:#8b5cf6!important;color:#fff!important}
        html[data-theme="dark"] .s-qa-btn.green{background:rgba(16,185,129,.15)!important;border-color:rgba(16,185,129,.3)!important;color:#6ee7b7!important}
        html[data-theme="dark"] .s-qa-btn.green:hover{background:#10b981!important;color:#fff!important}
        html[data-theme="dark"] .s-prog-head .s-prog-name{color:#cbd5e1!important}
        html[data-theme="dark"] .s-prog-head .s-prog-pct{color:#e2e8f0!important}
        html[data-theme="dark"] .s-prog-bar{background:#334155!important}
        html[data-theme="dark"] .s-tl::before{background:rgba(255,255,255,.08)!important}
        html[data-theme="dark"] .s-tl-text{color:#cbd5e1!important}
        html[data-theme="dark"] .s-tl-time{color:#64748b!important}
        html[data-theme="dark"] .s-list-item{border-bottom-color:rgba(255,255,255,.06)!important}
        html[data-theme="dark"] .s-list-item:hover{background:rgba(255,255,255,.04)!important}
        html[data-theme="dark"] .s-empty{color:#64748b!important}
        html[data-theme="dark"] .s-table th{color:#94a3b8!important;border-bottom-color:rgba(255,255,255,.07)!important}
        html[data-theme="dark"] .s-table td{color:#cbd5e1!important;border-bottom-color:rgba(255,255,255,.06)!important}
        html[data-theme="dark"] .s-table tbody tr:hover{background:rgba(255,255,255,.04)!important}
        html[data-theme="dark"] .s-gauge-num{color:#f1f5f9!important}
        html[data-theme="dark"] .s-gauge-cap{color:#94a3b8!important}
        html[data-theme="dark"] .s-gauge-labels span{color:#94a3b8!important}
        html[data-theme="dark"] .s-gauge-stats .s-gs{background:#0f172a!important;border-color:#334155!important}
        html[data-theme="dark"] .s-gauge-stats .s-gs b{color:#f1f5f9!important}
        html[data-theme="dark"] .s-gauge-stats .s-gs span{color:#64748b!important}
        html[data-theme="dark"] .s-legend{background:#0f172a!important;border-color:#334155!important;color:#94a3b8!important}
        html[data-theme="dark"] .s-chart-sum{background:#0f172a!important;border-color:#334155!important}
        html[data-theme="dark"] .s-chart-sum .c b{color:#f1f5f9!important}
        html[data-theme="dark"] .s-chart-sum .c span{color:#64748b!important}
        html[data-theme="dark"] .s-chart-sum .div{background:#334155!important}

        /* Inline pastel backgrounds → dark tints (specific overrides must come after the catch-all) */
        html[data-theme="dark"] [style*="background:#fff"]{background:#1e293b!important}
        html[data-theme="dark"] [style*="background:#f8fafc"]{background:#0f172a!important}
        html[data-theme="dark"] [style*="background:#f1f5f9"]{background:rgba(255,255,255,.05)!important}
        html[data-theme="dark"] [style*="background:#eef2f7"]{background:rgba(255,255,255,.05)!important}
        html[data-theme="dark"] [style*="background:#e2e8f0"]{background:#334155!important}
        html[data-theme="dark"] [style*="background:#fff7f7"]{background:rgba(239,68,68,.08)!important}
        html[data-theme="dark"] [style*="border-color:#fee2e2"]{border-color:rgba(239,68,68,.3)!important}
        html[data-theme="dark"] [style*="background:#fef2f2"]{background:rgba(239,68,68,.15)!important}
        html[data-theme="dark"] [style*="background:#fee2e2"]{background:rgba(239,68,68,.18)!important}
        html[data-theme="dark"] [style*="background:#fde8e8"]{background:rgba(239,68,68,.18)!important}
        html[data-theme="dark"] [style*="background:#fffbeb"]{background:rgba(245,158,11,.14)!important}
        html[data-theme="dark"] [style*="background:#fef3c7"]{background:rgba(245,158,11,.16)!important}
        html[data-theme="dark"] [style*="background:#fff7ed"]{background:rgba(249,115,22,.15)!important}
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
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= getSiteLogo('32') ?>" alt="<?= e(getSiteName()) ?>" style="height:32px;width:32px;object-fit:contain;border-radius:6px;">
            <span><?= e(getSiteName()) ?></span>
            <button type="button" class="brand-close" id="sidebarClose" aria-label="Close sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mt-3">
            <div class="nav-section">Main</div>
            <?php $currentUrl = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ($_SERVER['REQUEST_URI'] ?? ''); ?>
            <a href="<?= url('/manager/dashboard') ?>" class="nav-link <?= strpos($currentUrl, '/manager/dashboard') !== false ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
            <a href="<?= url('/manager/analytics') ?>" class="nav-link <?= strpos($currentUrl, '/manager/analytics') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i><span>Analytics</span>
            </a>
            <a href="<?= url('/manager/notifications') ?>" class="nav-link <?= strpos($currentUrl, '/manager/notification') !== false ? 'active' : '' ?>">
                <i class="fas fa-bell"></i><span>Notifications</span>
                <span class="sidebar-badge badge-danger" data-module-badge="notifications" style="<?= empty($unreadNotifications) || $unreadNotifications <= 0 ? 'display:none' : '' ?>"><?= $unreadNotifications > 99 ? '99+' : $unreadNotifications ?></span>
            </a>

            <div class="nav-section">Management</div>
            <a href="<?= url('/manager/rooms') ?>" class="nav-link <?= strpos($currentUrl, '/manager/room') !== false ? 'active' : '' ?>">
                <i class="fas fa-door-open"></i><span>Rooms</span>
                <span class="sidebar-badge badge-available" data-module-badge="rooms" style="<?= empty($sidebarCounts['rooms_available']) ? 'display:none' : '' ?>"><?= $sidebarCounts['rooms_available'] ?></span>
            </a>
            <a href="<?= url('/manager/reservations') ?>" class="nav-link <?= strpos($currentUrl, '/manager/reservation') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i><span>Reservation</span>
                <span class="sidebar-badge badge-pending" data-module-badge="reservations" style="<?= empty($sidebarCounts['pending_reservations']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_reservations'] ?></span>
            </a>
            <a href="<?= url('/manager/students') ?>" class="nav-link <?= strpos($currentUrl, '/manager/student') !== false && strpos($currentUrl, 'walk-in') === false ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i><span>Student</span>
                <span class="sidebar-badge badge-info" data-module-badge="students" style="<?= empty($sidebarCounts['total_students']) ? 'display:none' : '' ?>"><?= $sidebarCounts['total_students'] ?></span>
            </a>
            <a href="<?= url('/manager/tenants') ?>" class="nav-link <?= strpos($currentUrl, '/manager/tenant') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-tag"></i><span>Tenant</span>
                <span class="sidebar-badge badge-info" data-module-badge="tenants" style="<?= empty($sidebarCounts['total_tenants']) ? 'display:none' : '' ?>"><?= $sidebarCounts['total_tenants'] ?></span>
            </a>
            <a href="<?= url('/manager/students/walk-in') ?>" class="nav-link <?= strpos($currentUrl, 'walk-in') !== false && strpos($currentUrl, 'walk-in-payment') === false && strpos($currentUrl, 'walk-in-student-payment') === false ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i><span>Walk-In Registration Form</span>
            </a>
            <a href="<?= url('/manager/students/walk-in-payment') ?>" class="nav-link <?= strpos($currentUrl, 'walk-in-payment') !== false && strpos($currentUrl, 'walk-in-student-payment') === false ? 'active' : '' ?>">
                <i class="fas fa-money-check-alt"></i><span>Walk-In Payment</span>
            </a>
            <a href="<?= url('/manager/students/walk-in-student-payment') ?>" class="nav-link <?= strpos($currentUrl, 'walk-in-student-payment') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i><span>Walk-In Student Payment</span>
            </a>
            <a href="<?= url('/manager/payments') ?>" class="nav-link <?= strpos($currentUrl, '/manager/payment') !== false ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i><span>Payments</span>
                <span class="sidebar-badge badge-warning" data-module-badge="payments" style="<?= empty($sidebarCounts['pending_payments']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_payments'] ?></span>
            </a>
            <a href="<?= url('/manager/receipts') ?>" class="nav-link <?= strpos($currentUrl, '/manager/receipt') !== false ? 'active' : '' ?>">
                <i class="fas fa-receipt"></i><span>Receipts</span>
            </a>

            <div class="nav-section">Communication</div>
            <a href="<?= url('/manager/announcements') ?>" class="nav-link <?= strpos($currentUrl, '/manager/announcement') !== false ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i><span>Announcements</span>
                <span class="sidebar-badge badge-info" data-module-badge="announcements" style="<?= empty($sidebarCounts['total_announcements']) ? 'display:none' : '' ?>"><?= $sidebarCounts['total_announcements'] ?></span>
            </a>
            <a href="<?= url('/manager/gallery') ?>" class="nav-link <?= strpos($currentUrl, '/manager/gallery') !== false ? 'active' : '' ?>">
                <i class="fas fa-images"></i><span>Gallery</span>
            </a>
            <a href="<?= url('/manager/amenities') ?>" class="nav-link <?= strpos($currentUrl, '/manager/amenit') !== false ? 'active' : '' ?>">
                <i class="fas fa-concierge-bell"></i><span>Amenities</span>
            </a>

            <div class="nav-section">Services</div>
            <a href="<?= url('/manager/maintenance') ?>" class="nav-link <?= strpos($currentUrl, '/manager/maintenance') !== false ? 'active' : '' ?>">
                <i class="fas fa-tools"></i><span>Maintenance</span>
                <span class="sidebar-badge badge-danger" data-module-badge="maintenance" style="<?= empty($sidebarCounts['open_maintenance']) ? 'display:none' : '' ?>"><?= $sidebarCounts['open_maintenance'] ?></span>
            </a>
            <a href="<?= url('/manager/complaints') ?>" class="nav-link <?= strpos($currentUrl, '/manager/complaint') !== false ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle"></i><span>Complaints</span>
                <span class="sidebar-badge badge-danger" data-module-badge="complaints" style="<?= empty($sidebarCounts['open_complaints']) ? 'display:none' : '' ?>"><?= $sidebarCounts['open_complaints'] ?></span>
            </a>
            <a href="<?= url('/manager/feedback') ?>" class="nav-link <?= strpos($currentUrl, '/manager/feedback') !== false ? 'active' : '' ?>">
                <i class="fas fa-comment-dots"></i><span>Feedback</span>
                <span class="sidebar-badge badge-warning" data-module-badge="feedback" style="<?= empty($sidebarCounts['new_feedback']) ? 'display:none' : '' ?>"><?= $sidebarCounts['new_feedback'] ?></span>
            </a>
            <a href="<?= url('/manager/contact-messages') ?>" class="nav-link <?= strpos($currentUrl, '/manager/contact-message') !== false ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i><span>Contact Messages</span>
                <span class="sidebar-badge badge-danger" data-module-badge="contact_messages" style="<?= empty($sidebarCounts['new_messages']) ? 'display:none' : '' ?>"><?= $sidebarCounts['new_messages'] ?></span>
            </a>
            <a href="<?= url('/manager/refunds') ?>" class="nav-link <?= strpos($currentUrl, '/manager/refund') !== false ? 'active' : '' ?>">
                <i class="fas fa-hand-holding-usd"></i><span>Refund Requests</span>
                <span class="sidebar-badge badge-warning" data-module-badge="refunds" style="<?= empty($sidebarCounts['pending_refunds']) ? 'display:none' : '' ?>"><?= $sidebarCounts['pending_refunds'] ?></span>
            </a>

            <div class="nav-section">Reports</div>
            <a href="<?= url('/manager/reports') ?>" class="nav-link <?= strpos($currentUrl, '/manager/report') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>

            <div class="nav-section">Account</div>
            <a href="<?= url('/manager/profile') ?>" class="nav-link <?= strpos($currentUrl, '/manager/profile') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i><span>Profile</span>
            </a>
            <a href="<?= url('/manager/settings') ?>" class="nav-link <?= strpos($currentUrl, '/manager/settings') !== false ? 'active' : '' ?>">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
            <a href="<?= url('/manager/archive') ?>" class="nav-link <?= strpos($currentUrl, '/manager/archive') !== false ? 'active' : '' ?>">
                <i class="fas fa-archive"></i><span>Archive & Recovery</span>
            </a>
            <a href="<?= url('/logout') ?>" class="nav-link text-warning">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-content" id="mainContent">
        <div class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-right">
                <button type="button" class="theme-toggle" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="dropdown notification-dropdown-wrap">
                    <a href="#" class="notification-badge text-decoration-none text-muted position-relative dropdown-toggle" data-bs-toggle="dropdown" title="Notifications" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span id="bell-badge" style="position:absolute;top:-4px;right:-6px;min-width:16px;height:16px;border-radius:8px;background:#e94560;color:#fff;font-size:.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;border:2px solid #fff;line-height:1;transition:all .3s ease;<?= empty($unreadNotifications) || $unreadNotifications <= 0 ? 'display:none' : '' ?>"><?= $unreadNotifications > 99 ? '99+' : $unreadNotifications ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-panel" aria-labelledby="dropdownMenu">
                        <div class="notification-panel-head">
                            <h6>Notifications</h6>
                            <button type="button" class="notification-mark-all" data-notif-mark-all>Mark all read</button>
                        </div>
                        <div class="notification-list" id="notification-list"><div class="notification-empty">Loading…</div></div>
                        <a href="<?= url('/manager/notifications') ?>" class="notification-panel-foot">View all notifications</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg text-muted"></i>
                        <span class="ms-2 text-dark d-none d-md-inline"><?= e($_SESSION['user_email'] ?? 'Manager') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('/manager/profile') ?>"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= url('/manager/settings') ?>"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                        <?= e($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var sidebarEl = document.getElementById('sidebar');
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebarClose = document.getElementById('sidebarClose');
        var sidebarBackdrop = document.getElementById('sidebarBackdrop');
        var mainContentEl = document.getElementById('mainContent');
        var isMobileView = function() { return window.innerWidth <= 992; };

        function openMobileSidebar() {
            sidebarEl.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
            mainContentEl.classList.remove('sidebar-collapsed');
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
                mainContentEl.classList.toggle('sidebar-collapsed');
            }
        });
        if (sidebarClose) sidebarClose.addEventListener('click', closeMobileSidebar);
        if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);
        document.addEventListener('click', function(e) {
            if (isMobileView() && e.target.closest('.sidebar .nav-link')) {
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
        document.querySelectorAll('[data-confirm]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: this.dataset.confirm || 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, proceed!'
                }).then(function(result) {
                    if (result.isConfirmed && form) form.submit();
                });
            });
        });
        document.querySelectorAll('.open-reject').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var viewModal = bootstrap.Modal.getInstance(document.getElementById('viewModal' + id));
                if (viewModal) {
                    viewModal._element.addEventListener('hidden.bs.modal', function handler() {
                        viewModal._element.removeEventListener('hidden.bs.modal', handler);
                        new bootstrap.Modal(document.getElementById('rejectModal' + id)).show();
                    });
                    viewModal.hide();
                } else {
                    new bootstrap.Modal(document.getElementById('rejectModal' + id)).show();
                }
            });
        });
        var themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            var themeRoot = document.documentElement;
            var currentTheme = themeRoot.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var paintThemeIcon = function(t) {
                themeToggle.innerHTML = '<i class="fas fa-' + (t === 'dark' ? 'sun' : 'moon') + '"></i>';
                themeToggle.title = t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
            };
            paintThemeIcon(currentTheme);
            themeToggle.addEventListener('click', function() {
                currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                themeRoot.setAttribute('data-theme', currentTheme);
                try { localStorage.setItem('sbh_theme', currentTheme); } catch (e) {}
                paintThemeIcon(currentTheme);
                window.dispatchEvent(new CustomEvent('sbh:theme', { detail: { theme: currentTheme } }));
            });
        }
    </script>
    <script src="<?= asset('js/money.js') ?>"></script>
    <script src="<?= asset('js/manager.js') ?>"></script>
    <script src="<?= asset('js/badges.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.alert.alert-dismissible, .alert.message-autodismiss').forEach(function(el) {
                    var a = bootstrap.Alert.getOrCreateInstance(el);
                    if (a) {
                        a.close();
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>
