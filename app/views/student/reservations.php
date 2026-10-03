<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Reservations</h4>
    <?php if (!empty($hasActiveReservation)): ?>
        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2"><i class="fas fa-info-circle me-1"></i>You already have a pending or approved reservation</span>
    <?php else: ?>
        <button class="btn btn-primary btn-sm" id="newReservationBtn" data-bs-toggle="modal" data-bs-target="#newReservationModal">
            <i class="fas fa-plus me-1"></i> New Reservation
        </button>
    <?php endif; ?>
</div>

<!-- Reservations Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Reservation Code</th>
                    <th>Room</th>
                    <th>Move-in Date</th>
                    <th>Duration</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reservations)): ?>
                    <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><strong><?= e($res['reservation_code']) ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($res['primary_image'])): ?>
                                <div class="thumb-wrap" onclick="openImageViewer(<?= (int)$res['room_id'] ?>, 0)" title="Click to view images">
                                    <img src="<?= UPLOAD_URL . e($res['primary_image']) ?>" alt="<?= e($res['room_name']) ?>" style="width:64px;height:48px;object-fit:cover;">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                                <?php else: ?>
                                <div style="width:64px;height:48px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;"><i class="fas fa-bed text-muted"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold small"><?= e($res['room_name']) ?></div>
                                    <div class="text-muted" style="font-size:11px;">Room <?= e($res['room_number']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= formatDate($res['move_in_date']) ?></td>
                        <td><?= !empty($res['expected_duration']) ? (int)$res['expected_duration'] . ' month' . ((int)$res['expected_duration'] !== 1 ? 's' : '') : '—' ?></td>
                        <td><?= !empty($res['expected_duration']) ? formatDate(addCalendarMonths($res['move_in_date'] ?? '', (int)$res['expected_duration'])) : '—' ?></td>
                        <td><?= statusBadge($res['status']) ?></td>
                        <td><?= formatDate($res['created_at']) ?></td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm me-1 mb-1"
                                    data-res='<?= e(json_encode([
                                        'id' => $res['id'],
                                        'room_id' => (int)$res['room_id'] ?? 0,
                                        'reservation_code' => $res['reservation_code'] ?? '',
                                        'room_name' => $res['room_name'] ?? '',
                                        'room_number' => $res['room_number'] ?? '',
                                        'room_type' => $res['room_type'] ?? 'bedspacer',
                                        'move_in_date' => $res['move_in_date'] ?? '',
                                        'expected_duration' => $res['expected_duration'] ?? '',
                                        'monthly_rent' => $res['monthly_rent'] ?? 0,
                                        'advance_payment' => $res['advance_payment'] ?? 0,
                                        'status' => $res['status'] ?? '',
                                        'created_at' => $res['created_at'] ?? '',
                                        'valid_id_path' => $res['valid_id_path'] ?? '',
                                        'rejection_reason' => $res['rejection_reason'] ?? '',
                                        'admin_notes' => $res['admin_notes'] ?? '',
                                        'primary_image' => $res['primary_image'] ?? '',
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>'
                                    onclick="openReservationDetails(this)">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <?php if ($res['status'] === 'pending'): ?>
                                <form method="POST" action="<?= url('/student/reservation/cancel/' . $res['id']) ?>" style="display:inline;" data-confirm="Cancel this reservation?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-times me-1"></i>Cancel</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($res['status'] !== 'approved'): ?>
                            <form method="POST" action="<?= url('/student/reservation/delete/' . $res['id']) ?>" style="display:inline;" data-confirm="Delete reservation <?= e($res['reservation_code']) ?>? This action cannot be undone.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            No reservations found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Reservation Modal -->
<style>
.new-res-modal .modal-dialog { max-width: 1080px; }
.new-res-modal .modal-body { max-height: 70vh; overflow-y: auto; }
.room-pick .card { transition: border-color .2s, box-shadow .2s, transform .2s; }
.room-pick:hover { transform: translateY(-2px); }
.room-pick .room-img {
    height: 120px; object-fit: cover; display: block; width: 100%;
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
}
.room-pick .card-body { padding: 12px 14px; }
.room-pick .room-name { font-size: .95rem; }

.thumb-wrap {
    position: relative; width: 64px; height: 48px; border-radius: 8px;
    overflow: hidden; cursor: zoom-in; flex-shrink: 0;
}
.thumb-wrap img { display: block; width: 100%; height: 100%; object-fit: cover; }
.thumb-wrap i {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: rgba(15, 23, 42, .45); color: #fff; font-size: 14px;
    opacity: 0; transition: opacity .2s;
}
.thumb-wrap:hover i { opacity: 1; }

.room-img-wrap { cursor: zoom-in; }
.room-img-wrap .badge { z-index: 3; }
.room-img-zoom {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: rgba(15, 23, 42, .35); color: #fff; font-size: 20px;
    opacity: 0; transition: opacity .2s; cursor: zoom-in; z-index: 2;
}
.room-img-wrap:hover .room-img-zoom { opacity: 1; }

#imageViewerModal .modal-content { background: transparent; border: none; }
.viewer-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 42px; height: 42px; border-radius: 50%; border: none;
    background: rgba(0, 0, 0, .5); color: #fff; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 4; transition: background .2s;
}
.viewer-nav:hover { background: rgba(0, 0, 0, .75); }
.viewer-count {
    position: absolute; bottom: -34px; left: 50%; transform: translateX(-50%);
    background: rgba(0, 0, 0, .55); color: #fff; padding: 3px 14px;
    border-radius: 12px; font-size: 12px; font-weight: 600; white-space: nowrap;
}
#roomd-thumbs img {
    width: 56px; height: 42px; object-fit: cover; border-radius: 8px;
    cursor: zoom-in; border: 2px solid transparent; flex-shrink: 0;
}
#roomd-thumbs img.active { border-color: #4f46e5; }
</style>
<div class="modal fade new-res-modal" id="newReservationModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form method="POST" action="<?= url('/student/reservations') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
                    <h5 class="modal-title fw-bold" style="font-size: 1.05rem;">New Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($availableRooms)): ?>
                        <?php
                        $genderMsg = '';
                        if (!empty($student['gender'])) {
                            $genderMsg = $student['gender'] === 'male' 
                                ? ' No "Boys Only" rooms are currently available.' 
                                : ' No "Girls Only" rooms are currently available.';
                        }
                        ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-1"></i> No rooms are currently available for your gender.<?= $genderMsg ?>
                        </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-2">Choose a Room <span class="text-danger">*</span></label>
                        <input type="hidden" name="room_id" id="selectedRoomId" value="<?= (int)$selectedRoomId ?>">
                        <div class="row g-2" id="roomGrid">
                            <?php foreach ($availableRooms as $room): ?>
                            <?php
                            $cap = max(1, (int)($room['max_capacity'] ?? 1));
                            $occ = max((int)($room['current_occupancy'] ?? 0), (int)($room['approved_count'] ?? 0));
                            $avail = max(0, $cap - $occ);
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border h-100 room-pick" data-id="<?= (int)$room['id'] ?>"
                                     data-name="<?= e($room['room_name']) ?>"
                                     data-number="<?= e($room['room_number']) ?>"
                                     data-rent="<?= e(formatCurrency((float)$room['monthly_rent'])) ?>"
                                     data-details='<?= e(json_encode([
                                        'id' => $room['id'],
                                        'room_name' => $room['room_name'] ?? '',
                                        'room_number' => $room['room_number'] ?? '',
                                        'room_type' => $room['room_type'] ?? 'bedspacer',
                                        'monthly_rent' => $room['monthly_rent'] ?? 0,
                                        'advance_payment' => $room['advance_payment'] ?? 0,
                                        'floor' => $room['floor'] ?? null,
                                        'size_sqm' => $room['size_sqm'] ?? null,
                                        'max_capacity' => $room['max_capacity'] ?? 1,
                                        'current_occupancy' => $room['current_occupancy'] ?? 0,
                                        'has_aircon' => (int)($room['has_aircon'] ?? 0),
                                        'has_bathroom' => (int)($room['has_bathroom'] ?? 0),
                                        'has_balcony' => (int)($room['has_balcony'] ?? 0),
                                        'furniture' => $room['furniture'] ?? '',
                                        'description' => $room['description'] ?? '',
                                        'house_rules' => $room['house_rules'] ?? '',
                                        'primary_image' => $room['primary_image'] ?? '',
                                     ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>'
                                     style="cursor:pointer;border-radius:10px;overflow:hidden;">
                                    <div class="position-relative room-img-wrap">
                                        <?php if (!empty($room['primary_image'])): ?>
                                        <img src="<?= UPLOAD_URL . e($room['primary_image']) ?>" alt="<?= e($room['room_name']) ?>" class="room-img" onclick="event.stopPropagation(); openImageViewer(<?= (int)$room['id'] ?>, 0)">
                                        <span class="room-img-zoom" onclick="event.stopPropagation(); openImageViewer(<?= (int)$room['id'] ?>, 0)"><i class="fas fa-search-plus"></i></span>
                                        <?php else: ?>
                                        <div class="room-img d-flex align-items-center justify-content-center"><i class="fas fa-bed fa-2x text-muted opacity-50"></i></div>
                                        <?php endif; ?>
                                        <span class="badge position-absolute top-0 start-0 m-2" style="background:rgba(0,0,0,.7);color:#fff;font-size:.65rem;"><?= ucwords(str_replace('_', ' ', $room['room_type'] ?? 'bedspacer')) ?></span>
                                        <span class="badge position-absolute top-0 end-0 m-2 <?= $avail > 0 ? 'bg-success' : 'bg-danger' ?>" style="font-size:.65rem;"><?= $avail > 0 ? $avail . ' slot' . ($avail !== 1 ? 's' : '') . ' left' : 'Full' ?></span>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <h6 class="fw-bold mb-0 room-name"><?= e($room['room_name']) ?></h6>
                                                <small class="text-muted"><i class="fas fa-door-open me-1"></i>Room <?= e($room['room_number']) ?></small>
                                            </div>
                                            <span class="fw-bold text-primary small"><?= formatCurrency((float)$room['monthly_rent']) ?><small class="text-muted fw-normal" style="font-size:.7rem;">/mo</small></span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <?php if (!empty($room['has_aircon'])): ?><span class="badge bg-light text-dark" style="font-size:.65rem;"><i class="fas fa-snowflake me-1"></i>AC</span><?php endif; ?>
                                            <?php if (!empty($room['has_bathroom'])): ?><span class="badge bg-light text-dark" style="font-size:.65rem;"><i class="fas fa-bath me-1"></i>Bath</span><?php endif; ?>
                                            <?php if (!empty($room['has_balcony'])): ?><span class="badge bg-light text-dark" style="font-size:.65rem;"><i class="fas fa-building me-1"></i>Balcony</span><?php endif; ?>
                                            <?php if (!empty($room['size_sqm'])): ?><span class="badge bg-light text-dark" style="font-size:.65rem;"><i class="fas fa-ruler-combined me-1"></i><?= e($room['size_sqm']) ?>m&sup2;</span><?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 btn-view-room" style="font-size:.78rem;" onclick="openRoomDetails(this)">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm flex-grow-1 btn-reserve-room" style="font-size:.78rem;">
                                                <i class="fas fa-calendar-plus me-1"></i>Reserve Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div id="reservationDetails" class="mt-1" style="display:none;">
                        <div class="alert alert-success py-2 px-3 mb-2" style="font-size:.82rem;" id="selectedRoomSummary"></div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small mb-1">Move-in Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="move_in_date" required placeholder="Select move-in date" data-max-date="<?= serverNow()->modify('+3 months')->format('Y-m-d') ?>" readonly>
                                <small class="text-muted" style="font-size:.72rem;">Within 3 months from today</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small mb-1">Expected Duration (months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="expected_duration" value="1" min="1" max="12" step="1" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value && parseInt(this.value) > 12) this.value = 12; if(this.value && parseInt(this.value) < 1) this.value = 1;">
                                <small class="text-muted" style="font-size:.72rem;">1–12 months, whole numbers only</small>
                                <div class="alert alert-success py-1 px-2 mt-2 mb-0" style="font-size:.75rem;border-radius:8px;background:#f0fdf4;border-color:#bbf7d0;display:none;" id="resDueDatePreview">
                                    <i class="fas fa-calendar-alt me-1"></i>Next Payment Due Date: <strong id="resDueDatePreviewValue">—</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small mb-1">Valid ID <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-sm" name="valid_id" accept="image/*,.pdf" required>
                                <small class="text-muted" style="font-size:.72rem;">Government-issued ID (JPG, PNG, PDF. Max 5MB)</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($availableRooms)): ?>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitReservationBtn" disabled><i class="fas fa-paper-plane me-1"></i> Submit Reservation</button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
                <h5 class="modal-title fw-bold" style="font-size: 1.05rem;"><i class="fas fa-calendar-check me-2 text-primary"></i>Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img id="resd-image" src="" alt="" class="d-none" style="width:110px;height:82px;object-fit:cover;border-radius:10px;cursor:zoom-in;">
                    <div>
                        <div class="small text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Reservation Code</div>
                        <div class="fw-bold" id="resd-code" style="font-size:1.1rem;">—</div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room</div>
                            <div class="fw-semibold small mt-1" id="resd-room">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Number</div>
                            <div class="fw-semibold small mt-1" id="resd-number">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Type</div>
                            <div class="fw-semibold small mt-1 text-capitalize" id="resd-type">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Move-in Date</div>
                            <div class="fw-semibold small mt-1" id="resd-movein">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Duration</div>
                            <div class="fw-semibold small mt-1" id="resd-duration">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Due Date</div>
                            <div class="fw-semibold small mt-1" id="resd-due">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Monthly Rent</div>
                            <div class="fw-semibold small mt-1" style="color:#059669;" id="resd-rent">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Advance Payment</div>
                            <div class="fw-semibold small mt-1" id="resd-advance">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Status</div>
                            <div class="mt-1" id="resd-status">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Date Requested</div>
                            <div class="fw-semibold small mt-1" id="resd-created">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Valid ID</div>
                            <div class="small mt-1" id="resd-id">—</div>
                        </div>
                    </div>
                    <div class="col-12" id="resd-note-wrap" style="display:none;">
                        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:10px 12px;">
                            <div class="small text-uppercase" style="font-size:10px;color:#9a3412;letter-spacing:.5px;">Admin Notes / Reason</div>
                            <div class="small mt-1" style="color:#7c2d12;" id="resd-note">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                <form method="POST" action="" id="resd-cancel-form" style="display:none;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Cancel this reservation?"><i class="fas fa-times me-1"></i>Cancel Reservation</button>
                </form>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
                <h5 class="modal-title fw-bold" style="font-size: 1.05rem;" id="roomd-title">Room Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img id="roomd-image" src="" alt="" class="d-none w-100 mb-2" style="height:160px;object-fit:cover;border-radius:10px;cursor:zoom-in;">
                <div class="d-flex gap-2 flex-wrap mb-3" id="roomd-thumbs" style="display:none;"></div>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Number</div>
                            <div class="fw-semibold small mt-1" id="roomd-number">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Type</div>
                            <div class="fw-semibold small mt-1 text-capitalize" id="roomd-type">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Monthly Rent</div>
                            <div class="fw-semibold small mt-1" style="color:#059669;" id="roomd-rent">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Advance Payment</div>
                            <div class="fw-semibold small mt-1" id="roomd-advance">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Floor</div>
                            <div class="fw-semibold small mt-1" id="roomd-floor">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Size</div>
                            <div class="fw-semibold small mt-1" id="roomd-size">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Availability</div>
                            <div class="mt-1" id="roomd-avail">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Amenities</div>
                            <div class="mt-1 d-flex flex-wrap gap-1" id="roomd-amenities"></div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-furn-wrap" style="display:none;">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Furniture</div>
                            <div class="small mt-1" id="roomd-furniture">—</div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-desc-wrap" style="display:none;">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Description</div>
                            <div class="small mt-1" style="line-height:1.6;" id="roomd-description">—</div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-rules-wrap" style="display:none;">
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;">
                            <div class="small text-uppercase" style="font-size:10px;color:#92400e;letter-spacing:.5px;">House Rules</div>
                            <div class="small mt-1" style="color:#78350f;line-height:1.6;" id="roomd-rules">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="roomd-reserve-btn"><i class="fas fa-calendar-plus me-1"></i>Reserve This Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" style="z-index:1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:900px;">
        <div class="modal-content">
            <div class="position-relative text-center p-2">
                <button type="button" class="btn-close btn-close-white position-absolute" style="top:-38px;right:0;z-index:5;" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="viewer-image" src="" alt="" style="max-width:100%;max-height:82vh;border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,.45);">
                <button type="button" class="viewer-nav" id="viewer-prev" style="left:10px;"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="viewer-nav" id="viewer-next" style="right:10px;"><i class="fas fa-chevron-right"></i></button>
                <div id="viewer-count" class="viewer-count">1 / 1</div>
            </div>
        </div>
    </div>
</div>

<script>
var openReserveFlag = <?= json_encode(!empty($openReserve)) ?>;
var preselectRoomId = <?= (int)($selectedRoomId ?? 0) ?>;
var uploadBaseUrl = <?= json_encode(UPLOAD_URL) ?>;
var csym = window.APP_CURRENCY_SYMBOL || '₱';
var roomImagesMap = <?= json_encode($roomImagesMap ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
var viewerImages = [];
var viewerIndex = 0;

function fmtMoney(v) {
    var n = parseFloat(v) || 0;
    return csym + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fmtType(t) {
    t = t || 'bedspacer';
    return t.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
}

function fmtDuration(d) {
    var n = parseInt(d, 10) || 0;
    return n + ' month' + (n !== 1 ? 's' : '');
}

function calcDueDate(moveIn, months) {
    var n = parseInt(months, 10) || 0;
    if (!moveIn || n <= 0) return '';
    var d = new Date(moveIn);
    if (isNaN(d.getTime())) return '';
    var day = d.getDate();
    var lastDay = new Date(d.getFullYear(), d.getMonth() + 1 + n, 0).getDate();
    d.setDate(1);
    d.setMonth(d.getMonth() + n);
    d.setDate(Math.min(day, lastDay));
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var dd = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + mm + '-' + dd;
}

function openImageViewer(roomId, idx) {
    var paths = roomImagesMap[roomId] || roomImagesMap[String(roomId)] || [];
    if (!paths || !paths.length) return;
    viewerImages = paths.map(function(p) { return uploadBaseUrl + p; });
    viewerIndex = Math.max(0, idx || 0);
    if (viewerIndex >= viewerImages.length) viewerIndex = 0;
    showViewerImage();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('imageViewerModal')).show();
}

function showViewerImage() {
    var img = document.getElementById('viewer-image');
    var count = document.getElementById('viewer-count');
    img.src = viewerImages[viewerIndex];
    img.alt = '';
    count.textContent = (viewerIndex + 1) + ' / ' + viewerImages.length;
    var multi = viewerImages.length > 1;
    document.getElementById('viewer-prev').style.visibility = multi ? 'visible' : 'hidden';
    document.getElementById('viewer-next').style.visibility = multi ? 'visible' : 'hidden';
}

function statusBadgeHtml(s) {
    s = s || 'pending';
    var map = {
        pending:   ['bg-warning', 'text-dark',  'PENDING'],
        approved:  ['bg-success', '',          'APPROVED'],
        rejected:  ['bg-danger',  '',          'REJECTED'],
        cancelled: ['bg-secondary','',         'CANCELLED'],
        expired:   ['bg-dark',    '',          'EXPIRED']
    };
    var m = map[s] || ['bg-secondary', '', s.toUpperCase()];
    return '<span class="badge ' + m[0] + ' ' + m[1] + '">' + m[2] + '</span>';
}

function openReservationDetails(btn) {
    var data = JSON.parse(btn.getAttribute('data-res'));
    var img = document.getElementById('resd-image');
    if (data.primary_image) {
        img.src = uploadBaseUrl + data.primary_image;
        img.classList.remove('d-none');
        img.onclick = function() { openImageViewer(data.room_id, 0); };
    } else {
        img.src = '';
        img.classList.add('d-none');
        img.onclick = null;
    }
    document.getElementById('resd-code').textContent = data.reservation_code || '—';
    document.getElementById('resd-room').textContent = data.room_name || '—';
    document.getElementById('resd-number').textContent = data.room_number || '—';
    document.getElementById('resd-type').textContent = fmtType(data.room_type);
    document.getElementById('resd-movein').textContent = data.move_in_date || '—';
    document.getElementById('resd-duration').textContent = fmtDuration(data.expected_duration);
    var due = calcDueDate(data.move_in_date, data.expected_duration);
    document.getElementById('resd-due').textContent = due ? due : '—';
    document.getElementById('resd-rent').textContent = fmtMoney(data.monthly_rent);
    document.getElementById('resd-advance').textContent = fmtMoney(data.monthly_rent);
    document.getElementById('resd-status').innerHTML = statusBadgeHtml(data.status);
    document.getElementById('resd-created').textContent = data.created_at ? String(data.created_at).slice(0, 10) : '—';

    var idEl = document.getElementById('resd-id');
    if (data.valid_id_path) {
        idEl.innerHTML = '<a href="' + uploadBaseUrl + data.valid_id_path + '" target="_blank" class="fw-semibold" style="color:#4f46e5;"><i class="fas fa-file-alt me-1"></i>View Uploaded ID</a>';
    } else {
        idEl.innerHTML = '<span class="text-muted">No valid ID uploaded</span>';
    }

    var note = (data.status === 'rejected' && data.rejection_reason) ? data.rejection_reason : (data.admin_notes || '');
    var noteWrap = document.getElementById('resd-note-wrap');
    var noteEl = document.getElementById('resd-note');
    if (note) {
        noteEl.textContent = note;
        noteWrap.style.display = 'block';
    } else {
        noteWrap.style.display = 'none';
    }

    var cancelForm = document.getElementById('resd-cancel-form');
    if (data.status === 'pending') {
        cancelForm.action = <?= json_encode(url('/student/reservation/cancel/')) ?> + data.id;
        cancelForm.style.display = 'inline-block';
    } else {
        cancelForm.style.display = 'none';
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('reservationDetailsModal')).show();
}

function openRoomDetails(btn) {
    var card = btn.closest('.room-pick');
    if (!card) return;
    var data = JSON.parse(card.getAttribute('data-details'));

    document.getElementById('roomd-title').textContent = data.room_name || 'Room Details';
    var img = document.getElementById('roomd-image');
    if (data.primary_image) {
        img.src = uploadBaseUrl + data.primary_image;
        img.classList.remove('d-none');
        img.onclick = function() { openImageViewer(data.id, 0); };
    } else {
        img.src = '';
        img.classList.add('d-none');
        img.onclick = null;
    }

    var thumbs = document.getElementById('roomd-thumbs');
    var paths = roomImagesMap[data.id] || roomImagesMap[String(data.id)] || [];
    if (paths.length > 1) {
        thumbs.innerHTML = '';
        paths.forEach(function(p, i) {
            var t = document.createElement('img');
            t.src = uploadBaseUrl + p;
            t.alt = '';
            if (i === 0) t.className = 'active';
            t.onclick = function() { openImageViewer(data.id, i); };
            thumbs.appendChild(t);
        });
        thumbs.style.display = 'flex';
    } else {
        thumbs.innerHTML = '';
        thumbs.style.display = 'none';
    }
    document.getElementById('roomd-number').textContent = data.room_number || '—';
    document.getElementById('roomd-type').textContent = fmtType(data.room_type);
    document.getElementById('roomd-rent').textContent = fmtMoney(data.monthly_rent);
    document.getElementById('roomd-advance').textContent = fmtMoney(data.monthly_rent);
    document.getElementById('roomd-floor').textContent = data.floor !== null && data.floor !== '' ? 'Floor ' + data.floor : '—';
    document.getElementById('roomd-size').textContent = data.size_sqm ? data.size_sqm + ' m²' : '—';

    var cap = Math.max(1, parseInt(data.max_capacity, 10) || 1);
    var occ = parseInt(data.current_occupancy, 10) || 0;
    var avail = Math.max(0, cap - occ);
    document.getElementById('roomd-avail').innerHTML =
        avail > 0
            ? '<span class="badge bg-success">' + avail + ' slot' + (avail !== 1 ? 's' : '') + ' left</span> <span class="text-muted small ms-1">(' + occ + '/' + cap + ' occupied)</span>'
            : '<span class="badge bg-danger">Full</span>';

    var am = document.getElementById('roomd-amenities');
    var amen = '';
    if (parseInt(data.has_aircon, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-snowflake me-1"></i>Aircon</span>';
    if (parseInt(data.has_bathroom, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-bath me-1"></i>Bathroom</span>';
    if (parseInt(data.has_balcony, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-building me-1"></i>Balcony</span>';
    am.innerHTML = amen || '<span class="text-muted small">Standard room</span>';

    var furniture = data.furniture || '';
    document.getElementById('roomd-furniture').textContent = furniture;
    document.getElementById('roomd-furn-wrap').style.display = furniture ? 'block' : 'none';

    var desc = data.description || '';
    document.getElementById('roomd-description').textContent = desc;
    document.getElementById('roomd-desc-wrap').style.display = desc ? 'block' : 'none';

    var rules = data.house_rules || '';
    document.getElementById('roomd-rules').textContent = rules;
    document.getElementById('roomd-rules-wrap').style.display = rules ? 'block' : 'none';

    document.getElementById('roomd-reserve-btn').setAttribute('data-id', data.id);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('roomDetailsModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    var roomGrid = document.getElementById('roomGrid');
    var selectedRoomId = document.getElementById('selectedRoomId');
    var reservationDetails = document.getElementById('reservationDetails');
    var selectedRoomSummary = document.getElementById('selectedRoomSummary');
    var submitBtn = document.getElementById('submitReservationBtn');

    function selectRoom(id, btn) {
        if (!roomGrid) return;
        var active = null;
        roomGrid.querySelectorAll('.room-pick').forEach(function(card) {
            var isActive = String(card.dataset.id) === String(id);
            card.classList.toggle('border-primary', isActive);
            card.classList.toggle('shadow', isActive);
            var b = card.querySelector('.btn-reserve-room');
            if (b) {
                b.classList.toggle('btn-primary', !isActive);
                b.classList.toggle('btn-success', isActive);
                b.innerHTML = isActive
                    ? '<i class="fas fa-check me-1"></i>Selected'
                    : '<i class="fas fa-calendar-plus me-1"></i>Reserve Now';
            }
            if (isActive) active = card;
        });
        if (active) {
            selectedRoomId.value = id;
            if (reservationDetails) {
                reservationDetails.style.display = 'block';
                selectedRoomSummary.innerHTML = '<i class="fas fa-check-circle me-1"></i><strong>' + active.dataset.name + '</strong> (Room ' + active.dataset.number + ') selected &mdash; ' + active.dataset.rent + '/mo.';
            }
            if (submitBtn) submitBtn.disabled = false;
            var detailScroll = document.getElementById('reservationDetails');
            if (detailScroll && detailScroll.style.display === 'block') {
                detailScroll.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    window.selectRoom = selectRoom;

    if (roomGrid) {
        roomGrid.querySelectorAll('.room-pick').forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.btn-view-room')) return;
                selectRoom(card.dataset.id);
            });
        });
    }

    var reserveBtn = document.getElementById('roomd-reserve-btn');
    if (reserveBtn) {
        reserveBtn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (!id) return;
            var roomModal = bootstrap.Modal.getInstance(document.getElementById('roomDetailsModal'));
            if (roomModal) roomModal.hide();
            var newModalEl = document.getElementById('newReservationModal');
            var newModal = bootstrap.Modal.getInstance(newModalEl) || new bootstrap.Modal(newModalEl);
            newModal.show();
            var onShown = function() {
                if (window.selectRoom) window.selectRoom(id);
                newModalEl.removeEventListener('shown.bs.modal', onShown);
            };
            newModalEl.addEventListener('shown.bs.modal', onShown);
        });
    }

    var modalEl = document.getElementById('newReservationModal');
    if (modalEl && (openReserveFlag || preselectRoomId)) {
        var onShown = function() {
            if (preselectRoomId) {
                var target = roomGrid ? roomGrid.querySelector('.room-pick[data-id="' + preselectRoomId + '"]') : null;
                if (target) selectRoom(preselectRoomId);
            }
            modalEl.removeEventListener('shown.bs.modal', onShown);
        };
        modalEl.addEventListener('shown.bs.modal', onShown);
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function addCalendarMonths(dateStr, months) {
        var parts = dateStr.split('-');
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        var d = parseInt(parts[2], 10);
        var total = y * 12 + (m - 1) + months;
        var ny = Math.floor(total / 12);
        var nm = (total % 12) + 1;
        var lastDay = new Date(ny, nm, 0).getDate();
        var nd = Math.min(d, lastDay);
        var mm = String(nm).padStart(2, '0');
        var dd = String(nd).padStart(2, '0');
        return ny + '-' + mm + '-' + dd;
    }

    var moveInInput = document.querySelector('input[name="move_in_date"]');
    var durationInput = document.querySelector('input[name="expected_duration"]');
    var dueDatePreview = document.getElementById('resDueDatePreview');
    var dueDatePreviewValue = document.getElementById('resDueDatePreviewValue');

    if (moveInInput && typeof flatpickr !== 'undefined') {
        flatpickr(moveInInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            maxDate: new Date().fp_incr(90),
            allowInput: false,
            onChange: function(selectedDates, dateStr) {
                updateDueDatePreview();
            }
        });
    }

    function updateDueDatePreview() {
        if (!moveInInput || !durationInput || !dueDatePreview || !dueDatePreviewValue) return;
        var mi = moveInInput.value;
        var dur = parseInt(durationInput.value, 10);
        if (!mi || !dur || dur < 1) {
            dueDatePreview.style.display = 'none';
            return;
        }
        dueDatePreviewValue.textContent = addCalendarMonths(mi, dur);
        dueDatePreview.style.display = 'block';
    }
    if (moveInInput) moveInInput.addEventListener('change', updateDueDatePreview);
    if (durationInput) durationInput.addEventListener('input', updateDueDatePreview);

    var prevBtn = document.getElementById('viewer-prev');
    var nextBtn = document.getElementById('viewer-next');    if (prevBtn) prevBtn.addEventListener('click', function() {
        viewerIndex = (viewerIndex - 1 + viewerImages.length) % viewerImages.length;
        showViewerImage();
    });
    if (nextBtn) nextBtn.addEventListener('click', function() {
        viewerIndex = (viewerIndex + 1) % viewerImages.length;
        showViewerImage();
    });

    document.addEventListener('keydown', function(e) {
        var viewerEl = document.getElementById('imageViewerModal');
        if (!viewerEl || !viewerEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') {
            viewerIndex = (viewerIndex - 1 + viewerImages.length) % viewerImages.length;
            showViewerImage();
        } else if (e.key === 'ArrowRight') {
            viewerIndex = (viewerIndex + 1) % viewerImages.length;
            showViewerImage();
        }
    });
});
</script>
