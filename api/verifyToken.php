<?php

  require_once("server.php");
  require_once("libraries/dbconnect.php");

  var_dump($_SERVER);

  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    $server->getResponse()->send();
    die;
  }

  echo('{"success": true}');

?>
