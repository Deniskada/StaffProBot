console.log('Auth.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    console.log('Login form found:', loginForm);
    if (!loginForm) return;

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        // Очищаем предыдущие ошибки
        document.querySelectorAll('.alert').forEach(el => el.remove());
        
        const formData = new FormData(this);
        console.log('Form data:', Object.fromEntries(formData));
        
        fetch('/login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.error || 'Ошибка сервера');
                }
                return data;
            });
        })
        .then(data => {
            console.log('Success:', data);
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = error.message || 'Произошла ошибка при входе';
            loginForm.insertBefore(errorDiv, loginForm.firstChild);
        });
    });
}); 