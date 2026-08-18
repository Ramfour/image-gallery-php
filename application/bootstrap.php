<?php
session_start();
require_once __DIR__ . '/../config.php';
$pdo = require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/View.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/route.php';
Route::start(); // запускаем маршрутизатор
