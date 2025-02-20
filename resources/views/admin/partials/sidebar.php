<aside class="dashboard-sidebar">
    <div class="brand">
        <img src="/assets/img/logo.svg" alt="Logo" class="brand-logo">
        <span class="brand-name">StaffProBot Admin</span>
    </div>
    
    <nav class="dashboard-nav">
        <ul>
            <li>
                <a href="/admin/dashboard" <?= $_SERVER['REQUEST_URI'] === '/admin/dashboard' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Панель управления
                </a>
            </li>
            <li>
                <a href="/admin/users" <?= $_SERVER['REQUEST_URI'] === '/admin/users' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Пользователи
                </a>
            </li>
            <li>
                <a href="/admin/updates" <?= $_SERVER['REQUEST_URI'] === '/admin/updates' ? 'class="active"' : '' ?>>
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Обновления
                </a>
            </li>
        </ul>
    </nav>
</aside> 