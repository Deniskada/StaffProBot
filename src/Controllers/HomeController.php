<?php
namespace Spbot\Controllers;

class HomeController extends BaseController {
    public function index() {
        // Если пользователь авторизован - редирект на dashboard
        if ($this->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }
        
        return $this->view('home/index', [
            'title' => 'Главная страница'
        ]);
    }
} 