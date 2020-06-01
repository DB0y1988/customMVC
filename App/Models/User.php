<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;

class User extends \Core\Model
{

    public $errors = [];

    public function __construct($data = []){
      foreach($data as $key => $value) {
        $this->$key = $value;
      }
    }

    /*
    * Breadcrumb: Generic
    * Description: Saves the users data
    * Action: Save the users data in the users database
    */
    public function save() {

      $this->validate();

      if(empty($this->errors)) {

      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

      $token = new Token();
      $hashed_token = $token->getHash();
      $this->activation_token = $token->getValue();

      $sql = "INSERT INTO user (name, email, password_hash, activation_hash)
      VALUES (:name, :email, :password_hash, :activation_hash)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
      $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

      return $stmt->execute();

    }

    return false;

    }

    public function validate() {

      if($this->name == '') {
        $this->errors[] = 'Name is required';
      }

      if(filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
        $this->errors[] = 'Invalid email';
      }

      if(static::emailExists($this->email, $this->id ?? null)){
        $this->errors[] = 'Email already taken';
      }

      if(isset($this->password)) {
        if(strlen($this->password) < 6) {
          $this->errors[] = 'Please enter at least 6 characters for the password';
        }

        if(preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
          $this->errors[] = 'Password needs at least one letter';
        }

        if(preg_match('/.*\d+.*/i', $this->password) == 0) {
          $this->errors[] = 'Password needs at least one number';
        }

      }
    }

    /*
    * Description: See if a user record already exists with the specified email
    * Breadcrumb: User/validate -> (User/emailExists)
    */
    public static function emailExists($email, $ignore_id = null) {

      $user = static::findByEmail($email);

      if($user) {
        if($user->id != $ignore_id) {
          return true;
        }
        else {
          return false;
        }
      }

    }

    /*
    * Breadcrumb: Login/create -> User/authenticate -> (User/findByEmail)
    * Description: Find the user by the email provided
    * Action: Find email in the database and return as an object
    */
    public static function findByEmail($email) {

      $sql = "SELECT * FROM user WHERE email = :email";

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);

      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

      $stmt->execute();

      return $stmt->fetch();

    }

    public static function findByID($id) {

      $sql = "SELECT * FROM user WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

      $stmt->execute();

      return $stmt->fetch();

    }

    // Remember the login by inserting a new unique token into the remembered_logins table for this user record
    public function rememberLogin(){

      $token = new Token();
      $hashed_token = $token->getHash();
      $this->remember_token = $token->getValue();

      $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; // 30 days from now

      $sql = "INSERT INTO remembered_logins (token_hash, user_id, expires_at) VALUES (:token_hash, :user_id, :expires_at)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
      $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
      $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

      return $stmt->execute();

    }


    /*
    * Breadcrumb: Login/create -> (User/authenticate)
    * Description: Looks for the email in the database
    * Action: Find user by email and return the object if true
    */
    public static function authenticate($email, $password) {

      $user = static::findByEmail($email);

      if($user && $user->is_active) {
        if(password_verify($password, $user->password_hash)) {
          return $user;
        }
      }
      return false;
    }

    /*
    * Breadcrumb: Password/Forgot -> Password/Request-Reset -> (User/sendPasswordReset)
    * Action:
    */
    public static function sendPasswordReset($email){

      $user = static::findByEmail($email);

      // If the user is found by the email supplied
      if($user){
        if($user->startPasswordReset()) {
          $user->sendPasswordResetEmail();
        }
      }

    }

    /*
    * Breadcrumb: Password/Forgot -> Password/Request-Reset -> User/sendPasswordReset -> (User/startPasswordReset)
    * Action:
    */
    protected function startPasswordReset(){

      // Creates a new random token
      $token = new Token();

      // getHash() -> Generate a keyed hash value using the HMAC method
      $hashed_token = $token->getHash();
      $this->password_reset_token = $token->getValue();

      $expiry_timestamp = time() + 60 * 60 * 2; // 2 hours from now

      $sql = "UPDATE user SET password_reset_hash = :token_hash, password_reset_expiry = :expires_at WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
      $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

      return $stmt->execute();

    }

    /*
    * Breadcrumb: // Password/Forgot -> Password/Request-Reset -> User/sendPasswordResetEmail
    * Action: Create the email string with token id and send the email
    */
    protected function sendPasswordResetEmail() {

      // Build the email link to reset the password from the password_reset_token
      $url = "http://" . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

      $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);

      // Send the email
      Mail::send($this->email, 'Password reset', $text);

    }

    /*
    * Breadcrumb: Password/reset -> Password/getUserOrExit -> (User/findByPasswordReset)
    * Action: Find the user by the token sent in the email
    */
    public static function findByPasswordReset($token) {

      $token = new Token($token);
      $hashed_token = $token->getHash();

      $sql = "SELECT * FROM user WHERE password_reset_hash = :token_hash";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

      $stmt->execute();

      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

      $user = $stmt->fetch();

      if($user) {
        // Check password reset token hasn't expired
        if(strtotime($user->password_reset_expiry) > time()) {
          return $user;
        }
      }

    }

    /**
    * Breadcrumb: Password/ResetPassword -> (User/resetPassword)
    * Description: User has filled in the password reset form
    * Action: Reset the user's password
    */
    public function resetPassword($password){

      // Store password in the object
      $this->password = $password;

      // Validate the password -> If password is < 6 char
      $this->validate();

      if(empty($this->errors)) {

        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = "UPDATE user
        SET password_hash = :password_hash,
        password_reset_hash = NULL,
        password_reset_expiry = NULL
        WHERE id = :id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

        // return true or false
        return $stmt->execute();

      }

      return false;

    }

    /*
    * Breadcrumb: // Signup/create -> User/Save -> (User/sendActivationEmail)
    * Action: Send the activation email from a new user signup
    */
    public function sendActivationEmail() {

      // Build the email link to reset the password from the password_reset_token
      $url = "http://" . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

      $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);

      // Send the email
      Mail::send($this->email, 'Account activation', $text);

    }

    /*
    * Breadcrumb: // Signup/activate -> (User/activate)
    * Action: sets the activation_hash field in the database to true (1)
    */
    public static function activate($value){

      $token = new Token($value);
      $hashed_token = $token->getHash();

      $sql = "UPDATE user SET is_active = 1, activation_hash = null WHERE activation_hash = :hashed_token";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

      $stmt->execute();

    }

    public function updateProfile($data){

      if(empty($this->errors)) {

        $sql = "UPDATE user SET name = :name, email = :email";

        // Only update the sql query with the password hash if a password has been supplied
        if(isset($this->password)) {
          $sql .= ", password_hash = :password:hash";
        }

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        if(isset($this->password)) {
          $password_hash = password_hash($this->password, PDO::PARAM_STR);
          $stmt->bindValue(':password_hash', $this->password, PDO::PARAM_STR);
        }

        return $stmt->execute();

      }

      return false;

    }


}
