<header class="dashboard-header">
    <h1><?= htmlspecialchars($title ?? 'Панель управления') ?></h1>
    <div class="user-info">
        <?php if (isset($user) && !empty($user)): ?>
            <span class="welcome">Добро пожаловать, <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></span>
            <span class="role">(<?= htmlspecialchars($user['role'] ?? 'пользователь') ?>)</span>
        <?php endif; ?>
        <a href="/logout" class="btn btn-logout">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Выйти
        </a>
    </div>
</header> 