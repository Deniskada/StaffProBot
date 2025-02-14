<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;

// Создаем новый документ
$phpWord = new PhpWord();

// Добавляем стили
$phpWord->addTitleStyle(1, ['size' => 16, 'bold' => true]);
$phpWord->addTitleStyle(2, ['size' => 14, 'bold' => true]);
$phpWord->addTitleStyle(3, ['size' => 12, 'bold' => true]);
$codeStyle = ['name' => 'Courier New', 'size' => 10];

// Создаем секцию
$section = $phpWord->addSection();

// Титульный лист
$section->addTitle('Техническая документация SPBot', 1);
$section->addText('Версия 1.0', ['italic' => true]);
$section->addTextBreak(2);

// 1. Общее описание системы
$section->addTitle('1. Общее описание системы', 2);

// 1.1 Назначение
$section->addTitle('1.1 Назначение', 3);
$section->addText('SPBot - система управления сменами, предназначенная для автоматизации процессов учета рабочего времени сотрудников и управления объектами.');

// 1.2 Архитектура
$section->addTitle('1.2 Архитектура', 3);
$section->addText('Система построена на основе MVC-архитектуры с использованием:');
$section->addListItem('PHP 7.4+', 0);
$section->addListItem('MySQL 5.7+', 0);
$section->addListItem('Bootstrap 5', 0);
$section->addListItem('JavaScript/AJAX', 0);

// 1.3 Структура каталогов
$section->addTitle('1.3 Структура каталогов', 3);
$section->addText('
spbot/
├── config/           # Конфигурационные файлы
├── controllers/      # Контроллеры
├── core/            # Ядро системы
├── migrations/      # Миграции БД
├── models/          # Модели
├── public/          # Публичные файлы
├── views/           # Представления
└── logs/            # Логи', $codeStyle);

// 2. Компоненты системы
$section->addTitle('2. Компоненты системы', 2);

$section->addTitle('2.1 Ядро (Core)', 3);
$section->addText('Основные компоненты ядра системы:');
$components = [
    'App.php' => 'Основной класс приложения, инициализация компонентов',
    'Router.php' => 'Маршрутизация HTTP запросов',
    'Controller.php' => 'Базовый класс для всех контроллеров',
    'Model.php' => 'Базовый класс для работы с данными',
    'View.php' => 'Система шаблонов и отображения',
    'Database.php' => 'Работа с базой данных MySQL',
    'Request.php' => 'Обработка входящих запросов',
    'Session.php' => 'Управление сессиями',
    'Validator.php' => 'Валидация данных',
    'JWT.php' => 'Работа с JSON Web Tokens',
    'TelegramAPI.php' => 'Интеграция с Telegram Bot API',
    'PaymentGateway.php' => 'Обработка платежей'
];

foreach ($components as $file => $desc) {
    $section->addListItem("$file - $desc", 1);
}

// 3. База данных
$section->addTitle('3. База данных', 2);

$section->addTitle('3.1 Структура таблиц', 3);
$tables = [
    'users' => 'Пользователи системы',
    'facilities' => 'Объекты',
    'shifts' => 'Смены',
    'plans' => 'Тарифные планы',
    'subscriptions' => 'Подписки',
    'payments' => 'Платежи',
    'notifications' => 'Уведомления',
    'system_logs' => 'Системные логи'
];

foreach ($tables as $table => $desc) {
    $section->addListItem("$table - $desc", 1);
}

// 4. API
$section->addTitle('4. API', 2);

$section->addTitle('4.1 REST API', 3);
$section->addText('Система предоставляет следующие API endpoints:');
$endpoints = [
    '/api/shifts' => 'Управление сменами',
    '/api/facilities' => 'Управление объектами',
    '/api/employees' => 'Управление сотрудниками',
    '/api/payments' => 'Управление платежами'
];

foreach ($endpoints as $endpoint => $desc) {
    $section->addListItem("$endpoint - $desc", 1);
}

// 5. Безопасность
$section->addTitle('5. Безопасность', 2);

$section->addTitle('5.1 Аутентификация', 3);
$section->addText('В системе реализованы следующие механизмы аутентификации:');
$section->addListItem('Сессии для веб-интерфейса', 0);
$section->addListItem('JWT токены для API', 0);
$section->addListItem('CSRF защита для форм', 0);

// 6. Развертывание
$section->addTitle('6. Развертывание', 2);
$section->addText('Требования к серверу:');
$section->addListItem('PHP 7.4 или выше', 0);
$section->addListItem('MySQL 5.7 или выше', 0);
$section->addListItem('Composer', 0);
$section->addListItem('Apache/Nginx', 0);

$section->addTitle('Процесс установки:', 3);
$section->addListItem('Клонировать репозиторий', 0);
$section->addListItem('Установить зависимости: composer install', 0);
$section->addListItem('Настроить .env файл', 0);
$section->addListItem('Создать базу данных', 0);
$section->addListItem('Запустить миграции: php migrate.php', 0);

// Сохраняем документ
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
$objWriter->save(__DIR__ . '/technical_documentation.odt'); 