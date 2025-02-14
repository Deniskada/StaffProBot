<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">Регистрация работодателя</h4>
                
                <form id="registerForm" method="POST" action="/register">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Подтверждение пароля</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="/login">Уже есть аккаунт? Войти</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (document.getElementById('password').value !== document.getElementById('password_confirm').value) {
        alert('Пароли не совпадают');
        return;
    }
    
    const formData = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };
    
    try {
        const response = await fetch('/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            window.location.href = '/verify-email?email=' + encodeURIComponent(formData.email);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Произошла ошибка при регистрации');
    }
});
</script> 