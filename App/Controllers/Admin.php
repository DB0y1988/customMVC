<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\recipePosts;
use \App\Flash;

class Admin extends Authenticated {

  public $postPerPage = 6;

  /*
    * Breadcrumb: (Admin/Index)
    * Description: User has logged navigated to the admin page
    * Action: Presents the login form if user is not logged in or tha admin homepage if the user is loggeed in
    */
  public function indexAction() {

    View::renderTemplate('Admin/index.html');

  }

  public function mediaLibraryAction() {

    if(!empty($_GET['page'])) {
      $currentPage = (int)$_GET['page'];
      $offset = (int)$_GET['offset'];
    }

    $images[] = recipePosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

    $totalItems = count($images[0]); // 8

    switch($currentPage) {
      case "1":
        $images[0] = array_slice($images[0], 0, $this->postPerPage);
        break;
      case $currentPage:
        $images[0] = array_slice($images[0], $offset, $this->postPerPage);
        break;
      default:
        $images[0] = array_slice($images[0], 0, $this->postPerPage);
    }

    $totalPages = $this->createPagintation($this->postPerPage, $totalItems);

    $totalPageCount = count($totalPages);
    $previousLink = $currentPage - 1;
    $nextLink = $currentPage * 6;
    $previousOffset = $offset - 6;

    View::renderTemplate('Admin/media-library.html', [
      'medialibrary' => $images[0],
      'total_pages' => $totalPages,
      'current_page' => $currentPage,
      'previous_link' => $previousLink,
      'total_page_count' => $totalPageCount,
      'offset' => '0',
      'next_link' => $nextLink,
      'previous_offset' => $previousOffset
    ]);

  }

  public function mediaUploadAction(){
    View::renderTemplate('Admin/media-upload.html');
  }

  public function uploadMediaImageAction(){

    if(!empty($_FILES['media-image'])) {
      // Just use the recipe posts controller
      $images = new recipePosts();

      foreach($_FILES as $mediaImage) {
        $bool[] = $images->setAndUploadImage($mediaImage);
      }

      Flash::addMessage($bool[0]);
      $this->redirect('/admin/media-library?page=1&offset=0');

    }

  }

}
