<?php

namespace App\Models;

use PDO;

class homepagePosts extends \Core\Model
{

  public $sliderTitle = NULL;
  public $sliderContent = NULL;

  public function saveSlide(){

    // Validate fields and set sql code
    $setQuery = $this->validateSlideFields();

    // Lets build the SQL statement based on whats in the object
    if(!empty($this->id) && !empty($setQuery)) {
      $sql = "UPDATE hp_home_slider SET $setQuery WHERE id=$this->id";
    }
    else {
      $sql = "INSERT INTO hp_home_slider ($this->fields) VALUES ";
      $sql .= "(";
      $sql .= $this->values;
      $sql .= ");";
    }

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    if(!empty($this->sliderTitle)){
      $stmt->bindValue(':slider_title', $this->sliderTitle, PDO::PARAM_STR);
    }

    if(!empty($this->uploadFile)) {
      $stmt->bindValue(':slider_image', $this->uploadFile, PDO::PARAM_STR);
    }

    $stmt->bindValue(':slider_image', $this->featuredImage, PDO::PARAM_STR);

    // Excerpt is never empty
    $stmt->bindValue(':slider_content', $this->sliderContent, PDO::PARAM_STR);

    return $stmt->execute();

  }

  private function validateSlideFields() {

    $setQuery = null;

    if(!empty($_POST['slider_title'])) {
      $this->sliderTitle = filter_var($_POST['slider_title'], FILTER_SANITIZE_STRING);
      $setQuery .= "slider_title=:slider_title";
      $this->fields = "slider_title";
      $this->values =  "'" . $this->sliderTitle . "'";
    }
    else {
      $this->sliderTitle = " ";
      $setQuery .= "slider_title=:slider_title";
      $this->fields = "slider_title";
      $this->values =  "'" . $this->sliderTitle . "'";
    }

    if(!empty($_POST['featured_image'])) {
      $this->featuredImage = filter_var($_POST['featured_image'], FILTER_SANITIZE_STRING);
      $setQuery .= ", slider_image=:slider_image";
      $this->fields .= ", slider_image";
      $this->values .=", '" . $this->featuredImage . "'";
    }
    else {
      $this->featuredImage = Null;
      $setQuery .= ", slider_image=:slider_image";
      $this->fields .= ", slider_image";
      $this->values .=", '" . $this->featuredImage . "'";
    }

    if(!empty($this->uploadFile)) {
      $setQuery .= ", slider_image=:slider_image";
      $this->fields .= ", slider_image";
      $this->values .=", '" . $this->uploadFile . "'";
    }

    if(!empty($_POST['slider_content'])) {
      $this->sliderContent = filter_var($_POST['slider_content'], FILTER_SANITIZE_STRING);
      $setQuery .= ", slider_content=:slider_content";
      $this->fields .= ", slider_content";
      $this->values .= ", '" . $this->sliderContent . "'";
    }
    else {
      $this->sliderContent = " ";
      $setQuery .= "slider_content=:slider_content";
      $this->fields = "slider_content";
      $this->values =  "'" . $this->sliderContent . "'";
    }

    if(!empty($_POST['id'])) {
      $this->id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    }

    return $setQuery;

  }

  public static function getRecentPost() {

    $sql = "SELECT * FROM recipes ORDER BY ID DESC LIMIT 1";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

}
