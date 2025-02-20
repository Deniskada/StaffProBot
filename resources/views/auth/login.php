<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Login</h1>
        
        <?php if (isset($flash['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($flash['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($flash['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($flash['success']); ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($flash['old']['email']) ? htmlspecialchars($flash['old']['email']) : ''; ?>">
                <?php if (isset($flash['errors']['email'])): ?>
                    <div class="error">
                        <?php 
                        if (is_array($flash['errors']['email'])) {
                            echo htmlspecialchars(implode(', ', $flash['errors']['email']));
                        } else {
                            echo htmlspecialchars($flash['errors']['email']);
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($flash['errors']['password'])): ?>
                    <div class="error">
                        <?php echo htmlspecialchars(is_array($flash['errors']['password']) ? implode(', ', $flash['errors']['password']) : $flash['errors']['password']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <div class="auth-links">
            <a href="/register">Register</a> |
            <a href="/password/reset">Forgot Password?</a>
        </div>
    </div>
    
    <script src="/assets/js/auth.js"></script>
</body>
</html> 