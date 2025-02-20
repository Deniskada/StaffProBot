<?php
namespace Spbot\Core;

class App {
    private static $instance = null;
    private $router;
    private $request;
    private $session;
    private $config;
    private $db;
    
    private function __construct() {
        $this->loadConfig();
        $this->initErrorHandler();
        $this->initDatabase();
        
        $this->router = new Router($this);
        $this->request = new Request();
        $this->session = new Session();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        error_log('Loading config...');
        error_log('ENV variables in App: ' . print_r($_ENV, true));
        
        // Определяем пути по умолчанию
        if (!isset($_ENV['CONFIG_PATH'])) {
            $_ENV['CONFIG_PATH'] = 'config';
        }
        
        $rootPath = dirname(dirname(__DIR__));
        $configFile = $rootPath . DIRECTORY_SEPARATOR . $_ENV['CONFIG_PATH'] . DIRECTORY_SEPARATOR . 'config.php';
        
        if (!file_exists($configFile)) {
            throw new \Exception('Configuration file not found: ' . $configFile);
        }
        
        $this->config = require $configFile;
        
        if (!is_array($this->config)) {
            throw new \Exception('Invalid configuration format');
        }
    }
    
    private function initErrorHandler() {
        $debug = Environment::get('APP_DEBUG', false);
        $handler = new ErrorHandler($debug);
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? 1 : 0);
    }
    
    private function initDatabase() {
        $this->db = new Database(
            $_ENV['DB_HOST'],
            $_ENV['DB_DATABASE'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );
    }
    
    public function getDB() {
        if (!$this->db) {
            $this->initDatabase();
        }
        return $this->db;
    }
    
    public function run() {
        try {
            error_log("=== Route Loading Debug ===");
            
            // Логируем базовые пути
            $rootPath = dirname(dirname(__DIR__));
            error_log("Root path: " . $rootPath);
            
            // Логируем ROUTES_PATH из Environment
            $routesPathEnv = Environment::get('ROUTES_PATH');
            error_log("Environment ROUTES_PATH: " . $routesPathEnv);
            
            // Логируем полный путь к директории маршрутов
            $routesPath = $rootPath . DIRECTORY_SEPARATOR . $routesPathEnv;
            error_log("Full routes directory path: " . $routesPath);
            
            // Логируем путь к файлу web маршрутов
            $webRoutesFile = $routesPath . DIRECTORY_SEPARATOR . 'web.php';
            error_log("Web routes file path: " . $webRoutesFile);
            error_log("Web routes file exists: " . (file_exists($webRoutesFile) ? 'YES' : 'NO'));
            
            // Проверяем существование файла
            if (!file_exists($webRoutesFile)) {
                throw new \Exception('Routes file not found: ' . $webRoutesFile);
            }
            
            // Загружаем маршруты и логируем их
            error_log("Loading routes from file...");
            $routes = require $webRoutesFile;
            error_log("Loaded routes array: " . print_r($routes, true));
            
            // Добавляем регистрацию маршрутов
            error_log("Registering routes in router...");
            $this->registerRoutes($routes);
            
            // Логируем информацию о роутере перед вызовом handle
            error_log("Router class: " . get_class($this->router));
            error_log("Current URI: " . $_SERVER['REQUEST_URI']);
            error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
            
            // Обрабатываем запрос
            $this->router->handle();
        } catch (\Exception $e) {
            error_log("Exception in route handling: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if (Environment::get('APP_DEBUG', false)) {
                throw $e;
            }
            $this->handleError($e);
        }
    }
    
    private function registerRoutes($routes) {
        foreach ($routes as $path => $route) {
            // Проверяем, содержит ли путь метод
            if (strpos($path, ':') !== false) {
                list($method, $actualPath) = explode(':', $path);
                error_log("Registering {$method} route: {$actualPath} => " . $route['controller'] . '@' . $route['action']);
                $this->router->{strtolower($method)}($actualPath, $route['controller'] . '@' . $route['action']);
            } else {
                // По умолчанию регистрируем GET-маршрут
                error_log("Registering GET route: {$path} => " . $route['controller'] . '@' . $route['action']);
                $this->router->get($path, $route['controller'] . '@' . $route['action']);
            }
        }
        
        // Проверяем зарегистрированные маршруты
        error_log("Registered routes in router: " . print_r($this->router->getRoutes(), true));
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