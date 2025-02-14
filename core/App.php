<?php
namespace Spbot\Core;

class App {
    private static $instance = null;
    private $router;
    private $request;
    private $session;
    private $config;
    
    private function __construct() {
        $this->loadConfig();
        $this->initErrorHandler();
        
        $this->router = new Router();
        $this->request = new Request();
        $this->session = Session::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        $configFile = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configFile)) {
            throw new \Exception('Configuration file not found');
        }
        require $configFile;
        
        // Определяем основные константы
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__) . '/views');
        }
    }
    
    private function initErrorHandler() {
        $handler = new ErrorHandler(APP_DEBUG);
        error_reporting(E_ALL);
        ini_set('display_errors', APP_DEBUG ? 1 : 0);
    }
    
    public function run() {
        try {
            // Загружаем маршруты
            require dirname(__DIR__) . '/routes/web.php';
            require dirname(__DIR__) . '/routes/api.php';
            
            // Обрабатываем запрос
            $this->router->handle();
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                throw $e;
            }
            $this->handleError($e);
        }
    }
    
    private function handleError($e) {
        if ($this->request->isAjax() || strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error']);
        } else {
            http_response_code(500);
            include VIEWS_PATH . '/errors/500.php';
        }
    }
    
    public function getRouter() {
        return $this->router;
    }
    
    public function getRequest() {
        return $this->request;
    }
    
    public function getSession() {
        return $this->session;
    }
} 