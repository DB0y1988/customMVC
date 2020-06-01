<?php

namespace App\Models;

use PDO;

class themeOptionPosts extends \Core\Model {

  public static function updateSocialLinks() {

    $sql = "UPDATE hp_theme_options SET facebook_link= :facebook, instagram_link=:instagram WHERE id='1' ";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':facebook', $_POST['facebook-link'], PDO::PARAM_STR);
    $stmt->bindValue(':instagram', $_POST['instagram-link'], PDO::PARAM_STR);

    return $stmt->execute();

  }

  public static function getSocialLink($type) {

    $sql = "SELECT ". $type ."_link from hp_theme_options WHERE id='1' ";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    $facebookLink = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $facebookLink[0][$type . '_link'];

  }

  public static function setWidget() {

    if($_POST['widget-title'] == "Not set") {
      $sql = "UPDATE hp_widgets SET widget_title='Not set', widget_content=NULL ,widget_active='0' WHERE widget_name=:widgetName";
    }
    else {
      $page = static::getPageByTitle($_POST['widget-title']);
      $sql = "UPDATE hp_widgets SET widget_title=:title, widget_content=:content, widget_image=:image, widget_active='1' WHERE widget_name=:widgetName";
    }

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':widgetName', $_POST['widget-name'], PDO::PARAM_STR);

    if($_POST['widget-title'] !== "Not set"){
      $stmt->bindValue(':title', $_POST['widget-title'], PDO::PARAM_STR);
      $stmt->bindValue(':content', $page[0]['content'], PDO::PARAM_STR);
      $stmt->bindValue(':image', $page[0]['featured_image'], PDO::PARAM_STR);
    }

    return $stmt->execute();

  }

}
