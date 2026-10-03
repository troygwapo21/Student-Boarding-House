<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $faqs = $faqs ?? []; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">FAQs</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Frequently Asked Questions</h1>
    </div>
</section>

<section class="section-padding">
    <div class="container">
     <div class="card border-0 shadow-sm p-5" style="border-radius:16px;background:darkwhite;">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <?php if (!empty($faqs)): ?>
                    <div class="accordion" id="faqsAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius:12px !important;overflow:hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $index ?>" style="color:#1e293b;">
                                        <i class="fas fa-question-circle text-primary me-2"></i>
                                        <?= e($faq['question']) ?>
                                    </button>
                                </h2>
                                <div id="faq<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faqsAccordion">
                                    <div class="accordion-body text-muted" style="line-height:1.8;">
                                        <?= nl2br(e($faq['answer'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <h4 class="fw-bold">No FAQs Available</h4>
                        <p class="text-muted">We're working on adding helpful FAQs. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
         </div>
      </div>

        <div class="text-center mt-5">
            <div class="card border-0 shadow-sm p-5" style="border-radius:16px;background:darkwhite;">
                <h4 class="fw-bold mb-2">Still Have Questions?</h4>
                <p class="text-muted mb-4">Can't find what you're looking for? Feel free to reach out to us.</p>
                <a href="<?= url('/contact') ?>" class="btn btn-primary px-4 py-2 fw-medium">
                    <i class="fas fa-envelope me-2"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
