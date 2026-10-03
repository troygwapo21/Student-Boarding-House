<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Amenities</h1>
    <a href="<?= url('/manager/amenity/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Amenity</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $a): ?>
                            <tr>
                                <td><i class="<?= e($a['icon'] ?? 'fas fa-check') ?> fa-lg text-primary"></i></td>
                                <td class="fw-bold"><?= e($a['name']) ?></td>
                                <td class="text-muted"><?= e(truncate($a['description'] ?? '', 60)) ?></td>
                                <td><?= statusBadge($a['status']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/manager/amenity/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="<?= url('/manager/amenity/delete/' . $a['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this amenity?"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No amenities found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
