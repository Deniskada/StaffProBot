<aside class="dashboard-sidebar">
    <div class="brand">
        <img src="/assets/img/logo.svg" alt="Logo" class="brand-logo">
        <span class="brand-name">StaffProBot</span>
    </div>
    
    <nav class="dashboard-nav">
        <ul>
            <li>
                <a href="/dashboard" <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Главная
                </a>
            </li>
            <li>
                <a href="/shifts" <?= $_SERVER['REQUEST_URI'] === '/shifts' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Смены
                </a>
            </li>
            <li>
                <a href="/profile" <?= $_SERVER['REQUEST_URI'] === '/profile' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Профиль
                </a>
            </li>
            <?php if ($user['role'] === 'admin'): ?>
                <li>
                    <a href="/users" <?= $_SERVER['REQUEST_URI'] === '/users' ? 'class="active"' : '' ?>>
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Пользователи
                    </a>
                </li>
                <li>
                    <a href="/facilities" <?= $_SERVER['REQUEST_URI'] === '/facilities' ? 'class="active"' : '' ?>>
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Объекты
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside> 