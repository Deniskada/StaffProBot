<div class="row">
    <!-- API ключи -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">API ключи</h5>
                    <button class="btn btn-primary" onclick="showApiKeyModal()">
                        Создать ключ
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Ключ</th>
                                <th>Создан</th>
                                <th>Последнее использование</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="apiKeysTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика использования API -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Использование API</h5>
                <div class="stats-container">
                    <div class="mb-3 p-3 border rounded">
                        <h6 class="mb-2">Сегодня</h6>
                        <div class="d-flex justify-content-between">
                            <span>Запросов:</span>
                            <strong id="requestsToday">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ошибок:</span>
                            <strong id="errorsToday">0</strong>
                        </div>
                    </div>
                    <canvas id="apiUsageChart" class="mt-4"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Внешние интеграции -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Интеграции</h5>
                
                <!-- Telegram -->
                <div class="integration-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-1">Telegram Bot</h6>
                            <small class="text-muted" id="telegramStatus">Не подключен</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="configureTelegram()">
                            Настроить
                        </button>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar" id="telegramProgress" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- SMS шлюз -->
                <div class="integration-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-1">SMS Gateway</h6>
                            <small class="text-muted" id="smsStatus">Не подключен</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="configureSms()">
                            Настроить
                        </button>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar" id="smsProgress" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Геолокация -->
                <div class="integration-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-1">Геолокация</h6>
                            <small class="text-muted" id="geoStatus">Не подключен</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="configureGeo()">
                            Настроить
                        </button>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar" id="geoProgress" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Логи интеграций -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Логи интеграций</h5>
                <div class="mb-3">
                    <select class="form-select" id="integrationLogType">
                        <option value="all">Все интеграции</option>
                        <option value="telegram">Telegram</option>
                        <option value="sms">SMS</option>
                        <option value="geo">Геолокация</option>
                    </select>
                </div>
                <div class="integration-logs" style="height: 300px; overflow-y: auto;">
                    <div id="integrationLogs">
                        <!-- Будет заполнено через JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно API ключа -->
<div class="modal fade" id="apiKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новый API ключ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="apiKeyForm">
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" class="form-control" id="keyName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Разрешения</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permRead">
                            <label class="form-check-label">Чтение данных</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permWrite">
                            <label class="form-check-label">Запись данных</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permAdmin">
                            <label class="form-check-label">Административные функции</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Срок действия</label>
                        <select class="form-select" id="keyExpiration">
                            <option value="never">Бессрочно</option>
                            <option value="30">30 дней</option>
                            <option value="90">90 дней</option>
                            <option value="365">1 год</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="generateApiKey()">Создать</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let apiKeys = [];
let usageChart = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadApiKeys();
    loadApiUsage();
    loadIntegrationStatus();
    loadIntegrationLogs();
});

async function loadApiKeys() {
    try {
        const response = await fetch('/api/admin/integrations/keys', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        apiKeys = await response.json();
        renderApiKeys();
    } catch (error) {
        console.error('Error loading API keys:', error);
        alert('Ошибка при загрузке API ключей');
    }
}

function renderApiKeys() {
    const tbody = document.getElementById('apiKeysTable');
    tbody.innerHTML = apiKeys.map(key => `
        <tr>
            <td>${key.name}</td>
            <td>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" 
                           value="${key.key.substring(0, 8)}..." readonly>
                    <button class="btn btn-outline-secondary btn-sm" 
                            onclick="copyApiKey('${key.key}')">
                        Копировать
                    </button>
                </div>
            </td>
            <td>${new Date(key.created_at).toLocaleDateString()}</td>
            <td>${key.last_used ? new Date(key.last_used).toLocaleString() : 'Никогда'}</td>
            <td>
                <span class="badge bg-${key.is_active ? 'success' : 'danger'}">
                    ${key.is_active ? 'Активен' : 'Отключен'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" 
                        onclick="revokeApiKey(${key.id})">
                    Отозвать
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadApiUsage() {
    try {
        const response = await fetch('/api/admin/integrations/usage', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const usage = await response.json();
        renderApiUsage(usage);
    } catch (error) {
        console.error('Error loading API usage:', error);
    }
}

function renderApiUsage(usage) {
    // Обновляем счетчики
    document.getElementById('requestsToday').textContent = usage.today.requests;
    document.getElementById('errorsToday').textContent = usage.today.errors;
    
    // Обновляем график
    const ctx = document.getElementById('apiUsageChart').getContext('2d');
    
    if (usageChart) {
        usageChart.destroy();
    }
    
    usageChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: usage.chart.labels,
            datasets: [
                {
                    label: 'Запросы',
                    data: usage.chart.requests,
                    borderColor: '#4e73df',
                    tension: 0.1
                },
                {
                    label: 'Ошибки',
                    data: usage.chart.errors,
                    borderColor: '#e74a3b',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

async function loadIntegrationStatus() {
    try {
        const response = await fetch('/api/admin/integrations/status', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const status = await response.json();
        
        // Обновляем статусы интеграций
        updateIntegrationStatus('telegram', status.telegram);
        updateIntegrationStatus('sms', status.sms);
        updateIntegrationStatus('geo', status.geo);
    } catch (error) {
        console.error('Error loading integration status:', error);
    }
}

function updateIntegrationStatus(integration, status) {
    const statusElement = document.getElementById(`${integration}Status`);
    const progressElement = document.getElementById(`${integration}Progress`);
    
    statusElement.textContent = status.connected ? 'Подключено' : 'Не подключено';
    progressElement.style.width = `${status.health}%`;
    progressElement.className = `progress-bar bg-${status.health > 70 ? 'success' : 
                                                   status.health > 30 ? 'warning' : 'danger'}`;
}

// Остальные функции для работы с модальными окнами и управления интеграциями...
</script>

<style>
.integration-item {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
}

.integration-logs {
    font-family: monospace;
    font-size: 0.875rem;
}
</style> 