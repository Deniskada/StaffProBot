class ShiftManager {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        // Обработка формы создания смены
        const createForm = document.getElementById('create-shift-form');
        if (createForm) {
            createForm.addEventListener('submit', this.handleCreateShift.bind(this));
        }

        // Обработка кнопок управления сменой
        document.querySelectorAll('.shift-action').forEach(button => {
            button.addEventListener('click', this.handleShiftAction.bind(this));
        });
    }

    async handleCreateShift(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        
        try {
            const response = await fetch('/api/shifts', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                throw new Error('Ошибка создания смены');
            }
        } catch (error) {
            console.error(error);
            alert('Произошла ошибка при создании смены');
        }
    }

    async handleShiftAction(event) {
        const button = event.target;
        const action = button.dataset.action;
        const shiftId = button.dataset.shiftId;
        
        try {
            const response = await fetch(`/api/shifts/${shiftId}/${action}`, {
                method: 'POST'
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                throw new Error(`Ошибка выполнения действия ${action}`);
            }
        } catch (error) {
            console.error(error);
            alert('Произошла ошибка при выполнении действия');
        }
    }
} 