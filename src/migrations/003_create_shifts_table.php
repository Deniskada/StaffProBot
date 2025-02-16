<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateShiftsTable extends Migration {
    protected $table = 'shifts';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'employee_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'employer_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'facility_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'start_time' => 'DATETIME NOT NULL',
            'end_time' => 'DATETIME NULL',
            'break_duration' => 'INT UNSIGNED DEFAULT 0',  // В минутах
            'notes' => 'TEXT NULL',
            'status' => "ENUM({$_ENV['DB_ENUM_SHIFT_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_SHIFT_DEFAULT_STATUS']}'",
            'payment_status' => "ENUM({$_ENV['DB_ENUM_PAYMENT_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_PAYMENT_DEFAULT_STATUS']}'",
            'hourly_rate' => "DECIMAL({$_ENV['DB_FIELD_MONEY_SCALE']},{$_ENV['DB_FIELD_MONEY_PRECISION']}) NOT NULL",
            'total_hours' => "DECIMAL({$_ENV['DB_FIELD_HOURS_SCALE']},{$_ENV['DB_FIELD_HOURS_PRECISION']}) NULL",
            'total_amount' => "DECIMAL({$_ENV['DB_FIELD_MONEY_SCALE']},{$_ENV['DB_FIELD_MONEY_PRECISION']}) NULL",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("shifts_employee_id_{$_ENV['DB_INDEX_PREFIX']}", 'employee_id');
        $this->addIndex("shifts_employer_id_{$_ENV['DB_INDEX_PREFIX']}", 'employer_id');
        $this->addIndex("shifts_facility_id_{$_ENV['DB_INDEX_PREFIX']}", 'facility_id');
        
        $this->addForeignKey(
            "shifts_employee_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'employee_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
        $this->addForeignKey(
            "shifts_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'employer_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
        $this->addForeignKey(
            "shifts_facility_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'facility_id',
            "facilities(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("shifts_employee_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropForeignKey("shifts_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropForeignKey("shifts_facility_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 