<?php
namespace Spbot\Models;

use Spbot\Core\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 
        'role', 'status', 'telegram_id', 'last_login'
    ];
    
    public function setPassword($password) {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function verifyPassword($password) {
        return password_verify($password, $this->attributes['password']);
    }
    
    public function getFullName() {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public static function findByEmail($email) {
        return self::findBy('email', $email);
    }
    
    public static function findByTelegramId($telegramId) {
        return self::findBy('telegram_id', $telegramId);
    }
    
    public function isAdmin() {
        return $this->role === 'admin';
    }
    
    public function isActive() {
        return $this->status === 'active';
    }
    
    public function updateLastLogin() {
        $this->last_login = date('Y-m-d H:i:s');
        return $this->save();
    }
} 