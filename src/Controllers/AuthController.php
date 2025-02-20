<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Core\JWT;
use Spbot\Models\User;
use Spbot\Models\SystemLog;

class AuthController extends BaseController {
    protected $user;
    
    public function __construct() {
        parent::__construct();
        $this->user = new User();
        
        // Получаем текущий маршрут из Router
        $currentRoute = \Spbot\Core\App::getInstance()->getRouter()->getCurrentRoute();
        
        if ($currentRoute) {
            $action = $currentRoute['action'] ?? '';
            
            // Проверяем что пользователь не авторизован для login и register
            if (in_array($action, ['login', 'register'])) {
                $this->requireGuest();
            }
            
            // Проверяем что пользователь авторизован для logout
            if ($action === 'logout') {
                $this->requireAuth();
            }
        }
    }
    
    public function login() {
        error_log("=== Login Method Debug ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Session data at start: " . print_r($_SESSION, true));
        
        // Проверяем авторизацию через метод isAuthenticated
        $isAuth = $this->isAuthenticated();
        error_log("isAuthenticated result: " . ($isAuth ? 'true' : 'false'));
        
        if ($isAuth) {
            error_log("User already logged in, redirecting to dashboard");
            return $this->redirect('/dashboard');
        }
        
        if ($this->request->isPost()) {
            try {
                if (!$this->request->isAjax()) {
                    $this->verifyCsrfToken();
                }
                
                $data = $this->request->post();
                $validatedData = $this->validate($data, [
                    'email' => 'required|email',
                    'password' => 'required|min:6'
                ]);
                
                $user = User::findByEmail($validatedData['email']);
                
                if (!$user) {
                    throw new \Exception('Неверный email или пароль');
                }
                
                if (!$user->hasPassword()) {
                    error_log("User found but has no password set");
                    throw new \Exception('Учетная запись не настроена. Пожалуйста, свяжитесь с администратором.');
                }
                
                if (!$user->verifyPassword($data['password'])) {
                    throw new \Exception('Неверный email или пароль');
                }
                
                if (!$user->isActive()) {
                    throw new \Exception('Аккаунт не активирован');
                }
                
                // Сохраняем пользователя в сессии
                $userData = $user->toArray();
                error_log("=== Saving user to session ===");
                error_log("User data to save: " . print_r($userData, true));
                $this->session->setUser($userData);
                
                // Перенаправляем в зависимости от роли
                if ($user->role === 'admin') {
                    return $this->redirect('/admin/dashboard');
                }
                // Здесь можно добавить другие роли когда они появятся
                return $this->redirect('/dashboard');
                
            } catch (\Exception $e) {
                error_log("Login error: " . $e->getMessage());
                
                if ($this->request->isAjax()) {
                    return $this->json([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], 401);
                }
                
                $_SESSION['error'] = $e->getMessage();
                $_SESSION['old'] = $data;
                return $this->redirect('/login');
            }
        }
        
        // Очищаем старые ошибки при GET запросе
        unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['error'], $_SESSION['success']);
        
        return $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }
    
    public function register() {
        return $this->view('auth/register');
    }
    
    public function logout() {
        // Очищаем remember token
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // Логируем выход
        if (isset($_SESSION['user'])) {
            SystemLog::create([
                'user_id' => $_SESSION['user']['id'],
                'action' => 'logout',
                'details' => 'User logged out'
            ]);
        }
        
        $this->session->destroy();
        return $this->redirect('/');
    }
} 