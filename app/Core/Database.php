<?php
/**
 * Database.php
 * Khadomeh Core Database Wrapper
 * 
 * Manages the PDO database connection using the Singleton pattern.
 * Provides simplified query helpers for security (prepared statements)
 * and robust error logging.
 */

namespace App\Core;

use PDO;
use Exception;

class Database {
    private static $instance = null;
    private $pdo;

    /**
     * Private constructor to prevent direct instantiations.
     */
    private function __construct() {
        $config = require APP_DIR . '/config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (Exception $e) {
            // Safe error handling: log details privately, display friendly message publicly
            if (APP_ENV === 'development') {
                throw new Exception("Database Connection Error: " . $e->getMessage());
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
                die("عذراً، حدث خطأ أثناء الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.");
            }
        }
    }

    /**
     * Get the Singleton instance of the Database wrapper.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the raw PDO connection object.
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Execute a prepared SQL query and return the statement object.
     * Use this helper to guarantee parameter sanitization.
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            if (APP_ENV === 'development') {
                throw new Exception("SQL Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            } else {
                error_log("SQL Query Error: " . $e->getMessage() . " | SQL: " . $sql);
                die("عذراً، حدث خطأ أثناء معالجة الطلب.");
            }
        }
    }

    /**
     * Fetch a single row matching the query.
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Fetch all rows matching the query.
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single scalar column value from the first matching row.
     */
    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Get the last inserted ID.
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Transaction Helpers
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
