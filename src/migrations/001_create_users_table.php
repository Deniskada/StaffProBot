<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateUsersTable extends Migration {
    protected $table = 'users';
    
    public function up() {
// Обрабатываем значения ENUM
            $roles = "'" . str_replace(",", "','", $_ENV['DB_ENUM_USER_ROLES']) . "'";
            $statuses = "'" . str_replace(",", "','", $_ENV['DB_ENUM_USER_STATUSES']) . "'";

        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'email' => "VARCHAR({$_ENV['DB_FIELD_EMAIL_LENGTH']}) NOT NULL UNIQUE",
            'password' => "VARCHAR({$_ENV['DB_FIELD_PASSWORD_LENGTH']}) NOT NULL",
            'first_name' => "VARCHAR({$_ENV['DB_FIELD_FIRSTNAME_LENGTH']}) NOT NULL",
            'last_name' => "VARCHAR({$_ENV['DB_FIELD_LASTNAME_LENGTH']}) NOT NULL",
           
            'role' => "ENUM($roles) NOT NULL",
            'status' => "ENUM($statuses) NOT NULL DEFAULT '{$_ENV['DB_ENUM_USER_DEFAULT_STATUS']}'",

            // 'role' => "ENUM({$_ENV['DB_ENUM_USER_ROLES']}) NOT NULL",
            // 'status' => "ENUM({$_ENV['DB_ENUM_USER_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_USER_DEFAULT_STATUS']}'",
            'telegram_id' => 'BIGINT UNSIGNED UNIQUE',
            'last_login' => "{$_ENV['DB_TYPE_TIMESTAMP']} NULL",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("users_email_{$_ENV['DB_INDEX_PREFIX']}", 'email', true);
        $this->addIndex("users_telegram_id_{$_ENV['DB_INDEX_PREFIX']}", 'telegram_id', true);
        
        // Создаем администратора по умолчанию
        $this->insert([
            'email' => $_ENV['ADMIN_EMAIL'],
            'password' => password_hash($_ENV['ADMIN_PASSWORD'], PASSWORD_DEFAULT),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date($_ENV['DB_DATETIME_FORMAT']),
            'updated_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ]);
    }
    
    public function down() {
        $this->dropTable();
    }
} 