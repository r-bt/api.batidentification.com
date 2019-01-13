<?php

  require_once("../libraries/dbconnect.php");
  require_once("BatIdentificationCredentialsInterface.php");
  require_once '../vendor/autoload.php';

  $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
  $storage = new OAuth2\Storage\Pdo($pdo);
  $server = new OAuth2\Server($storage, array(
    'access_lifetime' => 1209600,
    'always_issue_new_refresh_tokens' => true,
    'refresh_token_lifetime' => 241920
  ));

  $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
  $server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
  $server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));

  $storageCredentialsInterface = new BatIdentificationCredentialsInterface($connection);
  $server->addGrantType(new OAuth2\GrantType\UserCredentials($storageCredentialsInterface));

?>
