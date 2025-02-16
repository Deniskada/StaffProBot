<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\models\User;

class UserController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireRole('admin');
    }
    
    public function index() {
        $role = $this->request->get('role');
        $status = $this->request->get('status');
        
        $conditions = [];
        $params = [];
        
        if ($role) {
            $conditions[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        $users = empty($conditions) 
            ? User::all() 
            : User::where(implode(' AND ', $conditions), $params);
        
        $this->view->render('users/index', [
            'users' => $users,
            'filters' => [
                'role' => $role,
                'status' => $status
            ]
        ]);
    }
    
    public function create() {
        $this->view->render('users/create');
    }
    
    public function store() {
        $data = $this->validate($this->request->post(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'role' => 'required',
            'status' => 'required'
        ]);
        
        // Проверяем уникальность email
        if (User::findBy('email', $data['email'])) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Email уже используется'], 422);
            }
            $_SESSION['error'] = 'Email уже используется';
            return $this->back();
        }
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $user = new User();
        $user->fill($data)->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['id' => $user->id]);
        }
        
        $_SESSION['success'] = 'Пользователь успешно создан';
        $this->redirect('/users');
    }
    
    public function edit($id) {
        $user = User::find($id);
        
        if (!$user) {
            return $this->view->renderError(404, 'Пользователь не найден');
        }
        
        $this->view->render('users/edit', [
            'user' => $user
        ]);
    }
    
    public function update($id) {
        $user = User::find($id);
        
        if (!$user) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Пользователь не найден'], 404);
            }
            return $this->view->renderError(404, 'Пользователь не найден');
        }
        
        $rules = [
            'email' => 'required|email|max:255',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'role' => 'required',
            'status' => 'required'
        ];
        
        // Пароль необязателен при обновлении
        if ($this->request->post('password')) {
            $rules['password'] = 'min:8';
        }
        
        $data = $this->validate($this->request->post(), $rules);
        
        // Проверяем уникальность email
        if ($data['email'] !== $user->email && User::findBy('email', $data['email'])) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Email уже используется'], 422);
            }
            $_SESSION['error'] = 'Email уже используется';
            return $this->back();
        }
        
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $user->fill($data)->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Пользователь успешно обновлен';
        $this->redirect('/users');
    }
    
    public function delete($id) {
        $user = User::find($id);
        
        if (!$user) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Пользователь не найден'], 404);
            }
            return $this->view->renderError(404, 'Пользователь не найден');
        }
        
        // Запрещаем удалять самого себя
        if ($user->id === $this->user->id) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Нельзя удалить свой аккаунт'], 403);
            }
            $_SESSION['error'] = 'Нельзя удалить свой аккаунт';
            return $this->back();
        }
        
        $user->delete();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Пользователь успешно удален';
        $this->redirect('/users');
    }
} 