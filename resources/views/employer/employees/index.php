<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Сотрудники</h2>
    <button class="btn btn-primary" onclick="showInviteForm()">
        Пригласить сотрудника
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Объекты</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="employeesList">
                            <!-- Список сотрудников будет загружен через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Приглашение сотрудника</h5>
                <form id="inviteForm" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="employeeEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Объекты</label>
                        <select multiple class="form-select" id="facilities" required>
                            <!-- Список объектов будет загружен через JavaScript -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ставка (руб/час)</label>
                        <input type="number" class="form-control" id="hourlyRate" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Отправить приглашение</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let employees = [];
let facilities = [];

async function loadEmployees() {
    try {
        const response = await fetch('/api/employees', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        employees = await response.json();
        renderEmployees();
    } catch (error) {
        console.error('Error loading employees:', error);
        alert('Ошибка при загрузке списка сотрудников');
    }
}

function renderEmployees() {
    const container = document.getElementById('employeesList');
    container.innerHTML = employees.map(employee => `
        <tr>
            <td>${employee.first_name} ${employee.last_name}</td>
            <td>
                ${employee.facilities.map(f => f.name).join(', ')}
            </td>
            <td>
                ${employee.active_shift ? 
                    '<span class="badge bg-success">На смене</span>' : 
                    '<span class="badge bg-secondary">Не на смене</span>'}
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editEmployee(${employee.id})">
                        Редактировать
                    </button>
                    <button class="btn btn-outline-danger" onclick="removeEmployee(${employee.id})">
                        Удалить
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showInviteForm() {
    document.getElementById('inviteForm').classList.remove('d-none');
}

// Загружаем данные при загрузке страницы
loadEmployees();
</script> 