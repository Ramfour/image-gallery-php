<?php

namespace App\Core;

class Route
{
    public static function start(): void
    {
        // контроллер и действие по умолчанию
        $controllerName = 'Main';
        $actionName     = 'index';

        $routes = $_GET['url'] ?? '';

        // парсим URL: "auth/login" → controller=auth, action=login
        if (!empty($routes)) {
            $parts = explode('/', $routes);
            $controllerName = ucfirst($parts[0]);
            if (!empty($parts[1])) {
                $actionName = $parts[1];
            }
        }

        // формируем имена классов по PSR
        $controllerClass = 'App\\Controllers\\Controller' . $controllerName;
        $actionMethod    = 'action' . ucfirst($actionName);

        // проверяем существование класса и метода
        if (!class_exists($controllerClass)) {
            self::ErrorPage404();
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionMethod)) {
            self::ErrorPage404();
        }

        $controller->$actionMethod();
    }

    public static function ErrorPage404(): void
    {
        $host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
        header('Location: ' . $host . '404');
        exit;
    }
}
