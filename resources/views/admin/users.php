<div class="row">
    <!-- Список пользователей -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Пользователи системы</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="roleFilter" style="width: 150px;">
                            <option value="">Все роли</option>
                            <option value="employer">Работодатели</option>
                            <option value="employee">Сотрудники</option>
                            <option value="admin">Администраторы</option>
                        </select>
                        <input type="text" class="form-control" placeholder="Поиск..." id="searchInput">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Статус</th>
                                <th>Последний вход</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Будет заполнено через JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра пользователя -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Информация о пользователе</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="userTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#info">
                            Основная информация
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#activity">
                            Активность
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#security">
                            Безопасность
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="info">
                        <form id="userForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Имя пользователя</label>
                                    <input type="text" class="form-control" id="username">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Роль</label>
                                    <select class="form-select" id="role">
                                        <option value="employer">Работодатель</option>
                                        <option value="employee">Сотрудник</option>
                                        <option value="admin">Администратор</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" id="status">
                                        <option value="active">Активен</option>
                                        <option value="blocked">Заблокирован</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="activity">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Действие</th>
                                        <th>IP адрес</th>
                                    </tr>
                                </thead>
                                <tbody id="activityTable">
                                    <!-- Будет заполнено через JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="security">
                        <div class="mb-4">
                            <h6>Сброс пароля</h6>
                            <p class="text-muted">Отправить пользователю ссылку для сброса пароля</p>
                            <button class="btn btn-warning" onclick="sendPasswordReset()">
                                Сбросить пароль
                            </button>
                        </div>
                        <div class="mb-4">
                            <h6>Двухфакторная аутентификация</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="require2fa">
                                <label class="form-check-label">
                                    Требовать 2FA при входе
                                </label>
                            </div>
                        </div>
                        <div>
                            <h6>Активные сессии</h6>
                            <div id="activeSessions">
                                <!-- Будет заполнено через JavaScript -->
                            </div>
                            <button class="btn btn-danger mt-2" onclick="terminateAllSessions()">
                                Завершить все сессии
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let users = [];
let currentPage = 1;
const itemsPerPage = 10;

async function loadUsers() {
    const role = document.getElementById('roleFilter').value;
    const search = document.getElementById('searchInput').value;
    
    try {
        const response = await fetch(`/api/admin/users?role=${role}&search=${search}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        users = await response.json();
        renderUsers();
    } catch (error) {
        console.error('Error loading users:', error);
        alert('Ошибка при загрузке пользователей');
    }
}

function renderUsers() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageUsers = users.slice(startIndex, endIndex);
    
    const tbody = document.getElementById('usersTable');
    tbody.innerHTML = pageUsers.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td>
                <span class="badge bg-${getRoleBadgeColor(user.role)}">
                    ${getRoleDisplayName(user.role)}
                </span>
            </td>
            <td>
                <span class="badge bg-${user.is_blocked ? 'danger' : 'success'}">
                    ${user.is_blocked ? 'Заблокирован' : 'Активен'}
                </span>
            </td>
            <td>${formatDate(user.last_login)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="showUserDetails(${user.id})">
                        Детали
                    </button>
                    <button class="btn btn-outline-${user.is_blocked ? 'success' : 'danger'}" 
                            onclick="toggleUserBlock(${user.id})">
                        ${user.is_blocked ? 'Разблокировать' : 'Заблокировать'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

function getRoleBadgeColor(role) {
    switch (role) {
        case 'admin': return 'danger';
        case 'employer': return 'primary';
        case 'employee': return 'success';
        default: return 'secondary';
    }
}

function getRoleDisplayName(role) {
    switch (role) {
        case 'admin': return 'Администратор';
        case 'employer': return 'Работодатель';
        case 'employee': return 'Сотрудник';
        default: return role;
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleString();
}

async function showUserDetails(userId) {
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const user = await response.json();
        
        // Заполняем форму
        document.getElementById('username').value = user.username;
        document.getElementById('email').value = user.email;
        document.getElementById('role').value = user.role;
        document.getElementById('status').value = user.is_blocked ? 'blocked' : 'active';
        
        // Загружаем активность
        loadUserActivity(userId);
        
        // Показываем модальное окно
        new bootstrap.Modal(document.getElementById('userModal')).show();
    } catch (error) {
        console.error('Error loading user details:', error);
        alert('Ошибка при загрузке информации о пользователе');
    }
}

async function loadUserActivity(userId) {
    try {
        const response = await fetch(`/api/admin/users/${userId}/activity`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const activity = await response.json();
        
        document.getElementById('activityTable').innerHTML = activity.map(item => `
            <tr>
                <td>${formatDate(item.timestamp)}</td>
                <td>${item.action}</td>
                <td>${item.ip_address}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading user activity:', error);
    }
}

// Инициализация страницы
loadUsers();

// Обработчики фильтров
document.getElementById('roleFilter').addEventListener('change', () => {
    currentPage = 1;
    loadUsers();
});

document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    loadUsers();
});
</script> 