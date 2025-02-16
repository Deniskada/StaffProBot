<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateNotificationsTable extends Migration {
    protected $table = 'notifications';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'user_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'type' => "VARCHAR({$_ENV['DB_FIELD_NOTIFICATION_TYPE_LENGTH']}) NOT NULL",
            'title' => "VARCHAR({$_ENV['DB_FIELD_NOTIFICATION_TITLE_LENGTH']}) NOT NULL",
            'message' => 'TEXT NOT NULL',
            'data' => 'JSON NULL',
            'status' => "ENUM({$_ENV['DB_ENUM_NOTIFICATION_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_NOTIFICATION_DEFAULT_STATUS']}'",
            'sent_via' => "SET({$_ENV['DB_ENUM_NOTIFICATION_CHANNELS']}) NOT NULL",
            'read_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NULL",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("notifications_user_id_{$_ENV['DB_INDEX_PREFIX']}", 'user_id');
        $this->addIndex("notifications_status_{$_ENV['DB_INDEX_PREFIX']}", 'status');
        $this->addIndex("notifications_created_at_{$_ENV['DB_INDEX_PREFIX']}", 'created_at');
        
        $this->addForeignKey(
            "notifications_user_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'user_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("notifications_user_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 