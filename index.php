<?php
// Определяем корневую директорию
define('ROOT_DIR', __DIR__);

// Подключаем автозагрузчик
require ROOT_DIR . '/core/Autoloader.php';
Spbot\Core\Autoloader::register();

// Запускаем приложение
$app = Spbot\Core\App::getInstance();
$app->run(); 