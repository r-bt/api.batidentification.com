<?php

  require_once("server.php");
  require_once("../libraries/dbconnect.php");

  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: Authorization");

  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    echo('{"error": "access_token", "error_description":"The access token provided is invalid"}');
    $server->getResponse()->send();
    die;
  }

  $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

  $stmt = $connection->prepare("SELECT username from users WHERE id = ?");

  $stmt->bind_param("i", $token['user_id']);

  $stmt->execute();

  $stmt->bind_result($username);

  $stmt->fetch();

  $stmt->close();

  if($username != ""){
    echo("{\"username\": \"{$username}\"}");
  }else{
    echo('{"error": "no_response", "description": "Our server\'s failed to get a response, please try again later"}');
  }

?>
