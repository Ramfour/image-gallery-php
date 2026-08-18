<?php
class Controller
{
    public $model;
    public $view;
    protected $pdo;

    function __construct()
    {
        $this->view = new View();
        $this->pdo = require_once $_SERVER['DOCUMENT_ROOT'] . '/application/core/db.php';
    }

    protected function requireAuth()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /?url=auth');
            exit;
        }
    }
}
