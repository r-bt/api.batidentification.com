<?php

require_once 'server.php';

$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();

?>
