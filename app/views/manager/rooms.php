<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Rooms</h1>
    <a href="<?= url('/manager/room/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Room</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/manager/rooms') ?>" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by room number or name..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="available" <?= ($status ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="occupied" <?= ($status ?? '') === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                    <option value="reserved" <?= ($status ?? '') === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                    <option value="under_maintenance" <?= ($status ?? '') === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="room_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="bedspacer" <?= ($roomType ?? '') === 'bedspacer' ? 'selected' : '' ?>>Bedspace</option>
                    <option value="single" <?= ($roomType ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                    <option value="studio" <?= ($roomType ?? '') === 'studio' ? 'selected' : '' ?>>Studio</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
<thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Available</th>
                        <th>Room Name</th>
                        <th>Type</th>
                        <th>Monthly Rent</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($room['primary_image'])): ?>
                                        <div class="position-relative d-inline-block">
                                            <img src="<?= UPLOAD_URL . $room['primary_image'] ?>" alt="" class="rounded" style="width:50px;height:40px;object-fit:cover;">
                                            <?php if (($room['image_count'] ?? 0) > 1): ?>
                                                <span class="badge bg-dark position-absolute" style="bottom:-4px;right:-4px;font-size:10px;"><?= $room['image_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-image"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $cap = max(1, (int)($room['max_capacity'] ?? 1));
                                    $occ = (int)($room['current_occupancy'] ?? 0);
                                    $avail = max(0, $cap - $occ);
                                    $availColor = $avail > 0 ? '#22c55e' : '#ef4444';
                                    ?>
                                    <span class="fw-bold" style="color:<?= $availColor ?>;font-size:1.1rem;"><?= $avail ?></span>
                                    <div class="small text-muted"><?= $occ ?>/<?= $cap ?> occupied</div>
                                </td>
                                <td><?= e($room['room_name']) ?></td>
                                <td><?php
                                    $typeLabels = ['bedspacer' => 'Bedspace', 'single' => 'Single', 'studio' => 'Studio'];
                                    $typeColors = ['bedspacer' => 'bg-secondary', 'single' => 'bg-primary', 'studio' => 'bg-info'];
                                    $rt = $room['room_type'] ?? 'bedspacer';
                                    ?><span class="badge <?= $typeColors[$rt] ?? 'bg-secondary' ?>"><?= $typeLabels[$rt] ?? ucfirst($rt) ?></span> <?php if (!empty($room['is_featured'])): ?><span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Featured</span><?php endif; ?></td>
                                <td class="fw-bold text-success"><?= formatCurrency($room['monthly_rent']) ?></td>
                                <td>
                                    <?php
                                    $cap = max(1, (int)($room['max_capacity'] ?? 1));
                                    $occ = (int)($room['current_occupancy'] ?? 0);
                                    $avail = max(0, $cap - $occ);
                                    $pct = min(100, round(($occ / $cap) * 100));
                                    $barColor = $pct <= 50 ? '#22c55e' : ($pct <= 80 ? '#f59e0b' : ($pct < 100 ? '#f97316' : '#ef4444'));
                                    ?>
                                    <div class="small mb-1"><strong><?= $occ ?></strong>/<?= $cap ?> (<?= $avail ?> left)</div>
                                    <div class="progress" style="height:5px;border-radius:3px;">
                                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:3px;"></div>
                                    </div>
                                    <small class="text-muted"><?= $pct ?>% occupied</small>
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <?= ($room['status'] === 'occupied' && $occ >= $cap) ? '<span class="badge bg-danger">Fully Occupied</span>' : statusBadge($room['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Update Room" onclick="openInlineUpdate(<?= $room['id'] ?>, '<?= e($room['room_number']) ?>', '<?= e($room['room_name']) ?>', <?= (int)$room['monthly_rent'] ?>, <?= $cap ?>, <?= $occ ?>, '<?= e($room['status']) ?>')"><i class="fas fa-edit me-1"></i>Update</button>
                                    <form method="POST" action="<?= url('/manager/room/delete/' . $room['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this room?" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No rooms found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>&room_type=<?= e($roomType ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>&room_type=<?= e($roomType ?? '') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>&room_type=<?= e($roomType ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="modal fade" id="inlineUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-edit me-1"></i>Update Room</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted small">Room: <strong id="inlineUpdateLabel"></strong></p>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold mb-1">Capacity Left</label>
                        <input type="text" id="inlineCapacityLeft" class="form-control form-control-sm" value="" readonly style="background:#f1f5f9;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold mb-1">Monthly Rent</label>
                        <input type="text" id="inlineRent" class="form-control form-control-sm" data-money data-money-min="1" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold mb-1">Max Capacity</label>
                        <input type="number" id="inlineCapacity" class="form-control form-control-sm" min="1">
                        <div class="small text-muted mt-1" id="inlineOccupancyNote"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select id="inlineStatus" class="form-select form-select-sm">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="occupied">Occupied</option>
                            <option value="under_maintenance">Under Maintenance</option>
                        </select>
                        <div class="small text-muted mt-1">Saving notifies tenants, students, and managers by email.</div>
                    </div>
                </div>
                <div id="inlineUpdateError" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="inlineUpdateSaveBtn" onclick="saveInlineUpdate()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
let inlineEditId = null;
let inlineMinCap = 1;
let inlineRoomNumber = '';
let inlineCurrentOcc = 0;
function openInlineUpdate(id, roomNumber, roomName, currentRent, capacity, occ, status) {
    inlineEditId = id;
    inlineRoomNumber = roomNumber;
    inlineCurrentOcc = occ;
    inlineMinCap = Math.max(1, occ);
    document.getElementById('inlineUpdateLabel').textContent = roomNumber + ' - ' + roomName;
    document.getElementById('inlineRent').value = currentRent;
    const capInput = document.getElementById('inlineCapacity');
    capInput.value = capacity;
    capInput.min = inlineMinCap;
    document.getElementById('inlineOccupancyNote').textContent = occ + ' boarder' + (occ !== 1 ? 's' : '') + ' currently staying (capacity cannot be below this).';
    document.getElementById('inlineStatus').value = status || 'available';
    document.getElementById('inlineUpdateError').classList.add('d-none');
    document.getElementById('inlineUpdateError').textContent = '';
    updateCapacityLeft(capacity, occ);
    new bootstrap.Modal(document.getElementById('inlineUpdateModal')).show();
}
function updateCapacityLeft(capacity, occ) {
    const left = Math.max(0, parseInt(capacity, 10) - parseInt(occ, 10));
    document.getElementById('inlineCapacityLeft').value = left + ' slot' + (left !== 1 ? 's' : '') + ' available';
}
document.getElementById('inlineCapacity').addEventListener('input', function() {
    updateCapacityLeft(this.value, inlineCurrentOcc);
});
function saveInlineUpdate() {
    const errDiv = document.getElementById('inlineUpdateError');
    errDiv.classList.add('d-none');
    errDiv.textContent = '';

    const number = inlineRoomNumber;
    const rawRent = document.getElementById('inlineRent').value.replace(/[^0-9]/g, '');
    const rent = parseInt(rawRent, 10) || '';
    const capacity = document.getElementById('inlineCapacity').value;

    if (rent !== '' && rent < 1) {
        errDiv.textContent = 'Please enter a valid monthly rent (e.g., 100).';
        errDiv.classList.remove('d-none');
        return;
    }
    if (capacity !== '' && parseInt(capacity, 10) < 1) {
        errDiv.textContent = 'Capacity must be at least 1.';
        errDiv.classList.remove('d-none');
        return;
    }
    if (capacity !== '' && parseInt(capacity, 10) < inlineMinCap) {
        errDiv.textContent = 'Cannot set below current occupancy (' + inlineMinCap + ').';
        errDiv.classList.remove('d-none');
        return;
    }

    const btn = document.getElementById('inlineUpdateSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    const body = 'room_number=' + encodeURIComponent(number)
        + '&monthly_rent=' + encodeURIComponent(rent)
        + '&max_capacity=' + encodeURIComponent(capacity)
        + '&status=' + encodeURIComponent(document.getElementById('inlineStatus').value)
        + '&csrf_token=<?= csrf_token() ?>';
    fetch('<?= url('/manager/room/inline-update/') ?>' + inlineEditId, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    }).then(r => r.json()).then(data => {
        if (data.success) {
            location.reload();
        } else {
            errDiv.textContent = data.message || 'Failed to update room.';
            errDiv.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    }).catch(() => {
        errDiv.textContent = 'An error occurred.';
        errDiv.classList.remove('d-none');
        btn.disabled = false;
        btn.textContent = 'Save';
    });
}
</script>
