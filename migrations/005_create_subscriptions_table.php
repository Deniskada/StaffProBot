<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateSubscriptionsTable extends Migration {
    protected $table = 'subscriptions';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'employer_id' => 'INT UNSIGNED NOT NULL',
            'plan_id' => 'INT UNSIGNED NOT NULL',
            'payment_id' => 'INT UNSIGNED NULL',
            'start_date' => 'DATETIME NOT NULL',
            'end_date' => 'DATETIME NOT NULL',
            'status' => "ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active'",
            'auto_renew' => 'BOOLEAN NOT NULL DEFAULT 0',
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('subscriptions_employer_id_idx', 'employer_id');
        $this->addIndex('subscriptions_plan_id_idx', 'plan_id');
        
        $this->addForeignKey(
            'subscriptions_employer_id_fk',
            'employer_id',
            'users(id) ON DELETE CASCADE'
        );
        $this->addForeignKey(
            'subscriptions_plan_id_fk',
            'plan_id',
            'plans(id) ON DELETE RESTRICT'
        );
    }
    
    public function down() {
        $this->dropForeignKey('subscriptions_employer_id_fk');
        $this->dropForeignKey('subscriptions_plan_id_fk');
        $this->dropTable();
    }
} 