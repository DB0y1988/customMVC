<?php

namespace App\Controllers;

use \App\Auth;
use \Core\View;
use \App\Flash;
use \App\Models\pagesPosts;

class Pages extends \Core\Controller {

  public $postPerPage = 10;

  public function indexAction() {

    if(!empty($_GET['page'])) {
      $currentPage = (int)$_GET['page'];
    }

    $totalItems = pagesPosts::countRecords("hp_pages");
    $totalItems = $totalItems[0]['count(*)'];
    $offset = static::setPagintationOffset($currentPage, $this->postPerPage);
    $previousLink = $currentPage - 1;

    $pages = pagesPosts::getAllPostsWithPagnintation("hp_pages", $this->postPerPage, $offset);

    $totalPages = $this->createPagintation($this->postPerPage, $totalItems);
    $totalPageCount = count($totalPages);

    View::renderTemplate('/Pages/index.html', [
      'pages' => $pages,
      'total_pages' => $totalPages,
      'current_page' => $currentPage,
      'total_page_count' => $totalPageCount,
      'previous_link' => $previousLink
    ]);
  }

  public function pageAction() {

    $slug = $this->route_params['slug'];

    $pages = pagesPosts::getPageBySlug($slug);

    $template = $pages[0]['page_template'];

    View::renderTemplate('/Pages/Templates/' . $template, [
      'pages' => $pages,
    ]);

  }

  public function editPageAction() {

    // Get the ID from the route params
    if(isset($this->route_params['id'])) {

      $id = $this->route_params['id'];

      $page = pagesPosts::getPostsById($id, "hp_pages");

      $templates = $this->getAllTemplates();
      $allPages = static::getAllPageTitles();

      unset($templates[0][0]);
      unset($templates[0][1]);

      $title = $page[0]['title'];
      $content = $page[0]['content'];
      $featuredImage = $page[0]['featured_image'];
      $excerpt = strip_tags($page[0]['excerpt']);

      // The current page settings
      $currentTemplate = $templates[0];
      $currentParent = $page[0]["page_parent"];

      // Get all images from the media library
      $images[] = pagesPosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

      View::renderTemplate('/Pages/edit.html', [
        'title' => $title,
        'content' => $content,
        'featured_image' => $featuredImage,
        'excerpt' => $excerpt,
        'medialibrary' => $images[0],
        'id' => $id,
        'current_template' => $currentTemplate[2],
        'current_parent' => $currentParent,
        'pages' => $allPages,
        'templates' => $templates[0],
        'in_menu' => $page[0]['in_menu']
      ]);

    }
    else {
      $this->redirect('/pages/index');
    }
  }

  public function newPageAction() {

    // Get all images from the media library
    $images[] = pagesPosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

    $templates = $this->getAllTemplates();
    $pages = static::getAllPageTitles();

    if(empty($pages)) {
      $pages[0]['title'] = "No Parent";
    }

    // Remove the first 2 values in the templates array
    unset($templates[0][0]);
    unset($templates[0][1]);

    View::renderTemplate('/Pages/new-page.html', [
      'medialibrary' => $images[0],
      'templates' => $templates[0],
      'pages' => $pages[0]
    ]);

  }

  public function getAllTemplates() {

    $dir =  "../App/Views/Pages/Templates/";

    // Open a directory, and read its contents
    if (is_dir($dir)){
      $files[] = scandir($dir);
    }
    return $files;
  }

  public function getAllPageTitles() {

    return pagesPosts::getPageTitles();

  }

  public function deletePageAction(){

    // Get the ID from the route params
    if(isset($this->route_params['id'])) {

      $id = $this->route_params['id'];

      $delete = pagesPosts::deletePost($id, "hp_pages");

      if($delete) {
        Flash::addMessage('Page deleted');
      }
      else {
        Flash::addMessage('Page could not be deleted');
      }
      }
      $this->redirect('/pages/index?page=1');
    }

  public function updateAction() {

    if(!empty($_POST['id'])) {
      $id = $_POST['id'];
    }

    $posts = new pagesPosts();

    $postSaved = $posts->savePost();

    if($postSaved) {
      if(!empty($id)) {
        Flash::addMessage('Page updated');
        $this->redirect("/pages/$id/edit-page");
      }
      else {
        Flash::addMessage('New page added');
        $this->redirect('/pages/index?page=1');
      }
    }
    else {
      Flash::addMessage('Update failed', 'warning');
      if(!empty($id)) {
        $this->redirect("/pages/$id/edit-page");
      }
      else {
        $this->redirect('/pages/index?page=1');
      }
    }
  }

}
