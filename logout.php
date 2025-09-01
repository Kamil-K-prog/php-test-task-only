<?php

require_once 'src/init.php';

$_SESSION = [];


// Удаляем сессионную куку у пользователя
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Удаляем файл сесии
session_destroy();

header('Location: /index.php');
exit;