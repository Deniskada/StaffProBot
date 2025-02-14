<?php
// Определяем корневую директорию приложения
define('APP_ROOT', dirname(__FILE__));

// Автозагрузка через composer
require_once APP_ROOT . '/vendor/autoload.php';

// Загрузка переменных окружения из .env
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Установка обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Установка временной зоны
date_default_timezone_set('UTC'); 