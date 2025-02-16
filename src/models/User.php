<?php
namespace Spbot\models;

use Spbot\core\Model;

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
        $this->last_login = date($_ENV['DB_DATETIME_FORMAT']);
        return $this->save();
    }
    
    public function isBlocked() {
        return $this->status === 'blocked';
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedUpdatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->updated_at));
    }
    
    public function getFormattedLastLogin() {
        return $this->last_login ? date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->last_login)) : null;
    }
    
    public function getFormattedStatus() {
        $envKey = 'USER_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public function getFormattedRole() {
        $envKey = 'USER_ROLE_' . strtoupper($this->role);
        return $_ENV[$envKey] ?? $this->role;
    }
} 