<?php
namespace Spbot\Models;

use Spbot\Core\Model;

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
    
    public function markAsRead() {
        $this->read_at = date('Y-m-d H:i:s');
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
        
        $telegram = new \Spbot\Core\TelegramAPI();
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
            "SELECT * FROM {$notification->table} 
            WHERE user_id = ? AND read_at IS NULL 
            ORDER BY created_at DESC",
            [$userId]
        );
    }
} 