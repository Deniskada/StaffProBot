<div class="row">
    <!-- Шаблоны уведомлений -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Шаблоны уведомлений</h5>
                    <button class="btn btn-primary" onclick="showTemplateModal()">
                        Добавить шаблон
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Последнее изменение</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="templatesTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика отправок -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Статистика отправок</h5>
                <div class="stats-container">
                    <div class="mb-3 p-3 border rounded">
                        <h6 class="mb-2">За сегодня</h6>
                        <div class="row">
                            <div class="col">
                                <small class="text-muted">Email</small>
                                <h4 id="emailToday">0</h4>
                            </div>
                            <div class="col">
                                <small class="text-muted">Telegram</small>
                                <h4 id="telegramToday">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 p-3 border rounded">
                        <h6 class="mb-2">За месяц</h6>
                        <div class="row">
                            <div class="col">
                                <small class="text-muted">Email</small>
                                <h4 id="emailMonth">0</h4>
                            </div>
                            <div class="col">
                                <small class="text-muted">Telegram</small>
                                <h4 id="telegramMonth">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <canvas id="deliveryChart" class="mt-4"></canvas>
            </div>
        </div>
    </div>

    <!-- Активные рассылки -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Активные рассылки</h5>
                    <button class="btn btn-primary" onclick="showCampaignModal()">
                        Создать рассылку
                    </button>
                </div>

                <div id="activeCampaigns">
                    <!-- Будет заполнено через JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно шаблона -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">Новый шаблон</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <input type="hidden" id="templateId">
                    
                    <div class="mb-3">
                        <label class="form-label">Название шаблона</label>
                        <input type="text" class="form-control" id="templateName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Тип уведомления</label>
                        <select class="form-select" id="templateType" required>
                            <option value="email">Email</option>
                            <option value="telegram">Telegram</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Тема (для email)</label>
                        <input type="text" class="form-control" id="templateSubject">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Текст сообщения</label>
                        <textarea class="form-control" id="templateBody" rows="10" required></textarea>
                        <small class="text-muted">
                            Доступные переменные: {name}, {email}, {company}, {date}
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно рассылки -->
<div class="modal fade" id="campaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новая рассылка</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="campaignForm">
                    <div class="mb-3">
                        <label class="form-label">Название рассылки</label>
                        <input type="text" class="form-control" id="campaignName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Шаблон</label>
                        <select class="form-select" id="campaignTemplate" required>
                            <!-- Будет заполнено через JavaScript -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Получатели</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recipientEmployers">
                            <label class="form-check-label">Работодатели</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recipientEmployees">
                            <label class="form-check-label">Сотрудники</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Фильтры</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <select class="form-select" id="filterField">
                                    <option value="">Выберите поле</option>
                                    <option value="status">Статус</option>
                                    <option value="plan">Тарифный план</option>
                                    <option value="registration">Дата регистрации</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="filterValue" 
                                       placeholder="Значение">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Расписание</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="datetime-local" class="form-control" id="scheduleStart">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="scheduleRepeat">
                                    <option value="once">Однократно</option>
                                    <option value="daily">Ежедневно</option>
                                    <option value="weekly">Еженедельно</option>
                                    <option value="monthly">Ежемесячно</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveCampaign()">Запустить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let templates = [];
let campaigns = [];
let deliveryChart = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadTemplates();
    loadCampaigns();
    loadStats();
});

async function loadTemplates() {
    try {
        const response = await fetch('/api/admin/notifications/templates', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        templates = await response.json();
        renderTemplates();
    } catch (error) {
        console.error('Error loading templates:', error);
        alert('Ошибка при загрузке шаблонов');
    }
}

function renderTemplates() {
    const tbody = document.getElementById('templatesTable');
    tbody.innerHTML = templates.map(template => `
        <tr>
            <td>${template.name}</td>
            <td>
                <span class="badge bg-${template.type === 'email' ? 'primary' : 'info'}">
                    ${template.type}
                </span>
            </td>
            <td>${new Date(template.updated_at).toLocaleString()}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editTemplate(${template.id})">
                        Изменить
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteTemplate(${template.id})">
                        Удалить
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function loadStats() {
    try {
        const response = await fetch('/api/admin/notifications/stats', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const stats = await response.json();
        renderStats(stats);
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function renderStats(stats) {
    // Обновляем счетчики
    document.getElementById('emailToday').textContent = stats.today.email;
    document.getElementById('telegramToday').textContent = stats.today.telegram;
    document.getElementById('emailMonth').textContent = stats.month.email;
    document.getElementById('telegramMonth').textContent = stats.month.telegram;
    
    // Обновляем график
    const ctx = document.getElementById('deliveryChart').getContext('2d');
    
    if (deliveryChart) {
        deliveryChart.destroy();
    }
    
    deliveryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: stats.chart.labels,
            datasets: [
                {
                    label: 'Email',
                    data: stats.chart.email,
                    borderColor: '#4e73df',
                    tension: 0.1
                },
                {
                    label: 'Telegram',
                    data: stats.chart.telegram,
                    borderColor: '#36b9cc',
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

async function loadCampaigns() {
    try {
        const response = await fetch('/api/admin/notifications/campaigns', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        campaigns = await response.json();
        renderCampaigns();
    } catch (error) {
        console.error('Error loading campaigns:', error);
    }
}

function renderCampaigns() {
    const container = document.getElementById('activeCampaigns');
    container.innerHTML = campaigns.map(campaign => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${campaign.name}</h6>
                        <small class="text-muted">
                            Отправлено: ${campaign.sent_count} из ${campaign.total_count}
                        </small>
                    </div>
                    <div class="progress" style="width: 200px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${(campaign.sent_count / campaign.total_count) * 100}%">
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="stopCampaign(${campaign.id})">
                        Остановить
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Остальные функции для работы с модальными окнами и сохранения данных...
</script> 