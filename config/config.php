<?php
use Spbot\Core\Environment;

return [
    'app' => [
        'name' => 'SPBot',
        'debug' => Environment::get('APP_DEBUG', false),
        'url' => Environment::get('APP_URL', 'http://localhost'),
        'timezone' => Environment::get('TIMEZONE', 'Europe/Moscow'),
    ],
    
    'database' => [
        'host' => Environment::get('DB_HOST'),
        'port' => Environment::get('DB_PORT'),
        'database' => Environment::get('DB_DATABASE'),
        'username' => Environment::get('DB_USERNAME'),
        'password' => Environment::get('DB_PASSWORD'),
        'charset' => Environment::get('DB_CHARSET', 'utf8mb4'),
    ],
    
    'mail' => [
        'host' => Environment::get('MAIL_HOST'),
        'port' => Environment::get('MAIL_PORT'),
        'username' => Environment::get('MAIL_USERNAME'),
        'password' => Environment::get('MAIL_PASSWORD'),
        'encryption' => Environment::get('MAIL_ENCRYPTION'),
        'from_address' => Environment::get('MAIL_FROM_ADDRESS'),
        'from_name' => Environment::get('MAIL_FROM_NAME'),
    ],
    
    'telegram' => [
        'bot_token' => Environment::get('TELEGRAM_BOT_TOKEN'),
        'chat_id' => Environment::get('TELEGRAM_CHAT_ID'),
    ],
    
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . DIRECTORY_SEPARATOR . Environment::get('PUBLIC_PATH', 'public'),
        'storage' => dirname(__DIR__) . DIRECTORY_SEPARATOR . Environment::get('STORAGE_PATH', 'storage'),
        'logs' => dirname(__DIR__) . DIRECTORY_SEPARATOR . Environment::get('LOGS_PATH', 'logs'),
        'cache' => dirname(__DIR__) . DIRECTORY_SEPARATOR . Environment::get('CACHE_PATH', 'storage/cache'),
        'uploads' => dirname(__DIR__) . DIRECTORY_SEPARATOR . Environment::get('UPLOADS_PATH', 'storage/uploads'),
    ],
    
    'security' => [
        'session_lifetime' => Environment::get('SESSION_LIFETIME', 120),
        'token_lifetime' => Environment::get('AUTH_TOKEN_LIFETIME', 60),
    ],
    
    'api' => [
        'version' => Environment::get('API_VERSION', '1.0'),
        'key' => Environment::get('API_KEY'),
    ],
    
    'subscription' => [
        'duration_days' => Environment::get('SUBSCRIPTION_DURATION_DAYS', 30),
        'plans' => [
            'basic' => [
                'price' => Environment::get('PLAN_BASIC_PRICE'),
                'max_facilities' => Environment::get('PLAN_BASIC_MAX_FACILITIES'),
                'max_employees' => Environment::get('PLAN_BASIC_MAX_EMPLOYEES'),
            ],
            'business' => [
                'price' => Environment::get('PLAN_BUSINESS_PRICE'),
                'max_facilities' => Environment::get('PLAN_BUSINESS_MAX_FACILITIES'),
                'max_employees' => Environment::get('PLAN_BUSINESS_MAX_EMPLOYEES'),
            ],
        ],
    ],
]; 