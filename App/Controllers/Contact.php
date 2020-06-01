<?php

namespace App\Controllers;

use \Core\View;

class Contact extends \Core\Controller
{

    protected function before()
    {
        // You can use this function to run code before
    }

    protected function after()
    {
        // You can use this function to run code after
    }

    public function indexAction()
    {
        View::renderTemplate('Contact/index.html');
    }

}
