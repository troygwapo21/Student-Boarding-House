<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Terms & Conditions</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Terms & Conditions</h1>
        
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4">1. Acceptance of Terms</h4>
                        <p class="text-muted mb-4">By accessing and using the <?= e(getSiteName()) ?> website and services, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you may not use our services.</p>

                        <h4 class="fw-bold mb-4">2. Eligibility</h4>
                        <p class="text-muted mb-4">Our services are primarily intended for tenants seeking boarding house accommodations. By registering, you confirm that you are at least 18 years of age or have parental/guardian consent.</p>

                        <h4 class="fw-bold mb-4">3. Registration & Accounts</h4>
                        <p class="text-muted mb-2">When creating an account, you agree to:</p>
                        <ul class="text-muted mb-4">
                            <li class="mb-2">Provide accurate, current, and complete information.</li>
                            <li class="mb-2">Maintain the confidentiality of your account credentials.</li>
                            <li class="mb-2">Notify us immediately of any unauthorized use of your account.</li>
                            <li class="mb-2">Accept responsibility for all activities that occur under your account.</li>
                        </ul>

                        <h4 class="fw-bold mb-4">4. Reservations & Payments</h4>
                        <p class="text-muted mb-2">All reservations are subject to availability and approval. By making a reservation:</p>
                        <ul class="text-muted mb-4">
                            <li class="mb-2">You agree to pay the required fees including monthly rent, security deposit, and advance payment.</li>
                            <li class="mb-2">Payments are non-refundable unless otherwise specified in our refund policy.</li>
                            <li class="mb-2">Late payments may result in penalties or cancellation of your reservation.</li>
                        </ul>

                        <h4 class="fw-bold mb-4">5. House Rules</h4>
                        <p class="text-muted mb-2">All residents must comply with the following house rules:</p>
                        <ul class="text-muted mb-4">
                            <li class="mb-2">Maintain cleanliness and orderliness in all common areas.</li>
                            <li class="mb-2">Respect the quiet hours (10:00 PM - 7:00 AM).</li>
                            <li class="mb-2">No smoking, alcohol, or illegal substances within the premises.</li>
                            <li class="mb-2">No unauthorized guests staying overnight without prior approval.</li>
                            <li class="mb-2">Report any maintenance issues promptly.</li>
                        </ul>

                        <h4 class="fw-bold mb-4">6. Cancellation Policy</h4>
                        <p class="text-muted mb-4">Cancellations must be made at least 30 days before the intended move-out date. Failure to provide adequate notice may result in the forfeiture of the security deposit. Early termination of the lease agreement is subject to applicable penalties.</p>

                        <h4 class="fw-bold mb-4">7. Liability</h4>
                        <p class="text-muted mb-4"><?= e(getSiteName()) ?> is not liable for any loss, theft, or damage to personal property. Residents are encouraged to secure their belongings and obtain appropriate insurance coverage.</p>

                        <h4 class="fw-bold mb-4">8. Termination</h4>
                        <p class="text-muted mb-4">We reserve the right to terminate your access to our services and request immediate vacating of the premises for violations of these Terms and Conditions, non-payment, or behavior that is disruptive to other residents.</p>

                        <h4 class="fw-bold mb-4">9. Modifications</h4>
                        <p class="text-muted mb-4">We reserve the right to modify these Terms and Conditions at any time. Continued use of our services after changes constitutes acceptance of the modified terms.</p>

                        <h4 class="fw-bold mb-4">10. Contact</h4>
                        <p class="text-muted mb-0">For questions regarding these Terms and Conditions, please contact us at:</p>
                        <p class="text-muted mb-0"><strong>Email:</strong><?= e($settings['site_email'] ?? 'warlitovelliganio@gmail.com') ?></p>
                        <p class="text-muted"><strong>Phone:</strong><?= e($settings['site_phone'] ?? '0945-495-5140') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
