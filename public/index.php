<?php
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php-error.log');
error_log("=== Starting Application ===");
error_log("Current working directory: " . getcwd());
error_log("Script path: " . __FILE__);
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);

// Проверяем путь к bootstrap.php
$bootstrapPath = dirname(__DIR__) . '/bootstrap.php';
error_log("Bootstrap path: " . $bootstrapPath);
error_log("Bootstrap exists: " . (file_exists($bootstrapPath) ? "YES" : "NO"));

require_once $bootstrapPath;

use Spbot\Core\App;
use Spbot\Core\Environment;

// Проверяем загруженные переменные окружения
error_log("=== Environment Check ===");
error_log("ENV variables in index.php: " . print_r($_ENV, true));
error_log("getenv('DB_HOST'): " . getenv('DB_HOST'));

try {
    $app = App::getInstance();
    $app->run();
} catch (Exception $e) {
    error_log("Fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('An error occurred. Please check the error log for details.');
} 