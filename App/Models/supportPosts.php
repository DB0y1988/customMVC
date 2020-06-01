<?php

namespace App\Models;

use PDO;

class supportPosts extends \Core\Model {

  private $ticketTitle = null;
  private $ticketMessage = null;
  private $ticketType = null;
  private $ticketImportance = null;
  private $ticketScreenshot = null;

  public function createTicket() {

    $this->ticketTitle = filter_var($_POST['ticket-title'], FILTER_SANITIZE_STRING);
    $this->ticketMessage = filter_var($_POST['ticket-message'], FILTER_SANITIZE_STRING);
    $this->ticketType = filter_var($_POST['ticket-type'], FILTER_SANITIZE_STRING);
    $this->ticketImportance = filter_var($_POST['ticket-importance'], FILTER_SANITIZE_STRING);

    if(!empty($_FILES['ticket-screenshot']['name'])) {

      $this->ticketScreenshot = $_FILES['ticket-screenshot']['name'][0];

      $sql = "INSERT into tickets (ticket_title, ticket_message, ticket_type, ticket_importance, ticket_screenshot, ticket_status)
      VALUES (:ticketTitle, :ticketMessage, :ticketType, :ticketImportance, :ticketScreenshot, 'Open')";

      $this->setAndUploadImage($_FILES['ticket-screenshot'], "assets/uploads/screenshots/");

    }
    else {

      $sql = "INSERT into tickets (ticket_title, ticket_message, ticket_type, ticket_importance, ticket_status)
      VALUES (:ticketTitle, :ticketMessage, :ticketType, :ticketImportance, 'Open')";

    }

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':ticketTitle', $this->ticketTitle, PDO::PARAM_STR);
    $stmt->bindValue(':ticketMessage', $this->ticketMessage, PDO::PARAM_STR);
    $stmt->bindValue(':ticketType', $this->ticketType, PDO::PARAM_STR);
    $stmt->bindValue(':ticketImportance', $this->ticketImportance, PDO::PARAM_STR);

    if(!empty($_FILES['ticket-screenshot']['name'])) {
      $stmt->bindValue(':ticketScreenshot', $this->ticketScreenshot, PDO::PARAM_STR);
    }

    return $stmt->execute();

  }

  public static function getAllTicketPostsWithPagnintation($table, $limit, $offset){

    $sql = "SELECT * FROM $table ORDER BY ticket_status DESC LIMIT $limit OFFSET $offset";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public static function updateTicketPost($id, $status){

    $sql = "UPDATE tickets SET ticket_status = '$status' WHERE id=$id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    return $stmt->execute();

  }

}
