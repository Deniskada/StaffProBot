<?php
namespace Spbot\Core;

class MigrationManager {
    private $db;
    private $migrationsPath;
    private $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = ROOT_DIR . '/migrations';
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
        require_once $file;
        
        $className = 'Spbot\\Migrations\\' . str_replace('.php', '', basename($file));
        $instance = new $className();
        
        try {
            $this->db->beginTransaction();
            
            $instance->up();
            
            $this->db->insert($this->migrationsTable, [
                'migration' => $migration,
                'batch' => $batch,
                'executed_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            echo "Migrated: {$migration}\n";
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Migration failed: {$migration}\n" . $e->getMessage());
        }
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
        require_once $file;
        
        $className = 'Spbot\\Migrations\\' . str_replace('.php', '', $migration);
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