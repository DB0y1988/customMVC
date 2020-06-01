<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

class Password extends \Core\Controller
{


  /*
  * Breadcrumb: Password/Forgot
  * Action: User has clicked the forgotten password link
  */
  public function forgotAction(){
    View::renderTemplate('Password/forgot.html');
  }

  /*
  * Breadcrumb: Password/Forgot -> (Password/Request-Reset)
  * Action:
  */
  public function requestResetAction() {

    // Get the users email from the form
    User::sendPasswordReset($_POST['email']);

    // Display a message that email has been sent
    View::renderTemplate('Password/reset_requested.html');

  }


  /*
  * Breadcrumb: (Password/reset)
  * Action: User clicks the reset password link in the email
  */
  public function resetAction(){

    // User clicks email link
    $token = $this->route_params['token'];

    // Get the user by the token
    $user = $this->getUserOrExit($token);

    // If the user is found, show the password reset form
    if($user) {
      View::renderTemplate('Password/reset.html',[
        'token' => $token
      ]);
    }
    else {
      echo 'Password reset token invalid';
    }

  }

  /*
  * Breadcrumb: (Password/resetPassword)
  * Action: The user has filled in the password reset form
  */
  public function ResetPasswordAction() {

    // Get the token from the hidden input field in the reset form
    $token = $_POST['token'];

    // Gets the user by the token - Checking the token is correct
    $user = $this->getUserOrExit($token);

    if($user->resetPassword($_POST['password'])) {
      View::renderTemplate('Password/reset_success.html');
    }
    else {
      View::renderTemplate('Password/reset.html', [
        'token' => $token,
        'user' => $user
      ]);
    }

  }

  /*
  * Breadcrumb: Generic
  * Action: User clicks the reset password link in the email
  */
  protected function getUserOrExit($token){

    // Get the user by the token
    $user = User::findByPasswordReset($token);

    if($user) {
      return $user;
    }
    else {
      View::renderTemplate('Password/token_expired.html');
      exit;
    }

  }

}
