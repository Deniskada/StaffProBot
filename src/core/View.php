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
    
    public function render($name, $data = [], $isLayout = false) {
        error_log("=== View Render Debug ===");
        error_log("Original view name: " . $name);
        
        // Не модифицируем имя view
        $viewPath = $this->getViewPath($name);
        
        error_log("Final view path: " . $viewPath);
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }
        
        // Ограничиваем глубину рекурсии для layout
        if (!$isLayout && !empty($data['layout'])) {
            $content = $this->renderFile($viewPath, $data);
            $layoutData = array_merge($data, ['content' => $content]);
            return $this->render($data['layout'], $layoutData, true);
        }
        
        return $this->renderFile($viewPath, $data);
    }
    
    protected function renderFile($path, $data) {
        // Очищаем буфер перед рендерингом
        if (ob_get_level()) ob_end_clean();
        
        ob_start();
        try {
            extract($data);
            include $path;
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            error_log("View render error: " . $e->getMessage());
            throw $e;
        }
    }
    
    protected function getViewPath($name) {
        error_log("=== getViewPath Debug ===");
        error_log("Looking for view: " . $name);
        
        // Полный путь к файлу представления
        $viewPath = APP_ROOT . '/resources/views/' . $name . '.php';
        
        error_log("Checking path: " . $viewPath);
        error_log("Found at: " . ($viewPath));
        
        return $viewPath;
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