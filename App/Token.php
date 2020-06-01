<?php

namespace App;

class Token {

  protected $token;

  // Class constructor. Create a new random token or assign an existing one if passed in

  // random_bytes() -> Generates cryptographically secure pseudo-random bytes
  // bin2hex() -> Convert binary data into hexadecimal representation

  public function __construct($token_value = null){

    if($token_value) {
      $this->token = $token_value;
    }
    else {
      $this->token = bin2hex(random_bytes(16));
    }

  }

  public function getValue(){
    return $this->token;
  }

  // hash_hmac() -> Generate a keyed hash value using the HMAC method

  public function getHash(){
    return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY);
  }

}
