<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= !empty($room['id']) ? 'Edit Room' : 'Create Room' ?></h1>
    <a href="<?= url('/manager/rooms') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url(!empty($room['id']) ? '/manager/room/edit/' . $room['id'] : '/manager/room/create') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if (!empty($room['id'])): ?>
                <input type="hidden" name="id" value="<?= $room['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Room Number <span class="text-danger">*</span></label>
                    <input type="text" name="room_number" id="roomNumber" class="form-control" value="<?= e($room['room_number'] ?? '') ?>" required <?= empty($room['id']) ? 'readonly style="background:#f1f5f9;"' : '' ?>>
                    <?php if (empty($room['id'])): ?>
                    <div class="form-text">Auto-generated on create.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Room Name <span class="text-danger">*</span></label>
                    <select name="room_name" id="roomName" class="form-select" required>
                        <option value="">-- Select Room Name --</option>
                        <option value="Boys Only" <?= (!empty($room['room_name']) && $room['room_name'] === 'Boys Only') ? 'selected' : '' ?>>Boys Only</option>
                        <option value="Girls Only" <?= (!empty($room['room_name']) && $room['room_name'] === 'Girls Only') ? 'selected' : '' ?>>Girls Only</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Monthly Rent <span class="text-danger">*</span></label>
                    <input type="text" name="monthly_rent" class="form-control" data-money data-money-min="1" required placeholder="0" value="<?= (int)($room['monthly_rent'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Advance Payment</label>
                    <input type="text" name="advance_payment" id="advancePaymentInput" class="form-control" readonly placeholder="0" value="<?= (int)($room['monthly_rent'] ?? 0) ?>" style="background:#f1f5f9;">
                    <div class="form-text">Automatically equals the monthly rent.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max Capacity</label>
                    <input type="number" name="max_capacity" class="form-control" min="1" value="<?= $room['max_capacity'] ?? '1' ?>">
                </div>
                <?php if (!empty($room['id'])): ?>
                <div class="col-md-3">
                    <label class="form-label">Current Occupancy</label>
                    <input type="text" class="form-control" value="<?= ($room['current_occupancy'] ?? 0) . ' / ' . ($room['max_capacity'] ?? 1) ?>" readonly style="background:#f1f5f9;">
                    <div class="form-text">Managed automatically by reservations.</div>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label">Floor</label>
                    <input type="number" name="floor" class="form-control" min="0" value="<?= $room['floor'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Size (sqm)</label>
                    <input type="number" name="size_sqm" class="form-control" step="0.01" min="0" value="<?= $room['size_sqm'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="available" <?= ($room['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="occupied" <?= ($room['status'] ?? '') === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                        <option value="reserved" <?= ($room['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                        <option value="under_maintenance" <?= ($room['status'] ?? '') === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                    </select>
                    <?php if (!empty($room['id']) && (int)($room['current_occupancy'] ?? 0) > 0): ?>
                        <div class="form-text"><?= (int)$room['current_occupancy'] ?> boarder(s) staying — this room will be Occupied.</div>
                    <?php else: ?>
                        <div class="form-text">Set Reserved to hold an empty room.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                    <select name="room_type" class="form-select" required>
                        <option value="bedspacer" <?= ($room['room_type'] ?? '') === 'bedspacer' ? 'selected' : '' ?>>Bedspace</option>
                        <option value="single" <?= ($room['room_type'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                        <option value="studio" <?= ($room['room_type'] ?? '') === 'studio' ? 'selected' : '' ?>>Studio</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($room['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">House Rules</label>
                    <textarea name="house_rules" class="form-control" rows="3"><?= e($room['house_rules'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Furniture</label>
                    <textarea name="furniture" class="form-control" rows="2"><?= e($room['furniture'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold"><i class="fas fa-th-large me-2"></i>Room Features</label>
                    <div class="row g-2">
                        <div class="col-md-3 col-6">
                            <div class="border rounded-3 p-3 text-center feature-check <?= ($room['has_bathroom'] ?? 0) ? 'border-primary bg-primary bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="toggleFeature('hasBathroom', this)">
                                <input type="hidden" name="has_bathroom" value="0" id="hasBathroomHidden">
                                <input class="form-check-input d-none" type="checkbox" name="has_bathroom" value="1" id="hasBathroom" <?= ($room['has_bathroom'] ?? 0) ? 'checked' : '' ?>>
                                <i class="fas fa-bath fa-lg mb-1 <?= ($room['has_bathroom'] ?? 0) ? 'text-primary' : 'text-muted' ?>"></i>
                                <div class="small fw-semibold">Private Bathroom</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded-3 p-3 text-center feature-check <?= ($room['has_balcony'] ?? 0) ? 'border-primary bg-primary bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="toggleFeature('hasBalcony', this)">
                                <input type="hidden" name="has_balcony" value="0" id="hasBalconyHidden">
                                <input class="form-check-input d-none" type="checkbox" name="has_balcony" value="1" id="hasBalcony" <?= ($room['has_balcony'] ?? 0) ? 'checked' : '' ?>>
                                <i class="fas fa-door-open fa-lg mb-1 <?= ($room['has_balcony'] ?? 0) ? 'text-primary' : 'text-muted' ?>"></i>
                                <div class="small fw-semibold">Balcony</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded-3 p-3 text-center feature-check <?= ($room['has_aircon'] ?? 0) ? 'border-primary bg-primary bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="toggleFeature('hasAircon', this)">
                                <input type="hidden" name="has_aircon" value="0" id="hasAirconHidden">
                                <input class="form-check-input d-none" type="checkbox" name="has_aircon" value="1" id="hasAircon" <?= ($room['has_aircon'] ?? 0) ? 'checked' : '' ?>>
                                <i class="fas fa-snowflake fa-lg mb-1 <?= ($room['has_aircon'] ?? 0) ? 'text-primary' : 'text-muted' ?>"></i>
                                <div class="small fw-semibold">Air Conditioning</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded-3 p-3 text-center feature-check <?= ($room['is_featured'] ?? 0) ? 'border-warning bg-warning bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="toggleFeature('isFeatured', this)">
                                <input type="hidden" name="is_featured" value="0" id="isFeaturedHidden">
                                <input class="form-check-input d-none" type="checkbox" name="is_featured" value="1" id="isFeatured" <?= ($room['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                <i class="fas fa-star fa-lg mb-1 <?= ($room['is_featured'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                                <div class="small fw-semibold">Featured</div>
                                <div class="text-muted" style="font-size:0.7rem;">Show on homepage</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold"><i class="fas fa-concierge-bell me-2"></i>Amenities</label>
                    <div class="row g-2">
                        <?php if (!empty($amenities)): ?>
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="col-md-3 col-6">
                                    <div class="border rounded-3 p-3 text-center feature-check <?= in_array($amenity['id'], $selectedAmenities ?? []) ? 'border-success bg-success bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="toggleAmenity(<?= $amenity['id'] ?>, this)">
                                        <input class="form-check-input d-none" type="checkbox" name="amenities[]" value="<?= $amenity['id'] ?>" id="amenity<?= $amenity['id'] ?>" <?= in_array($amenity['id'], $selectedAmenities ?? []) ? 'checked' : '' ?>>
                                        <i class="<?= e($amenity['icon'] ?? 'fas fa-check') ?> fa-lg mb-1 <?= in_array($amenity['id'], $selectedAmenities ?? []) ? 'text-success' : 'text-muted' ?>"></i>
                                        <div class="small fw-semibold"><?= e($amenity['name']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (empty($room['id'])): ?>
                <div class="col-12">
                    <label class="form-label fw-bold">Room Images</label>
                    <div class="border rounded-3 p-3" style="background:#f8f9fc;">
                        <input type="file" name="room_images[]" class="form-control" multiple accept="image/jpeg,image/png,image/gif,image/webp" id="roomImagesInput">
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>You can select multiple images. Allowed: JPG, PNG, GIF, WebP. Max 5MB each. First image will be set as primary.</small>
                        <div id="imagePreviewContainer" class="row g-2 mt-2"></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($images)): ?>
                    <div class="col-12">
                        <label class="form-label fw-bold">Current Images (<?= count($images) ?>)</label>
                        <div class="row g-2" id="currentImages">
                            <?php foreach ($images as $img): ?>
                                <div class="col-6 col-md-4 col-lg-3" id="img-wrapper-<?= $img['id'] ?>">
                                    <div class="card border position-relative" style="border-radius:10px;overflow:hidden;">
                                        <img src="<?= UPLOAD_URL . $img['image_path'] ?>" alt="Room image" style="height:130px;object-fit:cover;" class="card-img-top">
                                        <?php if ($img['is_primary']): ?>
                                            <span class="badge bg-primary position-absolute top-0 start-0 m-1"><i class="fas fa-star me-1"></i>Primary</span>
                                        <?php endif; ?>
                                        <div class="card-body p-2 d-flex gap-1">
                                            <button type="button" class="btn btn-info btn-sm flex-fill text-white" title="Edit image (add / replace)" onclick="editImage(<?= $img['id'] ?>)">
                                                <i class="fas fa-pen"></i> Edit
                                            </button>
                                            <?php if (!$img['is_primary']): ?>
                                                <form method="POST" action="<?= url('/manager/room/edit/' . $room['id']) ?>" class="flex-fill">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="set_primary" value="<?= $img['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100" title="Set as primary">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-success btn-sm flex-fill" title="Add image after this" onclick="toggleAddForm(<?= $img['id'] ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm flex-fill" title="Replace" onclick="replaceImage(<?= $img['id'] ?>)">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <form method="POST" action="<?= url('/manager/room/image/delete/' . $img['id']) ?>" class="flex-fill" onsubmit="return confirm('Delete this image?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div id="replace-form-<?= $img['id'] ?>" class="mt-1" style="display:none;">
                                        <form method="POST" action="<?= url('/manager/room/image/replace/' . $img['id']) ?>" enctype="multipart/form-data">
                                            <?= csrf_field() ?>
                                            <input type="file" name="replace_image" class="form-control form-control-sm mb-1" accept="image/*" required>
                                            <div class="d-flex gap-1">
                                                <button type="submit" class="btn btn-sm btn-success flex-fill"><i class="fas fa-check me-1"></i>Save</button>
                                                <button type="button" class="btn btn-sm btn-secondary flex-fill" onclick="cancelReplace(<?= $img['id'] ?>)"><i class="fas fa-times"></i> Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="add-form-<?= $img['id'] ?>" class="mt-1" style="display:none;">
                                        <form method="POST" action="<?= url('/manager/room/image/add/' . $img['id']) ?>" enctype="multipart/form-data">
                                            <?= csrf_field() ?>
                                            <input type="file" name="add_image" class="form-control form-control-sm mb-1" accept="image/*" required>
                                            <div class="d-flex gap-1">
                                                <button type="submit" class="btn btn-sm btn-success flex-fill"><i class="fas fa-plus me-1"></i>Add</button>
                                                <button type="button" class="btn btn-sm btn-secondary flex-fill" onclick="cancelAdd(<?= $img['id'] ?>)"><i class="fas fa-times"></i> Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($room['id'])): ?>
                    <div class="col-12">
                        <label class="form-label fw-bold"><i class="fas fa-image me-1"></i>Room Images (add on Update)</label>
                        <div class="border rounded-3 p-3" style="background:#f8f9fc;">
                            <input type="file" name="room_images[]" class="form-control" multiple accept="image/jpeg,image/png,image/gif,image/webp" id="updateImagesInput">
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Optionally select new image(s) to add when you click "Update Room". Allowed: JPG, PNG, GIF, WebP. Max 5MB each.</small>
                            <div id="updateImagesPreview" class="row g-2 mt-2"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= !empty($room['id']) ? 'Update Room' : 'Create Room' ?></button>
                <a href="<?= url('/manager/rooms') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
var rentInput = document.querySelector('input[name="monthly_rent"]');
var advInput = document.getElementById('advancePaymentInput');
if (rentInput && advInput) {
    rentInput.addEventListener('input', function() { advInput.value = rentInput.value; });
}
var roomImagesInput = document.getElementById('roomImagesInput');
if (roomImagesInput) {
    roomImagesInput.addEventListener('change', function() {
        var container = document.getElementById('imagePreviewContainer');
        container.innerHTML = '';
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            if (files[i].type.startsWith('image/')) {
                (function(file, index) {
                    var col = document.createElement('div');
                    col.className = 'col-6 col-md-4 col-lg-3';
                    var card = document.createElement('div');
                    card.className = 'card border position-relative';
                    card.style.cssText = 'border-radius:10px;overflow:hidden;';
                    var img = document.createElement('img');
                    img.style.cssText = 'height:130px;object-fit:cover;';
                    img.className = 'card-img-top';
                    var reader = new FileReader();
                    reader.onload = function(e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    card.appendChild(img);
                    if (index === 0) {
                        var badge = document.createElement('span');
                        badge.className = 'badge bg-primary position-absolute top-0 start-0 m-1';
                        badge.innerHTML = '<i class="fas fa-star me-1"></i>Primary';
                        card.appendChild(badge);
                    }
                    var body = document.createElement('div');
                    body.className = 'card-body p-2';
                    body.innerHTML = '<small class="text-muted d-block text-truncate">' + file.name + '</small><small class="text-muted">' + (file.size / 1024).toFixed(1) + ' KB</small>';
                    card.appendChild(body);
                    col.appendChild(card);
                    container.appendChild(col);
                })(files[i], i);
            }
        }
    });
}
var addMoreInput = document.getElementById('updateImagesInput');
if (addMoreInput) {
    addMoreInput.addEventListener('change', function() {
        var container = document.getElementById('updateImagesPreview');
        container.innerHTML = '';
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            if (files[i].type.startsWith('image/')) {
                (function(file) {
                    var col = document.createElement('div');
                    col.className = 'col-6 col-md-4 col-lg-3';
                    var card = document.createElement('div');
                    card.className = 'card border position-relative';
                    card.style.cssText = 'border-radius:10px;overflow:hidden;';
                    var img = document.createElement('img');
                    img.style.cssText = 'height:130px;object-fit:cover;';
                    img.className = 'card-img-top';
                    var reader = new FileReader();
                    reader.onload = function(e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    card.appendChild(img);
                    var body = document.createElement('div');
                    body.className = 'card-body p-2';
                    body.innerHTML = '<small class="text-muted d-block text-truncate">' + file.name + '</small><small class="text-muted">' + (file.size / 1024).toFixed(1) + ' KB</small>';
                    card.appendChild(body);
                    col.appendChild(card);
                    container.appendChild(col);
                })(files[i]);
            }
        }
    });
}
function replaceImage(id) {
    document.getElementById('replace-form-' + id).style.display = 'block';
    document.getElementById('add-form-' + id).style.display = 'none';
}
function editImage(id) {
    var addForm = document.getElementById('add-form-' + id);
    var replaceForm = document.getElementById('replace-form-' + id);
    var showing = addForm.style.display === 'block' || replaceForm.style.display === 'block';
    if (showing) {
        addForm.style.display = 'none';
        replaceForm.style.display = 'none';
    } else {
        addForm.style.display = 'block';
        replaceForm.style.display = 'block';
    }
}
function cancelReplace(id) {
    document.getElementById('replace-form-' + id).style.display = 'none';
    var fileInput = document.querySelector('#replace-form-' + id + ' input[type="file"]');
    if (fileInput) fileInput.value = '';
}
function toggleAddForm(id) {
    var addForm = document.getElementById('add-form-' + id);
    var replaceForm = document.getElementById('replace-form-' + id);
    if (addForm.style.display === 'none') {
        addForm.style.display = 'block';
        replaceForm.style.display = 'none';
    } else {
        addForm.style.display = 'none';
    }
}
function cancelAdd(id) {
    document.getElementById('add-form-' + id).style.display = 'none';
    var fileInput = document.querySelector('#add-form-' + id + ' input[type="file"]');
    if (fileInput) fileInput.value = '';
}
function toggleFeature(name, el) {
    var cb = document.getElementById(name);
    var isFeatured = (name === 'isFeatured');
    cb.checked = !cb.checked;
    if (cb.checked) {
        el.classList.add(isFeatured ? 'border-warning' : 'border-primary');
        el.classList.add(isFeatured ? 'bg-warning' : 'bg-primary');
        el.classList.add('bg-opacity-10');
        el.querySelector('i').classList.remove('text-muted');
        el.querySelector('i').classList.add(isFeatured ? 'text-warning' : 'text-primary');
    } else {
        el.classList.remove('border-warning', 'border-primary', 'bg-warning', 'bg-primary', 'bg-opacity-10');
        el.querySelector('i').classList.remove('text-warning', 'text-primary');
        el.querySelector('i').classList.add('text-muted');
    }
}
function toggleAmenity(id, el) {
    var cb = document.getElementById('amenity' + id);
    cb.checked = !cb.checked;
    if (cb.checked) {
        el.classList.add('border-success', 'bg-success', 'bg-opacity-10');
        el.querySelector('i').classList.remove('text-muted');
        el.querySelector('i').classList.add('text-success');
    } else {
        el.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
        el.querySelector('i').classList.remove('text-success');
        el.querySelector('i').classList.add('text-muted');
    }
}

// Auto-generate room number on create
document.addEventListener('DOMContentLoaded', function() {
    var roomNumberInput = document.getElementById('roomNumber');
    var roomIdInput = document.querySelector('input[name="id"]');
    
    // Only auto-generate for new rooms (no ID)
    if (roomNumberInput && !roomIdInput) {
        fetch('<?= url("/manager/rooms/next-number") ?>')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.next_number) {
                    roomNumberInput.value = data.next_number;
                }
            })
            .catch(function() {
                // Fallback
                var maxNum = 0;
                document.querySelectorAll('[data-room-number]').forEach(function(el) {
                    var num = parseInt(el.getAttribute('data-room-number').match(/\d+/)?.[0] || '0');
                    if (num > maxNum) maxNum = num;
                });
                roomNumberInput.value = maxNum + 1;
            });
    }
});

</script>
