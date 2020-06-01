<?php

namespace App\Models;

use PDO;

class recipePosts extends \Core\Model
{

  public static function getPostsByCategory($category) {

    $sql = "SELECT * FROM recipes
    INNER JOIN hp_recipe_relationships ON recipes.id = hp_recipe_relationships.post_id
    INNER JOIN recipe_categories ON hp_recipe_relationships.category_id = recipe_categories.id
    WHERE recipe_categories.category_name = '$category'";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public function savePost() {

    // Validate fields and set sql code
    $setQuery = $this->validatePostFields();

    // Lets build the SQL statement based on whats in the object
    if(!empty($this->id) && !empty($setQuery)) {
      $sql = "UPDATE recipes SET $setQuery WHERE id=$this->id";
    }
    else {
      $sql = "INSERT INTO recipes ($this->fields) VALUES ";
      $sql .= "(";
      $sql .= $this->values;
      $sql .= ");";
    }

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    if(!empty($this->title)){
      $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
    }

    if(!empty($this->introduction)) {
      $stmt->bindValue(':introduction', $this->introduction, PDO::PARAM_STR);
    }

    // Excerpt is never empty
    $stmt->bindValue(':excerpt', $this->excerpt, PDO::PARAM_STR);

    if(!empty($this->uploadFile)) {
      $stmt->bindValue(':image', $this->uploadFile, PDO::PARAM_STR);
    }

    // Featured image accepts Null
    $stmt->bindValue(':image', $this->featuredImage, PDO::PARAM_STR);

    if(!empty($this->steps)) {
      $stmt->bindValue(':steps', $this->steps, PDO::PARAM_STR);
    }

    if(!empty($this->pageSlug)) {
      $stmt->bindValue(':slug', $this->pageSlug, PDO::PARAM_STR);
    }

    if(!empty($this->isFeatured || $this->isFeatured == 0)) {
      $stmt->bindValue(':isfeatured', $this->isFeatured, PDO::PARAM_STR);
    }

    $stmt->execute();

    // Lets create a category / recipe relationship
    if(!empty($_POST['categories'])) {
      // Build the relationship
      if(empty($this->id)) {
        $postID = $db->lastInsertId();
      }
      else {
        // The ID is the current ID in the object
        $postID = $this->id;
      }
    }

    $status = $this->createPostRelationship($postID, $_POST['categories']);

    return $status;

  }

  private function createPostRelationship($postID, $categorID) {

    $db = static::getDB();

    // Lets reset the relationships
    $sql = "DELETE FROM hp_recipe_relationships WHERE post_id = '$postID'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    // Get the ID's of the set categories
    foreach($categorID as $category) {
      $sth = $db->prepare("SELECT id FROM recipe_categories WHERE category_name LIKE '%$category%'");
      $sth->execute();
      $results[] = $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    $count = count($results) -1;

    for($i = 0; $i <= $count; $i++) {
      $sql = "INSERT INTO hp_recipe_relationships (post_id, category_id) VALUES (:recipeid, :categoryid)";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':recipeid', $postID, PDO::PARAM_INT);
      $stmt->bindValue(':categoryid', $results[$i][0]['id'], PDO::PARAM_INT);
      $status[] = $stmt->execute();
    }

    if(in_array(false, $status)) {
      return false;
    }
    else {
      return true;
    }

  }

  private function validatePostFields(){

    $setQuery = null;

    if(!empty($_POST['title'])) {
      $this->title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
      $setQuery .= "title=:title";
      $this->fields = "title";
      $this->values =  "'" . $this->title . "'";
      $this->pageSlug = $this->createPageSlug();
    }
    else {
      $this->title = "No title";
      $setQuery .= "title=:title";
      $this->fields = "title";
      $this->values =  "'" . $this->title . "'";
      $this->pageSlug = $this->createPageSlug();
    }

    if(!empty($this->pageSlug)) {
      $setQuery .= ", page_slug=:slug";
      $this->fields .= ", page_slug";
      $this->values .=", '" . $this->pageSlug . "'";
    }

    if(!empty($_POST['introduction'])) {
      $this->introduction = str_replace("'", "`", $_POST['introduction']);
      $setQuery .= ", introduction=:introduction";
      $this->fields .= ", introduction";
      $this->values .= ", '" . $this->introduction . "'";
    }
    else {
      $this->introduction = NULL;
      $setQuery .= ", introduction=:introduction";
      $this->fields .= ", introduction";
      $this->values .= ", '" . $this->introduction . "'";
    }

    if(!empty($_POST['is_featured'])) {
      if($_POST["is_featured"] == "Yes") {
        $this->isFeatured = 1;
        $setQuery .= ", is_featured=:isfeatured";
        $this->fields .= ", is_featured";
        $this->values .= ", '" . $this->isFeatured . "'";
      }
    }
    else {
      $this->isFeatured = 0;
      $setQuery .= ", is_featured=:isfeatured";
      $this->fields .= ", is_featured";
      $this->values .= ", '" . $this->isFeatured . "'";
    }

    if(!empty($_POST['excerpt'])) {
      $this->excerpt = substr(filter_var($_POST['excerpt'], FILTER_SANITIZE_STRING), 0, 250);
      $setQuery .= ", excerpt=:excerpt";
      $this->fields .= ", excerpt";
      $this->values .= ", '" . $this->excerpt . "'";
    }
    else {
      $this->excerpt = substr($this->introduction, 0, 150);
      $this->fields .= ", excerpt";
      $this->values .=", '" . $this->excerpt . "'";
    }

    if(!empty($_POST['id'])) {
      $this->id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    }

    if(!empty($this->uploadFile)) {
      $setQuery .= ", featured_image=:image";
      $this->fields .= ", featured_image";
      $this->values .=", '" . $this->uploadFile . "'";
    }

    if(!empty($_POST['featured_image'])) {
      $this->featuredImage = filter_var($_POST['featured_image'], FILTER_SANITIZE_STRING);
      $setQuery .= ", featured_image=:image";
      $this->fields .= ", featured_image";
      $this->values .=", '" . $this->featuredImage . "'";
    }
    else {
      $this->featuredImage = NULL;
      $setQuery .= ", featured_image=:image";
      $this->fields .= ", featured_image";
      $this->values .=", '" . $this->featuredImage . "'";
    }

    if(!empty($_POST['steps'])) {
      $this->steps = serialize($_POST['steps']);
      $setQuery .= ", steps=:steps";
      $this->fields .= ", steps ";
      $this->values .=", '" . $this->steps . "'";
    }

    return $setQuery;

  }

  public static function setCategory($categoryName, $categorySlug) {

    $sql = "INSERT INTO recipe_categories (category_name, category_slug) VALUES (:categoryName, :categorySlug)";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);
    $stmt->bindValue(':categorySlug', $categorySlug, PDO::PARAM_STR);

    return $stmt->execute();

  }

  public static function getRecipeCategoriesAndID() {

    $sql = "SELECT category_name, id FROM recipe_categories";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public static function getAllCategories() {

    $sql = "SELECT category_name FROM recipe_categories";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public static function getCurrentRecipeCategories($id) {

    $sql = "SELECT recipe_categories.category_name FROM hp_recipe_relationships
    INNER JOIN recipe_categories ON hp_recipe_relationships.category_id = recipe_categories.id AND post_id = $id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public static function deleteCategory($id, $categoryName){

    $sql = "DELETE FROM recipe_categories WHERE id=$id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();

    $status = static::unhookPosts($id);

    return $status;

  }

  private static function unhookPosts($id){

    $sql = "DELETE FROM hp_recipe_relationships WHERE category_id = $id ";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    return $stmt->execute();

  }

  public static function updateRecipeCategory() {

    // If the include in menu checkbox is checked
    if(isset($_POST['in_menu'])) {
      $inMenu = (int)$_POST['in_menu'];
    }
    else {
      $inMenu = 0;
    }

    $sql = "UPDATE recipe_categories SET category_name=:categoryName, category_slug=:categorySlug, category_parent=:categoryParent, in_menu=:inMenu WHERE id=:categoryID";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':categoryName', $_POST['category_name'], PDO::PARAM_STR);
    $stmt->bindValue(':categorySlug', $_POST['category_slug'], PDO::PARAM_STR);
    $stmt->bindValue(':categoryParent', $_POST['category_parent'], PDO::PARAM_STR);
    $stmt->bindValue(':inMenu', $inMenu, PDO::PARAM_INT);
    $stmt->bindValue(':categoryID', $_POST['id'], PDO::PARAM_INT);

    return $stmt->execute();

  }

  public static function getAllFeaturedRecipePosts() {

    $sql = "SELECT * FROM recipes WHERE is_featured='1'";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

}
