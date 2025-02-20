<?php
return [
    'api/auth/login' => ['controller' => 'Api\AuthController', 'action' => 'login'],
    'api/auth/register' => ['controller' => 'Api\AuthController', 'action' => 'register'],
    'api/notifications/unread-count' => ['controller' => 'Api\NotificationController', 'action' => 'unreadCount'],
    'api/notifications/mark-read' => ['controller' => 'Api\NotificationController', 'action' => 'markRead'],
    'api/notifications/mark-all-read' => ['controller' => 'Api\NotificationController', 'action' => 'markAllRead'],
    'api/shifts' => ['controller' => 'Api\ShiftController', 'action' => 'index'],
    'api/shifts/create' => ['controller' => 'Api\ShiftController', 'action' => 'create'],
    'api/profile' => ['controller' => 'Api\ProfileController', 'action' => 'update'],
    'api/profile/password' => ['controller' => 'Api\ProfileController', 'action' => 'updatePassword'],
    'api/admin/stats' => ['controller' => 'Api\Admin\StatsController', 'action' => 'index'],
]; 