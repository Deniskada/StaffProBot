<?php
namespace Spbot\Core;

class Router {
    private $routes = [];
    protected $currentRoute;
    protected $app;
    
    public function __construct($app) {
        $this->app = $app;
    }
    
    private function getUri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return '/' . trim($uri, '/');
    }
    
    public function add($method, $path, $handler, $middleware = []) {
        // Разбираем handler на controller и action
        list($controller, $action) = explode('@', $handler);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware,
            'pattern' => $this->buildPattern($path)
        ];
    }
    
    public function get($path, $handler) {
        $this->add('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->add('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->add('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->add('DELETE', $path, $handler);
    }
    
    public function handle() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        error_log("Router handling request: " . $method . " " . $uri);
        
        // Игнорируем запросы к favicon.ico
        if ($uri === '/favicon.ico') {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        
        // Проверяем, является ли запрос к статическому файлу
        if ($this->isStaticFile($uri)) {
            return $this->handleStaticFile($uri);
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $params)) {
                array_shift($params);
                $this->currentRoute = $route;
                return $this->executeHandler($route['handler'], $params);
            }
        }
        
        error_log("=== Route Not Found Debug ===");
        error_log("Requested path: " . $uri);
        error_log("Request method: " . $method);
        error_log("Available routes: " . print_r($this->routes, true));
        
        throw new \Exception('Route not found', 404);
    }
    
    private function buildPattern($path) {
        return '#^' . preg_replace_callback(
            '#{([^}]+)}#',
            function($match) {
                return '([^/]+)';
            },
            str_replace('/', '\/', $path)
        ) . '$#';
    }
    
    protected function executeHandler($handler, $params = []) {
        [$controller, $action] = explode('@', $handler);
        
        // Проверяем middleware если есть
        if (!empty($this->currentRoute['middleware'])) {
            foreach ($this->currentRoute['middleware'] as $middleware) {
                $this->executeMiddleware($middleware);
            }
        }
        
        $controllerClass = "Spbot\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controller} not found");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Action {$action} not found in controller {$controller}");
        }
        
        return call_user_func_array([$controllerInstance, $action], $params);
    }
    
    protected function executeMiddleware($middleware) {
        switch ($middleware) {
            case 'auth':
                if (!App::getInstance()->getSession()->isAuthenticated()) {
                    header('Location: /login');
                    exit();
                }
                break;
            
            case 'admin':
                $user = App::getInstance()->getSession()->getUser();
                if (empty($user) || $user['role'] !== 'admin') {
                    header('Location: /dashboard');
                    exit();
                }
                break;
        }
    }
    
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    public function url($path, $params = []) {
        $url = rtrim($_ENV['APP_URL'], '/') . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    private function isStaticFile($path) {
        $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, $staticExtensions);
    }
    
    private function handleStaticFile($path) {
        $publicPath = dirname(dirname(__DIR__)) . '/public' . $path;
        
        if (!file_exists($publicPath)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        header("Content-Type: {$contentType}");
        readfile($publicPath);
        exit;
    }
} 