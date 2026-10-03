<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Submit Feedback</h4>
    <a href="<?= url('/student/feedback') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <form method="POST" action="<?= url('/student/feedback/create') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="subject" placeholder="Brief subject of your feedback" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="message" rows="6" placeholder="Share your feedback with us..." required></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category</label>
                        <select class="form-select" name="category">
                            <option value="suggestion">Suggestion</option>
                            <option value="compliment">Compliment</option>
                            <option value="complaint">Complaint</option>
                            <option value="inquiry">Inquiry</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rating</label>
                        <div class="d-flex gap-1 mt-1" id="ratingStars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star star-btn" data-rating="<?= $i ?>" style="font-size: 28px; color: #d1d5db; cursor: pointer; transition: color 0.2s;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="5">
                        <small class="text-muted">Click to rate (1-5)</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/student/feedback') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var stars = document.querySelectorAll('.star-btn');
    var ratingInput = document.getElementById('ratingInput');
    var currentRating = 5;

    function updateStars(rating) {
        stars.forEach(function(star) {
            var val = parseInt(star.getAttribute('data-rating'));
            star.style.color = val <= rating ? '#f59e0b' : '#d1d5db';
        });
    }

    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.getAttribute('data-rating'));
            ratingInput.value = currentRating;
            updateStars(currentRating);
        });
        star.addEventListener('mouseenter', function() {
            updateStars(parseInt(this.getAttribute('data-rating')));
        });
        star.addEventListener('mouseleave', function() {
            updateStars(currentRating);
        });
    });

    updateStars(currentRating);
});
</script>
