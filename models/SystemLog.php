<?php
namespace Spbot\Models;

use Spbot\Core\Model;

class SystemLog extends Model {
    protected $table = 'system_logs';
    protected $fillable = [
        'level', 'message', 'context', 'user_id',
        'ip_address', 'user_agent', 'created_at'
    ];
    
    public function user() {
        return User::find($this->user_id);
    }
    
    public function getContext() {
        return json_decode($this->context, true) ?? [];
    }
    
    public function setContext($context) {
        $this->context = json_encode($context);
    }
    
    public static function log($level, $message, $context = []) {
        $log = new static();
        return $log->fill([
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'user_id' => $_SESSION['user']['id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ])->save();
    }
    
    public static function getRecent($limit = 100, $level = null) {
        $log = new static();
        $sql = "SELECT l.*, u.email, u.first_name, u.last_name 
                FROM {$log->table} l
                LEFT JOIN users u ON l.user_id = u.id";
        $params = [];
        
        if ($level) {
            $sql .= " WHERE l.level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $log->db->fetchAll($sql, $params);
    }
    
    public static function getStatistics($period = 'day') {
        $log = new static();
        $sql = "SELECT 
                level,
                COUNT(*) as count,
                DATE(created_at) as date
                FROM {$log->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 {$period})
                GROUP BY level, DATE(created_at)
                ORDER BY date DESC, level";
                
        return $log->db->fetchAll($sql);
    }
    
    public static function clear($daysOld = 30, $level = null) {
        $log = new static();
        $sql = "DELETE FROM {$log->table} WHERE created_at < ?";
        $params = [date('Y-m-d H:i:s', strtotime("-{$daysOld} days"))];
        
        if ($level) {
            $sql .= " AND level = ?";
            $params[] = $level;
        }
        
        return $log->db->query($sql, $params);
    }
} 