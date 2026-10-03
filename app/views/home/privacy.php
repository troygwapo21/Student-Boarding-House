<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Privacy Policy</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Privacy Policy</h1>
     
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4">1. Introduction</h4>
                        <p class="text-muted mb-4">Welcome to <?= e(getSiteName()) ?>. We are committed to protecting your privacy and personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our services and visit our website.</p>

                        <h4 class="fw-bold mb-4">2. Information We Collect</h4>
                        <p class="text-muted mb-2">We may collect the following types of information:</p>
                        <ul class="text-muted mb-4">
                            <li class="mb-2"><strong>Personal Information:</strong> Name, email address, phone number, school/university, course/program, and other registration details.</li>
                            <li class="mb-2"><strong>Account Information:</strong> User credentials, role, and account preferences.</li>
                            <li class="mb-2"><strong>Payment Information:</strong> Transaction details related to room reservations and payments.</li>
                            <li class="mb-2"><strong>Usage Data:</strong> Information about how you interact with our website and services.</li>
                            <li class="mb-2"><strong>Device Information:</strong> Browser type, operating system, IP address, and device identifiers.</li>
                        </ul>

                        <h4 class="fw-bold mb-4">3. How We Use Your Information</h4>
                        <p class="text-muted mb-2">We use the collected information for the following purposes:</p>
                        <ul class="text-muted mb-4">
                            <li class="mb-2">To process room reservations and manage your tenancy.</li>
                            <li class="mb-2">To communicate with you about your reservations, payments, and inquiries.</li>
                            <li class="mb-2">To provide customer support and respond to your requests.</li>
                            <li class="mb-2">To improve our services and website functionality.</li>
                            <li class="mb-2">To send important notifications and announcements.</li>
                            <li class="mb-2">To ensure the security and integrity of our platform.</li>
                        </ul>

                        <h4 class="fw-bold mb-4">4. Information Sharing</h4>
                        <p class="text-muted mb-4">We do not sell, trade, or rent your personal information to third parties. We may share your information only with trusted service providers who assist us in operating our platform, subject to confidentiality agreements.</p>

                        <h4 class="fw-bold mb-4">5. Data Security</h4>
                        <p class="text-muted mb-4">We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet is 100% secure.</p>

                        <h4 class="fw-bold mb-4">6. Cookies</h4>
                        <p class="text-muted mb-4">Our website may use cookies and similar tracking technologies to enhance your experience. You can control cookie preferences through your browser settings.</p>

                        <h4 class="fw-bold mb-4">7. Your Rights</h4>
                        <p class="text-muted mb-4">You have the right to access, correct, or delete your personal information. You may also opt out of certain communications. To exercise these rights, please contact us at <?= e($settings['site_email'] ?? 'warlitovelliganio@gmail.com') ?>.</p>

                        <h4 class="fw-bold mb-4">8. Changes to This Policy</h4>
                        <p class="text-muted mb-4">We reserve the right to update this Privacy Policy at any time. We will notify you of any material changes by posting the new policy on this page with an updated effective date.</p>

                        <h4 class="fw-bold mb-4">9. Contact Us</h4>
                        <p class="text-muted mb-0">If you have any questions about this Privacy Policy, please contact us at:</p>
                        <p class="text-muted mb-0"><strong>Email:</strong> <?= e($settings['site_email'] ?? 'warlitovelliganio@gmail.com') ?></p> 
                        <p class="text-muted mb-0 small"><strong>Phone:</strong> <?= e($settings['site_phone'] ?? '0945-495-5140') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
