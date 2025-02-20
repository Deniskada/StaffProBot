<?php
/**
 * @var array $user Данные пользователя
 * @var string $title Заголовок страницы
 */
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title ?? 'Панель управления') ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <aside class="dashboard-sidebar">
            <div class="brand">
                <img src="/assets/img/logo.svg" alt="Logo" class="brand-logo">
                <span class="brand-name">StaffProBot</span>
            </div>
            
            <nav class="dashboard-nav">
                <ul>
                    <li>
                        <a href="/dashboard" class="active">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Главная
                        </a>
                    </li>
                    <li>
                        <a href="/shifts">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Смены
                        </a>
                    </li>
                    <li>
                        <a href="/profile">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Профиль
                        </a>
                    </li>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li>
                            <a href="/users">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Пользователи
                            </a>
                        </li>
                        <li>
                            <a href="/settings">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Настройки
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <header class="dashboard-header">
            <h1>Панель управления</h1>
            <div class="user-info">
                <span class="welcome">Добро пожаловать, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                <span class="role">(<?= htmlspecialchars($user['role']) ?>)</span>
                <a href="/logout" class="btn btn-logout">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Выйти
                </a>
            </div>
        </header>

        <main class="dashboard-content">
            <div class="dashboard-widgets">
                <div class="widget">
                    <h3>Текущая смена</h3>
                    <div class="widget-content">
                        <p>Нет активной смены</p>
                        <a href="/shifts/start" class="btn btn-primary">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Начать смену
                        </a>
                    </div>
                </div>

                <div class="widget">
                    <h3>Статистика</h3>
                    <div class="widget-content">
                        <ul>
                            <li>Отработано часов: 0</li>
                            <li>Смен за месяц: 0</li>
                            <li>Следующая смена: Не назначена</li>
                        </ul>
                    </div>
                </div>

                <div class="widget">
                    <h3>Уведомления</h3>
                    <div class="widget-content">
                        <p>Нет новых уведомлений</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/dashboard.js"></script>
</body>
</html> 