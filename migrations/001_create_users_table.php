<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateUsersTable extends Migration {
    protected $table = 'users';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'email' => 'VARCHAR(255) NOT NULL UNIQUE',
            'password' => 'VARCHAR(255) NOT NULL',
            'first_name' => 'VARCHAR(100) NOT NULL',
            'last_name' => 'VARCHAR(100) NOT NULL',
            'role' => "ENUM('admin', 'employer', 'employee') NOT NULL",
            'status' => "ENUM('active', 'blocked') NOT NULL DEFAULT 'active'",
            'telegram_id' => 'BIGINT UNSIGNED UNIQUE',
            'last_login' => 'DATETIME NULL',
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('users_email_idx', 'email', true);
        $this->addIndex('users_telegram_id_idx', 'telegram_id', true);
        
        // Создаем администратора по умолчанию
        $this->insert([
            'email' => ADMIN_EMAIL,
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function down() {
        $this->dropTable();
    }
} 