<?php

namespace App\Models;

use PDO;

class pagesPosts extends \Core\Model
{


  public $pageSlug = NULL;


  public function savePost(){

    // Validate fields and set sql code
    $setQuery = $this->validatePostFields();

    // Lets build the SQL statement based on whats in the object
    if(!empty($this->id) && !empty($setQuery)) {
      $sql = "UPDATE hp_pages SET $setQuery WHERE id=$this->id";
    }
    else {
      $sql = "INSERT INTO hp_pages ($this->fields) VALUES ";
      $sql .= "(";
      $sql .= $this->values;
      $sql .= ");";
    }

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    if(!empty($this->title)){
      $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
    }

    if(!empty($this->template)){
      $stmt->bindValue(':template', $this->template, PDO::PARAM_STR);
    }

    if(!empty($this->content)) {
      $stmt->bindValue(':content', $this->content);
    }

    // Excerpt is never empty
    $stmt->bindValue(':excerpt', $this->excerpt, PDO::PARAM_STR);

    if(!empty($this->uploadFile)) {
      $stmt->bindValue(':image', $this->uploadFile, PDO::PARAM_STR);
    }

    // Featured image accepts null
    $stmt->bindValue(':image', $this->featuredImage, PDO::PARAM_STR);

    if($this->inMenu == "1" || $this->inMenu == "0") {
      $stmt->bindValue(':inmenu', $this->inMenu, PDO::PARAM_INT);
    }

    if(!empty($this->parent)) {
      $stmt->bindValue(':parent', $this->parent, PDO::PARAM_STR);
    }

    if(!empty($this->pageSlug)) {
      $stmt->bindValue(':slug', $this->pageSlug, PDO::PARAM_STR);
    }

    return $stmt->execute();

  }

  public static function getPageBySlug($slug){

    $sql = "SELECT * from hp_pages WHERE page_slug='$slug'";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    if(!empty($_POST['content'])) {
      $this->content = str_replace("'", "`", $_POST['content']);
      $setQuery .= ", content=:content";
      $this->fields .= ", content";
      $this->values .= ", '" . $this->content . "'";
    }
    else {
      $this->content = NULL;
      $setQuery .= ", content=:content";
      $this->fields .= ", content";
      $this->values .= ", '" . $this->content . "'";
    }

    if(!empty($_POST['parent'])) {
      $this->parent = filter_var($_POST['parent'], FILTER_SANITIZE_STRING);
      $setQuery .= ", page_parent=:parent";
      $this->fields .= ", page_parent";
      $this->values .= ", '" . $this->parent . "'";
    }

    if(!empty($_POST['in_menu'])) {
      $this->inMenu = (int) $_POST['in_menu'];
      $setQuery .= ", in_menu=:inmenu";
      $this->fields .= ", in_menu";
      $this->values .= ", '" . $this->inMenu . "'";
    }
    else {
      $this->inMenu = "0";
      $setQuery .= ", in_menu=:inmenu";
      $this->fields .= ", in_menu";
      $this->values .= ", '" . $this->inMenu . "'";
    }

    if(!empty($_POST['template'])) {
      $this->template = filter_var($_POST['template'], FILTER_SANITIZE_STRING);
      $setQuery .= ", page_template=:template";
      $this->fields .= ", page_template";
      $this->values .= ", '" . $this->template . "'";
    }

    if(!empty($_POST['excerpt'])) {
      $this->excerpt = substr(filter_var($_POST['excerpt'], FILTER_SANITIZE_STRING), 0, 250);
      $setQuery .= ", excerpt=:excerpt";
      $this->fields .= ", excerpt";
      $this->values .= ", '" . $this->excerpt . "'";
    }
    else {
      $this->excerpt = substr($this->content, 0, 150);
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

    return $setQuery;

  }

  public static function getPageTitles(){

    $sql = "SELECT title from hp_pages";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }
}
