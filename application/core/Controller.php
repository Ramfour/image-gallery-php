<?php

namespace App\Core;

class Controller
{
    public $model;
    public $view;
    protected $pdo;

    public function __construct()
    {
        $this->view = new View();
        $this->pdo = $GLOBALS['pdo'];
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /?url=auth');
            exit;
        }
    }
}
