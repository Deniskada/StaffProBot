<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateNotificationTemplatesTable extends Migration {
    protected $table = 'notification_templates';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(100) NOT NULL',
            'description' => 'TEXT NULL',
            'subject' => 'VARCHAR(255) NOT NULL',
            'email_body' => 'TEXT NULL',
            'telegram_body' => 'TEXT NULL',
            'web_body' => 'TEXT NULL',
            'variables' => 'JSON NULL',
            'status' => "ENUM('active', 'inactive') NOT NULL DEFAULT 'active'",
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        // Добавляем базовые шаблоны
        $templates = [
            [
                'name' => 'welcome_email',
                'description' => 'Приветственное письмо после регистрации',
                'subject' => 'Добро пожаловать в {site_name}!',
                'email_body' => "Здравствуйте, {name}!\n\nДобро пожаловать в {site_name}. Мы рады видеть вас в числе наших пользователей.",
                'telegram_body' => "Добро пожаловать в {site_name}, {name}! 👋",
                'variables' => json_encode(['site_name', 'name']),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'shift_started',
                'description' => 'Уведомление о начале смены',
                'subject' => 'Начало смены на объекте {facility_name}',
                'email_body' => "Сотрудник {employee_name} начал смену на объекте {facility_name} в {time}",
                'telegram_body' => "🏢 Начало смены\nОбъект: {facility_name}\nВремя: {time}",
                'variables' => json_encode(['facility_name', 'employee_name', 'time']),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($templates as $template) {
            $this->insert($template);
        }
    }
    
    public function down() {
        $this->dropTable();
    }
} 