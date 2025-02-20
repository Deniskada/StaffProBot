<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .error-container { max-width: 800px; margin: 0 auto; }
        .error-message { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Error</h1>
        <div class="error-message">
            <?php 
            use Spbot\Core\Environment;
            if (Environment::get('APP_DEBUG', false)): 
            ?>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($error['message']); ?></p>
                <?php if (isset($error['file'])): ?>
                    <p><strong>File:</strong> <?php echo htmlspecialchars($error['file']); ?></p>
                <?php endif; ?>
                <?php if (isset($error['line'])): ?>
                    <p><strong>Line:</strong> <?php echo htmlspecialchars($error['line']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>An error occurred. Please try again later.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 