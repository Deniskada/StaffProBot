<?php
// В начале файла
error_log("=== Starting Application Bootstrap ===");
error_log("Current working directory: " . getcwd());
error_log("Script path: " . __FILE__);

// Определяем корневую директорию приложения
define('APP_ROOT', dirname(__FILE__));
error_log("APP_ROOT set to: " . APP_ROOT);

// Проверяем наличие и права доступа к .env
$envPath = APP_ROOT . '/.env';
error_log("Checking .env at: " . $envPath);
error_log("File exists: " . (file_exists($envPath) ? "YES" : "NO"));
if (file_exists($envPath)) {
    error_log("File permissions: " . substr(sprintf('%o', fileperms($envPath)), -4));
    error_log("File owner: " . posix_getpwuid(fileowner($envPath))['name']);
}

// Автозагрузка через composer
require_once APP_ROOT . '/vendor/autoload.php';

use Spbot\Core\Environment;

// Загружаем переменные окружения через наш класс
Environment::load();

// Проверяем, что основные переменные загружены
$criticalVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
foreach ($criticalVars as $var) {
    error_log("Critical var {$var}: " . (empty($_ENV[$var]) ? "NOT SET" : "SET"));
}

// Проверка обязательных переменных
if (!Environment::get('CONFIG_PATH')) {
    die('CONFIG_PATH not set in .env file');
}

// Проверяем обязательные переменные окружения
$required_env_vars = [
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'APP_NAMESPACE',
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_USERNAME',
    'MAIL_PASSWORD',
    'MAIL_ENCRYPTION',
    'MAIL_FROM_ADDRESS',
    'MAIL_FROM_NAME',
    'STRIPE_KEY',
    'STRIPE_SECRET',
    'TELEGRAM_BOT_TOKEN',
    'TELEGRAM_CHAT_ID',
    'DEFAULT_CURRENCY',
    'AUTH_TOKEN_LIFETIME',
    'SESSION_LIFETIME',
    'API_VERSION',
    'API_KEY',
    'SUBSCRIPTION_DURATION_DAYS',
    'LOG_RETENTION_DAYS',
    'LOG_DEFAULT_LIMIT',
    'PRICE_DECIMAL_PLACES',
    'DATE_FORMAT',
    'TIMEZONE',
    'UPLOAD_ALLOWED_TYPES',
    'UPLOAD_MAX_SIZE',
    'CACHE_PATH',
    'ADMIN_EMAIL',
    'ADMIN_PASSWORD',
    'PLAN_BASIC_PRICE',
    'PLAN_BUSINESS_PRICE',
    'PLAN_BASIC_MAX_FACILITIES',
    'PLAN_BASIC_MAX_EMPLOYEES',
    'PLAN_BUSINESS_MAX_FACILITIES',
    'PLAN_BUSINESS_MAX_EMPLOYEES',
    'ROUTES_PATH',
    'CONFIG_PATH',
    'ERROR_UNAUTHORIZED',
    'ERROR_FORBIDDEN',
    'ERROR_ACCESS_DENIED',
    'VALIDATION_REQUIRED',
    'VALIDATION_EMAIL',
    'VALIDATION_MIN',
    'VALIDATION_MAX',
    'VALIDATION_NUMERIC',
    'VALIDATION_MIN_NUMERIC',
    'VALIDATION_MAX_NUMERIC',
    'VALIDATION_DATE',
    'VALIDATION_IN',
    'VALIDATION_UNIQUE',
    'DB_FIELD_NAME_LENGTH',
    'DB_FIELD_ADDRESS_LENGTH',
    'DB_FIELD_CITY_LENGTH',
    'DB_FIELD_STATE_LENGTH',
    'DB_FIELD_ZIP_LENGTH',
    'DB_FIELD_COORDINATES_PRECISION',
    'DB_FIELD_COORDINATES_SCALE',
    'DB_FIELD_MONEY_PRECISION',
    'DB_FIELD_MONEY_SCALE',
    'DB_FIELD_HOURS_PRECISION',
    'DB_FIELD_HOURS_SCALE',
    'DB_FIELD_NOTIFICATION_TYPE_LENGTH',
    'DB_FIELD_NOTIFICATION_TITLE_LENGTH',
    'DB_FIELD_TEMPLATE_NAME_LENGTH',
    'DB_FIELD_TEMPLATE_SUBJECT_LENGTH',
    'DB_FIELD_LOG_LEVEL_LENGTH',
    'DB_FIELD_IP_ADDRESS_LENGTH',
    'DB_FIELD_USER_AGENT_LENGTH',
    'DB_FIELD_EMAIL_LENGTH',
    'DB_FIELD_PASSWORD_LENGTH',
    'DB_FIELD_FIRSTNAME_LENGTH',
    'DB_FIELD_LASTNAME_LENGTH',
    'DB_ENUM_FACILITY_STATUSES',
    'DB_ENUM_FACILITY_DEFAULT_STATUS',
    'DB_ENUM_USER_ROLES',
    'DB_ENUM_USER_STATUSES',
    'DB_ENUM_USER_DEFAULT_STATUS',
    'DB_ENUM_SHIFT_STATUSES',
    'DB_ENUM_SHIFT_DEFAULT_STATUS',
    'DB_ENUM_PAYMENT_STATUSES',
    'DB_ENUM_PAYMENT_DEFAULT_STATUS',
    'DB_ENUM_SUBSCRIPTION_STATUSES',
    'DB_ENUM_SUBSCRIPTION_DEFAULT_STATUS',
    'DB_ENUM_PLAN_DURATIONS',
    'DB_ENUM_PLAN_STATUSES',
    'DB_ENUM_PLAN_DEFAULT_STATUS',
    'DB_ENUM_PAYMENT_METHODS',
    'DB_ENUM_PAYMENT_METHOD_STATUSES',
    'DB_ENUM_PAYMENT_METHOD_DEFAULT_STATUS',
    'DB_ENUM_NOTIFICATION_STATUSES',
    'DB_ENUM_NOTIFICATION_DEFAULT_STATUS',
    'DB_ENUM_NOTIFICATION_CHANNELS',
    'DB_ENUM_TEMPLATE_STATUSES',
    'DB_ENUM_TEMPLATE_DEFAULT_STATUS',
    'DB_INDEX_PREFIX',
    'DB_FOREIGN_KEY_PREFIX',
    'DB_FOREIGN_KEY_ACTION_DELETE',
    'DB_FOREIGN_KEY_ACTION_RESTRICT',
    'DB_FOREIGN_KEY_ACTION_SET_NULL',
    'DB_FIELD_CURRENCY_LENGTH',
    'DB_FIELD_TRANSACTION_ID_LENGTH',
    'DB_TYPE_PRIMARY_KEY',
    'DB_TYPE_FOREIGN_KEY',
    'DB_TYPE_TIMESTAMP',
    'DB_DATE_FORMAT',
    'DB_DATETIME_FORMAT',
    'DB_TIME_FORMAT',
    'DB_DATETIME_DISPLAY_FORMAT',
    'DB_DATE_DISPLAY_FORMAT',
    'DB_TIME_DISPLAY_FORMAT',
    'DB_DATE_MYSQL_FORMAT',
    'DB_DATETIME_MYSQL_FORMAT',
    'DB_TIME_MYSQL_FORMAT'
];

Environment::required($required_env_vars);

// Установка обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Установка временной зоны
$timezone = Environment::get('TIMEZONE', 'Europe/Moscow');
$timezone = trim($timezone, '"\'');
date_default_timezone_set($timezone);

foreach ($required_env_vars as $var) {
    if (!isset($_ENV[$var])) {
        die("Error: Environment variable {$var} is not set in .env file");
    }
} 