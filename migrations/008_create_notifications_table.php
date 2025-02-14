<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateNotificationsTable extends Migration {
    protected $table = 'notifications';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT UNSIGNED NOT NULL',
            'type' => "ENUM('info', 'warning', 'success', 'error') NOT NULL DEFAULT 'info'",
            'title' => 'VARCHAR(255) NOT NULL',
            'message' => 'TEXT NOT NULL',
            'data' => 'JSON NULL',
            'status' => "ENUM('unread', 'read') NOT NULL DEFAULT 'unread'",
            'sent_via' => 'SET("email", "telegram", "web") NOT NULL',
            'read_at' => 'DATETIME NULL',
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('notifications_user_id_idx', 'user_id');
        $this->addIndex('notifications_status_idx', 'status');
        $this->addIndex('notifications_created_at_idx', 'created_at');
        
        $this->addForeignKey(
            'notifications_user_id_fk',
            'user_id',
            'users(id) ON DELETE CASCADE'
        );
    }
    
    public function down() {
        $this->dropForeignKey('notifications_user_id_fk');
        $this->dropTable();
    }
} 