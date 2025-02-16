<?php
namespace Spbot\Core;

class Autoloader {
    private static $registered = false;
    
    public static function register() {
        if (self::$registered) {
            return;
        }
        
        spl_autoload_register([__CLASS__, 'loadClass']);
        self::$registered = true;
    }
    
    public static function loadClass($class) {
        // Преобразуем namespace в путь к файлу
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        
        // Определяем базовую директорию проекта
        $baseDir = dirname(__DIR__);
        
        // Убираем namespace проекта из пути
        $file = str_replace('Spbot\\', '', $file);
        
        // Полный путь к файлу
        $file = $baseDir . DIRECTORY_SEPARATOR . $file . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
        
        return false;
    }
} 