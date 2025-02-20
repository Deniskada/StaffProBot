<?php
namespace Spbot\Core;

class Environment {
    private static $variables = [];
    
    public static function load() {
        error_log("=== Environment Debug ===");
        error_log("Current working directory: " . getcwd());
        error_log("Environment file path: " . dirname(dirname(__DIR__)) . '/.env');
        
        if (file_exists(dirname(dirname(__DIR__)) . '/.env')) {
            $lines = file(dirname(dirname(__DIR__)) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
                putenv(sprintf('%s=%s', trim($name), trim($value)));
            }
            
            error_log("Loaded environment variables: " . print_r($_ENV, true));
        } else {
            error_log("Environment file not found!");
        }
    }
    
    public static function get($key, $default = null) {
        error_log("=== Environment::get Debug ===");
        error_log("Getting key: " . $key);
        error_log("Current value: " . ($_ENV[$key] ?? 'not set'));
        error_log("Default value: " . ($default ?? 'null'));
        
        return $_ENV[$key] ?? $default;
    }
    
    public static function required($vars) {
        error_log("=== Checking Required Environment Variables ===");
        $missing = [];
        
        foreach ($vars as $var) {
            $value = self::get($var);
            error_log("Required var {$var}: " . (empty($value) ? "MISSING" : "SET"));
            
            if (empty($value)) {
                $missing[] = $var;
            }
        }
        
        if (!empty($missing)) {
            $message = "Missing required environment variables: " . implode(', ', $missing);
            error_log("ERROR: " . $message);
            throw new \RuntimeException($message);
        }
        
        error_log("All required variables are present");
        return true;
    }
} 