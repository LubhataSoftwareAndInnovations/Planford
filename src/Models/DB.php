<?php

class DB {
    private static ?DB $instance = null;
    private mysqli $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conn->connect_error) {
            throw new RuntimeException('DB connection failed: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
        $this->conn->query("SET time_zone = '+05:30'");
    }

    public static function get(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    // Auto-detect param types
    private function types(array $params): string {
        return implode('', array_map(fn($v) =>
            is_int($v) ? 'i' : (is_float($v) ? 'd' : 's'), $params));
    }

    public function query(string $sql, array $params = []): mysqli_result|bool {
        if (empty($params)) {
            $r = $this->conn->query($sql);
            if ($r === false) {
                $this->logError($sql, $params);
                return false;
            }
            return $r;
        }
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { $this->logError($sql, $params); return false; }
        $stmt->bind_param($this->types($params), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false ? $result : true;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $r = $this->query($sql, $params);
        if (!$r || $r === true) return [];
        return $r->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $r = $this->query($sql, $params);
        if (!$r || $r === true) return null;
        return $r->fetch_assoc() ?: null;
    }

    public function fetchColumn(string $sql, array $params = []): mixed {
        $row = $this->fetchOne($sql, $params);
        return $row ? reset($row) : null;
    }

    public function insert(string $table, array $data): int|false {
        $cols   = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
        $marks  = implode(',', array_fill(0, count($data), '?'));
        $sql    = "INSERT INTO `$table` ($cols) VALUES ($marks)";
        $stmt   = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $vals   = array_values($data);
        $stmt->bind_param($this->types($vals), ...$vals);
        if (!$stmt->execute()) return false;
        return $this->conn->insert_id;
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): bool {
        $sets   = implode(',', array_map(fn($k) => "`$k`=?", array_keys($data)));
        $sql    = "UPDATE `$table` SET $sets WHERE $where";
        $vals   = array_merge(array_values($data), $whereParams);
        $stmt   = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param($this->types($vals), ...$vals);
        return $stmt->execute();
    }

    public function delete(string $table, string $where, array $params = []): bool {
        $sql  = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if ($params) $stmt->bind_param($this->types($params), ...$params);
        return $stmt->execute();
    }

    public function count(string $table, string $where = '1', array $params = []): int {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM `$table` WHERE $where", $params);
    }

    public function lastId(): int { return $this->conn->insert_id; }
    public function affected(): int { return $this->conn->affected_rows; }
    public function escape(string $v): string { return $this->conn->real_escape_string($v); }

    public function transaction(callable $fn): bool {
        $this->conn->begin_transaction();
        try {
            $fn($this);
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Transaction failed: ' . $e->getMessage());
            return false;
        }
    }

    private function logError(string $sql, array $params): void {
        error_log('[Planford DB] ' . $this->conn->error . ' | SQL: ' . $sql . ' | Params: ' . json_encode($params));
    }
}

// Global shorthand
function db(): DB { return DB::get(); }
