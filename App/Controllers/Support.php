<?php

namespace App\Controllers;

use \Core\View;
use \App\Flash;
use \App\Models\supportPosts;

class Support extends \Core\Controller {

  public $postPerPage = 10;

  public function indexAction() {

    if(!empty($_GET['page'])) {
      $currentPage = (int)$_GET['page'];
    }

    $totalItems = supportPosts::countRecords("tickets");
    $totalItems = $totalItems[0]['count(*)'];
    $offset = static::setPagintationOffset($currentPage, $this->postPerPage);
    $previousLink = $currentPage - 1;

    $tickets = supportPosts::getAllTicketPostsWithPagnintation("tickets", $this->postPerPage, $offset);

    $totalPages = $this->createPagintation($this->postPerPage, $totalItems);
    $totalPageCount = count($totalPages);

    View::renderTemplate('Support/support.html', [
      'tickets' => $tickets,
      'total_pages' => $totalPages,
      'current_page' => $currentPage,
      'total_page_count' => $totalPageCount,
      'previous_link' => $previousLink
    ]);

  }

  public function newSupportTicketAction() {
    View::renderTemplate('Support/new-ticket.html');
  }

  public function createTicketAction() {

    $support = new supportPosts();
    $ticket = $support->createTicket();

    if($ticket) {
      $this->redirect('/support/index?page=1');
    }

  }

  public function viewTicketAction() {

    if(!empty($this->route_params['id'])) {
      $id = $this->route_params['id'];

      $support = new supportPosts();
      $ticket = $support->getPostsById($id, "tickets");

      View::renderTemplate('Support/view-ticket.html', [
        'tickets' => $ticket[0]
      ]);

    }

    else {
      $this->redirect('/support/index?page=1');
    }

  }

  public function updateTicketAction() {

    if(!empty($_POST['id'])) {
      $id = $_POST['id'];

      $status = supportPosts::updateTicketPost($id, $_POST['change-status']);

      if($status) {
        Flash::addMessage('The ticket is now ' . $_POST['change-status']);
        $this->redirect('/support/index?page=1');
      }

    }

  }

  public function deleteTicketAction(){

    if(!empty($this->route_params['id'])) {

      $status = supportPosts::deletePost($this->route_params['id'], "tickets");

      if($status) {
        Flash::addMessage('The ticket has been deleted');
        $this->redirect('/support/index?page=1');
      }

    }

  }

}
