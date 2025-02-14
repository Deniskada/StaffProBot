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
        $type = $unique ? 'UNIQUE INDEX' : 'INDEX';
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        
        return $this->db->query(
            "ALTER TABLE {$this->table} ADD {$type} {$name} ({$columns})"
        );
    }
    
    protected function dropIndex($name) {
        return $this->db->query(
            "ALTER TABLE {$this->table} DROP INDEX {$name}"
        );
    }
    
    protected function addForeignKey($name, $column, $reference) {
        return $this->db->query(
            "ALTER TABLE {$this->table} ADD CONSTRAINT {$name} 
            FOREIGN KEY ({$column}) REFERENCES {$reference}"
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
} 