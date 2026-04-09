<?php

namespace App\Core;

use PDO;
use PDOStatement;
use PDOException;
use RuntimeException;

/**
 * Database – PDO singleton wrapper
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $row = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [1]);
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $cfg = config('app.db');
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset={$cfg['charset']}";
        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            // Never expose credentials in production
            $msg = config('app.app.debug') ? $e->getMessage() : 'Database connection failed.';
            throw new RuntimeException($msg, 500, $e);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prevent cloning / unserialization
    private function __clone() {}
    public function __wakeup(): never { throw new RuntimeException('Cannot unserialize singleton.'); }

    /* ── Raw PDO access ──────────────────────────────────── */

    public function getPdo(): PDO { return $this->pdo; }

    /* ── Prepared query helpers ──────────────────────────── */

    private function prepare(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch all rows */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->prepare($sql, $params)->fetchAll();
    }

    /** Fetch single row or null */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->prepare($sql, $params)->fetch();
        return $row ?: null;
    }

    /** Fetch single column value */
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->prepare($sql, $params)->fetchColumn();
    }

    /** INSERT / UPDATE / DELETE – returns affected rows */
    public function execute(string $sql, array $params = []): int
    {
        return $this->prepare($sql, $params)->rowCount();
    }

    /** INSERT – returns last insert id */
    public function insert(string $table, array $data): int|string
    {
        [$cols, $placeholders, $vals] = $this->buildInsert($data);
        $sql = "INSERT INTO `{$table}` ({$cols}) VALUES ({$placeholders})";
        $this->prepare($sql, $vals);
        return $this->pdo->lastInsertId();
    }

    /** UPDATE by id */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set  = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $sql  = "UPDATE `{$table}` SET {$set} WHERE {$where}";
        return $this->execute($sql, [...array_values($data), ...$whereParams]);
    }

    /** DELETE */
    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->execute("DELETE FROM `{$table}` WHERE {$where}", $params);
    }

    /* ── Transactions ────────────────────────────────────── */

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollback(): void         { $this->pdo->rollBack(); }

    /**
     * Run callable inside a transaction.
     * Rolls back and rethrows on any exception.
     */
    public function transaction(callable $fn): mixed
    {
        $this->beginTransaction();
        try {
            $result = $fn($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /* ── Helpers ─────────────────────────────────────────── */

    private function buildInsert(array $data): array
    {
        $cols         = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $vals         = array_values($data);
        return [$cols, $placeholders, $vals];
    }

    public function quote(string $val): string
    {
        return $this->pdo->quote($val);
    }

    /** Total row count – useful for pagination */
    public function count(string $table, string $where = '1', array $params = []): int
    {
        return (int) $this->fetchColumn("SELECT COUNT(*) FROM `{$table}` WHERE {$where}", $params);
    }
}