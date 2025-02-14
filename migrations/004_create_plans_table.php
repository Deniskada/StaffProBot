<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreatePlansTable extends Migration {
    protected $table = 'plans';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(100) NOT NULL',
            'description' => 'TEXT NULL',
            'price' => 'DECIMAL(10,2) NOT NULL',
            'duration' => "ENUM('month', 'quarter', 'year') NOT NULL",
            'max_facilities' => 'INT UNSIGNED NOT NULL',
            'max_employees' => 'INT UNSIGNED NOT NULL',
            'features' => 'JSON NULL',
            'status' => "ENUM('active', 'inactive') NOT NULL DEFAULT 'active'",
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        // Добавляем базовые тарифные планы
        $this->insert([
            'name' => 'Базовый',
            'description' => 'Для небольших компаний',
            'price' => 1000.00,
            'duration' => 'month',
            'max_facilities' => 3,
            'max_employees' => 10,
            'features' => json_encode(['basic_support', 'statistics']),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->insert([
            'name' => 'Бизнес',
            'description' => 'Для средних компаний',
            'price' => 2500.00,
            'duration' => 'month',
            'max_facilities' => 10,
            'max_employees' => 50,
            'features' => json_encode(['priority_support', 'statistics', 'api_access']),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function down() {
        $this->dropTable();
    }
} 