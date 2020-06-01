<?php

namespace App\Controllers;

use \App\Auth;
use \Core\View;
use \App\Models\recipePosts;
use \App\Flash;

class Recipes extends \Core\Controller {

    public $postPerPage = 10;

  public function list() {

    if(!empty($_GET['page'])) {
      $currentPage = (int)$_GET['page'];
    }

    $totalItems = recipePosts::countRecords("recipes");
    $totalItems = $totalItems[0]['count(*)'];
    $offset = static::setPagintationOffset($currentPage, $this->postPerPage);
    $previousLink = $currentPage - 1;

    $posts = recipePosts::getAllPostsWithPagnintation("recipes", $this->postPerPage, $offset);

    $totalPages = $this->createPagintation($this->postPerPage, $totalItems);
    $totalPageCount = count($totalPages);

    View::renderTemplate('/Recipes/list.html', [
      'posts' => $posts,
      'total_pages' => $totalPages,
      'current_page' => $currentPage,
      'total_page_count' => $totalPageCount,
      'previous_link' => $previousLink
    ]);

  }

  public function editAction() {

    // Get the ID from the route params
    if(isset($this->route_params['id'])) {

      $id = $this->route_params['id'];

      $posts = recipePosts::getPostsById($id, "recipes");

      // Removes null & empty values from array
      $posts[0] = array_filter($posts[0]);

      $title = $posts[0]['title'];
      $introduction = $posts[0]['introduction'];
      $id = $posts[0]['id'];
      $excerpt = strip_tags($posts[0]['excerpt']);
      $steps = unserialize($posts[0]['steps']);

      if(isset($posts[0]['is_featured'])) {
        $isFeatured = $posts[0]['is_featured'];
      }
      else {
        $isFeatured = 0;
      }

      if(isset($posts[0]['featured_image'])) {
        $image = $posts[0]['featured_image'];
      }
      else {
        $image = NULL;
      }

      // Get all set categories
      $categories = recipePosts::getAllCategories();
      $setCategories = recipePosts::getCurrentRecipeCategories($id);

      // Work out the difference between the set categories and all categories
      foreach($categories as $category) {
        $allCategories[] = $category['category_name'];
      }

      foreach($setCategories as $category) {
        $currentCategories[] = $category['category_name'];
      }

      $uniqueCategories = array_diff($allCategories, $currentCategories);

      // Get all images from the media library
      $images[] = recipePosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

      View::renderTemplate('/Recipes/edit.html', [
        'title' => $title,
        'introduction' => $introduction,
        'id' => $id,
        'excerpt' => $excerpt,
        // 'steps' => $steps,
        'featured_image' => $image,
        'set_categories' => $setCategories,
        'all_categories' => $uniqueCategories,
        'steps' => $steps,
        'medialibrary' => $images[0],
        'is_featured' => $isFeatured
      ]);

    }
    else {
      $this->redirect('/Recipes/list?page=1');
    }

  }

  public function updateAction() {

    if(!empty($_POST['id'])) {
      $id = $_POST['id'];
    }

    $posts = new recipePosts();

    // If there is a featured image, lets deal with that
    if(!empty($_FILES['featured_image']['name'])) {
      $bool = $posts->setAndUploadImage($_FILES['featured_image']);
    }

    $postSaved = $posts->savePost();

    if($postSaved) {
      if(!empty($id)) {
        Flash::addMessage('Post updated');
        $this->redirect("/Recipes/$id/edit");
      }
      else {
        Flash::addMessage('New post added');
        $this->redirect('/Recipes/list?page=1');
      }
    }
    else {
      Flash::addMessage('Update failed', 'warning');
      if(!empty($id)) {
        $this->redirect("/Recipes/$id/edit");
      }
      else {
        $this->redirect('/Recipes/list?page=1');
      }
    }
  }

  public function newAction() {

    $images[] = recipePosts::getImagesFromUploadFolder("/home/webbrowe/public_html/custom-mvc/public/assets/uploads/*.*");

    $categories = recipePosts::getRecipeCategories();

    View::renderTemplate('/Recipes/new.html', [
      'categories' => $categories,
      'medialibrary' => $images[0]
    ]);

  }

  public function deleteAction() {

    if(!empty($this->route_params['id'])) {
      $bool = recipePosts::deletePost($this->route_params['id'], "recipes");
    }
    if($bool) {
      Flash::addMessage('The recipe has been deleted');
    }
    else {
      Flash::addMessage('Could not delete recipe post', 'warning');
    }
    $this->redirect('/Recipes/list?page=1');

  }

  public function categoriesAction(){

    $categories = recipePosts::getRecipeCategoriesAndID();

    View::renderTemplate('/Recipes/categories.html', [
      'categories' => $categories
    ]);
  }

  public function addNewCategoryAction() {

    $categoryName = filter_var($_POST['category_name'], FILTER_SANITIZE_STRING);

    // Create the category slug
    $categorySlug = strtolower($categoryName);
    $categorySlug = str_replace(' ', '-', $categorySlug);

    $bool = recipePosts::setCategory($categoryName, $categorySlug);

    if($bool) {
      Flash::addMessage('Your new category has been added');
      $this->redirect('/Recipes/categories');
    }
    else {
      Flash::addMessage('Failed to add category, something went wrong', 'warning');
      $this->redirect('/Recipes/categories');
    }
  }

  public function editRecipeCategoryAction() {

    $id = $this->route_params['id'];

    $category = recipePosts::getPostsById($id, "recipe_categories");

    $categories = recipePosts::getAllPosts("recipe_categories");

    $categoryName = $category[0]['category_name'];
    $categorySlug = $category[0]['category_slug'];
    $categoryParent = $category[0]['category_parent'];
    $categoryID = $category[0]['id'];
    $inMenu = $category[0]['in_menu'];

    View::renderTemplate('/Recipes/edit-category.html', [
      'category_name' => $categoryName,
      'category_slug' => $categorySlug,
      'category_parent' => $categoryParent,
      'category_id' => $categoryID,
      'all_categories' => $categories,
      'in_menu' => $inMenu
    ]);

  }

  public function updateCategoryAction() {

    $bool = recipePosts::updateRecipeCategory();

    if($bool) {
      Flash::addMessage('Category updated');
      $this->redirect('/Recipes/categories');
    }
    else {
      Flash::addMessage('Failed to update category, please try again', 'warning');
      $this->redirect('/Recipes/categories');
    }

  }

  public function deleteRecipeCategory() {

    if(isset($_POST['category-id'])) {

      $id = $_POST['category-id'];
      $categoryName = $_POST['category-name'];

      $bool = recipePosts::deleteCategory($id, $categoryName);

    }

    if($bool) {
      Flash::addMessage('Your new category has been deleted');
      $this->redirect('/Recipes/categories');
    }
    else {
      Flash::addMessage('Failed to delete category, please try again', 'warning');
      $this->redirect('/Recipes/categories');
    }
  }
}
