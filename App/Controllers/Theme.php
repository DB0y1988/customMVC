<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\pagesPosts;
use \App\Models\themeOptionPosts;
use \App\Flash;

class Theme extends Authenticated {

  public function optionsAction() {

    $pages = pagesPosts::getAllPosts("hp_pages");
    $links = themeOptionPosts::getAllPosts("hp_theme_options");
    $widgetOne = themeOptionPosts::getPostsById('1', 'hp_widgets');

    View::renderTemplate('ThemeOptions/index.html', [
      'pages' => $pages,
      'facebook' => $links[0]['facebook_link'],
      'instagram' => $links[0]['instagram_link'],
      'widget_one' => $widgetOne[0]['widget_title']
    ]);

  }

  public function updateWidgetOptions(){

    $widget = themeOptionPosts::setWidget();

    if($widget) {
      Flash::addMessage('Widget settings updated');
      $this->redirect('/theme/options');
    }
    else {
      Flash::addMessage('Update failed', 'warning');
      $this->redirect('/theme/options');
    }

  }

  public function updateSocialOptionsAction() {

    $update = themeOptionPosts::updateSocialLinks();

    if($update) {
      Flash::addMessage('Social links updated');
      $this->redirect('/theme/options');
    }
    else {
      Flash::addMessage('Update failed', 'warning');
      $this->redirect('/theme/options');
    }

  }

}
