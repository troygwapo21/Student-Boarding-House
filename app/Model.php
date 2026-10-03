<?php
/**
 * Base Model
 */

require_once __DIR__ . '/Database.php';

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    public function findAll(string $where = '1', array $params = [], string $orderBy = 'created_at DESC', int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy}";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int {
        return $this->db->insert($this->table, $data);
    }

    public function updateById(int $id, array $data): int {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function deleteById(int $id): int {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function count(string $where = '1', array $params = []): int {
        return $this->db->count($this->table, $where, $params);
    }

    public function paginate(string $where = '1', array $params = [], int $perPage = 10, int $page = 1, string $orderBy = 'created_at DESC'): array {
        $total = $this->count($where, $params);
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public function findWhere(string $where, array $params = []): ?array {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE {$where}", $params);
    }

    public function select(string $columns, string $where = '1', array $params = []): array {
        return $this->db->fetchAll("SELECT {$columns} FROM {$this->table} WHERE {$where}", $params);
    }
}
