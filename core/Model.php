<?php
namespace Spbot\Core;

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $attributes = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance();
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
        return $this->insert();
    }
    
    protected function insert() {
        if ($this->timestamps) {
            $this->attributes['created_at'] = date('Y-m-d H:i:s');
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
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
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
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
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$field} = ?",
            [$value]
        );
        
        if (!$result) {
            return null;
        }
        
        return $instance->fill($result);
    }
    
    public static function all() {
        $instance = new static();
        return $instance->db->fetchAll("SELECT * FROM {$instance->table}");
    }
    
    public static function where($conditions, $params = []) {
        $instance = new static();
        return $instance->db->fetchAll(
            "SELECT * FROM {$instance->table} WHERE {$conditions}",
            $params
        );
    }
    
    public static function count($conditions = null, $params = []) {
        $instance = new static();
        $sql = "SELECT COUNT(*) as count FROM {$instance->table}";
        
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        
        $result = $instance->db->fetch($sql, $params);
        return $result['count'];
    }
} 