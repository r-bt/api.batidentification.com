<?php

  require_once '../vendor/autoload.php';

  $CLIENT_ID = '12798888631-oaihc82j00ltkqget4a4jnscg2np3m0u.apps.googleusercontent.com';

  class BatIdentificationCredentialsInterface implements OAuth2\Storage\UserCredentialsInterface{

    public function __construct( $connection ) {
      $this->connection = $connection;
    }

    public function getUserDetails($email){
      $stmt = $this->connection->prepare("SELECT id, scope from users WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $stmt->store_result();

      if($stmt->num_rows > 0){
        $stmt->bind_result($id, $scope);
        $stmt->fetch();
        $stmt->close();
        return array(
          "user_id" => $id,
          "scope" => $scope
        );
      }

      $stmt->close();
      return false;

    }

    public function checkAccount($email, $password){
      $stmt = $this->connection->prepare("SELECT id, username, password FROM users WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $stmt->bind_result($id, $username, $retrivedPassword);
      $stmt->fetch();
      if(password_verify($password, $retrivedPassword)){
        return(array($id, $username, $email));
      }else{
        return false;
      }
    }

    //The interfaces defines the variable as 'username' but we instead use it as the user's email

    public function checkUserCredentials($username, $password){

      $result = $this->checkAccount($username, $password);

      if($result == false){
        return false;
      }else{
        return true;
      }

    }

  }

?>
