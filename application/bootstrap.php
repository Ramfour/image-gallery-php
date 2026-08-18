<?php
session_start();
require_once __DIR__ . '/../config.php';
$pdo = require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/model.php';
require_once __DIR__ . '/core/view.php';
require_once __DIR__ . '/core/controller.php';
require_once __DIR__ . '/core/route.php';
Route::start(); // запускаем маршрутизатор
