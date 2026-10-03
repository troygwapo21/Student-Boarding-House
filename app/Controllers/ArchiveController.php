<?php

class ArchiveController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('super_admin', 'manager');
    }

    private function baseUrl(): string {
        return ($_SESSION['user_role'] ?? '') === 'manager' ? '/manager' : '/admin';
    }

    public function index(): void {
        $module = $this->input('module', '');
        $search = trim($this->input('search', ''));
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 25;

        $where = "1";
        $params = [];
        if ($module && isset(self::ARCHIVE_MODULES[$module])) {
            $where .= " AND ar.module = ?";
            $params[] = $module;
        }
        if ($search !== '') {
            $where .= " AND (u.email LIKE ? OR ar.module LIKE ? OR ar.data LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as c FROM archived_records ar LEFT JOIN users u ON ar.deleted_by = u.id WHERE {$where}",
            $params
        )['c'];
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $records = $this->db->fetchAll(
            "SELECT ar.*, u.email as deleted_by_email
             FROM archived_records ar
             LEFT JOIN users u ON ar.deleted_by = u.id
             WHERE {$where}
             ORDER BY ar.deleted_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $rows = [];
        foreach ($records as $rec) {
            $data = json_decode($rec['data'], true);
            if (!is_array($data)) {
                $data = [];
            }
            $rows[] = [
                'archive_id' => (int)$rec['id'],
                'module' => $rec['module'],
                'module_label' => self::ARCHIVE_MODULES[$rec['module']]['label'] ?? ucwords(str_replace('_', ' ', $rec['module'])),
                'module_icon' => self::ARCHIVE_MODULES[$rec['module']]['icon'] ?? 'fa-archive',
                'record_id' => (int)$rec['record_id'],
                'summary' => $this->archiveSummary($rec['module'], $data),
                'related_count' => $this->countRelatedRows($data),
                'deleted_by_email' => $rec['deleted_by_email'] ?? 'Unknown',
                'deleted_at' => $rec['deleted_at'],
            ];
        }

        $stats = $this->buildStats($module);

        $data = [
            'pageTitle' => 'Archive & Recovery',
            'rows' => $rows,
            'stats' => $stats,
            'modules' => self::ARCHIVE_MODULES,
            'module' => $module,
            'search' => $search,
            'pagination' => ['total' => (int)$total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages],
            'baseUrl' => $this->baseUrl(),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.archive', $data, ($_SESSION['user_role'] ?? '') === 'manager' ? 'manager' : 'admin');
    }

    private function countRelatedRows(array $data): int {
        $related = $data['related'] ?? [];
        if (!is_array($related)) {
            return 0;
        }
        $count = 0;
        foreach ($related as $rows) {
            if (is_array($rows)) {
                $count += count($rows);
            }
        }
        return $count;
    }

    private const SENSITIVE_FIELDS = [
        'password', 'password_hash', 'remember_token', 'verification_token',
        'reset_token', 'two_factor_secret', 'otp_secret', 'recovery_codes',
        'api_key', 'api_token', 'secret',
    ];

    private function redactDetail($value) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                if (is_string($k) && in_array($k, self::SENSITIVE_FIELDS, true)) {
                    $out[$k] = '[REDACTED]';
                } else {
                    $out[$k] = $this->redactDetail($v);
                }
            }
            return $out;
        }
        return $value;
    }

    private function buildFileList(string $module, array $data): array {
        $primaryTable = self::ARCHIVE_MODULES[$module]['table'] ?? null;
        $primary = $data['primary'] ?? null;
        $related = $data['related'] ?? [];
        $paths = [];

        if ($primaryTable && is_array($primary)) {
            foreach (self::ARCHIVE_FILE_COLUMNS[$primaryTable] ?? [] as $col) {
                if (!empty($primary[$col])) {
                    $paths[] = $primary[$col];
                }
            }
        }
        if (is_array($related)) {
            foreach ($related as $table => $rows) {
                if (!is_array($rows)) {
                    continue;
                }
                foreach (self::ARCHIVE_FILE_COLUMNS[$table] ?? [] as $col) {
                    foreach ($rows as $row) {
                        if (is_array($row) && !empty($row[$col])) {
                            $paths[] = $row[$col];
                        }
                    }
                }
            }
        }

        $files = [];
        foreach (array_unique(array_filter($paths)) as $rel) {
            $files[$rel] = ['path' => $rel, 'embedded' => false, 'exists' => is_file(UPLOAD_PATH . $rel)];
        }
        $embedded = $data['files'] ?? [];
        if (is_array($embedded)) {
            foreach ($embedded as $f) {
                if (is_array($f) && !empty($f['orig'])) {
                    $rel = $f['orig'];
                    if (isset($files[$rel])) {
                        $files[$rel]['embedded'] = true;
                    } else {
                        $files[$rel] = ['path' => $rel, 'embedded' => true, 'exists' => is_file(UPLOAD_PATH . $rel)];
                    }
                }
            }
        }
        return array_values($files);
    }

    public function detail(): void {
        $id = (int)$this->input('id', 0);
        $archive = $this->db->fetch(
            "SELECT ar.*, u.email AS deleted_by_email
             FROM archived_records ar
             LEFT JOIN users u ON ar.deleted_by = u.id
             WHERE ar.id = ?",
            [$id]
        );
        if (!$archive) {
            $this->sendJson(['ok' => false, 'message' => 'Archived record not found.'], 404);
            return;
        }

        $data = json_decode($archive['data'], true);
        if (!is_array($data)) {
            $data = [];
        }
        $primary = $data['primary'] ?? null;
        $related = $data['related'] ?? [];
        if (!is_array($related)) {
            $related = [];
        }
        if (!is_array($primary)) {
            if ($archive['module'] === 'student' && isset($data['students']) && isset($data['users'])) {
                $primary = $data['students'];
                $related = ['users' => [$data['users']]];
            } else {
                $primary = $data;
                $related = [];
            }
        }

        $module = $archive['module'];
        $this->sendJson([
            'ok' => true,
            'archive_id' => (int)$archive['id'],
            'module' => $module,
            'module_label' => self::ARCHIVE_MODULES[$module]['label'] ?? ucwords(str_replace('_', ' ', $module)),
            'module_icon' => self::ARCHIVE_MODULES[$module]['icon'] ?? 'fa-archive',
            'record_id' => (int)$archive['record_id'],
            'summary' => $this->archiveSummary($module, $data),
            'deleted_by_email' => $archive['deleted_by_email'] ?? 'Unknown',
            'deleted_at' => $archive['deleted_at'],
            'primary' => $this->redactDetail($primary),
            'related' => $this->redactDetail($related),
            'files' => $this->buildFileList($module, $data),
        ]);
    }

    private function sendJson(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    private function buildStats(string $module): array {
        $total = (int)$this->db->fetch("SELECT COUNT(*) as c FROM archived_records")['c'];
        $monthStart = serverDate('Y-m-01');
        $thisMonth = (int)$this->db->fetch("SELECT COUNT(*) as c FROM archived_records WHERE deleted_at >= ?", [$monthStart . ' 00:00:00'])['c'];

        $moduleRows = $this->db->fetchAll(
            "SELECT module, COUNT(*) as c FROM archived_records GROUP BY module ORDER BY c DESC"
        );
        $byModule = [];
        foreach ($moduleRows as $m) {
            $byModule[] = [
                'module' => $m['module'],
                'label' => self::ARCHIVE_MODULES[$m['module']]['label'] ?? ucwords(str_replace('_', ' ', $m['module'])),
                'icon' => self::ARCHIVE_MODULES[$m['module']]['icon'] ?? 'fa-archive',
                'count' => (int)$m['c'],
            ];
        }

        return ['total' => $total, 'this_month' => $thisMonth, 'by_module' => $byModule];
    }

    public function restore(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect($this->baseUrl() . '/archive');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect($this->baseUrl() . '/archive');
            return;
        }
        $id = (int)$this->input('id', 0);
        $result = $this->restoreArchivedRecord($id);
        $this->flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->redirect($this->baseUrl() . '/archive');
    }

    public function deletePermanent(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect($this->baseUrl() . '/archive');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect($this->baseUrl() . '/archive');
            return;
        }
        $id = (int)$this->input('id', 0);
        $result = $this->permanentlyDeleteArchivedRecord($id);
        $this->flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->redirect($this->baseUrl() . '/archive');
    }
}
