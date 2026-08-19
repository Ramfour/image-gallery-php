<?php

namespace App\Controllers;

use App\Core\Controller;

class ControllerMain extends Controller
{
    public function actionIndex(): void
    {
        $this->view->generate('main_view.php', 'template_view.php');
    }
}
