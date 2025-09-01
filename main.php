<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Мой сайт</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Навбар -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">Мой сайт</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Главная</a>
                </li>

                <?php if ($user->isLogged()): ?>
                    <!-- Ссылки для авторизованных пользователей -->
                    <!-- Небольшой костыль: чтобы это не падало, нужно из любого файла начинать запрашивать init.php -->
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">Моя страница</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php">Выйти</a>
                    </li>
                <?php else: ?>
                    <!-- Ссылки для гостей -->
                    <li class="nav-item">
                        <a class="nav-link" href="/register.php">Зарегистрироваться</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">Войти</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- Контент -->
<main class="container my-4">
    <?php
    // Здесь PHP будет подставлять разные страницы
    if (!empty($content)) {
        echo $content;
    } else {
        echo "<h1>Добро пожаловать!</h1>";
    }
    ?>
</main>

<!-- Bootstrap JS (только для навбара) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
