<?php
// Режим работы приложения
define('APP_ENV', 'development'); // development, production
define('APP_DEBUG', true);
define('APP_TIMEZONE', 'Europe/Moscow');

// Основные настройки
define('SITE_URL', 'https://staffprobot.ru');
define('SITE_NAME', 'SPBot');
define('ADMIN_EMAIL', 'admin@staffprobot.ru');

// База данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'staffpro');
define('DB_USER', 'spdb_user');
define('DB_PASS', 'aY0nD9hI6qfG3uU0');
define('DB_CHARSET', 'utf8mb4');

// Пути к директориям
define('ROOT_DIR', dirname(__DIR__));
define('VIEWS_PATH', ROOT_DIR . '/views');
define('STORAGE_PATH', ROOT_DIR . '/storage');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');

// Настройки сессии
define('SESSION_NAME', 'spbot_session');
define('SESSION_LIFETIME', 7200); // 2 часа
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false);
define('SESSION_HTTP_ONLY', true);

// Настройки безопасности
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('PASSWORD_MIN_LENGTH', 8);
define('JWT_SECRET', '1e8dbfe7b1e4e9dfb5037a2e5c6bf189df19524fca1b1e5111afed00b8620c43');
define('JWT_LIFETIME', 86400); // 24 часа

// Настройки почты
define('MAIL_HOST', 'smtp.your-domain.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@your-domain.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_NAME', SITE_NAME);

// Настройки Telegram
define('TELEGRAM_BOT_TOKEN', '7894030467:AAE3bDi7t3FSbbhUZjv_D8IY3rQc4TpJpXU');
define('TELEGRAM_WEBHOOK_URL', SITE_URL . '/api/telegram/webhook');

// Загружаем дополнительные конфигурации
require_once 'payment.php'; 