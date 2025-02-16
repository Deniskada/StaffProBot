<?php
namespace Spbot\models;

use Spbot\core\Model;

class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data',
        'status', 'sent_via', 'read_at'
    ];
    
    public function user() {
        return User::find($this->user_id);
    }
    
    public function getData() {
        return json_decode($this->data, true) ?? [];
    }
    
    public function setData($data) {
        $this->data = json_encode($data);
    }
    
    public function isRead() {
        return $this->read_at !== null;
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedReadAt() {
        return $this->read_at ? date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->read_at)) : null;
    }
    
    public function markAsRead() {
        $this->read_at = date($_ENV['DB_DATETIME_FORMAT']);
        return $this->save();
    }
    
    public function send() {
        if ($this->sent_via && strpos($this->sent_via, 'telegram') !== false) {
            $this->sendViaTelegram();
        }
        if ($this->sent_via && strpos($this->sent_via, 'email') !== false) {
            $this->sendViaEmail();
        }
        
        $this->status = 'sent';
        return $this->save();
    }
    
    private function sendViaTelegram() {
        $user = $this->user();
        if (!$user || !$user->telegram_id) {
            return false;
        }
        
        $telegram = new \Spbot\core\TelegramAPI();
        return $telegram->sendMessage($user->telegram_id, $this->message);
    }
    
    private function sendViaEmail() {
        $user = $this->user();
        if (!$user || !$user->email) {
            return false;
        }
        
        // TODO: Реализовать отправку email
        return true;
    }
    
    public static function getUnread($userId) {
        $notification = new static();
        return $notification->db->fetchAll(
            "SELECT *, 
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(read_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as read_at
              FROM {$notification->table} 
              WHERE user_id = ? AND read_at IS NULL 
              ORDER BY created_at DESC",
            [$userId]
        );
    }
    
    public function getFormattedStatus() {
        $envKey = 'NOTIFICATION_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public function getFormattedChannel() {
        $channels = explode(',', $this->sent_via);
        $formatted = [];
        
        foreach ($channels as $channel) {
            $envKey = 'NOTIFICATION_CHANNEL_' . strtoupper(trim($channel));
            $formatted[] = $_ENV[$envKey] ?? $channel;
        }
        
        return implode(', ', $formatted);
    }
    
    public function getFormattedType() {
        $envKey = 'NOTIFICATION_TYPE_' . strtoupper(str_replace('-', '_', $this->type));
        return $_ENV[$envKey] ?? $this->type;
    }
} 