<?php $pagination = $pagination ?? ['total' => 0, 'page' => 1, 'per_page' => 15, 'total_pages' => 1]; ?>
<?php $stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'suspended' => 0]; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Managers</h1>
    <a href="<?= url('/admin/manager/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Manager</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:42px;height:42px">
                        <i class="fas fa-user-tie text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Managers</div>
                        <div class="fw-bold fs-5"><?= number_format($stats['total']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:42px;height:42px">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active</div>
                        <div class="fw-bold fs-5"><?= number_format($stats['active']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:42px;height:42px">
                        <i class="fas fa-pause-circle text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Inactive</div>
                        <div class="fw-bold fs-5"><?= number_format($stats['inactive']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:42px;height:42px">
                        <i class="fas fa-ban text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Suspended</div>
                        <div class="fw-bold fs-5"><?= number_format($stats['suspended']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/manage-managers') ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <?php if (!empty($search) || !empty($status)): ?>
                <div class="col-md-2">
                    <a href="<?= url('/admin/manage-managers') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-times me-1"></i>Clear</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($managers)): ?>
                        <?php foreach ($managers as $m): ?>
                            <?php $isCurrentUser = ($m['user_id'] == ($_SESSION['user_id'] ?? 0)); ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($m['profile_picture'])): ?>
                                            <img src="<?= UPLOAD_URL . $m['profile_picture'] ?>" class="rounded-circle me-2" style="width:36px;height:36px;object-fit:cover" alt="<?= e($m['first_name']) ?>">
                                        <?php else: ?>
                                            <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px">
                                                <i class="fas fa-user text-secondary small"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></div>
                                            <?php if ($isCurrentUser): ?>
                                                <small class="text-muted">(You)</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted"><?= e($m['email']) ?></td>
                                <td class="text-muted"><?= e($m['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($m['link'])): ?>
                                        <a href="<?= e($m['link']) ?>" class="text-primary text-decoration-none small" target="_blank" title="<?= e($m['link']) ?>"><i class="fas fa-external-link-alt me-1"></i>Visit</a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= statusBadge($m['user_status'] ?? 'active') ?></td>
                                <td class="text-muted small"><?= formatDate($m['user_created'] ?? $m['created_at'] ?? '') ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/manager/edit/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if (!$isCurrentUser): ?>
                                        <form method="POST" action="<?= url('/admin/manager/delete/' . $m['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete manager <?= e($m['first_name'] . ' ' . $m['last_name']) ?>? This will remove their account and cannot be undone." title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-user-tie fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-1 fw-semibold">No managers found</p>
                                    <p class="small mb-3">
                                        <?php if (!empty($search) || !empty($status)): ?>
                                            No managers match your search criteria.
                                        <?php else: ?>
                                            Get started by adding your first manager.
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($search) || !empty($status)): ?>
                                        <a href="<?= url('/admin/manage-managers') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-times me-1"></i>Clear Filters</a>
                                    <?php else: ?>
                                        <a href="<?= url('/admin/manager/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Manager</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer bg-white border-0 pt-0">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing <?= (($pagination['page'] - 1) * $pagination['per_page']) + 1 ?>–<?= min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?> of <?= number_format($pagination['total']) ?> managers
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>
