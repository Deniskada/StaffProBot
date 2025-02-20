<?php
return [
    // GET routes
    '/' => [
        'controller' => 'HomeController',
        'action' => 'index',
        'middleware' => []
    ],
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/register' => ['controller' => 'AuthController', 'action' => 'register'],
    '/dashboard' => [
        'controller' => 'DashboardController',
        'action' => 'index',
        'middleware' => ['auth']
    ],
    '/profile' => ['controller' => 'ProfileController', 'action' => 'index'],
    '/shifts' => ['controller' => 'ShiftController', 'action' => 'index'],
    '/notifications' => ['controller' => 'NotificationController', 'action' => 'index'],
    
    // POST routes
    'POST:/login' => ['controller' => 'AuthController', 'action' => 'login'],
    'POST:/register' => ['controller' => 'AuthController', 'action' => 'register'],
    
    // Админ маршруты
    '/admin/dashboard' => [
        'controller' => 'Admin\DashboardController',
        'action' => 'index',
        'middleware' => ['auth', 'admin']
    ],
    '/admin/users' => ['controller' => 'Admin\UsersController', 'action' => 'index'],
    '/admin/updates' => ['controller' => 'Admin\UpdatesController', 'action' => 'index'],
]; 