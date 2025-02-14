<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Мои объекты</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#facilityModal">
        Добавить объект
    </button>
</div>

<div class="row" id="facilitiesList">
    <!-- Список объектов будет загружен через JavaScript -->
</div>

<!-- Модальное окно для добавления/редактирования объекта -->
<div class="modal fade" id="facilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавление объекта</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="facilityForm">
                    <input type="hidden" id="facilityId">
                    
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Часы работы</label>
                        <div class="row">
                            <div class="col">
                                <input type="time" class="form-control" id="workingHoursFrom" required>
                            </div>
                            <div class="col">
                                <input type="time" class="form-control" id="workingHoursTo" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Координаты</label>
                        <div class="row">
                            <div class="col">
                                <input type="number" class="form-control" id="latitude" placeholder="Широта" step="any" required>
                            </div>
                            <div class="col">
                                <input type="number" class="form-control" id="longitude" placeholder="Долгота" step="any" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Радиус контроля (метров)</label>
                        <input type="number" class="form-control" id="accuracyThreshold" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Комментарий</label>
                        <textarea class="form-control" id="comments"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveFacility()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
let facilities = [];

async function loadFacilities() {
    try {
        const response = await fetch('/api/facilities', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        facilities = await response.json();
        renderFacilities();
    } catch (error) {
        console.error('Error loading facilities:', error);
        alert('Ошибка при загрузке объектов');
    }
}

function renderFacilities() {
    const container = document.getElementById('facilitiesList');
    container.innerHTML = facilities.map(facility => `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${facility.name}</h5>
                    <p class="card-text">
                        Часы работы: ${facility.working_hours.from} - ${facility.working_hours.to}<br>
                        Статус: ${facility.is_open ? 
                            '<span class="badge bg-success">Открыт</span>' : 
                            '<span class="badge bg-secondary">Закрыт</span>'}
                    </p>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" onclick="editFacility(${facility.id})">
                            Редактировать
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteFacility(${facility.id})">
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

async function saveFacility() {
    const facilityId = document.getElementById('facilityId').value;
    const data = {
        name: document.getElementById('name').value,
        working_hours: {
            from: document.getElementById('workingHoursFrom').value,
            to: document.getElementById('workingHoursTo').value
        },
        coordinates: {
            latitude: parseFloat(document.getElementById('latitude').value),
            longitude: parseFloat(document.getElementById('longitude').value)
        },
        accuracy_threshold: parseInt(document.getElementById('accuracyThreshold').value),
        comments: document.getElementById('comments').value
    };
    
    try {
        const url = facilityId ? `/api/facilities/${facilityId}` : '/api/facilities';
        const method = facilityId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('facilityModal')).hide();
            loadFacilities();
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error saving facility:', error);
        alert('Ошибка при сохранении объекта');
    }
}

// Загружаем объекты при загрузке страницы
loadFacilities();
</script> 