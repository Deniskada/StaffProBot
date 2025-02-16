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
        $configFile = dirname(__DIR__) . '/' . $_ENV['CONFIG_PATH'] . '/config.php';
        if (!file_exists($configFile)) {
            throw new \Exception('Configuration file not found');
        }
        require $configFile;
    }
    
    private function initErrorHandler() {
        $handler = new ErrorHandler($_ENV['APP_DEBUG']);
        error_reporting(E_ALL);
        ini_set('display_errors', $_ENV['APP_DEBUG'] ? 1 : 0);
    }
    
    public function run() {
        try {
            // Загружаем маршруты из конфигурируемых путей
            $routesPath = dirname(__DIR__) . '/' . $_ENV['ROUTES_PATH'] . '/';
            require $routesPath . 'web.php';
            require $routesPath . 'api.php';
            
            $this->router->handle();
        } catch (\Exception $e) {
            if ($_ENV['APP_DEBUG']) {
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
            include dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/errors/500.php';
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