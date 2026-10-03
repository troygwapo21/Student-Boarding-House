<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Gallery Images</h1>
    <a href="<?= url('/manager/gallery') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('/manager/gallery/create') ?>" enctype="multipart/form-data" id="galleryForm">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                    <div class="form-text">This title will be applied to all uploaded images.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="general">General</option>
                        <option value="rooms">Rooms</option>
                        <option value="amenities">Amenities</option>
                        <option value="events">Events</option>
                        <option value="facilities">Facilities</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                    <div class="form-text">This description will be applied to all uploaded images.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Images <span class="text-danger">*</span></label>
                    <div class="upload-zone" id="uploadZone" style="border:2px dashed #dee2e6;border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-1 fw-bold">Click or drag images here</p>
                        <p class="text-muted small mb-3">Select multiple images at once</p>
                        <input type="file" name="images[]" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" multiple style="display:none;">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('imageInput').click()"><i class="fas fa-plus me-1"></i>Add Images</button>
                    </div>
                    <div class="form-text">Accepted: JPG, PNG, GIF, WebP, SVG. Max 5MB per image.</div>
                </div>
                <div class="col-12" id="previewContainer" style="display:none;">
                    <label class="form-label fw-bold">Selected Images (<span id="imageCount">0</span>)</label>
                    <div class="row g-2" id="previewGrid"></div>
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled><i class="fas fa-upload me-2"></i>Upload Images</button>
                <a href="<?= url('/manager/gallery') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const zone = document.getElementById('uploadZone');
    const input = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewGrid = document.getElementById('previewGrid');
    const imageCount = document.getElementById('imageCount');
    const submitBtn = document.getElementById('submitBtn');
    let selectedFiles = [];

    zone.addEventListener('dragover', function(e){ e.preventDefault(); zone.style.borderColor='#2563eb'; zone.style.background='#eff6ff'; });
    zone.addEventListener('dragleave', function(e){ e.preventDefault(); zone.style.borderColor='#dee2e6'; zone.style.background=''; });
    zone.addEventListener('drop', function(e){
        e.preventDefault();
        zone.style.borderColor='#dee2e6';
        zone.style.background='';
        addFiles(e.dataTransfer.files);
    });
    zone.addEventListener('click', function(e){
        if (e.target.tagName !== 'BUTTON') input.click();
    });

    input.addEventListener('change', function(){
        addFiles(this.files);
        this.value = '';
    });

    function addFiles(fileList){
        for (let i = 0; i < fileList.length; i++){
            const file = fileList[i];
            if (!file.type.startsWith('image/')) continue;
            if (file.size > 5242880) { alert(file.name + ' exceeds 5MB limit.'); continue; }
            let duplicate = false;
            for (let j = 0; j < selectedFiles.length; j++){
                if (selectedFiles[j].name === file.name && selectedFiles[j].size === file.size){ duplicate = true; break; }
            }
            if (!duplicate) selectedFiles.push(file);
        }
        renderPreviews();
    }

    function renderPreviews(){
        previewGrid.innerHTML = '';
        if (selectedFiles.length === 0){
            previewContainer.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }
        previewContainer.style.display = '';
        submitBtn.disabled = false;
        imageCount.textContent = selectedFiles.length;
        selectedFiles.forEach(function(file, idx){
            const col = document.createElement('div');
            col.className = 'col-md-2 col-4';
            const reader = new FileReader();
            reader.onload = function(e){
                col.innerHTML = '<div class="position-relative"><img src="'+e.target.result+'" style="width:100%;height:100px;object-fit:cover;border-radius:8px;" alt="'+file.name+'"><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle" data-idx="'+idx+'" style="width:24px;height:24px;padding:0;font-size:10px;"><i class="fas fa-times"></i></button><div class="text-truncate small text-muted mt-1" title="'+file.name+'">'+file.name+'</div></div>';
                col.querySelector('button').addEventListener('click', function(){
                    selectedFiles.splice(parseInt(this.dataset.idx), 1);
                    syncInput();
                    renderPreviews();
                });
                syncInput();
            };
            reader.readAsDataURL(file);
        });
    }

    function syncInput(){
        const dt = new DataTransfer();
        selectedFiles.forEach(function(f){ dt.items.add(f); });
        input.files = dt.files;
    }

    document.getElementById('galleryForm').addEventListener('submit', function(e){
        if (selectedFiles.length === 0){ e.preventDefault(); alert('Please select at least one image.'); }
    });
})();
</script>

<style>
.upload-zone:hover { border-color:#2563eb !important; background:#eff6ff !important; }
</style>
