<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreatePaymentsTable extends Migration {
    protected $table = 'payments';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'employer_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'subscription_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NULL",
            'amount' => "DECIMAL({$_ENV['DB_FIELD_MONEY_SCALE']},{$_ENV['DB_FIELD_MONEY_PRECISION']}) NOT NULL",
            'currency' => "VARCHAR({$_ENV['DB_FIELD_CURRENCY_LENGTH']}) NOT NULL DEFAULT '{$_ENV['DEFAULT_CURRENCY']}'",
            'payment_method' => "ENUM({$_ENV['DB_ENUM_PAYMENT_METHODS']}) NOT NULL",
            'status' => "ENUM({$_ENV['DB_ENUM_PAYMENT_METHOD_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_PAYMENT_METHOD_DEFAULT_STATUS']}'",
            'transaction_id' => "VARCHAR({$_ENV['DB_FIELD_TRANSACTION_ID_LENGTH']}) NULL",
            'payment_data' => 'JSON NULL',
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("payments_employer_id_{$_ENV['DB_INDEX_PREFIX']}", 'employer_id');
        $this->addIndex("payments_subscription_id_{$_ENV['DB_INDEX_PREFIX']}", 'subscription_id');
        $this->addIndex("payments_transaction_id_{$_ENV['DB_INDEX_PREFIX']}", 'transaction_id');
        
        $this->addForeignKey(
            "payments_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'employer_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
        $this->addForeignKey(
            "payments_subscription_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'subscription_id',
            "subscriptions(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_SET_NULL']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("payments_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropForeignKey("payments_subscription_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 