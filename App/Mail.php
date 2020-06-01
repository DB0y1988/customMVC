<?php

Namespace App;

class Mail {

  public static function send($to, $subject, $text){
    mail($to, $subject, $text, 'From: enquiries@webbrowebdesigns.co.uk');
  }

}
