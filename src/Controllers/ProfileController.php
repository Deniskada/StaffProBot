<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\models\User;

class ProfileController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->session->get('user')) {
            return $this->redirect('/login');
        }
        return $this->view('profile/index', [
            'user' => $this->session->get('user')
        ]);
    }
    
    public function show() {
        $this->view->render('profile/show', [
            'user' => $this->user
        ]);
    }
    
    public function edit() {
        $this->view->render('profile/edit', [
            'user' => $this->user
        ]);
    }
    
    public function update() {
        $data = $this->validate($this->request->post(), [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:255'
        ]);
        
        if ($data['email'] !== $this->user->email && User::findBy('email', $data['email'])) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Email уже используется'], 422);
            }
            $_SESSION['error'] = 'Email уже используется';
            return $this->back();
        }
        
        $this->user->fill($data)->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Профиль обновлен';
        $this->redirect('/profile');
    }
    
    public function password() {
        $data = $this->validate($this->request->post(), [
            'current_password' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);
        
        if (!password_verify($data['current_password'], $this->user->password)) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Неверный текущий пароль'], 422);
            }
            $_SESSION['error'] = 'Неверный текущий пароль';
            return $this->back();
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Пароли не совпадают'], 422);
            }
            $_SESSION['error'] = 'Пароли не совпадают';
            return $this->back();
        }
        
        $this->user->fill([
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Пароль изменен';
        $this->redirect('/profile');
    }
} 