<?php
namespace Spbot\Core;

class Router {
    private $routes = [];
    private $currentRoute;
    
    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
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
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $path, $matches)) {
                $this->currentRoute = $route;
                array_shift($matches); // Удаляем полное совпадение
                
                return $this->executeHandler($route['handler'], $matches);
            }
        }
        
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
    
    private function executeHandler($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                list($controller, $action) = explode('@', $handler);
                $controller = 'Spbot\\Controllers\\' . $controller;
                
                if (!class_exists($controller)) {
                    throw new \Exception("Controller not found: {$controller}");
                }
                
                $instance = new $controller();
                
                if (!method_exists($instance, $action)) {
                    throw new \Exception("Action not found: {$action}");
                }
                
                return call_user_func_array([$instance, $action], $params);
            }
        }
        
        throw new \Exception('Invalid route handler');
    }
    
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    public function url($path, $params = []) {
        $url = rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
} 