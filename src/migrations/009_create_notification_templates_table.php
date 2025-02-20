<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateNotificationTemplatesTable extends Migration {
    protected $table = 'notification_templates';
    
    public function up() {
        $this->beginTransaction();
        
        $statuses = "'" . str_replace(",", "','", $_ENV['DB_ENUM_TEMPLATE_STATUSES']) . "'";

        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'name' => "VARCHAR({$_ENV['DB_FIELD_TEMPLATE_NAME_LENGTH']}) NOT NULL",
            'description' => 'TEXT NULL',
            'subject' => "VARCHAR({$_ENV['DB_FIELD_TEMPLATE_SUBJECT_LENGTH']}) NOT NULL",
            'email_body' => 'TEXT NULL',
            'telegram_body' => 'TEXT NULL',
            'web_body' => 'TEXT NULL',
            'variables' => 'JSON NULL',
            'status' => "ENUM({$statuses}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_TEMPLATE_DEFAULT_STATUS']}'",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
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
                'created_at' => date($_ENV['DB_DATETIME_FORMAT']),
                'updated_at' => date($_ENV['DB_DATETIME_FORMAT'])
            ],
            [
                'name' => 'shift_started',
                'description' => 'Уведомление о начале смены',
                'subject' => 'Начало смены на объекте {facility_name}',
                'email_body' => "Сотрудник {employee_name} начал смену на объекте {facility_name} в {time}",
                'telegram_body' => "🏢 Начало смены\nОбъект: {facility_name}\nВремя: {time}",
                'variables' => json_encode(['facility_name', 'employee_name', 'time']),
                'status' => 'active',
                'created_at' => date($_ENV['DB_DATETIME_FORMAT']),
                'updated_at' => date($_ENV['DB_DATETIME_FORMAT'])
            ]
        ];
        
        foreach ($templates as $template) {
            $this->insert($template);
        }
        
        $this->commit();
    }
    
    public function down() {
        $this->beginTransaction();
        $this->dropTable();
        $this->commit();
    }
} 