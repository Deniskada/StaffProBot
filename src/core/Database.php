<?php
namespace Spbot\Core;

class Database {
    private static $instance = null;
    private $pdo;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    
    private function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT'];
        $this->dbname = $_ENV['DB_DATABASE'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
        
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->host,
                $this->dbname,
                DB_CHARSET
            );
            
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->pdo = new \PDO($dsn, $this->username, $this->password, $options);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            $this->logError($e, $sql, $params);
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
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
    
    private function logError($e, $sql, $params) {
        $message = sprintf(
            "Database Error: %s\nSQL: %s\nParams: %s",
            $e->getMessage(),
            $sql,
            json_encode($params)
        );
        
        error_log($message);
        
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
} 