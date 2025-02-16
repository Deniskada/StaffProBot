<?php
namespace Spbot\Core;

class View {
    private $layout = 'default';
    private $data = [];
    
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    public function assign($key, $value) {
        $this->data[$key] = $value;
    }
    
    public function render($template, $data = []) {
        $this->data = array_merge($this->data, $data);
        
        // Извлекаем переменные для шаблона
        extract($this->data);
        
        // Начинаем буферизацию
        ob_start();
        
        $templatePath = dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/' . $template . '.php';
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }
        
        // Подключаем шаблон
        require $templatePath;
        
        // Получаем содержимое шаблона
        $content = ob_get_clean();
        
        // Подключаем layout, если он есть
        if ($this->layout) {
            $layoutPath = dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/layouts/' . $this->layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout not found: {$this->layout}");
            }
            
            require $layoutPath;
        } else {
            echo $content;
        }
    }
    
    public function renderPartial($template, $data = []) {
        extract(array_merge($this->data, $data));
        
        $templatePath = dirname(__DIR__) . '/' . $_ENV['VIEWS_PATH'] . '/partials/' . $template . '.php';
        if (!file_exists($templatePath)) {
            throw new \Exception("Partial template not found: {$template}");
        }
        
        require $templatePath;
    }
    
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    public function url($path = '') {
        return rtrim($_ENV['APP_URL'], '/') . '/' . ltrim($path, '/');
    }
    
    public function asset($path) {
        return $this->url($_ENV['ASSETS_PATH'] . '/' . ltrim($path, '/'));
    }
    
    public function csrf() {
        $token = Session::getInstance()->get('csrf_token');
        return '<input type="hidden" name="csrf_token" value="' . $this->escape($token) . '">';
    }
    
    public function old($key, $default = '') {
        return Session::getInstance()->flash('old.' . $key) ?? $default;
    }
    
    public function error($key) {
        $errors = Session::getInstance()->flash('errors') ?? [];
        return $errors[$key] ?? null;
    }
    
    public function hasError($key) {
        return $this->error($key) !== null;
    }
    
    public function renderError($code, $message) {
        http_response_code($code);
        $this->render('errors/error', [
            'code' => $code,
            'message' => $message
        ]);
    }
} 