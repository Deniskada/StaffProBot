<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateSystemLogsTable extends Migration {
    protected $table = 'system_logs';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'level' => "ENUM('info', 'warning', 'error', 'security', 'api') NOT NULL",
            'message' => 'TEXT NOT NULL',
            'context' => 'JSON NULL',
            'user_id' => 'INT UNSIGNED NULL',
            'ip_address' => 'VARCHAR(45) NULL',
            'user_agent' => 'VARCHAR(255) NULL',
            'created_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('system_logs_level_idx', 'level');
        $this->addIndex('system_logs_user_id_idx', 'user_id');
        $this->addIndex('system_logs_created_at_idx', 'created_at');
        
        $this->addForeignKey(
            'system_logs_user_id_fk',
            'user_id',
            'users(id) ON DELETE SET NULL'
        );
    }
    
    public function down() {
        $this->dropForeignKey('system_logs_user_id_fk');
        $this->dropTable();
    }
} 