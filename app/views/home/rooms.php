<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $rooms = $rooms ?? []; ?>
<?php $search = $search ?? ''; ?>
<?php $minPrice = $minPrice ?? 0; ?>
<?php $maxPrice = $maxPrice ?? 0; ?>
<?php $roomType = $roomType ?? ''; ?>
<?php $status = $status ?? ''; ?>
<?php $sort = $sort ?? 'newest'; ?>
<?php $pagination = $pagination ?? ['total' => 0, 'page' => 1, 'per_page' => 12, 'total_pages' => 1]; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Rooms</li>
            </ol>
        </nav>
        <h1 class="display-0 fw-bold">Our Rooms</h1>
        
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius:12px;position:sticky;top:90px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filters</h5>
                            <?php if ($search || $minPrice || $maxPrice || $roomType || $status): ?>
                                <a href="<?= url('/rooms') ?>" class="badge bg-danger text-decoration-none" style="cursor:pointer;">Clear All</a>
                            <?php endif; ?>
                        </div>
                        <form action="<?= url('/rooms') ?>" method="GET" id="filterForm">
                            <div class="mb-3">
                                <label class="form-label fw-medium small text-muted">SEARCH</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Room name or number..." value="<?= e($search) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium small text-muted">ROOM TYPE</label>
                                <select name="room_type" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="bedspacer" <?= $roomType === 'bedspacer' ? 'selected' : '' ?>>Bedspace</option>
                                    <option value="single" <?= $roomType === 'single' ? 'selected' : '' ?>>Single</option>
                                    <option value="studio" <?= $roomType === 'studio' ? 'selected' : '' ?>>Studio</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium small text-muted">STATUS</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="occupied" <?= $status === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                    <option value="reserved" <?= $status === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                    <option value="under_maintenance" <?= $status === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium small text-muted">PRICE RANGE (<?= e(getCurrencySymbol()) ?>)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="min_price" class="form-control" data-money-optional data-money-min="0" placeholder="Min" value="<?= $minPrice ? e((int)$minPrice) : '' ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="max_price" class="form-control" data-money-optional data-money-min="0" placeholder="Max" value="<?= $maxPrice ? e((int)$maxPrice) : '' ?>">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="sort" value="<?= e($sort) ?>">

                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                            <a href="<?= url('/rooms') ?>" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-muted mb-0">
                        Showing <strong><?= count($rooms) ?></strong> of <strong><?= number_format($pagination['total']) ?></strong> rooms
                        <?php if ($search): ?>
                            for "<strong><?= e($search) ?></strong>"
                        <?php endif; ?>
                    </p>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small mb-0">Sort:</label>
                        <select class="form-select form-select-sm" style="width:auto;" onchange="window.location.href=this.value">
                            <?php
                            $sortOptions = [
                                'newest' => 'Newest First',
                                'price_low' => 'Price: Low to High',
                                'price_high' => 'Price: High to Low',
                                'name_asc' => 'Name: A-Z',
                                'name_desc' => 'Name: Z-A',
                                'popular' => 'Most Popular',
                            ];
                            $baseUrl = url('/rooms');
                            $qp = [];
                            if ($search) $qp['search'] = $search;
                            if ($minPrice) $qp['min_price'] = (int)$minPrice;
                            if ($maxPrice) $qp['max_price'] = (int)$maxPrice;
                            if ($roomType) $qp['room_type'] = $roomType;
                            if ($status) $qp['status'] = $status;
                            foreach ($sortOptions as $val => $label):
                                $qp['sort'] = $val;
                            ?>
                                <option value="<?= $baseUrl . '?' . http_build_query($qp) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (!empty($rooms)): ?>
                    <div class="row g-2">
                        <?php foreach ($rooms as $room): ?>
                            <?php
                            $typeLabels = ['bedspacer' => 'Bedspace', 'single' => 'Single', 'studio' => 'Studio'];
                            $rt = $room['room_type'] ?? 'bedspacer';
                            $cap = max(1, (int)($room['max_capacity'] ?? 1));
                            $occ = max((int)($room['live_occupancy'] ?? 0), (int)($room['approved_count'] ?? 0));
                            $avail = max(0, $cap - $occ);
                            $pct = min(100, round(($occ / $cap) * 100));
                            $barColor = $pct <= 50 ? '#22c55e' : ($pct <= 80 ? '#f59e0b' : ($pct < 100 ? '#f97316' : '#ef4444'));
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100 room-list-card" style="border-radius:12px;overflow:hidden;transition:transform 0.3s,box-shadow 0.3s;cursor:pointer;" onclick="window.location='<?= url('/room/' . $room['id']) ?>'">
                                    <div class="position-relative" style="aspect-ratio:16/10;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);overflow:hidden;">
                                        <?php if (!empty($room['primary_image'])): ?>
                                            <img src="<?= UPLOAD_URL . $room['primary_image'] ?>" alt="<?= e($room['room_name']) ?>" class="w-100 h-100" style="object-fit:cover;display:block;transition:transform 0.4s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                                <i class="fas fa-bed fa-3x text-muted opacity-50"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1">
                                            <span class="badge" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.7rem;"><?= $typeLabels[$rt] ?? ucfirst($rt) ?></span>
                                            <?php if (!empty($room['is_featured'])): ?>
                                            <span class="badge" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:0.7rem;">
                                                <i class="fas fa-star me-1"></i>Featured
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <?php
                                            if (($room['status'] ?? '') === 'under_maintenance') {
                                                $sColors = 'bg-warning text-dark';
                                                $sLabel = 'Maintenance';
                                            } elseif ($avail > 0) {
                                                $sColors = 'bg-success';
                                                $sLabel = $avail . ' slot' . ($avail !== 1 ? 's' : '') . ' left';
                                            } else {
                                                $sColors = 'bg-danger';
                                                $sLabel = 'Fully Occupied';
                                            }
                                            ?>
                                            <span class="badge <?= $sColors ?>"><?= $sLabel ?></span>
                                        </div>
                                        <?php if (($room['image_count'] ?? 0) > 0): ?>
                                        <span class="badge position-absolute bottom-0 start-0 m-2" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.7rem;">
                                            <i class="fas fa-camera me-1"></i><?= $room['image_count'] ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="fw-bold mb-0"><?= e($room['room_name']) ?></h6>
                                            <span class="fw-bold text-primary" style="font-size:0.95rem;"><?= formatCurrency($room['monthly_rent']) ?><small class="text-muted fw-normal" style="font-size:0.75rem;">/mo</small></span>
                                        </div>
                                        <p class="text-muted small mb-2"><i class="fas fa-door-open me-1"></i>Room <?= e($room['room_number']) ?></p>
                                        <?php if (!empty($room['description'])): ?>
                                        <p class="text-muted small mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5;"><?= e($room['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <?php if (!empty($room['has_aircon'])): ?><span class="badge bg-light text-dark" style="font-size:0.7rem;"><i class="fas fa-snowflake me-1"></i>AC</span><?php endif; ?>
                                            <?php if (!empty($room['has_bathroom'])): ?><span class="badge bg-light text-dark" style="font-size:0.7rem;"><i class="fas fa-bath me-1"></i>Bath</span><?php endif; ?>
                                            <?php if (!empty($room['has_balcony'])): ?><span class="badge bg-light text-dark" style="font-size:0.7rem;"><i class="fas fa-building me-1"></i>Balcony</span><?php endif; ?>
                                            <?php if (!empty($room['size_sqm'])): ?><span class="badge bg-light text-dark" style="font-size:0.7rem;"><i class="fas fa-ruler-combined me-1"></i><?= e($room['size_sqm']) ?>m&sup2;</span><?php endif; ?>
                                            <?php if (!empty($room['floor'])): ?><span class="badge bg-light text-dark" style="font-size:0.7rem;"><i class="fas fa-layer-group me-1"></i>Floor <?= e($room['floor']) ?></span><?php endif; ?>
                                        </div>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small text-muted"><i class="fas fa-users me-1"></i><?= $occ ?>/<?= $cap ?> boarders</span>
                                                <span class="small fw-semibold" style="color:<?= $barColor ?>"><?= $avail ?> slot<?= $avail !== 1 ? 's' : '' ?> left</span>
                                            </div>
                                            <div class="progress" style="height:5px;border-radius:3px;">
                                                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:3px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav class="mt-5 d-flex justify-content-center">
                            <ul class="pagination pagination-sm">
                                <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                                    <?php
                                    $pp = ['page' => max(1, $pagination['page'] - 1)];
                                    if ($search) $pp['search'] = $search;
                                    if ($minPrice) $pp['min_price'] = (int)$minPrice;
                                    if ($maxPrice) $pp['max_price'] = (int)$maxPrice;
                                    if ($roomType) $pp['room_type'] = $roomType;
                                    if ($status) $pp['status'] = $status;
                                    if ($sort) $pp['sort'] = $sort;
                                    ?>
                                    <a class="page-link" href="<?= url('/rooms?' . http_build_query($pp)) ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                                    <?php
                                    $ip = ['page' => $i];
                                    if ($search) $ip['search'] = $search;
                                    if ($minPrice) $ip['min_price'] = (int)$minPrice;
                                    if ($maxPrice) $ip['max_price'] = (int)$maxPrice;
                                    if ($roomType) $ip['room_type'] = $roomType;
                                    if ($status) $ip['status'] = $status;
                                    if ($sort) $ip['sort'] = $sort;
                                    ?>
                                    <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('/rooms?' . http_build_query($ip)) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                                    <?php
                                    $np = ['page' => min($pagination['total_pages'], $pagination['page'] + 1)];
                                    if ($search) $np['search'] = $search;
                                    if ($minPrice) $np['min_price'] = (int)$minPrice;
                                    if ($maxPrice) $np['max_price'] = (int)$maxPrice;
                                    if ($roomType) $np['room_type'] = $roomType;
                                    if ($status) $np['status'] = $status;
                                    if ($sort) $np['sort'] = $sort;
                                    ?>
                                    <a class="page-link" href="<?= url('/rooms?' . http_build_query($np)) ?>"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e2e8f0,#f1f5f9);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="fas fa-bed fa-2x text-muted"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No Rooms Found</h4>
                        <p class="text-muted mb-3">We couldn't find any rooms matching your criteria.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= url('/rooms') ?>" class="btn btn-primary"><i class="fas fa-undo me-1"></i> Reset Filters</a>
                            <a href="<?= url('/') ?>" class="btn btn-outline-secondary"><i class="fas fa-home me-1"></i> Back to Home</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.room-list-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
}
</style>
