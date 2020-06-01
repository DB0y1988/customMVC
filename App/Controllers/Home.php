<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\recipePosts;
use \App\Models\homepagePosts;
use \App\Flash;
use \App\Models\themeOptionPosts;

class Home extends \Core\Controller {

  public $postPerPage = 10;

  public function indexAction() {

    $allRecipes = $this->getFeaturedRecipePosts();

    $slides = homepagePosts::getAllPosts("hp_home_slider");

    $widgetOne = homepagePosts::getPostsById('1', 'hp_widgets');

    $recentPost = homepagePosts::getRecentPost();
    $recentPost[0]['introduction'] = strip_tags($recentPost[0]['introduction']);

    if($widgetOne[0]['widget_title'] == "Not set") {
      $recipes = array_slice($allRecipes, "0", "3");
    }
    else {
      $recipes = array_slice($allRecipes, "0", "2");
    }

      View::renderTemplate('Home/index.html', [
        'featuredRecipes' => $recipes,
        'slider' => $slides,
        'uri' => $_SERVER['REQUEST_URI'],
        'widget_one' => $widgetOne,
        'recent_posts' => $recentPost
      ]);
  }

  public function homepageSliderAction() {

    if(!empty($_GET['page'])) {
      $currentPage = (int)$_GET['page'];
    }

    $totalItems = homepagePosts::countRecords("hp_home_slider");
    $totalItems = $totalItems[0]['count(*)'];
    $offset = static::setPagintationOffset($currentPage, $this->postPerPage);
    $previousLink = $currentPage - 1;

    $slides = homepagePosts::getAllPostsWithPagnintation("hp_home_slider", $this->postPerPage, $offset);

    $totalPages = $this->createPagintation($this->postPerPage, $totalItems);
    $totalPageCount = count($totalPages);

    View::renderTemplate('Home/homepage-slider.html', [
      'slider' => $slides,
      'total_pages' => $totalPages,
      'current_page' => $currentPage,
      'total_page_count' => $totalPageCount,
      'previous_link' => $previousLink
    ]);

    }

    public function newHomepageSlideAction() {

      // Get all images from the media library
      $images[] = homepagePosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

      View::renderTemplate('Home/new-slide.html', [
        'medialibrary' => $images[0]
      ]);

    }

    public function editSlideAction(){

      if(isset($this->route_params['id'])) {

        $id = $this->route_params['id'];

        // Get the slider posts
        $slide = homepagePosts::getPostsById($id, "hp_home_slider");

        $slideTitle = $slide[0]['slider_title'];
        $sliderImage = $slide[0]['slider_image'];
        $sliderContent = $slide[0]['slider_content'];

        // Get all images from the media library
        $images[] = homepagePosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

        View::renderTemplate('/Home/edit-slider.html', [
          'slide_title' => $slideTitle,
          'slide_image' => $sliderImage,
          'slider_content' => $sliderContent,
          'slider_id' => $id,
          'medialibrary' => $images[0]
        ]);

      }
      else {
        $this->redirect('/home/homepage-slider');
      }

    }

    public function updateAction() {

      if(!empty($_POST['id'])) {
        $id = $_POST['id'];
      }

      $slider = new homepagePosts();

      // If there is a featured image, lets deal with that
      if(!empty($_FILES['slider_image']['name'])) {
        $bool = $slider->setAndUploadImage($_FILES['slider_image']);
      }

      $slideSaved = $slider->saveSlide();

      if($slideSaved) {
        if(!empty($id)) {
          Flash::addMessage('Slide updated');
          $this->redirect("/home/$id/edit-slide");
        }
        else {
          Flash::addMessage('New slide added');
          $this->redirect('/home/homepage-slider');
        }
      }
      else {
        Flash::addMessage('Update failed', 'warning');
        if(!empty($id)) {
          $this->redirect("/home/$id/edit-slide");
        }
        else {
          $this->redirect('/home/homepage-slider');
        }
      }
    }

    public function getFeaturedRecipePosts(){

      return recipePosts::getAllFeaturedRecipePosts();

    }

    public function deleteAction() {
      if(!empty($this->route_params['id'])) {
        $bool = recipePosts::deletePost($this->route_params['id'], "hp_home_slider");
      }
      if($bool) {
        Flash::addMessage('The slide has been deleted');
      }
      else {
        Flash::addMessage('Could not delete slide', 'warning');
      }
      $this->redirect('/home/homepage-slider
      ');
    }

}
