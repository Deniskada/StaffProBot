<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateSystemLogsTable extends Migration {
    protected $table = 'system_logs';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'user_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NULL",
            'level' => "VARCHAR({$_ENV['DB_FIELD_LOG_LEVEL_LENGTH']}) NOT NULL",
            'message' => 'TEXT NOT NULL',
            'context' => 'JSON NULL',
            'ip_address' => "VARCHAR({$_ENV['DB_FIELD_IP_ADDRESS_LENGTH']}) NOT NULL",
            'user_agent' => "VARCHAR({$_ENV['DB_FIELD_USER_AGENT_LENGTH']}) NOT NULL",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("system_logs_level_{$_ENV['DB_INDEX_PREFIX']}", 'level');
        $this->addIndex("system_logs_user_id_{$_ENV['DB_INDEX_PREFIX']}", 'user_id');
        $this->addIndex("system_logs_created_at_{$_ENV['DB_INDEX_PREFIX']}", 'created_at');
        
        $this->addForeignKey(
            "system_logs_user_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'user_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_SET_NULL']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("system_logs_user_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 