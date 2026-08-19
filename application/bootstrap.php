<?php

session_start();

// Подключаем Composer автозагрузку
require_once __DIR__ . '/../vendor/autoload.php';

// Подключаем конфигурацию
require_once __DIR__ . '/../config.php';

// Подключаем БД (должен вернуть PDO)
$pdo = require_once __DIR__ . '/core/db.php';

// Запускаем маршрутизатор
\App\Core\Route::start();
