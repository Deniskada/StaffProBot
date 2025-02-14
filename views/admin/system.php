<div class="row">
    <!-- Резервное копирование -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Резервное копирование</h5>
                    <button class="btn btn-primary" onclick="createBackup()">
                        Создать копию
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Дата создания</th>
                                <th>Размер</th>
                                <th>Тип</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="backupsTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <h6>Настройки автоматического резервирования</h6>
                    <form id="backupSettingsForm" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label">Периодичность</label>
                            <select class="form-select" id="backupFrequency">
                                <option value="daily">Ежедневно</option>
                                <option value="weekly">Еженедельно</option>
                                <option value="monthly">Ежемесячно</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Хранить копий</label>
                            <input type="number" class="form-control" id="backupRetention">
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Системные задачи -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Системные задачи</h5>
                
                <div class="task-list">
                    <!-- Очистка кэша -->
                    <div class="task-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Очистка кэша</h6>
                                <small class="text-muted" id="cacheStatus">
                                    Размер: 0 MB
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="clearCache()">
                                Очистить
                            </button>
                        </div>
                    </div>

                    <!-- Оптимизация БД -->
                    <div class="task-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Оптимизация БД</h6>
                                <small class="text-muted" id="dbStatus">
                                    Последняя: никогда
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="optimizeDb()">
                                Запустить
                            </button>
                        </div>
                    </div>

                    <!-- Очистка логов -->
                    <div class="task-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Очистка логов</h6>
                                <small class="text-muted" id="logsStatus">
                                    Старше 30 дней
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="clearLogs()">
                                Очистить
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>Планировщик задач</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Задача</th>
                                    <th>Расписание</th>
                                    <th>Последний запуск</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody id="cronTable">
                                <!-- Будет заполнено через JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Мониторинг системы -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Мониторинг системы</h5>
                
                <div class="row">
                    <!-- CPU -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 border rounded">
                            <h6>Загрузка CPU</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar" id="cpuUsage" role="progressbar" 
                                     style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="cpuDetails">
                                Ядра: 0, Процессы: 0
                            </small>
                        </div>
                    </div>

                    <!-- Память -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 border rounded">
                            <h6>Использование памяти</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar" id="memoryUsage" role="progressbar" 
                                     style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="memoryDetails">
                                Использовано: 0 MB из 0 MB
                            </small>
                        </div>
                    </div>

                    <!-- Диск -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 border rounded">
                            <h6>Дисковое пространство</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar" id="diskUsage" role="progressbar" 
                                     style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="diskDetails">
                                Свободно: 0 GB из 0 GB
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <canvas id="systemChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="system-log p-3 bg-light" style="height: 300px; overflow-y: auto;">
                            <pre id="systemLog" class="mb-0"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let systemChart = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadBackups();
    loadSystemStatus();
    loadCronJobs();
    initSystemMonitoring();
});

async function loadBackups() {
    try {
        const response = await fetch('/api/admin/system/backups', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const backups = await response.json();
        renderBackups(backups);
    } catch (error) {
        console.error('Error loading backups:', error);
        alert('Ошибка при загрузке резервных копий');
    }
}

function renderBackups(backups) {
    const tbody = document.getElementById('backupsTable');
    tbody.innerHTML = backups.map(backup => `
        <tr>
            <td>${new Date(backup.created_at).toLocaleString()}</td>
            <td>${formatSize(backup.size)}</td>
            <td>
                <span class="badge bg-${backup.type === 'auto' ? 'info' : 'primary'}">
                    ${backup.type === 'auto' ? 'Авто' : 'Ручной'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" 
                            onclick="downloadBackup('${backup.id}')">
                        Скачать
                    </button>
                    <button class="btn btn-outline-success" 
                            onclick="restoreBackup('${backup.id}')">
                        Восстановить
                    </button>
                    <button class="btn btn-outline-danger" 
                            onclick="deleteBackup('${backup.id}')">
                        Удалить
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function initSystemMonitoring() {
    // Инициализируем график
    const ctx = document.getElementById('systemChart').getContext('2d');
    systemChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'CPU',
                    data: [],
                    borderColor: '#4e73df'
                },
                {
                    label: 'Память',
                    data: [],
                    borderColor: '#1cc88a'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Запускаем мониторинг
    updateSystemStatus();
    setInterval(updateSystemStatus, 5000);
}

async function updateSystemStatus() {
    try {
        const response = await fetch('/api/admin/system/status', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const status = await response.json();
        updateSystemMetrics(status);
    } catch (error) {
        console.error('Error updating system status:', error);
    }
}

function updateSystemMetrics(status) {
    // Обновляем индикаторы
    updateProgressBar('cpuUsage', status.cpu.usage);
    updateProgressBar('memoryUsage', status.memory.percentage);
    updateProgressBar('diskUsage', status.disk.percentage);
    
    document.getElementById('cpuDetails').textContent = 
        `Ядра: ${status.cpu.cores}, Процессы: ${status.cpu.processes}`;
    document.getElementById('memoryDetails').textContent = 
        `Использовано: ${formatSize(status.memory.used)} из ${formatSize(status.memory.total)}`;
    document.getElementById('diskDetails').textContent = 
        `Свободно: ${formatSize(status.disk.free)} из ${formatSize(status.disk.total)}`;
    
    // Обновляем график
    const timestamp = new Date().toLocaleTimeString();
    systemChart.data.labels.push(timestamp);
    systemChart.data.datasets[0].data.push(status.cpu.usage);
    systemChart.data.datasets[1].data.push(status.memory.percentage);
    
    // Ограничиваем количество точек на графике
    if (systemChart.data.labels.length > 20) {
        systemChart.data.labels.shift();
        systemChart.data.datasets.forEach(dataset => dataset.data.shift());
    }
    
    systemChart.update();
}

function updateProgressBar(id, value) {
    const element = document.getElementById(id);
    element.style.width = `${value}%`;
    element.textContent = `${value}%`;
    element.className = `progress-bar bg-${
        value > 90 ? 'danger' : 
        value > 70 ? 'warning' : 
        'success'
    }`;
}

function formatSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(1)} ${units[unitIndex]}`;
}

// Остальные функции для работы с резервными копиями и системными задачами...
</script>

<style>
.system-log {
    font-family: monospace;
    font-size: 0.875rem;
    background-color: #f8f9fa;
}

.task-item:hover {
    background-color: #f8f9fa;
}
</style> 