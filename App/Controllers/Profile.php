<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

class Profile extends Authenticated {

  public function before(){
    parent::before();
    $this->user = Auth::getUser();
  }

  /*
  * Breadcrumb: (Profile/show)
  * Description: User has navigated to the profile page
  */
  public function showAction() {
    View::renderTemplate('Profile/show.html', [
      'user' => $this->user
    ]);
  }

  /*
  * Breadcrumb: Profile/show -> (Profile/edit)
  * Description: User has clicked on the edit profile page -> present the form
  */
  public function editAction(){
    View::renderTemplate('Profile/edit.html', [
      'user' => $this->user
    ]);
  }

  /*
  * Breadcrumb: Profile/show -> Profile/edit -> (Profile/update)
  * Description: User has made changes on the edit profile and hit submit
  */
  public function updateAction(){

    if($this->user->updateProfile($_POST)) {
      Flash::addMessage('Changes Saved');
      $this->redirect('/profile/show');
    }
    else {
      view::renderTemplate('Profile/edit.html', [
        'user' => $this->user
      ]);
    }

  }

}
