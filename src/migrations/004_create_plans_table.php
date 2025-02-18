<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreatePlansTable extends Migration {
    protected $table = 'plans';
    
    public function up() {
        $durations = "'" . str_replace(",", "','", $_ENV['DB_ENUM_PLAN_DURATIONS']) . "'";
        $statuses = "'" . str_replace(",", "','", $_ENV['DB_ENUM_PLAN_STATUSES']) . "'";

        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'name' => "VARCHAR({$_ENV['DB_FIELD_NAME_LENGTH']}) NOT NULL",
            'description' => 'TEXT NULL',
            'price' => "DECIMAL({$_ENV['DB_FIELD_MONEY_SCALE']},{$_ENV['DB_FIELD_MONEY_PRECISION']}) NOT NULL",
            'duration' => "ENUM({$_ENV['DB_ENUM_PLAN_DURATIONS']}) NOT NULL",
            'max_facilities' => 'INT UNSIGNED NOT NULL',
            'max_employees' => 'INT UNSIGNED NOT NULL',
            'features' => 'JSON NULL',
            'status' => "ENUM({$_ENV['DB_ENUM_PLAN_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_PLAN_DEFAULT_STATUS']}'",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("plans_name_{$_ENV['DB_INDEX_PREFIX']}", 'name', true);
        $this->addIndex("plans_status_{$_ENV['DB_INDEX_PREFIX']}", 'status');
        
        // Добавляем базовые тарифные планы
        $this->insert([
            'name' => 'Базовый',
            'description' => 'Для небольших компаний',
            'price' => floatval($_ENV['PLAN_BASIC_PRICE']),
            'duration' => 'month',
            'max_facilities' => intval($_ENV['PLAN_BASIC_MAX_FACILITIES']),
            'max_employees' => intval($_ENV['PLAN_BASIC_MAX_EMPLOYEES']),
            'features' => json_encode(['basic_support', 'statistics']),
            'status' => 'active',
            'created_at' => date($_ENV['DB_DATETIME_FORMAT']),
            'updated_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ]);
        
        $this->insert([
            'name' => 'Бизнес',
            'description' => 'Для средних компаний',
            'price' => floatval($_ENV['PLAN_BUSINESS_PRICE']),
            'duration' => 'month',
            'max_facilities' => intval($_ENV['PLAN_BUSINESS_MAX_FACILITIES']),
            'max_employees' => intval($_ENV['PLAN_BUSINESS_MAX_EMPLOYEES']),
            'features' => json_encode(['priority_support', 'statistics', 'api_access']),
            'status' => 'active',
            'created_at' => date($_ENV['DB_DATETIME_FORMAT']),
            'updated_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ]);
    }
    
    public function down() {
        $this->dropTable();
    }
} 