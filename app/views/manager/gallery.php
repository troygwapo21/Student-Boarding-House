<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Gallery</h1>
    <a href="<?= url('/manager/gallery/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Image</a>
</div>

<div class="row g-3">
    <?php if (!empty($gallery)): ?>
        <?php foreach ($gallery as $item): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <img src="<?= UPLOAD_URL . $item['image_path'] ?>" class="card-img-top" alt="<?= e($item['title']) ?>" style="height:200px;object-fit:cover">
                        <form method="POST" action="<?= url('/manager/gallery/delete/' . $item['id']) ?>" class="position-absolute top-0 end-0 m-2">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger rounded-circle" data-confirm="Delete this image?"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                    <div class="card-body py-2 px-3">
                        <h6 class="card-title mb-0 small fw-bold"><?= e($item['title']) ?></h6>
                        <?php if (!empty($item['description'])): ?>
                            <p class="card-text small text-muted mb-0 mt-1"><?= e(truncate($item['description'], 50)) ?></p>
                        <?php endif; ?>
                        <small class="text-muted"><?= e(ucwords($item['category'] ?? 'General')) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No gallery images yet. Add your first image!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
