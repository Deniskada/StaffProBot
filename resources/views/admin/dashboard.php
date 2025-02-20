<?php
// Не нужно устанавливать layout здесь, он передается из контроллера
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>
    
    <div class="row">
        <!-- Общая статистика -->
        <div class="col-md-12 mb-4">
            <div class="stats-container">
                <?php if (!empty($stats)): ?>
                    <div class="stat-cards">
                        <div class="stat-card">
                            <h3>Пользователи</h3>
                            <p class="number"><?= $stats['users_count'] ?? 0 ?></p>
                            <p class="detail">Активных: <?= $stats['active_users'] ?? 0 ?></p>
                            <p class="detail">Новых: <?= $stats['new_users'] ?? 0 ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Объекты</h3>
                            <p class="number"><?= $stats['total_facilities'] ?? 0 ?></p>
                            <p class="detail">Активных: <?= $stats['active_facilities'] ?? 0 ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Смены</h3>
                            <p class="number"><?= $stats['total_shifts'] ?? 0 ?></p>
                            <p class="detail">Активных: <?= $stats['active_shifts'] ?? 0 ?></p>
                            <p class="detail">Завершенных: <?= $stats['completed_shifts'] ?? 0 ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Выручка</h3>
                            <p class="number"><?= number_format($stats['revenue'] ?? 0, 2, '.', ' ') ?> ₽</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Статистика временно недоступна
                        <?php if (isset($error)): ?>
                            <br>Причина: <?= htmlspecialchars($error) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- График активности -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Активность за последние 30 дней</h5>
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Последние действия -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Последние действия</h5>
                    <div class="activity-feed" id="activityFeed">
                        <!-- Будет заполнено через JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function loadDashboard() {
    try {
        const response = await fetch('/api/admin/stats', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const stats = await response.json();
        renderDashboard(stats);
    } catch (error) {
        console.error('Error loading dashboard:', error);
        alert('Ошибка при загрузке данных');
    }
}

function renderDashboard(stats) {
    // Обновляем статистику
    document.getElementById('totalUsers').textContent = stats.total_users;
    document.getElementById('activeFacilities').textContent = stats.active_facilities;
    document.getElementById('activeShifts').textContent = stats.active_shifts;
    document.getElementById('monthlyRevenue').textContent = 
        new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' })
            .format(stats.revenue);
    
    // Отрисовываем график
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: stats.activity.dates,
            datasets: [{
                label: 'Активные смены',
                data: stats.activity.shifts,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
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
    
    // Отображаем последние действия
    const feed = document.getElementById('activityFeed');
    feed.innerHTML = stats.recent_activity.map(activity => `
        <div class="activity-item">
            <small class="text-muted">
                ${new Date(activity.timestamp).toLocaleString()}
            </small>
            <p class="mb-0">${activity.description}</p>
        </div>
    `).join('');
}

// Загружаем данные при загрузке страницы
loadDashboard();

// Обновляем данные каждые 5 минут
setInterval(loadDashboard, 300000);
</script>

<style>
.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}
</style> 