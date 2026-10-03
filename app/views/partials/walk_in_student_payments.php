<?php $old = $old ?? []; $errors = $errors ?? []; $baseUrl = $baseUrl ?? '/admin'; $selectedStudent = $selectedStudent ?? null; $roomRent = $roomRent ?? 0; $roomAdvance = $roomAdvance ?? 0; $roomLabel = $roomLabel ?? ''; $nonTenantStudents = $nonTenantStudents ?? []; $availableRooms = $availableRooms ?? []; ?>
<?php if (!function_exists('fe4')) {
    function fe4(string $f, array $e): string { $msg = $e[$f] ?? ''; return $msg ? '<div class="field-error"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($msg) . '</div>' : ''; }
}
if (!function_exists('hasErr4')) {
    function hasErr4(string $f, array $e): string { return !empty($e[$f]) ? ' is-invalid' : ''; }
} ?>

<style>.field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;font-weight:500;}.form-control.is-invalid,.form-select.is-invalid{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.15);}</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Walk-In Student Payment</h1>
    <a href="<?= url($baseUrl . '/dashboard') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<div class="alert alert-info py-2 px-3 mb-4" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-info-circle me-1"></i> Select a registered student who is <strong>not yet a tenant</strong>, assign a room, collect payment — they will be automatically promoted to tenant.
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-exclamation-triangle me-1"></i>Please check the following:
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url($baseUrl . '/students/walk-in-student-payment') ?>" id="studentSelectForm" class="row g-3 align-items-end">
            <div class="col-md-11">
                <select name="student_id" id="studentSelect" class="form-select">
                    <option value="">— Select a registered student —</option>
                    <?php foreach ($nonTenantStudents as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $studentId == $s['id'] ? 'selected' : '' ?>><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['student_id_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedStudent): ?>
<?php if (empty($nonTenantStudents)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
        <h5 class="fw-bold text-muted mb-1">All students are already tenants</h5>
        <p class="text-muted mb-0">Every registered student has an approved reservation.</p>
    </div>
</div>
<?php else: ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10 text-primary fw-bold py-3">
                <i class="fas fa-user-graduate me-2"></i>Student Information
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted small" style="width:40%">Name</th><td class="fw-semibold"><?= e($selectedStudent['first_name'] . ' ' . $selectedStudent['last_name']) ?></td></tr>
                    <tr><th class="text-muted small">Student ID</th><td class="fw-semibold"><?= e($selectedStudent['student_id_number']) ?></td></tr>
                    <tr><th class="text-muted small">Email</th><td class="fw-semibold"><?= e($selectedStudent['email']) ?></td></tr>
                    <tr><th class="text-muted small">Status</th><td><span class="badge bg-success bg-opacity-10 text-success">Will become Tenant</span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <form method="POST" action="<?= url($baseUrl . '/students/walk-in-student-payment') ?>" id="paymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="student_id" value="<?= (int)$selectedStudent['id'] ?>">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-door-open me-2 text-info"></i>Room Assignment <span class="text-muted fw-normal small">— Required to become tenant</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Room <span class="text-danger">*</span></label>
                            <select name="room_id" id="roomSelect" class="form-select<?= hasErr4('room_id', $errors) ?>" required>
                                <option value="">— Choose a room —</option>
                                <?php foreach ($availableRooms as $rm): ?>
                                <?php $slotsLeft = (int)$rm['max_capacity'] - (int)$rm['current_occupancy']; ?>
                                <?php $images = $rm['images'] ?? []; ?>
                                <?php $imagesJson = json_encode(array_values($images), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
                                <option value="<?= (int)$rm['id'] ?>"
                                    data-rent="<?= (float)$rm['monthly_rent'] ?>"
                                    data-advance="<?= (float)$rm['advance_payment'] ?>"
                                    data-name="<?= e($rm['room_number'] . ' — ' . $rm['room_name']) ?>"
                                    data-capacity="<?= (int)$rm['max_capacity'] ?>"
                                    data-occupancy="<?= (int)$rm['current_occupancy'] ?>"
                                    data-type="<?= e($rm['room_type']) ?>"
                                    data-status="<?= e($rm['status']) ?>"
                                    data-images='<?= $imagesJson ?>'>
                                    <?= e($rm['room_number'] . ' — ' . $rm['room_name']) ?> · <?= e(ucwords($rm['room_type'])) ?> · <?= $slotsLeft > 0 ? $slotsLeft . ' slot' . ($slotsLeft > 1 ? 's' : '') . ' left' : 'Full' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?= fe4('room_id', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Move-In Date</label>
                            <input type="date" name="move_in_date" id="moveInDate" class="form-control" value="<?= e($old['move_in_date'] ?? date('Y-m-d')) ?>" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+3 months')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="3" name="duration" id="durationMonths" class="form-control" value="<?= e($old['duration'] ?? '1') ?>">
                                <span class="input-group-text">mo.</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2" id="roomInfoRow" style="display:none;">
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3">
                                <div class="row text-center mb-2">
                                    <div class="col-12 col-md-3">
                                        <div class="small text-muted">Room Type</div>
                                        <div class="fw-bold" id="displayType">—</div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="small text-muted">Status</div>
                                        <div id="displayStatus">—</div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="small text-muted">Capacity</div>
                                        <div class="fw-bold" id="displayCapacity">—</div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="small text-muted">Photos</div>
                                        <div class="fw-bold" id="displayImageCount">—</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div id="roomCarousel" class="carousel slide" data-bs-ride="false" style="display:none;">
                                            <div class="carousel-inner rounded" id="roomCarouselInner"></div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev" style="display:none;">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next" style="display:none;">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                            <div class="carousel-indicators" id="roomCarouselIndicators"></div>
                                        </div>
                                        <div id="roomNoImage" class="text-center text-muted py-4" style="display:none;">
                                            <i class="fas fa-image fa-2x mb-2 d-block"></i>
                                            <small>No images available</small>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted"><i class="fas fa-mouse-pointer me-1"></i>Click image to enlarge</small>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-1">
                                <div class="row text-center">
                                    <div class="col-12 col-md-4">
                                        <div class="small text-muted">Monthly Rent</div>
                                        <div class="fw-bold fs-5" id="displayRent">—</div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="small text-muted">Advance Payment</div>
                                        <div class="fw-bold fs-5" id="displayAdvance">—</div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="small text-muted">Total Due (Rent × Duration + Advance)</div>
                                        <div class="fw-bold fs-5 text-success" id="displayTotal">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Payment Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Amount Received <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= e(getCurrencySymbol()) ?></span>
                                <input type="number" step="1" min="0" name="payment_received" id="paymentReceived" class="form-control" value="<?= e($old['payment_received'] ?? '0') ?>">
                            </div>
                            <?= fe4('payment_received', $errors) ?>
                            <div class="form-text">Set to 0 if no payment collected now.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethodSelect" class="form-select<?= hasErr4('payment_method', $errors) ?>">
                                <option value="cash" <?= ($old['payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="gcash" <?= ($old['payment_method'] ?? '') === 'gcash' ? 'selected' : '' ?>>GCash</option>
                            </select>
                            <?= fe4('payment_method', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reference / OR Number <span class="text-danger gcash-required d-none" id="refRequired">*</span></label>
                            <input type="text" name="payment_reference" id="paymentReference" class="form-control" placeholder="Official receipt or GCash ref no." value="<?= e($old['payment_reference'] ?? '') ?>">
                            <div class="form-text gcash-hint d-none" id="refHint">Required for GCash payments.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Notes</label>
                            <input type="text" name="payment_notes" class="form-control" placeholder="Optional notes" value="<?= e($old['payment_notes'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3 d-flex flex-wrap align-items-center gap-3">
                                <div class="me-auto">
                                    <div class="small text-muted">On submit: student becomes <strong>Tenant</strong> + reservation auto-approved</div>
                                </div>
                                <button type="submit" class="btn btn-success px-4 py-2 fw-bold" id="submitBtn" <?= empty($availableRooms) ? 'disabled' : '' ?>>
                                    <i class="fas fa-user-tag me-1"></i>Register as Tenant & Record Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>
<?php else: ?>
<?php if (empty($nonTenantStudents)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
        <h5 class="fw-bold text-muted mb-1">All students are already tenants</h5>
        <p class="text-muted mb-0">Every registered student has an approved reservation.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-user-graduate fa-3x text-primary mb-3 d-block"></i>
        <h5 class="fw-bold text-muted mb-1">Select a student to get started</h5>
        <p class="text-muted mb-0">Choose a registered student above who does not have a reservation yet.</p>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- GCash QR Modal -->
<div class="modal fade" id="paymentAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
            <div class="modal-body text-center p-4">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="mb-3 mt-3"><i class="fas fa-qrcode fa-3x text-primary"></i></div>
                <h5 class="fw-bold mb-1">GCash</h5>
                <p class="text-muted small mb-4">Pay using your GCash app</p>
                <div class="mb-3 d-flex justify-content-center">
                    <div style="background:#fff;border:2px dashed #dee2e6;border-radius:16px;padding:16px;width:220px;text-align:center">
                        <img src="<?= url('/public/images/gcash_qr.jpg') ?>" alt="GCash QR Code" style="width:180px;height:auto;border-radius:8px">
                        <p class="text-muted small mt-2 mb-0">Scan to pay</p>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i> Go back to payment methods
                    </button>
                </div>
                <hr class="my-4">
                <small class="text-muted d-block">After payment, enter the reference number above and submit.</small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('studentSelect');
    if (select) {
        select.addEventListener('change', function() {
            if (this.value !== '') document.getElementById('studentSelectForm').submit();
        });
    }

    var roomSelect = document.getElementById('roomSelect');
    var durationInput = document.getElementById('durationMonths');
    var moveInDate = document.getElementById('moveInDate');
    var infoRow = document.getElementById('roomInfoRow');
    var displayRent = document.getElementById('displayRent');
    var displayAdvance = document.getElementById('displayAdvance');
    var displayTotal = document.getElementById('displayTotal');
    var displayType = document.getElementById('displayType');
    var displayStatus = document.getElementById('displayStatus');
    var displayCapacity = document.getElementById('displayCapacity');
    var displayImageCount = document.getElementById('displayImageCount');
    var roomCarousel = document.getElementById('roomCarousel');
    var roomCarouselInner = document.getElementById('roomCarouselInner');
    var roomCarouselIndicators = document.getElementById('roomCarouselIndicators');
    var roomNoImage = document.getElementById('roomNoImage');
    var carouselPrev = roomCarousel ? roomCarousel.querySelector('.carousel-control-prev') : null;
    var carouselNext = roomCarousel ? roomCarousel.querySelector('.carousel-control-next') : null;
    var paymentReceived = document.getElementById('paymentReceived');
    var symbol = <?= json_encode(getCurrencySymbol()) ?>;
    var currentImages = [];

    function fmt(n) {
        return symbol + Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function buildCarousel(images) {
        currentImages = images || [];
        if (!roomCarousel || !roomCarouselInner || !roomCarouselIndicators || !roomNoImage) return;
        if (!images || images.length === 0) {
            roomCarousel.style.display = 'none';
            roomNoImage.style.display = 'block';
            return;
        }
        roomCarouselInner.innerHTML = '';
        roomCarouselIndicators.innerHTML = '';
        images.forEach(function(img, index) {
            var indicator = document.createElement('button');
            indicator.type = 'button';
            indicator.setAttribute('data-bs-target', '#roomCarousel');
            indicator.setAttribute('data-bs-slide-to', index);
            indicator.setAttribute('aria-label', 'Slide ' + (index + 1));
            if (index === 0) {
                indicator.className = 'active';
                indicator.setAttribute('aria-current', 'true');
            }
            roomCarouselIndicators.appendChild(indicator);

            var item = document.createElement('div');
            item.className = 'carousel-item' + (index === 0 ? ' active' : '');
            var imageUrl = '<?= url("") ?>' + img.image_path;
            var altText = img.alt_text || 'Room image ' + (index + 1);
            item.innerHTML = '<img src="' + imageUrl + '" class="d-block w-100 rounded" alt="' + altText + '" style="max-height:300px;object-fit:cover;cursor:pointer;" data-index="' + index + '">';
            roomCarouselInner.appendChild(item);
        });
        roomCarousel.style.display = 'block';
        roomNoImage.style.display = 'none';
        if (carouselPrev) carouselPrev.style.display = images.length > 1 ? 'block' : 'none';
        if (carouselNext) carouselNext.style.display = images.length > 1 ? 'block' : 'none';
    }

    function updateRoomInfo() {
        if (!roomSelect || !infoRow) return;
        var opt = roomSelect.options[roomSelect.selectedIndex];
        if (!opt || opt.value === '') { infoRow.style.display = 'none'; return; }
        var rent = parseFloat(opt.dataset.rent) || 0;
        var advance = parseFloat(opt.dataset.advance) || 0;
        var months = parseInt(durationInput.value, 10) || 1;
        if (months < 1) months = 1;
        if (months > 3) months = 3;
        var totalRent = rent * months;
        var totalDue = totalRent + advance;
        var maxCap = parseInt(opt.dataset.capacity) || 0;
        var occ = parseInt(opt.dataset.occupancy) || 0;
        var slotsLeft = maxCap - occ;
        var typeName = opt.dataset.type || '';
        var status = opt.dataset.status || '';
        var images = [];
        try {
            images = JSON.parse(opt.dataset.images || '[]');
        } catch (e) {
            images = [];
        }
        var imageCount = images.length;

        var statusLabels = {available: 'Available', reserved: 'Reserved', occupied: 'Occupied', under_maintenance: 'Under Maintenance'};
        var statusBadges = {available: 'success', reserved: 'warning', occupied: 'danger', under_maintenance: 'secondary'};
        var sLabel = statusLabels[status] || status;
        var sBadge = statusBadges[status] || 'secondary';

        if (displayType) displayType.textContent = typeName.charAt(0).toUpperCase() + typeName.slice(1);
        if (displayStatus) displayStatus.innerHTML = '<span class="badge bg-' + sBadge + ' bg-opacity-10 text-' + sBadge + '">' + sLabel + '</span>';
        if (displayCapacity) displayCapacity.textContent = occ + ' / ' + maxCap + ' beds (' + slotsLeft + ' slot' + (slotsLeft !== 1 ? 's' : '') + ' left)';
        if (displayImageCount) displayImageCount.textContent = imageCount + ' photo' + (imageCount !== 1 ? 's' : '');
        displayRent.textContent = fmt(rent);
        displayAdvance.textContent = fmt(advance);
        displayTotal.textContent = fmt(totalDue);

        buildCarousel(images);

        infoRow.style.display = '';

        if (paymentReceived) {
            paymentReceived.value = Math.trunc(totalDue);
        }
    }

    if (roomSelect) roomSelect.addEventListener('change', updateRoomInfo);
    if (durationInput) durationInput.addEventListener('input', updateRoomInfo);
    updateRoomInfo();

    var payMethod = document.getElementById('paymentMethodSelect');
    var refInput = document.getElementById('paymentReference');
    var refReq = document.getElementById('refRequired');
    var refHint = document.getElementById('refHint');
    function toggleRefRequired() {
        var isGcash = payMethod && payMethod.value === 'gcash';
        if (refInput) refInput.required = isGcash;
        if (refReq) refReq.classList.toggle('d-none', !isGcash);
        if (refHint) refHint.classList.toggle('d-none', !isGcash);
    }
    if (payMethod) payMethod.addEventListener('change', toggleRefRequired);
    toggleRefRequired();

    // Show GCash QR modal when GCash is selected
    var gcashQrModal = new bootstrap.Modal(document.getElementById('paymentAppModal'));
    function showGcashQr() {
        if (payMethod && payMethod.value === 'gcash') gcashQrModal.show();
    }
    if (payMethod) payMethod.addEventListener('change', showGcashQr);
    showGcashQr();

    // Image Modal for full-size viewing
    var imageModal = new bootstrap.Modal(document.getElementById('roomImageModal'), {backdrop: 'static', keyboard: true});
    var modalImage = document.getElementById('modalRoomImage');
    var modalTitle = document.getElementById('modalRoomTitle');
    var currentImages = [];

    document.getElementById('roomCarouselInner')?.addEventListener('click', function(e) {
        var img = e.target.closest('img');
        if (img && currentImages.length > 0) {
            var index = parseInt(img.dataset.index, 10);
            if (!isNaN(index)) {
                openModal(index);
            }
        }
    });

    function openModal(index) {
        if (!modalImage || !modalTitle || currentImages.length === 0) return;
        var img = currentImages[index];
        modalImage.src = '<?= url("") ?>' + img.image_path;
        modalImage.alt = img.alt_text || 'Room image ' + (index + 1);
        modalTitle.textContent = 'Room Image ' + (index + 1) + ' of ' + currentImages.length;
        imageModal.show();
    }
});
</script>

<!-- Image Modal -->
<div class="modal fade" id="roomImageModal" tabindex="-1" aria-labelledby="roomImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRoomTitle">Room Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalRoomImage" src="" alt="" class="img-fluid" style="max-height:70vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
