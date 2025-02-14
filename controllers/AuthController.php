<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Core\JWT;
use Spbot\Models\User;
use Spbot\Models\SystemLog;

class AuthController extends Controller {
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->user = new User();
    }
    
    public function login() {
        if ($this->request->isPost()) {
            $this->validateRequest([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            $data = $this->request->getJson();
            $user = User::findByEmail($data['email']);
            
            if (!$user || !$user->verifyPassword($data['password'])) {
                SystemLog::log('auth', 'Failed login attempt', [
                    'email' => $data['email'],
                    'ip' => $this->request->getClientIp()
                ]);
                
                return $this->jsonError('Invalid credentials', 401);
            }
            
            if (!$user->isActive()) {
                return $this->jsonError('Account is not active', 403);
            }
            
            // Создаем JWT токен
            $token = JWT::encode([
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            
            // Обновляем время последнего входа
            $user->updateLastLogin();
            
            SystemLog::log('auth', 'Successful login', [
                'user_id' => $user->id,
                'ip' => $this->request->getClientIp()
            ]);
            
            return $this->jsonSuccess([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->getFullName(),
                    'role' => $user->role
                ]
            ]);
        }
        
        // Отображаем форму входа
        $this->view->render('auth/login');
    }
    
    public function register() {
        if ($this->request->isPost()) {
            $this->validateRequest([
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'first_name' => 'required',
                'last_name' => 'required',
                'role' => 'required|in:employer,employee'
            ]);
            
            $data = $this->request->getJson();
            
            $user = new User();
            $user->fill([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => $data['role'],
                'status' => 'active'
            ]);
            $user->setPassword($data['password']);
            
            if (!$user->save()) {
                return $this->jsonError('Failed to create account');
            }
            
            SystemLog::log('auth', 'New user registration', [
                'user_id' => $user->id,
                'ip' => $this->request->getClientIp()
            ]);
            
            return $this->jsonSuccess([
                'message' => 'Account created successfully'
            ]);
        }
        
        // Отображаем форму регистрации
        $this->view->render('auth/register');
    }
    
    public function logout() {
        if ($this->user) {
            SystemLog::log('auth', 'User logout', [
                'user_id' => $this->user->id,
                'ip' => $this->request->getClientIp()
            ]);
        }
        
        $this->session->logout();
        
        if ($this->request->isAjax()) {
            return $this->jsonSuccess(['message' => 'Logged out successfully']);
        }
        
        return $this->redirect('/login');
    }
} 