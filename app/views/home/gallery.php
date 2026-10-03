<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $gallery = $gallery ?? []; ?>
<?php $categories = $categories ?? []; ?>
<?php $selectedCategory = $selectedCategory ?? ''; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Gallery</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Our Gallery</h1>
     
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <a href="<?= url('/gallery') ?>" class="btn <?= $selectedCategory === '' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm me-2 mb-2">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= url('/gallery?category=' . urlencode($cat['category'])) ?>" class="btn <?= $selectedCategory === $cat['category'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm me-2 mb-2"><?= e(ucfirst($cat['category'])) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($gallery)): ?>
            <div class="row g-5">
                <?php foreach ($gallery as $index => $item): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm overflow-hidden gallery-item" style="border-radius:12px;cursor:pointer;" onclick="openGalleryModal('<?= UPLOAD_URL . e($item['image_path']) ?>', '<?= e(addslashes($item['title'] ?? 'Gallery Image')) ?>', '<?= e(addslashes($item['category'] ?? '')) ?>')">
                            <div class="gallery-img-wrap" style="aspect-ratio:4/3;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?= UPLOAD_URL . e($item['image_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                        <i class="fas fa-image fa-2x text-muted opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-expand fa-2x mb-2"></i>
                                        <p class="fw-bold mb-0"><?= e($item['title'] ?? 'Gallery Image') ?></p>
                                        <?php if (!empty($item['category'])): ?>
                                            <small class="opacity-75"><?= e(ucfirst($item['category'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <h4 class="fw-bold">No Gallery Items Found</h4>
                <p class="text-muted">Check back later for updates to our gallery.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background:rgba(0,0,0,0.9);border:none;border-radius:12px;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="text-white fw-bold mb-0" id="galleryModalTitle"></h6>
                    <small class="text-white-50" id="galleryModalCategory"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center p-2">
                <img id="galleryModalImage" src="" alt="" style="max-width:100%;max-height:75vh;object-fit:contain;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-img-wrap {
        position: relative;
        overflow: hidden;
    }
    .gallery-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;
    }
    .gallery-overlay {
        background: rgba(37,99,235,0.8);
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }
    .gallery-item:hover .gallery-overlay { opacity: 1 !important; }
    .gallery-item:hover .gallery-img-wrap img { transform: scale(1.05); }
    .gallery-item { transition: transform 0.3s, box-shadow 0.3s; }
    .gallery-item:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important; }
</style>

<script>
function openGalleryModal(src, title, category) {
    document.getElementById('galleryModalImage').src = src;
    document.getElementById('galleryModalImage').alt = title;
    document.getElementById('galleryModalTitle').textContent = title;
    document.getElementById('galleryModalCategory').textContent = category || '';
    var modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    modal.show();
}
</script>
