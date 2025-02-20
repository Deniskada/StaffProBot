<div class="row">
    <!-- Информация о системе -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Информация о системе</h5>
                
                <div class="system-info">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Версия системы:</span>
                        <strong id="currentVersion">1.0.0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Дата установки:</span>
                        <strong id="installDate">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Последнее обновление:</span>
                        <strong id="lastUpdate">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>PHP версия:</span>
                        <strong id="phpVersion">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>MySQL версия:</span>
                        <strong id="mysqlVersion">-</strong>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" onclick="checkUpdates()">
                        Проверить обновления
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Лицензия -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Лицензия</h5>
                
                <div id="licenseInfo">
                    <div class="alert alert-success mb-4" id="licenseStatus">
                        Лицензия активна до: <strong>31.12.2024</strong>
                    </div>
                    
                    <div class="license-details">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Тип лицензии:</span>
                            <strong id="licenseType">Коммерческая</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Владелец:</span>
                            <strong id="licenseOwner">ООО "Компания"</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Домены:</span>
                            <strong id="licenseDomains">staffprobot.ru</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Активные установки:</span>
                            <strong id="licenseInstalls">1 из 1</strong>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="showActivationModal()">
                            Активировать лицензию
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- История обновлений -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">История обновлений</h5>
                
                <div class="timeline" id="updatesTimeline">
                    <!-- Будет заполнено через JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно активации -->
<div class="modal fade" id="activationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Активация лицензии</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="activationForm">
                    <div class="mb-3">
                        <label class="form-label">Лицензионный ключ</label>
                        <input type="text" class="form-control" id="licenseKey" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email покупателя</label>
                        <input type="email" class="form-control" id="purchaseEmail" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="activateLicense()">Активировать</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно обновления -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Доступно обновление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="update-info mb-4">
                    <h6>Версия 1.1.0</h6>
                    <div class="update-changes">
                        <!-- Список изменений будет добавлен через JavaScript -->
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="backupBeforeUpdate" checked>
                    <label class="form-check-label">
                        Создать резервную копию перед обновлением
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="startUpdate()">
                    Установить обновление
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let activationModal = null;
let updateModal = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadSystemInfo();
    loadLicenseInfo();
    loadUpdatesHistory();
    
    activationModal = new bootstrap.Modal(document.getElementById('activationModal'));
    updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
});

async function loadSystemInfo() {
    try {
        const response = await fetch('/api/admin/system/info', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const info = await response.json();
        
        document.getElementById('currentVersion').textContent = info.version;
        document.getElementById('installDate').textContent = new Date(info.install_date).toLocaleDateString();
        document.getElementById('lastUpdate').textContent = info.last_update ? 
            new Date(info.last_update).toLocaleDateString() : '-';
        document.getElementById('phpVersion').textContent = info.php_version;
        document.getElementById('mysqlVersion').textContent = info.mysql_version;
    } catch (error) {
        console.error('Error loading system info:', error);
    }
}

async function checkUpdates() {
    try {
        const response = await fetch('/api/admin/system/check-updates', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const update = await response.json();
        
        if (update.available) {
            showUpdateInfo(update);
        } else {
            alert('У вас установлена последняя версия системы');
        }
    } catch (error) {
        console.error('Error checking updates:', error);
        alert('Ошибка при проверке обновлений');
    }
}

function showUpdateInfo(update) {
    const changes = document.querySelector('.update-changes');
    changes.innerHTML = `
        <div class="mt-3">
            <h6>Новые функции:</h6>
            <ul>
                ${update.features.map(f => `<li>${f}</li>`).join('')}
            </ul>
        </div>
        <div class="mt-3">
            <h6>Исправления:</h6>
            <ul>
                ${update.fixes.map(f => `<li>${f}</li>`).join('')}
            </ul>
        </div>
    `;
    
    updateModal.show();
}

async function startUpdate() {
    const backup = document.getElementById('backupBeforeUpdate').checked;
    
    try {
        const response = await fetch('/api/admin/system/update', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ backup })
        });
        
        if (response.ok) {
            updateModal.hide();
            alert('Система успешно обновлена');
            location.reload();
        } else {
            throw new Error('Update failed');
        }
    } catch (error) {
        console.error('Error updating system:', error);
        alert('Ошибка при обновлении системы');
    }
}

function showActivationModal() {
    activationModal.show();
}

async function activateLicense() {
    const data = {
        key: document.getElementById('licenseKey').value,
        email: document.getElementById('purchaseEmail').value
    };
    
    try {
        const response = await fetch('/api/admin/system/activate', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            activationModal.hide();
            loadLicenseInfo();
            alert('Лицензия успешно активирована');
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error activating license:', error);
        alert('Ошибка при активации лицензии');
    }
}
</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    padding-left: 70px;
    padding-bottom: 30px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 44px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #0d6efd;
}

.update-changes ul {
    padding-left: 20px;
    margin-bottom: 0;
}
</style> 