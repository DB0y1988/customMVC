<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

class Login extends \Core\Controller {

  /*
    * Breadcrumb: (Login/New)
    * Description: User has navigated to the login page
    * Action: Shows the login form
    */
  public function newAction() {
    View::renderTemplate('Login/new.html');
  }

  /*
    * Breadcrumb: Login/New -> (Login/Create)
    * Action: Login the user if the user is found / authenticated or re-display the login form if not with a flash error message
    */
  public function createAction() {

    // Authenticate the user from the submitted details
    $user = User::authenticate($_POST['email'], $_POST['password']);

    // Check if the user has ticked the remember me checkbox
    $remember_me = isset($_POST['remember_me']);

    // If the user has been found
    if($user) {

      Auth::login($user, $remember_me);

      Flash::addMessage('Login successful');

      $this->redirect("/Admin/index");

    }
    else {

      Flash::addMessage('Login unsuccessful, please try again', FLASH::WARNING);

      View::renderTemplate('Login/new.html', [
      'email' => $_POST['email'],
      'remember_me' => $remember_me
      ]);

    }
  }

  public function destroyAction() {

    Auth::logout();

    $this->redirect('/login/show-logout-message');

  }

  public function showLogoutMessageAction(){

    Flash::addMessage('Logout successful');

    $this->redirect('/');



  }

}
