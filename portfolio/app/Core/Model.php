<?php

namespace App\Core;

/**
 * Model – abstract base for all data models
 *
 * Generic methods:
 *   find($id), findAll(), create(), update(), delete(), paginate()
 *
 * Child classes declare:
 *   protected string $table
 *   protected string $primaryKey = 'id'
 *   protected array  $fillable   = []
 *   protected array  $casts      = []   // 'is_active' => 'bool', 'meta' => 'json'
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array  $fillable   = [];
    protected array  $casts      = [];

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ── Read ────────────────────────────────────────────── */

    public function find(int|string $id): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
        return $row ? $this->cast($row) : null;
    }

    public function findAll(string $where = '1', array $params = [], string $order = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        return array_map([$this, 'cast'], $this->db->fetchAll($sql, $params));
    }

    public function findBy(string $column, mixed $value): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1",
            [$value]
        );
        return $row ? $this->cast($row) : null;
    }

    public function count(string $where = '1', array $params = []): int
    {
        return $this->db->count($this->table, $where, $params);
    }

    /* ── Pagination ──────────────────────────────────────── */

    /**
     * @return array{items: array, total: int, pages: int, page: int, perPage: int}
     */
    public function paginate(
        int    $page    = 1,
        int    $perPage = 15,
        string $where   = '1',
        array  $params  = [],
        string $order   = ''
    ): array {
        $page    = max(1, $page);
        $total   = $this->count($where, $params);
        $pages   = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        $offset  = ($page - 1) * $perPage;

        $sql = "SELECT * FROM `{$this->table}` WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $items = array_map([$this, 'cast'], $this->db->fetchAll($sql, $params));

        return compact('items', 'total', 'pages', 'page', 'perPage');
    }

    /* ── Write ───────────────────────────────────────────── */

    public function create(array $data): int|string
    {
        return $this->db->insert($this->table, $this->filter($data));
    }

    public function update(int|string $id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $this->filter($data),
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function delete(int|string $id): int
    {
        return $this->db->delete(
            $this->table,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /* ── Helpers ─────────────────────────────────────────── */

    /** Strip non-fillable keys */
    protected function filter(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /** Apply type casts to a row */
    protected function cast(array $row): array
    {
        foreach ($this->casts as $col => $type) {
            if (!array_key_exists($col, $row)) continue;
            $row[$col] = match($type) {
                'int'   => (int) $row[$col],
                'float' => (float) $row[$col],
                'bool'  => (bool) $row[$col],
                'json'  => is_string($row[$col]) ? json_decode($row[$col], true) : $row[$col],
                'array' => is_string($row[$col]) ? explode(',', $row[$col]) : (array) $row[$col],
                default => $row[$col],
            };
        }
        return $row;
    }

    public function getTable(): string { return $this->table; }
    public function getDb(): Database  { return $this->db; }
}