<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="/assets/images/avatar-placeholder.png" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                    <h5 class="card-title" id="employeeName">Загрузка...</h5>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Telegram</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="telegramId" readonly>
                        <button class="btn btn-outline-primary" onclick="connectTelegram()">
                            Подключить
                        </button>
                    </div>
                </div>
                
                <button class="btn btn-primary w-100" onclick="showEditForm()">
                    Редактировать профиль
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Мои объекты и ставки</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Объект</th>
                                <th>Ставка</th>
                                <th>Действует с</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTable">
                            <!-- Данные будут загружены через JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Настройки уведомлений</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="emailNotifications">
                    <label class="form-check-label">
                        Email-уведомления
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="telegramNotifications">
                    <label class="form-check-label">
                        Telegram-уведомления
                    </label>
                </div>
                <button class="btn btn-primary" onclick="saveNotificationSettings()">
                    Сохранить настройки
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования профиля -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование профиля</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" class="form-control" id="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Фамилия</label>
                        <input type="text" class="form-control" id="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="phone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
async function loadProfile() {
    try {
        const response = await fetch('/api/employee/profile', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const profile = await response.json();
        renderProfile(profile);
    } catch (error) {
        console.error('Error loading profile:', error);
        alert('Ошибка при загрузке профиля');
    }
}

function renderProfile(profile) {
    document.getElementById('employeeName').textContent = 
        `${profile.first_name} ${profile.last_name}`;
    document.getElementById('email').value = profile.email;
    document.getElementById('telegramId').value = profile.telegram_id || '';
    
    // Заполняем таблицу ставок
    const ratesTable = document.getElementById('ratesTable');
    ratesTable.innerHTML = profile.rates.map(rate => `
        <tr>
            <td>${rate.facility_name || 'Все объекты'}</td>
            <td>${rate.hourly_rate} ₽/час</td>
            <td>${new Date(rate.start_date).toLocaleDateString()}</td>
        </tr>
    `).join('');
    
    // Заполняем форму редактирования
    document.getElementById('firstName').value = profile.first_name;
    document.getElementById('lastName').value = profile.last_name;
    document.getElementById('phone').value = profile.phone || '';
}

function showEditForm() {
    new bootstrap.Modal(document.getElementById('editProfileModal')).show();
}

async function saveProfile() {
    const data = {
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        phone: document.getElementById('phone').value
    };
    
    try {
        const response = await fetch('/api/employee/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
            loadProfile();
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error saving profile:', error);
        alert('Ошибка при сохранении профиля');
    }
}

// Загружаем профиль при загрузке страницы
loadProfile();
</script> 