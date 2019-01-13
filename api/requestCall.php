<?php

  require_once("server.php");
  require_once("../libraries/dbconnect.php");

  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    $server->getResponse()->send();
    die;
  }

  $sql = "SELECT id, call_url FROM bat_calls WHERE analysing_id IS NULL AND analyzed != true";
  $results = $connection->query($sql);
  if($results->num_rows > 0){

    $row = $results->fetch_assoc();

    $identifer = uniqid('', true);

    $stmt = $connection->prepare("UPDATE bat_calls SET analysing_id = ? WHERE id = ?");
    $stmt->bind_param("si", $identifer, $row['id']);
    $stmt->execute();
    $stmt->close();

    $call_to_return = array(
      'call_url' => 'https://batidentification.loc/' . $row['call_url'],
      'identifier' => $identifer
    );

    echo(json_encode($call_to_return));

  }else{

    echo('{"error":"no_calls","error_description":"There are no calls currently available to be analysed"}');

  }

?>
