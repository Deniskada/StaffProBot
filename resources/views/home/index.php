<?php
/**
 * @var array $user Данные пользователя
 * @var string $title Заголовок страницы
 */
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($title ?? 'Главная') ?></h1>
        
        <?php if (isset($user) && !empty($user)): ?>
            <p>Добро пожаловать, <?= htmlspecialchars($user['first_name'] ?? 'Гость') ?>!</p>
        <?php else: ?>
            <p>Добро пожаловать в нашу систему!</p>
        <?php endif; ?>
        
        <div class="features">
            <h2>Возможности системы:</h2>
            <ul>
                <li>Управление сменами</li>
                <li>Контроль рабочего времени</li>
                <li>Статистика и отчеты</li>
                <li>Уведомления</li>
            </ul>
        </div>
        
        <?php if (!isset($user) || empty($user)): ?>
            <div class="cta-buttons">
                <a href="/login" class="btn btn-primary">Войти</a>
                <a href="/register" class="btn btn-secondary">Зарегистрироваться</a>
            </div>
        <?php else: ?>
            <div class="dashboard-link">
                <a href="/dashboard" class="btn btn-primary">Перейти в панель управления</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 