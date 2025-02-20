<?php
/**
 * @var array $user Данные пользователя
 * @var string $title Заголовок страницы
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title ?? 'Профиль') ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <main class="dashboard-content">
            <div class="profile-container">
                <div class="profile-header">
                    <h2>Личные данные</h2>
                    <button class="btn btn-primary" onclick="enableEdit()">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Редактировать
                    </button>
                </div>

                <form id="profile-form" class="profile-form" method="POST" action="/profile/update">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Имя</label>
                            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Фамилия</label>
                            <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Роль</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Статус</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['status']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Telegram ID</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['telegram_id'] ?? '-') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Последний вход</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : '-') ?>" readonly>
                    </div>

                    <div class="form-actions" style="display: none;">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Отмена</button>
                    </div>
                </form>

                <div class="profile-section">
                    <h3>Смена пароля</h3>
                    <form class="password-form" method="POST" action="/profile/password">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Изменить пароль</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/profile.js"></script>
</body>
</html> 