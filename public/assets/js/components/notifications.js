class NotificationManager {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        document.querySelectorAll('.mark-read').forEach(button => {
            button.addEventListener('click', this.handleMarkAsRead.bind(this));
        });

        document.getElementById('mark-all-read')?.addEventListener('click', 
            this.handleMarkAllAsRead.bind(this)
        );
    }

    async handleMarkAsRead(event) {
        const notificationId = event.target.dataset.id;
        
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST'
            });
            
            if (response.ok) {
                event.target.closest('.notification-item').classList.add('read');
            } else {
                throw new Error('Ошибка отметки уведомления');
            }
        } catch (error) {
            console.error(error);
            alert('Ошибка при обработке уведомления');
        }
    }

    async handleMarkAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST'
            });
            
            if (response.ok) {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.add('read');
                });
            } else {
                throw new Error('Ошибка отметки уведомлений');
            }
        } catch (error) {
            console.error(error);
            alert('Ошибка при обработке уведомлений');
        }
    }
} 