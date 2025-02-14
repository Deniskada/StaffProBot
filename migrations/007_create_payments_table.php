<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreatePaymentsTable extends Migration {
    protected $table = 'payments';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'employer_id' => 'INT UNSIGNED NOT NULL',
            'subscription_id' => 'INT UNSIGNED NULL',
            'amount' => 'DECIMAL(10,2) NOT NULL',
            'currency' => "VARCHAR(3) NOT NULL DEFAULT 'RUB'",
            'payment_method' => "ENUM('card', 'bank_transfer') NOT NULL",
            'status' => "ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending'",
            'transaction_id' => 'VARCHAR(100) NULL',
            'payment_data' => 'JSON NULL',
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('payments_employer_id_idx', 'employer_id');
        $this->addIndex('payments_subscription_id_idx', 'subscription_id');
        $this->addIndex('payments_transaction_id_idx', 'transaction_id');
        
        $this->addForeignKey(
            'payments_employer_id_fk',
            'employer_id',
            'users(id) ON DELETE CASCADE'
        );
        $this->addForeignKey(
            'payments_subscription_id_fk',
            'subscription_id',
            'subscriptions(id) ON DELETE SET NULL'
        );
    }
    
    public function down() {
        $this->dropForeignKey('payments_employer_id_fk');
        $this->dropForeignKey('payments_subscription_id_fk');
        $this->dropTable();
    }
} 