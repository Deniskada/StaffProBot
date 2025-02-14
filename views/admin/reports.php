<div class="row">
    <!-- Фильтры отчетов -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form id="reportFilters" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Тип отчета</label>
                        <select class="form-select" id="reportType">
                            <option value="shifts">Смены</option>
                            <option value="employers">Работодатели</option>
                            <option value="employees">Сотрудники</option>
                            <option value="facilities">Объекты</option>
                            <option value="revenue">Выручка</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Период</label>
                        <select class="form-select" id="period">
                            <option value="day">День</option>
                            <option value="week">Неделя</option>
                            <option value="month" selected>Месяц</option>
                            <option value="quarter">Квартал</option>
                            <option value="year">Год</option>
                            <option value="custom">Произвольный</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Начало периода</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Конец периода</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Сформировать</button>
                        <button type="button" class="btn btn-outline-primary" onclick="exportReport()">
                            Экспорт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Графики -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Динамика показателей</h5>
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Круговая диаграмма -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Распределение</h5>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Таблица с данными -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="reportTable">
                        <thead>
                            <!-- Заголовки будут добавлены динамически -->
                        </thead>
                        <tbody>
                            <!-- Данные будут добавлены динамически -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let mainChartInstance = null;
let pieChartInstance = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    // Устанавливаем начальные даты
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('startDate').value = formatDateForInput(firstDayOfMonth);
    document.getElementById('endDate').value = formatDateForInput(today);
    
    // Загружаем начальный отчет
    loadReport();
});

// Обработчик формы
document.getElementById('reportFilters').addEventListener('submit', (e) => {
    e.preventDefault();
    loadReport();
});

async function loadReport() {
    const filters = {
        type: document.getElementById('reportType').value,
        period: document.getElementById('period').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value
    };
    
    try {
        const response = await fetch('/api/admin/reports?' + new URLSearchParams(filters), {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const data = await response.json();
        renderReport(data);
    } catch (error) {
        console.error('Error loading report:', error);
        alert('Ошибка при загрузке отчета');
    }
}

function renderReport(data) {
    renderMainChart(data);
    renderPieChart(data);
    renderTable(data);
}

function renderMainChart(data) {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    if (mainChartInstance) {
        mainChartInstance.destroy();
    }
    
    mainChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.timeline.labels,
            datasets: [{
                label: data.timeline.label,
                data: data.timeline.values,
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
}

function renderPieChart(data) {
    const ctx = document.getElementById('pieChart').getContext('2d');
    
    if (pieChartInstance) {
        pieChartInstance.destroy();
    }
    
    pieChartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.distribution.labels,
            datasets: [{
                data: data.distribution.values,
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

function renderTable(data) {
    const table = document.getElementById('reportTable');
    
    // Заголовки
    table.querySelector('thead').innerHTML = `
        <tr>
            ${data.table.headers.map(header => `<th>${header}</th>`).join('')}
        </tr>
    `;
    
    // Данные
    table.querySelector('tbody').innerHTML = data.table.rows.map(row => `
        <tr>
            ${row.map(cell => `<td>${cell}</td>`).join('')}
        </tr>
    `).join('');
}

async function exportReport() {
    const filters = {
        type: document.getElementById('reportType').value,
        period: document.getElementById('period').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        format: 'excel'
    };
    
    try {
        const response = await fetch('/api/admin/reports/export?' + new URLSearchParams(filters), {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `report_${filters.type}_${filters.start_date}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } else {
            throw new Error('Export failed');
        }
    } catch (error) {
        console.error('Error exporting report:', error);
        alert('Ошибка при экспорте отчета');
    }
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}
</script> 