<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $room = $room ?? []; ?>
<?php $images = $images ?? []; ?>
<?php $amenities = $amenities ?? []; ?>
<?php $similarRooms = $similarRooms ?? []; ?>
<?php $furniture = !empty($room['furniture']) ? array_map('trim', explode(',', $room['furniture'])) : []; ?>
<?php $rules = !empty($room['house_rules']) ? explode("\n", $room['house_rules']) : []; ?>

<?php
$cap = max(1, (int)($room['max_capacity'] ?? 1));
$occ = max((int)($room['live_occupancy'] ?? 0), (int)($room['approved_count'] ?? 0));
$avail = max(0, $cap - $occ);
$pct = min(100, round(($occ / $cap) * 100));
$barColor = $pct <= 50 ? '#22c55e' : ($pct <= 80 ? '#f59e0b' : ($pct < 100 ? '#f97316' : '#ef4444'));
$statusColors = ['available' => '#22c55e', 'occupied' => '#3b82f6', 'under_maintenance' => '#f59e0b', 'reserved' => '#06b6d4'];
$statusBg = ['available' => 'bg-success', 'occupied' => 'bg-primary', 'reserved' => 'bg-info', 'under_maintenance' => 'bg-warning text-dark'];
$rt = $room['room_type'] ?? 'bedspacer';
$rtLabels = ['bedspacer' => 'Bedspace', 'single' => 'Single', 'studio' => 'Studio'];
$isFull = $avail <= 0 && ($room['status'] ?? '') !== 'under_maintenance';
$totalMoveIn = ($room['monthly_rent'] ?? 0) + ($room['advance_payment'] ?? 0);
$detailStatusLabel = ($room['status'] ?? '') === 'under_maintenance'
    ? 'Under Maintenance'
    : ($isFull ? 'Fully Occupied' : (($room['status'] ?? '') === 'reserved' ? 'Reserved' : ($occ > 0 ? 'Occupied' : 'Available')));
$detailStatusClass = ($room['status'] ?? '') === 'under_maintenance'
    ? 'bg-warning text-dark'
    : ($isFull ? 'bg-danger' : ($statusBg[$room['status'] ?? 'available'] ?? 'bg-secondary'))
?>

<section class="page-header py-4" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:5rem 0 2.5rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/rooms') ?>" class="text-white-50">Rooms</a></li>
                <li class="breadcrumb-item active text-white"><?= e($room['room_name'] ?? 'Room') ?></li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div>
                <h1 class="display-6 fw-bold mb-1"><?= e($room['room_name'] ?? 'Room Detail') ?></h1>
                <p class="mb-0 opacity-75"><i class="fas fa-door-open me-1"></i> Room <?= e($room['room_number'] ?? '') ?> &middot; <?= $rtLabels[$rt] ?? ucfirst($rt) ?></p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <?php if (($room['status'] ?? '') === 'under_maintenance'): ?>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-wrench me-1"></i>Maintenance</span>
                <?php elseif ($isFull): ?>
                <span class="badge bg-danger fs-6 px-3 py-2">Fully Occupied</span>
                <?php else: ?>
                <span class="badge bg-success fs-6 px-3 py-2"><?= $avail ?> slot<?= $avail !== 1 ? 's' : '' ?> left</span>
                <?php endif; ?>
                <?php if (!empty($room['is_featured'])): ?>
                <span class="badge fs-6 px-3 py-2" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">
                    <i class="fas fa-star me-1"></i>Featured
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section-padding pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">

                <?php
                $allImages = [];
                if (!empty($room['primary_image'])) {
                    $allImages[] = ['path' => $room['primary_image'], 'alt' => $room['room_name']];
                }
                if (!empty($images)) {
                    foreach ($images as $img) {
                        if (empty($room['primary_image']) || $img['image_path'] !== $room['primary_image']) {
                            $allImages[] = ['path' => $img['image_path'], 'alt' => $img['alt_text'] ?? $room['room_name']];
                        }
                    }
                }
                $hasImages = count($allImages) > 0;
                ?>

                <?php if ($hasImages): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                    <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                        <?php if (count($allImages) > 1): ?>
                        <div class="carousel-indicators mb-0" style="bottom:12px;">
                            <?php foreach ($allImages as $i => $img): ?>
                                <button type="button" data-bs-target="#roomCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" style="width:10px;height:10px;border-radius:50%;<?= $i === 0 ? 'background-color:#2563eb;' : 'background-color:rgba(255,255,255,0.6);' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="carousel-inner" style="aspect-ratio:16/9;">
                            <?php foreach ($allImages as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                <img src="<?= UPLOAD_URL . $img['path'] ?>" alt="<?= e($img['alt']) ?>" class="d-block w-100 h-100" style="object-fit:cover;cursor:pointer;" onclick="openLightbox(<?= $i ?>)">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($allImages) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev" style="width:48px;height:48px;top:50%;transform:translateY(-50%);left:12px;background:rgba(0,0,0,0.5);border-radius:50%;opacity:1;transition:background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.7)'" onmouseout="this.style.background='rgba(0,0,0,0.5)'">
                            <span class="carousel-control-prev-icon" style="width:18px;height:18px;"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next" style="width:48px;height:48px;top:50%;transform:translateY(-50%);right:12px;background:rgba(0,0,0,0.5);border-radius:50%;opacity:1;transition:background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.7)'" onmouseout="this.style.background='rgba(0,0,0,0.5)'">
                            <span class="carousel-control-next-icon" style="width:18px;height:18px;"></span>
                        </button>
                        <?php endif; ?>

                        <div class="position-absolute top-0 end-0 m-3 d-flex gap-2" style="z-index:10;">
                            <span class="badge bg-dark bg-opacity-75 fs-6"><?= $rtLabels[$rt] ?? ucfirst($rt) ?></span>
                            <span class="badge <?= $detailStatusClass ?> fs-6">
                                <?= $detailStatusLabel ?>
                            </span>
                            <?php if (!empty($room['is_featured'])): ?>
                            <span class="badge fs-6" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;"><i class="fas fa-star me-1"></i>Featured</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($hasImages): ?>
                        <div class="position-absolute bottom-0 start-0 m-3" style="z-index:10;">
                            <button type="button" class="btn btn-dark bg-opacity-75 btn-sm" onclick="openLightbox(0)" style="backdrop-filter:blur(4px);">
                                <i class="fas fa-expand me-1"></i> View All (<?= count($allImages) ?>)
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($allImages) > 1): ?>
                <div class="d-flex gap-2 mb-4 overflow-auto pb-2" id="roomThumbnails" style="scrollbar-width:thin;">
                    <?php foreach ($allImages as $i => $img): ?>
                    <div class="flex-shrink-0 rounded overflow-hidden thumbnail-item" style="width:90px;height:65px;cursor:pointer;border:2px solid <?= $i === 0 ? '#2563eb' : '#e2e8f0' ?>;transition:all 0.2s;" onclick="switchSlide(<?= $i ?>)" data-index="<?= $i ?>">
                        <img src="<?= UPLOAD_URL . $img['path'] ?>" alt="<?= e($img['alt']) ?>" class="w-100 h-100" style="object-fit:cover;">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                    <div class="d-flex align-items-center justify-content-center position-relative" style="min-height:320px;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);">
                        <i class="fas fa-bed fa-5x text-muted opacity-40"></i>
                        <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
                            <span class="badge bg-dark bg-opacity-75 fs-6"><?= $rtLabels[$rt] ?? ucfirst($rt) ?></span>
                            <span class="badge <?= $detailStatusClass ?> fs-6">
                                <?= $detailStatusLabel ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                $features = [];
                if (!empty($room['has_aircon'])) $features[] = ['icon' => 'fas fa-snowflake', 'label' => 'Air Conditioning', 'color' => '#3b82f6'];
                if (!empty($room['has_bathroom'])) $features[] = ['icon' => 'fas fa-bath', 'label' => 'Private Bathroom', 'color' => '#06b6d4'];
                if (!empty($room['has_balcony'])) $features[] = ['icon' => 'fas fa-building', 'label' => 'Balcony', 'color' => '#8b5cf6'];
                if (!empty($room['size_sqm'])) $features[] = ['icon' => 'fas fa-ruler-combined', 'label' => e($room['size_sqm']) . ' m&sup2;', 'color' => '#f59e0b'];
                if (!empty($room['floor'])) $features[] = ['icon' => 'fas fa-layer-group', 'label' => 'Floor ' . e($room['floor']), 'color' => '#10b981'];
                ?>
                <?php if (!empty($features)): ?>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php foreach ($features as $f): ?>
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background:<?= $f['color'] ?>10;border:1px solid <?= $f['color'] ?>20;">
                        <i class="<?= $f['icon'] ?>" style="color:<?= $f['color'] ?>;"></i>
                        <span class="fw-medium small" style="color:<?= $f['color'] ?>;"><?= $f['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>Room Information</h4>
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#eff6ff;">
                                            <i class="fas fa-door-open text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">ROOM NUMBER</small>
                                            <span class="fw-bold"><?= e($room['room_number'] ?? 'N/A') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#f0fdf4;">
                                            <i class="fas fa-money-bill text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">MONTHLY RENT</small>
                                            <span class="fw-bold text-success"><?= formatCurrency($room['monthly_rent'] ?? 0) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#fef3c7;">
                                            <i class="fas fa-credit-card" style="color:#d97706;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">ADVANCE PAYMENT</small>
                                            <span class="fw-bold" style="color:#d97706;"><?= formatCurrency($room['advance_payment'] ?? 0) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#ede9fe;">
                                            <i class="fas fa-users" style="color:#7c3aed;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">CAPACITY</small>
                                            <span class="fw-bold"><?= e($room['max_capacity'] ?? 'N/A') ?> person(s)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#ecfdf5;">
                                            <i class="fas fa-user-check" style="color:<?= $barColor ?>;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">BOARDERS</small>
                                            <span class="fw-bold"><?= $occ ?> / <?= $cap ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;background:#fef2f2;">
                                            <i class="fas fa-door-open" style="color:<?= $barColor ?>;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">AVAILABLE SLOTS</small>
                                            <span class="fw-bold" style="color:<?= $barColor ?>;"><?= $avail ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background:#f8fafc;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-medium text-muted">Occupancy Rate</span>
                                        <span class="fw-bold" style="color:<?= $barColor ?>;"><?= $pct ?>%</span>
                                    </div>
                                    <div class="progress" style="height:10px;border-radius:5px;">
                                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:5px;transition:width 0.6s ease;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted"><?= $occ ?> occupied</small>
                                        <small class="text-muted"><?= $avail ?> available</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($room['description'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-align-left me-2 text-primary"></i>Description</h4>
                        <p class="text-muted mb-0" style="line-height:1.9;"><?= nl2br(e($room['description'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($amenities)): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-concierge-bell me-2 text-success"></i>Amenities</h4>
                        <div class="row g-2">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="col-6 col-sm-4">
                                    <div class="d-flex align-items-center p-3 rounded-3" style="background:#f0fdf4;border:1px solid #dcfce7;">
                                        <i class="<?= e($amenity['icon'] ?? 'fas fa-check') ?> text-success me-2"></i>
                                        <span class="fw-medium small"><?= e($amenity['name']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($furniture)): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-couch me-2" style="color:#8b5cf6;"></i>Furniture</h4>
                        <div class="row g-2">
                            <?php
                            $furnitureIcons = ['bed' => 'fas fa-bed', 'desk' => 'fas fa-desktop', 'chair' => 'fas fa-chair', 'wardrobe' => 'fas fa-door-closed', 'fan' => 'fas fa-fan', 'light' => 'fas fa-lightbulb', 'mirror' => 'fas fa-circle', 'cabinet' => 'fas fa-box', 'shelf' => 'fas fa-book', 'table' => 'fas fa-table'];
                            foreach ($furniture as $item):
                                $fIcon = 'fas fa-couch';
                                $itemLower = strtolower($item);
                                foreach ($furnitureIcons as $key => $icon) {
                                    if (strpos($itemLower, $key) !== false) { $fIcon = $icon; break; }
                                }
                            ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex align-items-center p-2 rounded-3" style="background:#faf5ff;border:1px solid #ede9fe;">
                                        <i class="<?= $fIcon ?> me-2" style="color:#8b5cf6;"></i>
                                        <span class="small"><?= e($item) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($rules)): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-clipboard-list me-2 text-warning"></i>House Rules</h4>
                        <div class="d-flex flex-column gap-2">
                            <?php $ruleNum = 1; foreach ($rules as $rule): ?>
                                <?php if (trim($rule) !== ''): ?>
                                    <div class="d-flex align-items-start p-2 rounded-3" style="background:#fffbeb;border:1px solid #fef3c7;">
                                        <span class="badge bg-warning text-dark me-3 flex-shrink-0 mt-1" style="min-width:24px;"><?= $ruleNum++ ?></span>
                                        <span class="small" style="color:#92400e;"><?= e(trim($rule)) ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;position:sticky;top:90px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Price Summary</h5>
                            <?php if ($avail > 0 && ($room['status'] ?? '') !== 'reserved' && ($room['status'] ?? '') !== 'under_maintenance'): ?>
                            <span class="badge bg-success bg-opacity-10 text-success">Available</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-3 rounded-3 mb-3" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Monthly Rent</span>
                                <span class="fw-bold text-primary fs-5"><?= formatCurrency($room['monthly_rent'] ?? 0) ?></span>
                            </div>
                            <small class="text-muted">per month</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom:1px dashed #e2e8f0;">
                            <span class="text-muted small">Advance Payment</span>
                            <span class="fw-medium"><?= formatCurrency($room['advance_payment'] ?? 0) ?></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
                            <span class="fw-bold">Total Move-in Cost</span>
                            <span class="fw-bold fs-4" style="color:#2563eb;">
                                <?= formatCurrency($totalMoveIn) ?>
                            </span>
                        </div>

                        <?php if (($room['status'] ?? '') === 'under_maintenance'): ?>
                            <button class="btn btn-lg w-100 py-3 fw-semibold mb-3" style="background:#fef3c7;color:#92400e;" disabled>
                                <i class="fas fa-wrench me-2"></i> Under Maintenance
                            </button>
                            <div class="alert alert-warning small mb-0 rounded-3"><i class="fas fa-info-circle me-1"></i> This room is currently under maintenance and cannot accept reservations.</div>
                        <?php elseif ($avail <= 0): ?>
                            <button class="btn btn-lg w-100 py-3 fw-semibold mb-3" style="background:#fef2f2;color:#dc2626;" disabled>
                                <i class="fas fa-times-circle me-2"></i> Room Full
                            </button>
                            <div class="alert alert-danger small mb-0 rounded-3"><i class="fas fa-info-circle me-1"></i> This room has reached its maximum capacity and is no longer available for reservation.</div>
                        <?php elseif (($room['status'] ?? '') === 'reserved'): ?>
                            <button class="btn btn-secondary btn-lg w-100 py-3 fw-semibold mb-3" disabled>
                                <i class="fas fa-calendar-check me-2"></i> Currently Reserved
                            </button>
                            <div class="alert alert-info small mb-0 rounded-3"><i class="fas fa-info-circle me-1"></i> This room is currently reserved. Please check other available rooms.</div>
                        <?php else: ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?= url('/student/reservations') ?>" class="btn btn-primary btn-lg w-100 py-3 fw-semibold mb-3" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;box-shadow:0 4px 12px rgba(37,99,235,0.3);">
                                    <i class="fas fa-calendar-check me-2"></i> Reserve Now
                                </a>
                            <?php else: ?>
                                <a href="<?= url('/login') ?>" class="btn btn-primary btn-lg w-100 py-3 fw-semibold mb-3" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;box-shadow:0 4px 12px rgba(37,99,235,0.3);">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login to Reserve
                                </a>
                                <p class="text-center text-muted small mb-0">Don't have an account? <a href="<?= url('/register') ?>" class="text-primary fw-medium">Register here</a></p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="mt-4">
                            <div class="d-flex align-items-center mb-3 p-2 rounded-3" style="background:#f0fdf4;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px;height:36px;background:#dcfce7;">
                                    <i class="fas fa-shield-halved text-success" style="font-size:0.85rem;"></i>
                                </div>
                                <span class="small fw-medium">Secure Reservation Process</span>
                            </div>
                            <div class="d-flex align-items-center mb-3 p-2 rounded-3" style="background:#eff6ff;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px;height:36px;background:#dbeafe;">
                                    <i class="fas fa-headset text-primary" style="font-size:0.85rem;"></i>
                                </div>
                                <span class="small fw-medium">24/7 Support Available</span>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-3" style="background:#faf5ff;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px;height:36px;background:#ede9fe;">
                                    <i class="fas fa-money-bill-wave" style="font-size:0.85rem;color:#7c3aed;"></i>
                                </div>
                                <span class="small fw-medium">Flexible Payment Options</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($similarRooms)): ?>
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0">Similar Rooms</h3>
                <a href="<?= url('/rooms') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-th-large me-1"></i> View All</a>
            </div>
            <div class="row g-4">
                <?php foreach ($similarRooms as $sRoom): ?>
                    <?php
                    $sRt = $sRoom['room_type'] ?? 'bedspacer';
                    $sCap = max(1, (int)($sRoom['max_capacity'] ?? 1));
                    $sOcc = max((int)($sRoom['live_occupancy'] ?? 0), (int)($sRoom['approved_count'] ?? 0));
                    $sAvail = max(0, $sCap - $sOcc);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;overflow:hidden;transition:transform 0.3s,box-shadow 0.3s;cursor:pointer;" onclick="window.location='<?= url('/room/' . $sRoom['id']) ?>'">
                            <div class="position-relative" style="aspect-ratio:16/10;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);overflow:hidden;">
                                <?php if (!empty($sRoom['primary_image'])): ?>
                                    <img src="<?= UPLOAD_URL . $sRoom['primary_image'] ?>" alt="<?= e($sRoom['room_name']) ?>" class="w-100 h-100" style="object-fit:cover;display:block;transition:transform 0.4s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                        <i class="fas fa-bed fa-3x text-muted opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="badge position-absolute top-0 end-0 m-2 <?= $sRoom['status'] === 'available' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $sRoom['status'] === 'available' ? 'Available' : ucwords(str_replace('_', ' ', $sRoom['status'] ?? '')) ?>
                                </span>
                                <span class="badge position-absolute top-0 start-0 m-2" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.7rem;"><?= $rtLabels[$sRt] ?? ucfirst($sRt) ?></span>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1"><?= e($sRoom['room_name']) ?></h6>
                                <p class="text-muted small mb-2"><i class="fas fa-door-open me-1"></i>Room <?= e($sRoom['room_number']) ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary"><?= formatCurrency($sRoom['monthly_rent']) ?><small class="text-muted fw-normal" style="font-size:0.75rem;">/mo</small></span>
                                    <span class="text-muted small"><i class="fas fa-users me-1"></i><?= $sAvail ?> slot<?= $sAvail !== 1 ? 's' : '' ?> left</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php if (!empty($sRoom['has_aircon'])): ?><span class="badge bg-light text-dark" style="font-size:0.65rem;"><i class="fas fa-snowflake me-1"></i>AC</span><?php endif; ?>
                                    <?php if (!empty($sRoom['has_bathroom'])): ?><span class="badge bg-light text-dark" style="font-size:0.65rem;"><i class="fas fa-bath me-1"></i>Bath</span><?php endif; ?>
                                    <?php if (!empty($sRoom['has_balcony'])): ?><span class="badge bg-light text-dark" style="font-size:0.65rem;"><i class="fas fa-building me-1"></i>Balcony</span><?php endif; ?>
                                </div>
                                <a href="<?= url('/room/' . $sRoom['id']) ?>" class="btn btn-outline-primary btn-sm w-100" onclick="event.stopPropagation()">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($hasImages): ?>
<div id="lightboxModal" class="position-fixed top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,0.95);z-index:9999;display:none;align-items:center;justify-content:center;">
    <button type="button" onclick="closeLightbox()" class="position-absolute top-0 end-0 m-3 btn btn-sm" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:40px;height:40px;border-radius:50%;font-size:1.2rem;z-index:10;">
        <i class="fas fa-times"></i>
    </button>
    <span id="lightboxCounter" class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 fs-6" style="z-index:10;"></span>
    <button type="button" onclick="lightboxPrev()" class="position-absolute start-0 top-50 translate-middle-y ms-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10;">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button type="button" onclick="lightboxNext()" class="position-absolute end-0 top-50 translate-middle-y me-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10;">
        <i class="fas fa-chevron-right"></i>
    </button>
    <img id="lightboxImage" src="" alt="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;">
</div>
<?php endif; ?>

<style>
.thumbnail-item:hover { border-color:#93c5fd !important; }
.thumbnail-item.active { border-color:#2563eb !important; }
#roomCarousel .carousel-item img { transition:transform 0.3s; }
.room-detail-card:hover { transform:translateY(-4px); box-shadow:0 12px 24px rgba(0,0,0,0.12) !important; }
</style>

<script>
var lightboxImages = <?= json_encode(array_map(function($img) { return UPLOAD_URL . $img['path']; }, $allImages)) ?>;
var lightboxIndex = 0;

function openLightbox(index) {
    lightboxIndex = index;
    var modal = document.getElementById('lightboxModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    updateLightbox();
}
function closeLightbox() {
    var modal = document.getElementById('lightboxModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}
function lightboxNext() {
    lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
    updateLightbox();
}
function lightboxPrev() {
    lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
    updateLightbox();
}
function updateLightbox() {
    var img = document.getElementById('lightboxImage');
    var counter = document.getElementById('lightboxCounter');
    if (img) img.src = lightboxImages[lightboxIndex];
    if (counter) counter.textContent = (lightboxIndex + 1) + ' / ' + lightboxImages.length;
}
document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('lightboxModal');
    if (!modal || modal.style.display !== 'flex') return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') lightboxNext();
    if (e.key === 'ArrowLeft') lightboxPrev();
});

function switchSlide(index) {
    var carousel = document.getElementById('roomCarousel');
    if (carousel) {
        var bsCarousel = bootstrap.Carousel.getInstance(carousel);
        if (!bsCarousel) bsCarousel = new bootstrap.Carousel(carousel);
        bsCarousel.to(index);
    }
    document.querySelectorAll('#roomThumbnails > div').forEach(function(el, i) {
        el.style.borderColor = (i === index) ? '#2563eb' : '#e2e8f0';
    });
}
document.addEventListener('DOMContentLoaded', function() {
    var carousel = document.getElementById('roomCarousel');
    if (carousel) {
        carousel.addEventListener('slid.bs.carousel', function(e) {
            var idx = e.to;
            document.querySelectorAll('#roomThumbnails > div').forEach(function(el, i) {
                el.style.borderColor = (i === idx) ? '#2563eb' : '#e2e8f0';
            });
        });
    }
});
</script>
