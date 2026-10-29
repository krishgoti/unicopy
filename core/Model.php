<?php
/**
 * UniHelp - Base Model
 * Provides database operations for models
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find all records
     */
    public function all(string $orderBy = 'id', string $order = 'DESC'): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}");
        return $stmt->fetchAll();
    }

    /**
     * Find by column
     */
    public function findBy(string $column, $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find all by column
     */
    public function findAllBy(string $column, $value, string $orderBy = 'id', string $order = 'DESC'): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy} {$order}");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /**
     * Create record
     */
    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $values = array_values($data);
        $values[] = $id;
        return $stmt->execute($values);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Execute raw query
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Count records
     */
    public function count(string $where = '', array $params = []): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
