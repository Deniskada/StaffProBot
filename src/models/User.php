<?php
namespace Spbot\Models;

use Spbot\Core\Model;
use Spbot\Core\Database;
use Spbot\Core\App;

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 
        'role', 'status', 'telegram_id', 'last_login'
    ];
    
    public static function where($conditions, $params = []) {
        if (is_array($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $conditions = implode(' AND ', $where);
        }
        
        return parent::where($conditions, $params);
    }
    
    public static function count($conditions = null, $params = []) {
        if (is_array($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $conditions = implode(' AND ', $where);
        }
        
        return parent::count($conditions, $params);
    }
    
    public static function getRecent($limit = 5) {
        $db = static::getDB();
        return $db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
    
    public function setPassword($password) {
        error_log("=== User::setPassword Debug ===");
        error_log("Setting password for user: " . $this->email);
        
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
        error_log("Password hash created successfully");
    }
    
    public function verifyPassword($password) {
        error_log("=== User::verifyPassword Debug ===");
        error_log("Checking password for user: " . $this->email);
        
        // Проверяем, есть ли хеш пароля
        if (!isset($this->attributes['password'])) {
            error_log("Password hash not found for user");
            return false;
        }
        
        $result = password_verify($password, $this->attributes['password']);
        error_log("Password verification result: " . ($result ? "SUCCESS" : "FAILED"));
        
        return $result;
    }
    
    public function getFullName() {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public static function findByEmail($email) {
        return static::findBy('email', $email);
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
        if (isset($this->attributes['id'])) {
            $sql = "UPDATE {$this->table} SET last_login = ? WHERE id = ?";
            return $this->db->query($sql, [$this->last_login, $this->attributes['id']]);
        }
        return false;
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
    
    public function toArray() {
        error_log("=== User::toArray Debug ===");
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'status' => $this->status,
            'last_login' => $this->last_login
        ];
        error_log("Converting user to array: " . print_r($data, true));
        return $data;
    }
    
    public function save() {
        if (isset($this->attributes['id'])) {
            // Если есть id, выполняем UPDATE
            $fields = [];
            $values = [];
            
            foreach ($this->fillable as $field) {
                if (isset($this->attributes[$field])) {
                    $fields[] = "{$field} = ?";
                    $values[] = $this->attributes[$field];
                }
            }
            
            // Добавляем updated_at
            $fields[] = "updated_at = ?";
            $values[] = date($_ENV['DB_DATETIME_FORMAT']);
            
            // Добавляем id в конец массива значений
            $values[] = $this->attributes['id'];
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            return $this->db->query($sql, $values);
        } else {
            // Если нет id, выполняем INSERT
            return parent::save();
        }
    }
    
    // Добавим метод для проверки наличия пароля
    public function hasPassword() {
        return isset($this->attributes['password']) && !empty($this->attributes['password']);
    }
    
    // Переопределим конструктор для установки значений по умолчанию
    public function __construct($attributes = []) {
        parent::__construct();
        
        error_log("=== User::__construct Debug ===");
        error_log("Initial attributes: " . print_r($attributes, true));
        
        // Устанавливаем значения по умолчанию
        $this->attributes = array_merge([
            'id' => null,
            'email' => null,
            'password' => null,
            'first_name' => null,
            'last_name' => null,
            'role' => 'user',
            'status' => 'inactive',
            'telegram_id' => null,
            'last_login' => null
        ], $attributes);
        
        error_log("Final attributes: " . print_r($this->attributes, true));
    }
    
    // Вспомогательные методы для удобства
    public static function countActive() {
        return self::count("status = 'active'");
    }
    
    public static function countNew($days = 7) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        return self::count("created_at >= :date", ['date' => $date]);
    }
    
    public static function countByRole($role) {
        return self::count(['role' => $role]);
    }
    
    // Добавляем вспомогательные методы для удобства
    public static function whereField($field, $value) {
        return self::where("{$field} = :{$field}", [$field => $value]);
    }
} 