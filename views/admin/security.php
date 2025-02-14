<div class="row">
    <!-- Настройки безопасности -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Настройки безопасности</h5>
                
                <form id="securitySettingsForm">
                    <!-- Пароли -->
                    <div class="mb-4">
                        <h6>Политика паролей</h6>
                        <div class="mb-3">
                            <label class="form-label">Минимальная длина пароля</label>
                            <input type="number" class="form-control" id="minPasswordLength">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="requireUppercase">
                            <label class="form-check-label">Требовать заглавные буквы</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="requireNumbers">
                            <label class="form-check-label">Требовать цифры</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="requireSpecial">
                            <label class="form-check-label">Требовать спецсимволы</label>
                        </div>
                    </div>

                    <!-- Сессии -->
                    <div class="mb-4">
                        <h6>Управление сессиями</h6>
                        <div class="mb-3">
                            <label class="form-label">Время жизни сессии (минуты)</label>
                            <input type="number" class="form-control" id="sessionLifetime">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="singleSession">
                            <label class="form-check-label">Запретить множественные сессии</label>
                        </div>
                    </div>

                    <!-- Двухфакторная аутентификация -->
                    <div class="mb-4">
                        <h6>Двухфакторная аутентификация</h6>
                        <div class="mb-3">
                            <select class="form-select" id="2faPolicy">
                                <option value="disabled">Отключена</option>
                                <option value="optional">Опциональная</option>
                                <option value="required">Обязательная</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="2faAdmin">
                            <label class="form-check-label">Обязательна для администраторов</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Журнал безопасности -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Журнал безопасности</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="securityLogType" style="width: 150px;">
                            <option value="all">Все события</option>
                            <option value="auth">Авторизация</option>
                            <option value="changes">Изменения</option>
                            <option value="alerts">Предупреждения</option>
                        </select>
                        <button class="btn btn-outline-primary" onclick="exportSecurityLog()">
                            Экспорт
                        </button>
                    </div>
                </div>

                <div class="security-log" style="height: 400px; overflow-y: auto;">
                    <div id="securityLogContent">
                        <!-- Будет заполнено через JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Активные сессии -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Активные сессии</h5>
                    <button class="btn btn-danger" onclick="terminateAllSessions()">
                        Завершить все сессии
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Пользователь</th>
                                <th>IP адрес</th>
                                <th>Устройство</th>
                                <th>Начало сессии</th>
                                <th>Последняя активность</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="sessionsTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Блокировки -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Блокировки и ограничения</h5>

                <div class="row">
                    <!-- IP блокировки -->
                    <div class="col-md-6">
                        <h6>Заблокированные IP</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>IP адрес</th>
                                        <th>Причина</th>
                                        <th>Дата блокировки</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="ipBlocksTable">
                                    <!-- Будет заполнено через JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-outline-primary mt-2" onclick="showAddIpBlockModal()">
                            Добавить блокировку
                        </button>
                    </div>

                    <!-- Правила безопасности -->
                    <div class="col-md-6">
                        <h6>Правила безопасности</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ruleFailedLogins">
                                <label class="form-check-label">
                                    Блокировать после 5 неудачных попыток входа
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ruleGeoCheck">
                                <label class="form-check-label">
                                    Проверять географию входов
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ruleVpnBlock">
                                <label class="form-check-label">
                                    Блокировать VPN/Proxy
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadSecuritySettings();
    loadSecurityLog();
    loadActiveSessions();
    loadSecurityBlocks();
});

async function loadSecuritySettings() {
    try {
        const response = await fetch('/api/admin/security/settings', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const settings = await response.json();
        
        // Заполняем форму настроек
        document.getElementById('minPasswordLength').value = settings.password.min_length;
        document.getElementById('requireUppercase').checked = settings.password.require_uppercase;
        document.getElementById('requireNumbers').checked = settings.password.require_numbers;
        document.getElementById('requireSpecial').checked = settings.password.require_special;
        
        document.getElementById('sessionLifetime').value = settings.session.lifetime;
        document.getElementById('singleSession').checked = settings.session.single_session;
        
        document.getElementById('2faPolicy').value = settings.two_factor.policy;
        document.getElementById('2faAdmin').checked = settings.two_factor.admin_required;
        
    } catch (error) {
        console.error('Error loading security settings:', error);
        alert('Ошибка при загрузке настроек безопасности');
    }
}

async function loadSecurityLog() {
    const logType = document.getElementById('securityLogType').value;
    
    try {
        const response = await fetch(`/api/admin/security/log?type=${logType}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const logs = await response.json();
        renderSecurityLog(logs);
    } catch (error) {
        console.error('Error loading security log:', error);
    }
}

function renderSecurityLog(logs) {
    const container = document.getElementById('securityLogContent');
    container.innerHTML = logs.map(log => `
        <div class="log-entry p-2 ${getLogEntryClass(log.level)}">
            <div class="d-flex justify-content-between">
                <strong>${log.event}</strong>
                <small>${new Date(log.timestamp).toLocaleString()}</small>
            </div>
            <div class="mt-1">
                <small>
                    Пользователь: ${log.user || 'Система'} | 
                    IP: ${log.ip_address} | 
                    Детали: ${log.details}
                </small>
            </div>
        </div>
    `).join('');
}

function getLogEntryClass(level) {
    switch (level) {
        case 'error': return 'bg-danger text-white';
        case 'warning': return 'bg-warning';
        case 'info': return 'bg-light';
        default: return '';
    }
}

// Обработчики форм и событий
document.getElementById('securitySettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        password: {
            min_length: parseInt(document.getElementById('minPasswordLength').value),
            require_uppercase: document.getElementById('requireUppercase').checked,
            require_numbers: document.getElementById('requireNumbers').checked,
            require_special: document.getElementById('requireSpecial').checked
        },
        session: {
            lifetime: parseInt(document.getElementById('sessionLifetime').value),
            single_session: document.getElementById('singleSession').checked
        },
        two_factor: {
            policy: document.getElementById('2faPolicy').value,
            admin_required: document.getElementById('2faAdmin').checked
        }
    };
    
    try {
        const response = await fetch('/api/admin/security/settings', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('Настройки безопасности сохранены');
        } else {
            throw new Error('Failed to save settings');
        }
    } catch (error) {
        console.error('Error saving security settings:', error);
        alert('Ошибка при сохранении настроек');
    }
});

// Обновляем лог при изменении фильтра
document.getElementById('securityLogType').addEventListener('change', loadSecurityLog);
</script>

<style>
.security-log {
    font-size: 0.875rem;
}

.log-entry {
    border-bottom: 1px solid #dee2e6;
}

.log-entry:last-child {
    border-bottom: none;
}
</style> 