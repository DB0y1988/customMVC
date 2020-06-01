<?php

// Use namespace so you can create duplicate class names
// Namespace is like a folder or directory
// To use the class in the namespace, add the namespace before calling the get_declared_class

namespace App\Controllers;
use \Core\View;
use \App\Models\User;

// You can say -> use App\Controllers\Admin; to enable the current namespace

class Signup extends \Core\Controller {

    protected function before() {
        // Make sure an admin user is logged in for example
    }

    /*
    * Breadcrumb: (Signup/New)
    * Description: User has navigated to the signup page
    * Action: Show the signup form template
    */
    public function newAction() {
      View::renderTemplate('Signup/new.html');
    }

    /*
    * Breadcrumb: Signup/New -> (Signup/Create)
    * Description: User has submitted the signup form
    * Action: Create a new user in the database
    */
    public function createAction() {

      // Collect Name, Email, Password from the form and store in the object
      $user = new User($_POST);

      if($user->save()) {

        $user->sendActivationEmail();

      $this->redirect('/signup/success');

    }

    else {
      View::renderTemplate('Signup/new.html', [
        'user' => $user
      ]);
    }

    }

    /*
    * Breadcrumb: Signup/New -> Signup/Create -> Signup/Success
    * Description: User has been created and success page is being shown, user can now login
    * Action: Redirects to the success page
    */
    public function successAction() {

      View::renderTemplate('Signup/success.html');

    }

    /*
    * Breadcrumb: (Signup/activate)
    * Action: User clicks the reset password link in the email
    */
    public function activateAction(){

      // User clicks email link so lets activate if token is right
      User::activate($this->route_params['token']);

      $this->redirect('/signup/activated');

    }

    /*
    * Breadcrumb: Signup/activate -> Signup/activated
    * Action: User clicks the reset password link in the email
    */
    public function activatedAction(){

      View::renderTemplate('Signup/activated.html');

    }

}
