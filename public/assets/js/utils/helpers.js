// Форматирование даты
function formatDate(date, format = 'DD.MM.YYYY') {
    const d = new Date(date);
    return format
        .replace('DD', String(d.getDate()).padStart(2, '0'))
        .replace('MM', String(d.getMonth() + 1).padStart(2, '0'))
        .replace('YYYY', d.getFullYear());
}

// Форматирование валюты
function formatCurrency(amount, currency = 'RUB') {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Валидация форм
function validateForm(form) {
    const errors = [];
    const required = form.querySelectorAll('[required]');
    
    required.forEach(field => {
        if (!field.value.trim()) {
            errors.push(`Поле ${field.name} обязательно для заполнения`);
        }
    });
    
    return errors;
}

// Обработка ошибок API
function handleApiError(error) {
    console.error('API Error:', error);
    const message = error.response?.data?.message || 'Произошла ошибка';
    alert(message);
} 