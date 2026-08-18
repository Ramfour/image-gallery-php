<?php
class Controller
{
    public $model;
    public $view;
    protected $pdo;

    function __construct()
    {
        $this->view = new View();
        $this->pdo = $GLOBALS['pdo'];
    }

    protected function requireAuth()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /?url=auth');
            exit;
        }
    }
}
