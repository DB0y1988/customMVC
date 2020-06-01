<?php

namespace Core;

use PDO;
use App\Config;

abstract class Model
{

    protected static function getDB()
    {

        $db = null;

        if ($db == null) {
            try {
                $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
            return $db;
        }
    }

    public function setAndUploadImage($theImage, $path = "assets/uploads/") {

      $count = count($theImage['name']) -1;

      for($i = 0; $i <= $count; $i++) {

      $filename = basename($theImage['name'][$i]);
      $type = pathinfo($theImage["name"][$i], PATHINFO_EXTENSION);
      $fileToUpload = $path . $filename;

      $uploadOk[] = move_uploaded_file($theImage["tmp_name"][$i], $fileToUpload);

      }

      if(in_array(false, $uploadOk)) {
        return "1 or more images could not be uploaded";
      }
      else {
        return "Your images have been uploaded";
      }

    }

    public static function getAllPosts($table){

      $sql = "SELECT * FROM $table";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getAllPostsWithPagnintation($table, $limit, $offset){

      $sql = "SELECT * FROM $table LIMIT $limit OFFSET $offset";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getPostsById($id, $table){

      $sql = "SELECT * FROM $table WHERE id=$id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function deletePost($id, $table) {

      $sql = "DELETE FROM $table WHERE id=$id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      return $stmt->execute();

    }

    public static function getImagesFromUploadFolder($folder) {

      $all_files = glob($folder);

      foreach($all_files as $files) {
        $images[] = str_replace("/home/webbrowe/public_html/custom-mvc/public", "", $files);
      }

      return $images;
    }

    public static function getPagesMenu() {

      $sql = "SELECT title, page_slug, page_parent FROM hp_pages WHERE in_menu='1' AND page_parent='No Parent'";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getRecipeMenu() {

      $sql = "SELECT category_name, category_slug, category_parent FROM recipe_categories WHERE in_menu='1' AND category_parent='No Parent' ORDER BY category_name ASC";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getPageChildren($page) {

      $sql = "SELECT title, page_slug FROM hp_pages WHERE page_parent='$page' AND in_menu='1' ";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getRecipeChildren($page) {

      $sql = "SELECT category_name, category_slug FROM recipe_categories WHERE category_parent='$page' AND in_menu='1' ";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function getPageByTitle($title) {

      $sql = "SELECT * FROM hp_pages WHERE title='$title'";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function countRecords($table) {

      $sql = "SELECT count(*) FROM $table";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->execute();

      return $stmt->fetchAll();

    }

    protected function createPageSlug() {

      if($this->title == "No title") {
        if(!isset($_POST['id'])) {
          $this->pageSlug = rand() . "_page";
        }
      }
      else {
      // If the page title has page in it
      $match = preg_match("/[a-z]+ (?i)[page]+/", $this->title);
      // Make lowercase
      $this->pageTitle = strtolower($this->title);

        if($match) {
          $this->pageSlug = str_replace(" ", "_", $this->pageTitle);
        }
        else {
          $this->pageSlug = str_replace(" ", "_", $this->pageTitle) . "_page";
        }
      }
      return $this->pageSlug;
    }
}
