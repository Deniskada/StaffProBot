<?php
namespace Spbot\Core;

class MigrationManager {
    private $db;
    private $migrationsPath;
    private $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = APP_ROOT . '/src/Migrations';
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at DATETIME NOT NULL
        )";
        
        $this->db->query($sql);
    }
    
    public function getMigrationFiles() {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }
    
    public function getExecutedMigrations() {
        return $this->db->fetchAll(
            "SELECT migration FROM {$this->migrationsTable} ORDER BY id ASC"
        );
    }
    
    public function runMigrations() {
        $files = $this->getMigrationFiles();
        $executed = array_column($this->getExecutedMigrations(), 'migration');
        $batch = $this->getLastBatch() + 1;
        $count = 0;
        
        foreach ($files as $file) {
            $migration = basename($file, '.php');
            
            if (!in_array($migration, $executed)) {
                $this->runMigration($file, $migration, $batch);
                $count++;
            }
        }
        
        return $count;
    }
    
    private function runMigration($file, $migration, $batch) {
        echo "Trying to load migration: " . basename($file) . "\n";
        $className = 'Spbot\\Migrations\\' . $this->getMigrationClassName(basename($file));
        echo "Class to check: {$className}\n";
        
        if (!class_exists($className, false)) {
            echo "Loading file: {$file}\n";
            require_once $file;
        } else {
            echo "Class already exists, skipping load\n";
        }
        
        $instance = new $className();
        
        try {
            $this->db->beginTransaction();
            
            $instance->up();
            
            $this->db->insert($this->migrationsTable, [
                'migration' => $migration,
                'batch' => $batch,
                'executed_at' => date($_ENV['DATE_FORMAT'])
            ]);
            
            $this->db->commit();
            echo "Migrated: {$migration}\n";
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Migration failed: {$migration}\n" . $e->getMessage());
        }
    }
    
    private function getMigrationClassName($filename) {
        $name = preg_replace('/^\d+_/', '', basename($filename, '.php'));
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
    
    public function rollback() {
        $batch = $this->getLastBatch();
        if (!$batch) {
            return 0;
        }
        
        $migrations = $this->db->fetchAll(
            "SELECT migration FROM {$this->migrationsTable} 
            WHERE batch = ? ORDER BY id DESC",
            [$batch]
        );
        
        $count = 0;
        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration['migration']);
            $count++;
        }
        
        return $count;
    }
    
    private function rollbackMigration($migration) {
        $file = $this->migrationsPath . '/' . $migration . '.php';
        $className = 'Spbot\\Migrations\\' . $this->getMigrationClassName($migration);
        
        if (!class_exists($className, false)) {
            require_once $file;
        }
        
        $instance = new $className();
        
        try {
            $this->db->beginTransaction();
            
            $instance->down();
            
            $this->db->delete(
                $this->migrationsTable,
                'migration = ?',
                [$migration]
            );
            
            $this->db->commit();
            echo "Rolled back: {$migration}\n";
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Rollback failed: {$migration}\n" . $e->getMessage());
        }
    }
    
    private function getLastBatch() {
        $result = $this->db->fetch(
            "SELECT MAX(batch) as batch FROM {$this->migrationsTable}"
        );
        return $result ? $result['batch'] : 0;
    }
} 