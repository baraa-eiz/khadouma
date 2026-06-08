<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        $config = Config::get('database');
        if (!$config) {
            throw new \RuntimeException('Database configuration not found.');
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $dbname = $config['dbname'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $config['username'] ?? 'root', $config['password'] ?? '', $config['options'] ?? []);
        } catch (PDOException $e) {
            throw new \RuntimeException("Database Connection Failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Get the Singleton instance of the Database wrapper.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the raw PDO connection object.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a prepared SQL query and return the statement object.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \RuntimeException("SQL Query Error: " . $e->getMessage() . " | SQL: " . $sql, (int)$e->getCode(), $e);
        }
    }

    /**
     * Execute an SQL statement (INSERT, UPDATE, DELETE) and return success status.
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \RuntimeException("SQL Execution Error: " . $e->getMessage() . " | SQL: " . $sql, (int)$e->getCode(), $e);
        }
    }

    /**
     * Fetch a single row matching the query.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Fetch all rows matching the query.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single scalar column value from the first matching row.
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Get the last inserted ID.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Transaction Helpers
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit active transaction.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback active transaction.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
