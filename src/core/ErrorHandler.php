<?php
namespace Spbot\Core;

class ErrorHandler {
    private $rootPath;
    private $viewsPath;
    
    public function __construct() {
        error_log("=== ErrorHandler Initialization ===");
        $this->rootPath = dirname(dirname(__DIR__));
        $this->viewsPath = $this->rootPath . '/resources/views';
        
        error_log("Root path: " . $this->rootPath);
        error_log("Views path: " . $this->viewsPath);
        
        // Проверяем наличие шаблонов ошибок
        $errorTemplate = $this->viewsPath . '/errors/error.php';
        $exceptionTemplate = $this->viewsPath . '/errors/exception.php';
        
        error_log("Error template exists: " . (file_exists($errorTemplate) ? "YES" : "NO"));
        error_log("Exception template exists: " . (file_exists($exceptionTemplate) ? "YES" : "NO"));
        
        // Регистрируем обработчики
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    public function handleError($level, $message, $file = '', $line = 0) {
        if (!(error_reporting() & $level)) {
            return false;
        }
        
        $errorData = [
            'level' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ];
        
        error_log(json_encode($errorData));
        
        if (Environment::get('APP_DEBUG', false)) {
            $this->displayError($errorData);
        }
        
        return true;
    }
    
    public function handleException($e) {
        $exceptionData = [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        
        error_log(json_encode($exceptionData));
        
        if (Environment::get('APP_DEBUG', false)) {
            $this->displayException($e);
        } else {
            $this->displayError(['message' => 'Internal Server Error']);
        }
    }
    
    private function displayError($error) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        $errorTemplate = $this->viewsPath . '/errors/error.php';
        if (file_exists($errorTemplate)) {
            include $errorTemplate;
        } else {
            echo "<h1>Error</h1>";
            if (Environment::get('APP_DEBUG', false)) {
                echo "<pre>" . print_r($error, true) . "</pre>";
            } else {
                echo "<p>An error occurred. Please try again later.</p>";
            }
        }
    }
    
    private function displayException($e) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        $exceptionTemplate = $this->viewsPath . '/errors/exception.php';
        if (file_exists($exceptionTemplate)) {
            include $exceptionTemplate;
        } else {
            echo "<h1>Exception</h1>";
            if (Environment::get('APP_DEBUG', false)) {
                echo "<pre>" . print_r($e, true) . "</pre>";
            } else {
                echo "<p>An error occurred. Please try again later.</p>";
            }
        }
    }
    
    public function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
} 