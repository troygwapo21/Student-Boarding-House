<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-archive me-2 text-primary"></i>Archive & Recovery</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                    <i class="fas fa-archive"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase">Total Archived</div>
                    <div class="fs-4 fw-bold"><?= number_format($stats['total']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase">This Month</div>
                    <div class="fs-4 fw-bold"><?= number_format($stats['this_month']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success-emphasis d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                    <i class="fas fa-rotate-left"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase">Modules Tracked</div>
                    <div class="fs-4 fw-bold"><?= count($stats['by_module']) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($stats['by_module'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($stats['by_module'] as $m): ?>
                <a href="<?= url($baseUrl . '/archive?module=' . urlencode($m['module'])) ?>" class="text-decoration-none">
                    <span class="badge bg-light border text-dark fs-6 fw-normal px-3 py-2">
                        <i class="fas <?= e($m['icon']) ?> me-1 text-primary"></i><?= e($m['label']) ?>
                        <span class="badge bg-primary-subtle text-primary-emphasis ms-1"><?= number_format($m['count']) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Deleted records are moved here instead of being permanently removed. Restore brings a record back with its related data and files; Delete Permanently removes it for good.
        </div>
        <form method="GET" action="<?= url($baseUrl . '/archive') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $key => $m): ?>
                        <option value="<?= e($key) ?>" <?= $module === $key ? 'selected' : '' ?>><?= e($m['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by deleted by, module, or record data..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
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
                        <th>Module</th>
                        <th>Record</th>
                        <th>Record ID</th>
                        <th>Details</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">
                                        <i class="fas <?= e($row['module_icon']) ?> me-1"></i><?= e($row['module_label']) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold"><?= e($row['summary']) ?></td>
                                <td class="text-muted">#<?= $row['record_id'] ?></td>
                                <td class="text-muted small text-nowrap">
                                    <?php if ($row['related_count'] > 0): ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis" title="Related rows preserved"><i class="fas fa-link me-1"></i><?= $row['related_count'] ?> related</span>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 ms-1" data-bs-toggle="modal" data-bs-target="#archiveDetailModal" data-detail-url="<?= e(url($baseUrl . '/archive/' . $row['archive_id'] . '/detail')) ?>" title="View archived data"><i class="fas fa-eye"></i></button>
                                </td>
                                <td class="text-muted"><?= e($row['deleted_by_email']) ?></td>
                                <td class="text-muted small">
                                    <div><?= formatDateTime($row['deleted_at']) ?></div>
                                    <small class="text-muted"><?= timeAgo($row['deleted_at']) ?></small>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="<?= url($baseUrl . '/archive/' . $row['archive_id'] . '/restore') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" data-confirm="Restore this <?= e(strtolower($row['module_label'])) ?> record with all its related data?" title="Restore"><i class="fas fa-undo me-1"></i>Restore</button>
                                    </form>
                                    <form method="POST" action="<?= url($baseUrl . '/archive/' . $row['archive_id'] . '/delete') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete Permanently this <?= e(strtolower($row['module_label'])) ?> record? This will completely remove it from the database and cannot be undone." title="Delete Permanently"><i class="fas fa-trash me-1"></i>Delete Permanently</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-archive fa-2x mb-3 d-block text-muted"></i>
                            No archived records found.
                        </td></tr>
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
            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&module=<?= urlencode($module) ?>&search=<?= urlencode($search) ?>">Previous</a>
        </li>
        <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&module=<?= urlencode($module) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&module=<?= urlencode($module) ?>&search=<?= urlencode($search) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="modal fade" id="archiveDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i><span id="archiveDetailTitle">Archived Record Details</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="archiveDetailLoading" class="text-center text-muted py-5">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Loading archived data...</div>
                </div>
                <div id="archiveDetailContent" style="display:none;"></div>
                <div id="archiveDetailError" style="display:none;" class="alert alert-danger mb-0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="archiveDetailToggleRaw"><i class="fas fa-code me-1"></i>Raw JSON</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('archiveDetailModal');
    if (!modalEl) return;

    const contentEl = document.getElementById('archiveDetailContent');
    const loadingEl = document.getElementById('archiveDetailLoading');
    const errorEl = document.getElementById('archiveDetailError');
    const titleEl = document.getElementById('archiveDetailTitle');
    const rawBtn = document.getElementById('archiveDetailToggleRaw');
    let rawVisible = false;
    let current = null;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function fmtValue(v) {
        if (v === null || v === undefined || v === '') return '<span class="text-muted">&mdash;</span>';
        if (typeof v === 'boolean') {
            return '<span class="badge ' + (v ? 'bg-success' : 'bg-secondary') + '">' + (v ? 'Yes' : 'No') + '</span>';
        }
        if (typeof v === 'number') return esc(v);
        if (Array.isArray(v) || typeof v === 'object') {
            return '<code class="small">' + esc(JSON.stringify(v)) + '</code>';
        }
        v = String(v);
        if (/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/.test(v)) {
            const d = new Date(v.replace(' ', 'T'));
            if (!isNaN(d.getTime())) return esc(d.toLocaleString());
        }
        if (/^(https?:\/\/|\/uploads\/)/.test(v)) {
            return '<a href="' + esc(v) + '" target="_blank" rel="noopener">' + esc(v) + '</a>';
        }
        return esc(v);
    }

    function kvRows(obj) {
        const keys = Object.keys(obj);
        if (!keys.length) return '<div class="text-muted small">No data.</div>';
        const rows = keys.map(function (k) {
            return '<tr><th scope="row" class="text-nowrap align-top text-secondary small fw-normal text-capitalize pe-3" style="width:32%;">' + esc(k.replace(/_/g, ' ')) + '</th><td class="text-break">' + fmtValue(obj[k]) + '</td></tr>';
        }).join('');
        return '<div class="table-responsive"><table class="table table-sm table-striped mb-0"><tbody>' + rows + '</tbody></table></div>';
    }

    function section(title, icon, badge, body) {
        return '<div class="mb-4">' +
            '<div class="d-flex align-items-center gap-2 mb-2 border-bottom pb-1">' +
            '<i class="fas ' + icon + ' text-primary"></i>' +
            '<h6 class="mb-0 fw-semibold text-uppercase small text-muted">' + esc(title) + '</h6>' +
            (badge ? '<span class="badge bg-primary-subtle text-primary-emphasis ms-auto">' + esc(badge) + '</span>' : '') +
            '</div>' + body + '</div>';
    }

    function render(data) {
        const html = [];

        html.push('<div class="d-flex flex-wrap align-items-center gap-2 mb-3">');
        html.push('<span class="badge bg-primary-subtle text-primary-emphasis"><i class="fas ' + esc(data.module_icon) + ' me-1"></i>' + esc(data.module_label) + '</span>');
        html.push('<span class="fw-semibold">' + esc(data.summary) + '</span>');
        html.push('<span class="text-muted">#</span><span class="text-muted">' + data.record_id + '</span>');
        html.push('<span class="ms-auto text-muted small"><i class="fas fa-user me-1"></i>' + esc(data.deleted_by_email) + ' &middot; <i class="fas fa-clock me-1"></i>' + esc(data.deleted_at) + '</span>');
        html.push('</div>');

        if (data.primary && Object.keys(data.primary).length) {
            html.push(section('Primary Record', 'fa-database', null, kvRows(data.primary)));
        }

        const relTables = Object.keys(data.related || {});
        relTables.forEach(function (t) {
            const rows = data.related[t];
            if (!rows || !rows.length) return;
            const body = rows.map(function (r, i) {
                const head = rows.length > 1 ? '<div class="small text-muted mb-1">Row ' + (i + 1) + ' of ' + rows.length + '</div>' : '';
                return head + kvRows(r);
            }).join('<hr class="my-2">');
            html.push(section(t.replace(/_/g, ' '), 'fa-link', rows.length + ' row' + (rows.length > 1 ? 's' : ''), body));
        });

        if ((data.files || []).length) {
            const fileRows = data.files.map(function (f) {
                const status = f.exists
                    ? '<span class="badge bg-success-subtle text-success-emphasis"><i class="fas fa-check me-1"></i>on disk</span>'
                    : '<span class="badge bg-danger-subtle text-danger-emphasis"><i class="fas fa-times me-1"></i>missing</span>';
                const embedded = f.embedded ? '<span class="badge bg-info-subtle text-info-emphasis ms-1">embedded in archive</span>' : '';
                return '<li class="list-group-item d-flex flex-wrap align-items-center gap-2">' +
                    '<i class="fas fa-file-image text-muted"></i>' +
                    '<code class="small text-break">' + esc(f.path) + '</code>' +
                    '<span class="ms-auto">' + embedded + status + '</span>' +
                    '</li>';
            }).join('');
            html.push(section('Files', 'fa-folder-open', data.files.length + ' file' + (data.files.length > 1 ? 's' : ''), '<ul class="list-group list-group-flush">' + fileRows + '</ul>'));
        }

        contentEl.innerHTML = html.join('');
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const url = btn ? btn.dataset.detailUrl : null;
        if (!url) return;
        rawVisible = false;
        current = null;
        rawBtn.querySelector('i').className = 'fas fa-code me-1';
        rawBtn.lastChild.textContent = ' Raw JSON';
        errorEl.style.display = 'none';
        contentEl.style.display = 'none';
        loadingEl.style.display = 'block';
        titleEl.textContent = 'Archived Record Details';

        fetch(url)
            .then(function (res) {
                if (!res.ok) return res.json().then(function (e) { throw new Error(e.message || 'Request failed.'); });
                return res.json();
            })
            .then(function (data) {
                if (!data.ok) throw new Error(data.message || 'Failed to load archived data.');
                loadingEl.style.display = 'none';
                current = data;
                titleEl.innerHTML = '<i class="fas ' + esc(data.module_icon) + ' me-2"></i>' + esc(data.module_label) + ' #' + data.record_id;
                render(data);
                contentEl.style.display = 'block';
            })
            .catch(function (err) {
                loadingEl.style.display = 'none';
                errorEl.textContent = 'Could not load archived details: ' + err.message;
                errorEl.style.display = 'block';
            });
    });

    rawBtn.addEventListener('click', function () {
        if (!current) return;
        rawVisible = !rawVisible;
        rawBtn.querySelector('i').className = 'fas ' + (rawVisible ? 'fa-archive' : 'fa-code') + ' me-1';
        rawBtn.lastChild.textContent = rawVisible ? ' Structured view' : ' Raw JSON';
        if (rawVisible) {
            contentEl.innerHTML = '<pre class="bg-light p-3 rounded border mb-0" style="white-space:pre-wrap;word-break:break-word;">' + esc(JSON.stringify(current, null, 2)) + '</pre>';
        } else {
            render(current);
        }
    });
});
</script>
