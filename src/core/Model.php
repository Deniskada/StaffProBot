<?php
namespace Spbot\Core;

abstract class Model {
    protected static $db = null;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $attributes = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = self::getDB();
    }
    
    public static function getDB() {
        if (static::$db === null) {
            static::$db = Database::getInstance();
        }
        return static::$db;
    }
    
    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }
    
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    public function fill($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }
    
    public function save() {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        
        if ($this->timestamps) {
            $this->attributes['created_at'] = date('Y-m-d H:i:s');
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        if ($this->db->query($sql, $values)) {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    protected function insert() {
        if ($this->timestamps) {
            $this->attributes['created_at'] = date($_ENV['DATE_FORMAT']);
            $this->attributes['updated_at'] = date($_ENV['DATE_FORMAT']);
        }
        
        $id = $this->db->insert($this->table, $this->attributes);
        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            return true;
        }
        return false;
    }
    
    protected function update() {
        if ($this->timestamps) {
            $this->attributes['updated_at'] = date($_ENV['DATE_FORMAT']);
        }
        
        return $this->db->update(
            $this->table,
            $this->attributes,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        ) > 0;
    }
    
    public function delete() {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        ) > 0;
    }
    
    public static function find($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );
        
        if (!$result) {
            return null;
        }
        
        return $instance->fill($result);
    }
    
    public static function findBy($field, $value) {
        $db = static::getDB();
        $table = (new static)->table;
        
        $sql = "SELECT * FROM {$table} WHERE {$field} = ?";
        $result = $db->fetch($sql, [$value]);
        
        return $result ? new static($result) : null;
    }
    
    public static function all() {
        $instance = new static();
        return $instance->db->fetchAll("SELECT * FROM {$instance->table}");
    }
    
    public static function where($conditions, $params = []) {
        $db = static::getDB();
        $table = (new static)->table;
        
        $sql = "SELECT * FROM {$table} WHERE {$conditions}";
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Возвращает первую запись, соответствующую условиям
     */
    public static function first($conditions = null, $params = []) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        
        $sql .= " LIMIT 1";
        
        if ($result = $instance->db->fetch($sql, $params)) {
            return $instance->fill($result);
        }
        
        return null;
    }
    
    public static function count($conditions = null, $params = []) {
        $db = static::getDB();
        $table = (new static)->table;
        
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        
        $result = $db->fetch($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Создает новую запись в базе данных
     */
    public static function create($data) {
        $model = new static();
        return $model->fill($data)->save();
    }
} 