<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Панель управления' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link href="/assets/css/admin.css" rel="stylesheet">

    <!-- Debug info -->
    <?php if (getenv('APP_DEBUG')): ?>
        <div class="debug-info" style="display:none">
            <pre><?= htmlspecialchars(print_r(get_defined_vars(), true)) ?></pre>
        </div>
    <?php endif; ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <nav class="admin-nav">
                <?php foreach ($admin_sidebar as $item): ?>
                <a href="<?= $item['url'] ?>" class="nav-item <?= $_SERVER['REQUEST_URI'] === $item['url'] ? 'active' : '' ?>">
                    <?= $item['title'] ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                    </button>
                    
                    <div class="d-flex">
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                                <?= $user['first_name'] ?> <?= $user['last_name'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile">Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">Выход</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Flash Messages -->
            <?php if (!empty($flash['success'])): ?>
                <div class="alert alert-success"><?= $flash['success'] ?></div>
            <?php endif; ?>
            
            <?php if (!empty($flash['error'])): ?>
                <div class="alert alert-danger"><?= $flash['error'] ?></div>
            <?php endif; ?>

            <!-- Main Content -->
            <main class="p-4">
                <?php if (!empty($content)): ?>
                    <?= $content ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Контент не загружен
                        <?php if (isset($error)): ?>
                            <br>Причина: <?= htmlspecialchars($error) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Admin JS -->
    <script src="/js/admin.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html> 