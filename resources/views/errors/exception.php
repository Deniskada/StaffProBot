<!DOCTYPE html>
<html>
<head>
    <title>Exception</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .error-container { max-width: 800px; margin: 0 auto; }
        .error-message { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; }
        .error-trace { background: #f8f9fa; padding: 15px; margin-top: 15px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Exception</h1>
        <div class="error-message">
            <?php 
            use Spbot\Core\Environment;
            if (Environment::get('APP_DEBUG', false)): 
            ?>
                <p><strong>Type:</strong> <?php echo get_class($e); ?></p>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                <p><strong>File:</strong> <?php echo htmlspecialchars($e->getFile()); ?></p>
                <p><strong>Line:</strong> <?php echo $e->getLine(); ?></p>
                <div class="error-trace">
                    <pre><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
                </div>
            <?php else: ?>
                <p>An error occurred. Please try again later.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 