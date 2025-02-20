<div class="row">
    <!-- Основные настройки -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Основные настройки</h5>
                <form id="generalSettingsForm">
                    <div class="mb-3">
                        <label class="form-label">Название системы</label>
                        <input type="text" class="form-control" id="appName" name="app_name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL системы</label>
                        <input type="url" class="form-control" id="appUrl" name="app_url">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Часовой пояс по умолчанию</label>
                        <select class="form-select" id="timezone" name="timezone">
                            <option value="Europe/Moscow">Москва (UTC+3)</option>
                            <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                            <!-- Другие часовые пояса -->
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Настройки почты -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Настройки почты</h5>
                <form id="emailSettingsForm">
                    <div class="mb-3">
                        <label class="form-label">SMTP Сервер</label>
                        <input type="text" class="form-control" id="smtpHost" name="smtp_host">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">SMTP Порт</label>
                        <input type="number" class="form-control" id="smtpPort" name="smtp_port">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email отправителя</label>
                        <input type="email" class="form-control" id="smtpUser" name="smtp_user">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="smtpPass" name="smtp_pass">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-outline-primary" onclick="testEmail()">
                        Тест
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Настройки Telegram -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Настройки Telegram</h5>
                <form id="telegramSettingsForm">
                    <div class="mb-3">
                        <label class="form-label">Токен бота</label>
                        <input type="text" class="form-control" id="telegramToken" name="telegram_token">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <div class="input-group">
                            <input type="url" class="form-control" id="webhookUrl" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                                Копировать
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setWebhook()">
                        Установить webhook
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Системные логи -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Системные логи</h5>
                <div class="mb-3">
                    <select class="form-select" id="logType">
                        <option value="system">Системные</option>
                        <option value="error">Ошибки</option>
                        <option value="auth">Авторизация</option>
                    </select>
                </div>
                <div class="log-container bg-light p-3" style="height: 300px; overflow-y: auto;">
                    <pre id="logContent" class="mb-0"></pre>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="refreshLogs()">
                        Обновить
                    </button>
                    <button class="btn btn-outline-primary" onclick="downloadLogs()">
                        Скачать
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadSettings() {
    try {
        const response = await fetch('/api/admin/settings', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const settings = await response.json();
        
        // Заполняем формы
        document.getElementById('appName').value = settings.app_name;
        document.getElementById('appUrl').value = settings.app_url;
        document.getElementById('timezone').value = settings.timezone;
        document.getElementById('smtpHost').value = settings.smtp_host;
        document.getElementById('smtpPort').value = settings.smtp_port;
        document.getElementById('smtpUser').value = settings.smtp_user;
        document.getElementById('telegramToken').value = settings.telegram_token;
        document.getElementById('webhookUrl').value = settings.webhook_url;
    } catch (error) {
        console.error('Error loading settings:', error);
        alert('Ошибка при загрузке настроек');
    }
}

async function saveSettings(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/admin/settings', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        if (response.ok) {
            alert('Настройки сохранены');
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        alert('Ошибка при сохранении настроек');
    }
}

async function testEmail() {
    try {
        const response = await fetch('/api/admin/settings/test-email', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        if (response.ok) {
            alert('Тестовое письмо отправлено');
        } else {
            const error = await response.json();
            alert(error.message);
        }
    } catch (error) {
        console.error('Error testing email:', error);
        alert('Ошибка при отправке тестового письма');
    }
}

async function refreshLogs() {
    const logType = document.getElementById('logType').value;
    
    try {
        const response = await fetch(`/api/admin/logs/${logType}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const logs = await response.json();
        document.getElementById('logContent').textContent = logs.join('\n');
    } catch (error) {
        console.error('Error loading logs:', error);
        alert('Ошибка при загрузке логов');
    }
}

// Инициализация страницы
loadSettings();
refreshLogs();

// Обработчики форм
document.getElementById('generalSettingsForm').addEventListener('submit', (e) => {
    e.preventDefault();
    saveSettings('generalSettingsForm');
});

document.getElementById('emailSettingsForm').addEventListener('submit', (e) => {
    e.preventDefault();
    saveSettings('emailSettingsForm');
});

document.getElementById('telegramSettingsForm').addEventListener('submit', (e) => {
    e.preventDefault();
    saveSettings('telegramSettingsForm');
});
</script> 