<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Students</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75">Total Students</div><div class="fs-4 fw-bold"><?= $counts['total'] ?></div></div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75">Active</div><div class="fs-4 fw-bold"><?= $counts['active'] ?></div></div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75">Inactive</div><div class="fs-4 fw-bold"><?= $counts['inactive'] ?></div></div>
                    <i class="fas fa-pause-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75">Suspended</div><div class="fs-4 fw-bold"><?= $counts['suspended'] ?></div></div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        
            <div class="col-md-6">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, ID number, or email..." value="<?= e($search ?? '') ?>">
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
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>School / Course</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($s['student_id_number'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px">
                                            <i class="fas fa-user text-primary small"></i>
                                        </div>
                                        <div class="fw-semibold"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                    </div>
                                </td>
                                <td class="text-muted small"><?= e($s['email'] ?? '') ?></td>
                                <td class="text-muted small"><?= e($s['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="small"><?= e($s['school_university'] ?? 'N/A') ?></div>
                                    <div class="text-muted small"><?= e($s['course_program'] ?? '') ?></div>
                                </td>
                                <td><?= statusBadge($s['user_status'] ?? 'active') ?></td>
                                <td class="text-end">
                                    <a href="<?= url($baseUrl . '/student/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View</a>
                                    <form method="POST" action="<?= url($baseUrl . '/student/delete/' . $s['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete student <?= e($s['first_name'] . ' ' . $s['last_name']) ?>? This will remove their account, reservations, and all related data. This action cannot be undone."><i class="fas fa-trash me-1"></i>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
