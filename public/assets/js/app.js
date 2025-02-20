// Импорт компонентов и утилит
import { ShiftManager } from './components/shifts.js';
import { ProfileManager } from './components/profile.js';
import { NotificationManager } from './components/notifications.js';
import * as helpers from './utils/helpers.js';

// Инициализация компонентов
document.addEventListener('DOMContentLoaded', () => {
    // Инициализация компонентов
    if (document.querySelector('.shifts-container')) {
        new ShiftManager();
    }
    
    if (document.querySelector('.profile-container')) {
        new ProfileManager();
    }
    
    if (document.querySelector('.notifications-container')) {
        new NotificationManager();
    }

    // Обработка уведомлений
    function checkNotifications() {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notifications-count');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(helpers.handleApiError);
    }

    // Инициализация всплывающих подсказок Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // Автоматическое скрытие алертов
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });

    // Проверка уведомлений каждые 30 секунд
    checkNotifications();
    setInterval(checkNotifications, 30000);
}); 