<?php
class Route
{
	public static function start()
	{
		// контроллер и действие по умолчанию
		$controller_name = 'Main';
		$action_name     = 'index';

		$routes = $_GET['url'] ?? '';

		// парсим URL: "auth/login" → controller=auth, action=login
		if (!empty($routes)) {
			$parts = explode('/', $routes);
			$controller_name = $parts[0];
			if (!empty($parts[1])) {
				$action_name = $parts[1];
			}
		}

		// добавляем префиксы
		$model_name      = 'model_' . $controller_name;
		$controller_name = 'controller_' . $controller_name;
		$action_name     = 'action_' . $action_name;

		// подцепляем файл с классом модели (файла модели может и не быть)
		$model_file = strtolower($model_name) . '.php';
		$model_path = $_SERVER['DOCUMENT_ROOT'] . '/application/models/' . $model_file;
		if (file_exists($model_path)) {
			include $model_path;
		}

		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name) . '.php';
		$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/application/controllers/' . $controller_file;
		if (file_exists($controller_path)) {
			include $controller_path;
		} else {
			Route::ErrorPage404();
		}

		// создаём контроллер и вызываем action
		$controller = new $controller_name;
		$action     = $action_name;
		if (method_exists($controller, $action)) {
			$controller->$action();
		} else {
			Route::ErrorPage404();
		}
	}

	public static function ErrorPage404()
	{
		$host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		header('Location: ' . $host . '404');
		exit;
	}
}