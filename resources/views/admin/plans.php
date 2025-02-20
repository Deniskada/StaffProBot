<div class="row">
    <!-- Список тарифов -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Тарифные планы</h5>
                    <button class="btn btn-primary" onclick="showPlanModal()">
                        Добавить тариф
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Цена</th>
                                <th>Лимиты</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="plansTable">
                            <!-- Будет заполнено через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика по тарифам -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Статистика</h5>
                <div id="planStats">
                    <!-- Будет заполнено через JavaScript -->
                </div>
                <canvas id="plansChart" class="mt-4"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно тарифа -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planModalTitle">Новый тарифный план</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="planForm">
                    <input type="hidden" id="planId">
                    
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" class="form-control" id="planName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" id="planDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Цена (₽/месяц)</label>
                        <input type="number" class="form-control" id="planPrice" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Лимиты</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Объекты</label>
                                <input type="number" class="form-control" id="facilitiesLimit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Сотрудники</label>
                                <input type="number" class="form-control" id="employeesLimit">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Функции</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featureApi">
                            <label class="form-check-label">API доступ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featureReports">
                            <label class="form-check-label">Расширенные отчеты</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featureSupport">
                            <label class="form-check-label">Приоритетная поддержка</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="savePlan()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let plans = [];
let planModal;

async function loadPlans() {
    try {
        const response = await fetch('/api/admin/plans', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        plans = await response.json();
        renderPlans();
        renderStats();
    } catch (error) {
        console.error('Error loading plans:', error);
        alert('Ошибка при загрузке тарифов');
    }
}

function renderPlans() {
    const tbody = document.getElementById('plansTable');
    tbody.innerHTML = plans.map(plan => `
        <tr>
            <td>
                <strong>${plan.name}</strong><br>
                <small class="text-muted">${plan.description}</small>
            </td>
            <td>${plan.price} ₽/мес</td>
            <td>
                <small>
                    Объекты: ${plan.facilities_limit || '∞'}<br>
                    Сотрудники: ${plan.employees_limit || '∞'}
                </small>
            </td>
            <td>
                <span class="badge bg-${plan.is_active ? 'success' : 'secondary'}">
                    ${plan.is_active ? 'Активен' : 'Отключен'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editPlan(${plan.id})">
                        Изменить
                    </button>
                    <button class="btn btn-outline-${plan.is_active ? 'danger' : 'success'}" 
                            onclick="togglePlan(${plan.id})">
                        ${plan.is_active ? 'Отключить' : 'Включить'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderStats() {
    // Отображаем общую статистику
    const stats = calculateStats();
    document.getElementById('planStats').innerHTML = `
        <div class="row g-3">
            <div class="col-6">
                <div class="border rounded p-3 text-center">
                    <h6 class="mb-1">Активных подписок</h6>
                    <h3 class="mb-0">${stats.activeSubscriptions}</h3>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-3 text-center">
                    <h6 class="mb-1">Выручка/мес</h6>
                    <h3 class="mb-0">${stats.monthlyRevenue} ₽</h3>
                </div>
            </div>
        </div>
    `;

    // Отрисовываем график
    const ctx = document.getElementById('plansChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: plans.map(p => p.name),
            datasets: [{
                data: plans.map(p => p.subscribers_count),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function calculateStats() {
    return {
        activeSubscriptions: plans.reduce((sum, plan) => sum + plan.subscribers_count, 0),
        monthlyRevenue: plans.reduce((sum, plan) => sum + (plan.price * plan.subscribers_count), 0)
    };
}

function showPlanModal(plan = null) {
    const form = document.getElementById('planForm');
    form.reset();
    
    if (plan) {
        document.getElementById('planModalTitle').textContent = 'Редактирование тарифа';
        document.getElementById('planId').value = plan.id;
        document.getElementById('planName').value = plan.name;
        document.getElementById('planDescription').value = plan.description;
        document.getElementById('planPrice').value = plan.price;
        document.getElementById('facilitiesLimit').value = plan.facilities_limit;
        document.getElementById('employeesLimit').value = plan.employees_limit;
        document.getElementById('featureApi').checked = plan.features.includes('api');
        document.getElementById('featureReports').checked = plan.features.includes('reports');
        document.getElementById('featureSupport').checked = plan.features.includes('support');
    } else {
        document.getElementById('planModalTitle').textContent = 'Новый тарифный план';
        document.getElementById('planId').value = '';
    }
    
    planModal = new bootstrap.Modal(document.getElementById('planModal'));
    planModal.show();
}

async function savePlan() {
    const data = {
        name: document.getElementById('planName').value,
        description: document.getElementById('planDescription').value,
        price: parseInt(document.getElementById('planPrice').value),
        facilities_limit: parseInt(document.getElementById('facilitiesLimit').value) || null,
        employees_limit: parseInt(document.getElementById('employeesLimit').value) || null,
        features: []
    };
    
    if (document.getElementById('featureApi').checked) data.features.push('api');
    if (document.getElementById('featureReports').checked) data.features.push('reports');
    if (document.getElementById('featureSupport').checked) data.features.push('support');
    
    const planId = document.getElementById('planId').value;
    
    try {
        const response = await fetch(`/api/admin/plans${planId ? '/' + planId : ''}`, {
            method: planId ? 'PUT' : 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            planModal.hide();
            loadPlans();
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error saving plan:', error);
        alert('Ошибка при сохранении тарифа');
    }
}

// Загружаем данные при загрузке страницы
loadPlans();
</script> 