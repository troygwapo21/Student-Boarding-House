<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $pageTitle = $pageTitle ?? getSiteName(); ?>
<?php $flashMessages = $flashMessages ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(getSiteName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <script>window.APP_CURRENCY_SYMBOL = <?= json_encode(getCurrencySymbol()) ?>;</script>
    <style>
        :root {
            --primary: #8fa61b;
            --primary-rgb: 143, 166, 27;
            --secondary: #95d02f;
            --secondary-rgb: 149, 208, 47;
            --accent: #cd8a16;
            --accent-rgb: 205, 138, 22;
            --dark: #0f172a;
            --dark-rgb: 15, 23, 42;
            --glass-bg: rgba(255, 255, 255, 0.82);
            --glass-border: rgba(255, 255, 255, 0.53);
            --nav-height: 72px;
        }
        body { font-family: 'Poppins', sans-serif; color: #334155; background: #f8fafc; }
        
        /* === PREMIUM NAVBAR === */
        .navbar-premium {
            padding: 0.6rem 0;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            background: transparent;    
            border-bottom: 1px solid transparent;
        }
        .navbar-premium.scrolled {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06);
            padding: 0.35rem 0;
        }
        .navbar-premium .navbar-brand {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--dark) !important;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .navbar-premium .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb),0.3);
            transition: transform 0.3s ease;
        }
        .navbar-premium .navbar-brand:hover .brand-icon {
            transform: rotate(-8deg) scale(1.05);
        }
        .navbar-premium .nav-link {
            font-weight: 500;
            color: #475569 !important;
            padding: 0.5rem 0.85rem !important;
            border-radius: 8px;
            transition: all 0.25s ease;
            font-size: 0.92rem;
            position: relative;
        }
        .navbar-premium .nav-link:hover {
            color: var(--primary) !important;
            background: rgba(var(--primary-rgb),0.06);
        }
        .navbar-premium .nav-link.active {
            color: var(--primary) !important;
            font-weight: 600;
            background: rgba(var(--primary-rgb),0.08);
        }
        .navbar-premium .btn-nav-login {
            border: 1.5px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            padding: 0.45rem 1.2rem;
            border-radius: 10px;
            font-size: 0.88rem;
            transition: all 0.25s ease;
            background: transparent;
        }
        .navbar-premium .btn-nav-login:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(var(--primary-rgb),0.04);
        }
        .navbar-premium .btn-nav-register {
            background: var(--primary);
            color: #ffffff;
            font-weight: 600;
            padding: 0.45rem 1.2rem;
            border-radius: 10px;
            font-size: 0.88rem;
            border: none;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb),0.25);
            transition: all 0.25s ease;
        }
        .navbar-premium .btn-nav-register:hover {
            background: var(--secondary);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(var(--primary-rgb),0.35);
        }
        .navbar-premium .btn-nav-dashboard {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #ffffff;
            font-weight: 600;
            padding: 0.45rem 1.2rem;
            border-radius: 10px;
            font-size: 0.88rem;
            border: none;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb),0.25);
            transition: all 0.25s ease;
        }
        .navbar-premium .btn-nav-dashboard:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(var(--primary-rgb),0.35);
        }
        .navbar-premium .btn-nav-logout {
            color: #64748b;
            font-weight: 500;
            padding: 0.45rem 0.8rem;
            border-radius: 10px;
            font-size: 0.85rem;
            border: none;
            background: transparent;
            transition: all 0.25s ease;
        }
        .navbar-premium .btn-nav-logout:hover {
            background: rgba(239,68,68,0.08);
            color: #ef4444;
        }
        .navbar-premium .navbar-toggler {
            border: none;
            padding: 0.4rem;
            font-size: 1.2rem;
            color: #475569;
        }
        .navbar-premium .navbar-toggler:focus {
            box-shadow: none;
        }

        /* === FOOTER PREMIUM === */
        .footer-premium {
            background: #0f172a;
            color: #94a3b8;
            position: relative;
            overflow: hidden;
        }
        .footer-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(37,99,235,0.5), transparent);
        }
        .footer-premium h5 {
            color: #f1f5f9;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.01em;
            margin-bottom: 1.2rem;
        }
        .footer-premium a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.25s ease;
            font-size: 0.9rem;
        }
        .footer-premium a:hover {
            color: #fff;
            padding-left: 4px;
        }
        .footer-premium .footer-brand {
            font-size: 1.25rem;
            font-weight: 800;
            color: #f1f5f9;
            letter-spacing: -0.02em;
        }
        .footer-premium .footer-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 0.6rem;
            vertical-align: middle;
        }
        .footer-premium .social-link {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .footer-premium .social-link:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(var(--primary-rgb),0.3);
            border-color: var(--primary);
        }
        .footer-premium .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .footer-premium .footer-contact-item i {
            color: var(--accent);
            width: 18px;
            text-align: center;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }
        .footer-premium .footer-bottom-bar {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 1.2rem 0;
        }
        .footer-premium .footer-bottom-bar a {
            font-size: 0.82rem;
        }
    </style>
</head>
<body>
    <!-- === PREMIUM NAVBAR === -->
    <nav class="navbar navbar-expand-lg navbar-premium fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="<?= url('/') ?>">
                <span class="brand-icon"><img src="<?= getSiteLogo('36') ?>" alt="<?= e(getSiteName()) ?>" style="height:36px;width:36px;object-fit:contain;border-radius:6px;"></span>
                <span><?= e(getSiteName()) ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php
                $reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
                $reqUri = rtrim(str_replace(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '', $reqUri), '/') ?: '/';
                ?>
                <ul class="navbar-nav mx-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/' ? 'active' : '' ?>" href="<?= url('/') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/about' ? 'active' : '' ?>" href="<?= url('/about') ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/rooms' || str_starts_with($reqUri, '/room/') ? 'active' : '' ?>" href="<?= url('/rooms') ?>">Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/amenities' ? 'active' : '' ?>" href="<?= url('/amenities') ?>">Amenities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/gallery' ? 'active' : '' ?>" href="<?= url('/gallery') ?>">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/faqs' ? 'active' : '' ?>" href="<?= url('/faqs') ?>">FAQs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reqUri === '/contact' ? 'active' : '' ?>" href="<?= url('/contact') ?>">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= url('/' . ($_SESSION['user_role'] === 'super_admin' ? 'admin' : ($_SESSION['user_role'] === 'manager' ? 'manager' : 'student')) . '/dashboard') ?>" class="btn btn-nav-dashboard">
                            <i class="fas fa-th-large me-1"></i> Dashboard
                        </a>
                        <a href="<?= url('/logout') ?>" class="btn btn-nav-logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-nav-login">Login</a>
                        <a href="<?= url('/register') ?>" class="btn btn-nav-register">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div style="padding-top: var(--nav-height);">
        <?php if (!empty($flashMessages)): ?>
            <div class="container mt-3">
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert" style="border-radius:12px;border:none;box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <?php if ($type === 'success'): ?>
                            <i class="fas fa-check-circle me-2"></i>
                        <?php elseif ($type === 'error'): ?>
                            <i class="fas fa-exclamation-circle me-2"></i>
                        <?php elseif ($type === 'warning'): ?>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle me-2"></i>
                        <?php endif; ?>
                        <?= e($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <!---- PREMIUM FOOTER ---->
    <footer class="footer-premium pt-5 pb-0">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <span class="footer-brand-icon"><img src="<?= getSiteLogo('32') ?>" alt="<?= e(getSiteName()) ?>" style="height:32px;width:32px;object-fit:contain;border-radius:6px;"></span>
                        <span class="footer-brand"><?= e(getSiteName()) ?></span>
                    </div>
                    <p class="mb-3" style="line-height:1.7;"><?= e(getSettingValue('site_tagline', 'Your home away from home. We provide comfortable, safe, and affordable boarding house accommodations for students.')) ?></p>
                    <div class="d-flex gap-2">
                        <?php $fb = getSettingValue('facebook_url', '#'); ?>
                        <?php $tw = getSettingValue('twitter_url', '#'); ?>
                        <?php $ig = getSettingValue('instagram_url', '#'); ?>
                        <?php if (!empty($fb) && $fb !== '#'): ?>
                            <a href="<?= e($fb) ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($tw) && $tw !== '#'): ?>
                            <a href="<?= e($tw) ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($ig) && $ig !== '#'): ?>
                            <a href="<?= e($ig) ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled" style="line-height:2.2;">
                        <li><a href="<?= url('/') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> Home</a></li>
                        <li><a href="<?= url('/about') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> About Us</a></li>
                        <li><a href="<?= url('/rooms') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> Rooms</a></li>
                        <li><a href="<?= url('/amenities') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> Amenities</a></li>
                        <li><a href="<?= url('/gallery') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> Gallery</a></li>
                        <li><a href="<?= url('/contact') ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.65rem;"></i> Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact Info</h5>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= e(getSettingValue('site_address', 'Pili Madredijos, Cebu')) ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?= e(getSettingValue('site_phone', '0945-495-5140')) ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?= e(getSettingValue('site_email', 'warlitovelliganio@gmail.com')) ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon - Fri: <?= e(getSettingValue('home_hours_weekday', '8:00 AM - 8:00 PM')) ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Sat: <?= e(getSettingValue('home_hours_saturday', '8:00 AM - 6:00 PM')) ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-calendar-xmark"></i>
                        <span>Sun: <?= e(getSettingValue('home_hours_sunday', 'Closed')) ?></span>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="footer-bottom-bar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0" style="font-size:0.82rem;color:#64748b;">&copy; 2014 - <?= serverDate('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                        <a href="<?= url('/privacy') ?>" class="me-3" style="font-size:0.82rem;">Privacy Policy</a>
                        <a href="<?= url('/terms') ?>" style="font-size:0.82rem;">Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= asset('js/money.js') ?>"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
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
