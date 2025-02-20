<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Core\Environment;

class BaseController extends Controller {
    protected $request;
    protected $session;
    
    public function __construct() {
        parent::__construct();
        $this->request = \Spbot\Core\App::getInstance()->getRequest();
        $this->session = \Spbot\Core\App::getInstance()->getSession();
        
        // Генерируем CSRF-токен для всех запросов
        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', bin2hex(random_bytes(32)));
        }
    }
    
    protected function view($name, $data = []) {
        // Минимизируем данные для view
        $baseData = [
            'app_url' => Environment::get('APP_URL'),
            'user' => $this->session->getUser(),
            'csrf_token' => $this->session->get('csrf_token')
        ];
        
        return $this->view->render($name, array_merge($baseData, $data));
    }
    
    protected function getViewPath($name) {
        // Не добавляем index.php к путям с явным указанием файла
        if (strpos($name, '/') !== false) {
            return APP_ROOT . '/resources/views/' . $name . '.php';
        }
        
        return APP_ROOT . '/resources/views/' . $name . '/index.php';
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    protected function json($data, $status = 200) {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json');
        }
        echo json_encode($data);
        return true;
    }
    
    protected function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Verifying CSRF token");
            error_log("Session token: " . ($this->session->get('csrf_token') ?? 'not set'));
            error_log("POST token: " . ($_POST['csrf_token'] ?? 'not set'));
            
            if (!$this->session->has('csrf_token') || 
                !isset($_POST['csrf_token']) || 
                $this->session->get('csrf_token') !== $_POST['csrf_token']) {
                error_log("CSRF token verification failed");
                throw new \Exception('Invalid CSRF token');
            }
            error_log("CSRF token verified successfully");
        }
    }
    
    protected function isAuthenticated() {
        $user = $this->session->getUser();
        return !empty($user) && !empty($user['id']);
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            if ($this->request->isAjax()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }
            $_SESSION['error'] = 'Пожалуйста, войдите в систему';
            return $this->redirect('/login');
        }
    }
    
    protected function requireGuest() {
        if ($this->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }
    }
} 