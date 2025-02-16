<?php
namespace Spbot\Core;

class ErrorHandler {
    private $logger;
    private $debug;
    
    public function __construct($debug = false) {
        $this->logger = new Logger();
        $this->debug = $debug;
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function handleError($level, $message, $file, $line) {
        if (error_reporting() & $level) {
            $this->logError($level, $message, $file, $line);
            
            if ($this->debug) {
                $this->displayError($level, $message, $file, $line);
            } else {
                $this->displayProductionError();
            }
            
            return true;
        }
        
        return false;
    }
    
    public function handleException($exception) {
        $this->logException($exception);
        
        if ($this->debug) {
            $this->displayException($exception);
        } else {
            $this->displayProductionError();
        }
    }
    
    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    private function logError($level, $message, $file, $line) {
        $this->logger->error($message, [
            'level' => $level,
            'file' => $file,
            'line' => $line
        ]);
    }
    
    private function logException($exception) {
        $this->logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    private function displayError($level, $message, $file, $line) {
        http_response_code(500);
        
        if ($this->isApiRequest()) {
            echo json_encode([
                'error' => [
                    'message' => $message,
                    'file' => $file,
                    'line' => $line
                ]
            ]);
        } else {
            include dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/errors/error.php';
        }
    }
    
    private function displayException($exception) {
        http_response_code(500);
        
        if ($this->isApiRequest()) {
            echo json_encode([
                'error' => [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ]
            ]);
        } else {
            include dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/errors/exception.php';
        }
    }
    
    private function displayProductionError() {
        http_response_code(500);
        
        if ($this->isApiRequest()) {
            echo json_encode([
                'error' => 'Внутренняя ошибка сервера'
            ]);
        } else {
            include dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/errors/500.php';
        }
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
    }
} 