<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateSubscriptionsTable extends Migration {
    protected $table = 'subscriptions';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'employer_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'plan_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'status' => "ENUM({$_ENV['DB_ENUM_SUBSCRIPTION_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_SUBSCRIPTION_DEFAULT_STATUS']}'",
            'start_date' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'end_date' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'amount' => "DECIMAL({$_ENV['DB_FIELD_MONEY_SCALE']},{$_ENV['DB_FIELD_MONEY_PRECISION']}) NOT NULL",
            'next_billing_date' => "{$_ENV['DB_TYPE_TIMESTAMP']} NULL",
            'cancel_at_period_end' => 'BOOLEAN NOT NULL DEFAULT 0',
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("subscriptions_employer_id_{$_ENV['DB_INDEX_PREFIX']}", 'employer_id');
        $this->addIndex("subscriptions_plan_id_{$_ENV['DB_INDEX_PREFIX']}", 'plan_id');
        
        $this->addForeignKey(
            "subscriptions_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'employer_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
        $this->addForeignKey(
            "subscriptions_plan_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'plan_id',
            "plans(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_RESTRICT']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("subscriptions_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropForeignKey("subscriptions_plan_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 