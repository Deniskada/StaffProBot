<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Работодатели</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Поиск..." id="searchInput">
                <button class="btn btn-primary" onclick="exportToExcel()">
                    Экспорт
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Email</th>
                        <th>Объекты</th>
                        <th>Сотрудники</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody id="employersTable">
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

<!-- Модальное окно просмотра деталей -->
<div class="modal fade" id="employerDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали работодателя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="employerTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#facilities">
                            Объекты
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#employees">
                            Сотрудники
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#stats">
                            Статистика
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="facilities">
                        <!-- Список объектов -->
                    </div>
                    <div class="tab-pane fade" id="employees">
                        <!-- Список сотрудников -->
                    </div>
                    <div class="tab-pane fade" id="stats">
                        <!-- Статистика -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let employers = [];
let currentPage = 1;
const itemsPerPage = 10;

async function loadEmployers() {
    try {
        const response = await fetch('/api/admin/employers', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        employers = await response.json();
        renderEmployers();
    } catch (error) {
        console.error('Error loading employers:', error);
        alert('Ошибка при загрузке списка работодателей');
    }
}

function renderEmployers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filteredEmployers = employers.filter(employer => 
        employer.name.toLowerCase().includes(searchTerm) || 
        employer.email.toLowerCase().includes(searchTerm)
    );
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageEmployers = filteredEmployers.slice(startIndex, endIndex);
    
    const tbody = document.getElementById('employersTable');
    tbody.innerHTML = pageEmployers.map(employer => `
        <tr>
            <td>${employer.id}</td>
            <td>${employer.name}</td>
            <td>${employer.email}</td>
            <td>${employer.facilities_count}</td>
            <td>${employer.employees_count}</td>
            <td>
                <span class="badge bg-${employer.is_blocked ? 'danger' : 'success'}">
                    ${employer.is_blocked ? 'Заблокирован' : 'Активен'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" 
                            onclick="showDetails(${employer.id})">
                        Детали
                    </button>
                    <button class="btn btn-outline-${employer.is_blocked ? 'success' : 'danger'}" 
                            onclick="${employer.is_blocked ? 'unblock' : 'block'}(${employer.id})">
                        ${employer.is_blocked ? 'Разблокировать' : 'Заблокировать'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    renderPagination(filteredEmployers.length);
}

async function showDetails(employerId) {
    try {
        const response = await fetch(`/api/admin/employers/${employerId}/details`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const details = await response.json();
        renderEmployerDetails(details);
        new bootstrap.Modal(document.getElementById('employerDetailsModal')).show();
    } catch (error) {
        console.error('Error loading employer details:', error);
        alert('Ошибка при загрузке деталей');
    }
}

function renderEmployerDetails(details) {
    // Отображаем объекты
    document.getElementById('facilities').innerHTML = `
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Адрес</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    ${details.facilities.map(facility => `
                        <tr>
                            <td>${facility.name}</td>
                            <td>${facility.address}</td>
                            <td>
                                <span class="badge bg-${facility.is_open ? 'success' : 'secondary'}">
                                    ${facility.is_open ? 'Открыт' : 'Закрыт'}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    // Аналогично для сотрудников и статистики...
}

// Загружаем данные при загрузке страницы
loadEmployers();

// Обработчик поиска
document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    renderEmployers();
});
</script> 