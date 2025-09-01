<?php

/* Отладка */
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once 'config.php';

// Устанавливаем время жизни cookie сессии в браузере на 1 месяц
ini_set('session.cookie_lifetime', 2592000);

// Устанавливаем время жизни сессии на сервере на 1 месяц
ini_set('session.gc_maxlifetime', 2592000);

session_start();


require_once 'src/User.php';
require_once 'src/DBWorker.php';
$db = new DBWorker(DATABASE_PATH);
$user = new User($db, $_SESSION['uid'] ?? null);