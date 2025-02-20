<?php
namespace Spbot\Core;

abstract class Migration {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    abstract public function up();
    abstract public function down();
    
    protected function createTable($columns) {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (\n";
        $sql .= implode(",\n", array_map(function($name, $definition) {
            return "    {$name} {$definition}";
        }, array_keys($columns), $columns));
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        return $this->db->query($sql);
    }
    
    protected function dropTable() {
        return $this->db->query("DROP TABLE IF EXISTS {$this->table}");
    }
    
    protected function addColumn($name, $definition) {
        return $this->db->query(
            "ALTER TABLE {$this->table} ADD COLUMN {$name} {$definition}"
        );
    }
    
    protected function dropColumn($name) {
        return $this->db->query(
            "ALTER TABLE {$this->table} DROP COLUMN {$name}"
        );
    }
    
    protected function addIndex($name, $columns, $unique = false) {
        if ($this->indexExists($name)) {
            return true;
        }
        
        $type = $unique ? 'UNIQUE INDEX' : 'INDEX';
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        
        return $this->db->query(
            "ALTER TABLE {$this->table} ADD {$type} {$name} ({$columns})"
        );
    }
    
    protected function dropIndex($name) {
        if (!$this->indexExists($name)) {
            return true;
        }
        
        return $this->db->query(
            "ALTER TABLE {$this->table} DROP INDEX {$name}"
        );
    }
    
    protected function addForeignKey($name, $column, $referenceTable, $referenceField, $onDelete = 'RESTRICT') {
        if ($this->foreignKeyExists($name)) {
            return true;
        }
        
        return $this->db->query(
            "ALTER TABLE {$this->table} 
            ADD CONSTRAINT {$name} 
            FOREIGN KEY ({$column}) 
            REFERENCES {$referenceTable}({$referenceField})
            ON DELETE {$onDelete}"
        );
    }
    
    protected function dropForeignKey($name) {
        return $this->db->query(
            "ALTER TABLE {$this->table} DROP FOREIGN KEY {$name}"
        );
    }
    
    protected function insert($data) {
        return $this->db->insert($this->table, $data);
    }
    
    protected function delete($where, $params = []) {
        return $this->db->delete($this->table, $where, $params);
    }
    
    protected function tableExists($table = null) {
        $table = $table ?? $this->table;
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count 
             FROM information_schema.tables 
             WHERE table_schema = ? AND table_name = ?",
            [$_ENV['DB_DATABASE'], $table]
        );
        return $result['count'] > 0;
    }
    
    protected function foreignKeyExists($name) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count 
             FROM information_schema.table_constraints 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND constraint_name = ? 
             AND constraint_type = 'FOREIGN KEY'",
            [$_ENV['DB_DATABASE'], $this->table, $name]
        );
        return $result['count'] > 0;
    }
    
    protected function indexExists($name) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$_ENV['DB_DATABASE'], $this->table, $name]
        );
        return $result['count'] > 0;
    }
} 