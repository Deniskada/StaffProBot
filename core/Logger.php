<?php
namespace Spbot\Core;

class Logger {
    private $db;
    private $logTable = 'system_logs';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('warning', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    public function security($message, $context = []) {
        $this->log('security', $message, $context);
    }
    
    private function log($level, $message, $context = []) {
        $data = [
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'user_id' => $_SESSION['user']['id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->insert($this->logTable, $data);
            
            if ($level === 'error' && APP_DEBUG) {
                error_log("[{$level}] {$message} " . json_encode($context));
            }
        } catch (\Exception $e) {
            error_log("Failed to write log: " . $e->getMessage());
        }
    }
    
    public function getRecent($limit = 100, $level = null) {
        $sql = "SELECT * FROM {$this->logTable}";
        $params = [];
        
        if ($level) {
            $sql .= " WHERE level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function clear($daysOld = 30, $level = null) {
        $sql = "DELETE FROM {$this->logTable} WHERE created_at < ?";
        $params = [date('Y-m-d H:i:s', strtotime("-{$daysOld} days"))];
        
        if ($level) {
            $sql .= " AND level = ?";
            $params[] = $level;
        }
        
        return $this->db->query($sql, $params);
    }
} 