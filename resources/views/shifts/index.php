<?php
/**
 * @var array $user Данные пользователя
 * @var array $shifts Список смен
 * @var string $title Заголовок страницы
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title ?? 'Смены') ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <main class="dashboard-content">
            <div class="shifts-container">
                <div class="shifts-header">
                    <h2>Управление сменами</h2>
                    <div class="shifts-actions">
                        <button class="btn btn-primary" onclick="startShift()">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Начать смену
                        </button>
                    </div>
                </div>

                <div class="shifts-filters">
                    <div class="form-group">
                        <label class="form-label">Период</label>
                        <select class="form-control" onchange="filterShifts(this.value)">
                            <option value="today">Сегодня</option>
                            <option value="week">Неделя</option>
                            <option value="month">Месяц</option>
                            <option value="custom">Произвольный период</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <?php if (!empty($shifts)): ?>
                        <table class="shifts-table">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Объект</th>
                                    <th>Начало</th>
                                    <th>Окончание</th>
                                    <th>Часы</th>
                                    <th>Ставка</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shifts as $shift): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d.m.Y', strtotime($shift['start_time']))) ?></td>
                                    <td><?= htmlspecialchars($shift['facility_name']) ?></td>
                                    <td><?= htmlspecialchars(date('H:i', strtotime($shift['start_time']))) ?></td>
                                    <td><?= htmlspecialchars($shift['end_time'] ? date('H:i', strtotime($shift['end_time'])) : '-') ?></td>
                                    <td><?= htmlspecialchars($shift['total_hours'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($shift['hourly_rate']) ?> ₽/ч</td>
                                    <td><?= htmlspecialchars($shift['total_amount'] ?? '-') ?> ₽</td>
                                    <td>
                                        <span class="status-badge status-<?= htmlspecialchars($shift['status']) ?>">
                                            <?= htmlspecialchars($shift['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <?php if ($shift['status'] === 'active'): ?>
                                                <button class="btn btn-icon btn-danger" onclick="endShift(<?= $shift['id'] ?>)">
                                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($shift['status'] === 'active' && ($user['role'] === 'admin' || $user['role'] === 'employer')): ?>
                                                <button class="btn btn-icon btn-warning" onclick="cancelShift(<?= $shift['id'] ?>)">
                                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Нет доступных смен</p>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <button class="btn btn-primary" onclick="startShift()">Начать новую смену</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="shifts-summary">
                    <div class="summary-card">
                        <h4>Всего часов</h4>
                        <p class="summary-value">120</p>
                    </div>
                    <div class="summary-card">
                        <h4>Смен за месяц</h4>
                        <p class="summary-value">15</p>
                    </div>
                    <div class="summary-card">
                        <h4>Средняя длительность</h4>
                        <p class="summary-value">8ч</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/shifts.js"></script>
</body>
</html> 