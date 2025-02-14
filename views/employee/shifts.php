<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4">История смен</h5>
        
        <!-- Фильтры -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Период</label>
                <select class="form-select" id="periodFilter">
                    <option value="week">Неделя</option>
                    <option value="month" selected>Месяц</option>
                    <option value="3months">3 месяца</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Объект</label>
                <select class="form-select" id="facilityFilter">
                    <option value="">Все объекты</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary d-block" onclick="applyFilters()">
                    Применить
                </button>
            </div>
        </div>

        <!-- Таблица смен -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Объект</th>
                        <th>Начало</th>
                        <th>Окончание</th>
                        <th>Часы</th>
                        <th>Ставка</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody id="shiftsTable">
                    <!-- Данные будут загружены через JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Страницы будут добавлены через JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<script>
let shifts = [];
let currentPage = 1;
const itemsPerPage = 10;

async function loadShifts() {
    const period = document.getElementById('periodFilter').value;
    const facilityId = document.getElementById('facilityFilter').value;
    
    try {
        const response = await fetch(`/api/employee/shifts?period=${period}&facility_id=${facilityId}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        shifts = await response.json();
        renderShifts();
    } catch (error) {
        console.error('Error loading shifts:', error);
        alert('Ошибка при загрузке смен');
    }
}

function renderShifts() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageShifts = shifts.slice(startIndex, endIndex);
    
    const tbody = document.getElementById('shiftsTable');
    tbody.innerHTML = pageShifts.map(shift => `
        <tr>
            <td>${new Date(shift.start_time).toLocaleDateString()}</td>
            <td>${shift.facility_name}</td>
            <td>${new Date(shift.start_time).toLocaleTimeString()}</td>
            <td>${shift.end_time ? new Date(shift.end_time).toLocaleTimeString() : '-'}</td>
            <td>${calculateHours(shift.start_time, shift.end_time)}</td>
            <td>${shift.hourly_rate} ₽/час</td>
            <td>${calculateEarnings(shift)} ₽</td>
        </tr>
    `).join('');
    
    renderPagination();
}

function calculateHours(start, end) {
    if (!end) return '-';
    const hours = (new Date(end) - new Date(start)) / (1000 * 60 * 60);
    return hours.toFixed(1);
}

function calculateEarnings(shift) {
    if (!shift.end_time) return '-';
    const hours = (new Date(shift.end_time) - new Date(shift.start_time)) / (1000 * 60 * 60);
    return Math.round(hours * shift.hourly_rate);
}

function renderPagination() {
    const totalPages = Math.ceil(shifts.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>
        `;
    }
    
    pagination.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    renderShifts();
}

function applyFilters() {
    currentPage = 1;
    loadShifts();
}

// Загружаем данные при загрузке страницы
loadShifts();
</script> 