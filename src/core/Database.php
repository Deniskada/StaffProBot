<?php
namespace Spbot\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            error_log("=== Database Constructor Debug ===");
            
            // Используем те же ключи, что в .env
            $host = Environment::get('DB_HOST', 'localhost');
            $port = Environment::get('DB_PORT', '3306');
            $dbname = Environment::get('DB_DATABASE'); // Было DB_NAME
            $charset = Environment::get('DB_CHARSET', 'utf8mb4');
            $username = Environment::get('DB_USERNAME'); // Было DB_USER
            $password = Environment::get('DB_PASSWORD'); // Было DB_PASS
            
            error_log("Database config:");
            error_log("Host: " . $host);
            error_log("Port: " . $port);
            error_log("Database: " . $dbname);
            error_log("Username: " . $username);
            
            // Проверяем обязательные параметры
            if (empty($dbname) || empty($username)) {
                throw new \RuntimeException('Database configuration is incomplete. Check your .env file.');
            }
            
            // Проверяем наличие сокета MySQL
            $socket = '/var/lib/mysql/mysql.sock';
            if (file_exists($socket)) {
                error_log("Found MySQL socket: " . $socket);
                $dsn = "mysql:unix_socket={$socket};dbname={$dbname};charset={$charset}";
            } else {
                error_log("Using TCP connection");
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            }
            
            error_log("Using connection string: " . $dsn);
            
            $this->pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            error_log("Database connection established successfully");
            
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Выполняет запрос и возвращает одну строку
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Выполняет запрос и возвращает все строки
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Выполняет запрос без возврата результата
     */
    public function query($sql, $params = []) {
        try {
            error_log("=== Database Query Debug ===");
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($params);
            
            error_log("Query execution success: " . ($success ? 'YES' : 'NO'));
            if (!$success) {
                error_log("PDO Error Info: " . print_r($stmt->errorInfo(), true));
            }
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("PDO Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Возвращает ID последней вставленной записи
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            $placeholders
        );
        
        $stmt = $this->query($sql, $values);
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $params = []) {
        $set = [];
        foreach ($data as $field => $value) {
            $set[] = "{$field} = ?";
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $set),
            $where
        );
        
        $values = array_merge(array_values($data), $params);
        return $this->query($sql, $values)->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        return $this->query($sql, $params)->rowCount();
    }
    
    private function logError(\PDOException $e, $sql, $params = []) {
        // Форматируем сообщение об ошибке
        $message = sprintf(
            "Database Error: %s\nSQL: %s\nParams: %s",
            $e->getMessage(),
            $sql,
            json_encode($params)
        );
        
        error_log($message);
        
        // Логируем в системный журнал, если класс существует
        if (class_exists('\\Spbot\\Models\\SystemLog')) {
            \Spbot\Models\SystemLog::log('error', 'Database error', [
                'message' => $e->getMessage(),
                'sql' => $sql,
                'params' => $params
            ]);
        }
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    public function fetchOne($sql, $params = []) {
        error_log("=== Database fetchOne Debug ===");
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        
        $stmt = $this->query($sql, $params);
        if ($stmt === false) {
            error_log("Query failed in fetchOne");
            return null;
        }
        
        $result = $stmt->fetch();
        error_log("Fetched result: " . print_r($result, true));
        return $result;
    }
} 