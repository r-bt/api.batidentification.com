<?php

  header("Content-Type: application/json");
  header("Access-Control-Allow-Origin: *");

  require_once("server.php");
  require_once("libraries/dbconnect.php");

  function modifySQL(string $inital, string $append, array $params){

    global $paramType, $a_params;
    $paramsInQuery = "";

    $sql = $inital . ' ' . (strpos($inital, "WHERE") == false ? "WHERE " : "AND ") . $append;

    for($i = 0; $i < count($params); $i++){

      $paramType .= gettype($params[$i])[0];
      $a_params[] = &$params[$i];
      $paramsInQuery .= ($i == 0 ? "?" : ",?");

    }

    $sql = str_replace("{%1%}", $paramsInQuery, $sql);

    return $sql;

  }

  // if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
  //   $server->getResponse()->send();
  //   die;
  // }

  $batCalls = array();
  $a_params = array();
  $paramType = "";

  $a_params[] = &$paramType;

  $sql = "Select id, lat, lng, address, classification FROM bat_calls";

  //If the user has set a range
  if(isset($_POST['range'])){

    list($start, $end) = explode(" - ", $_POST['range']);

    $sql = modifySQL($sql, "date(date_recorded) >= ? AND date(date_recorded) <= ?", [$start, $end]);

  }

  //Get if specific species have been set
  if(isset($_POST['bat_species'])){

    $sql = modifySQL($sql, "classification IN ({%1%})", $_POST['bat_species']);

  }

  $stmt = $connection->prepare($sql);

  if(isset($_POST['range']) || isset($_POST['bat_species'])){
    call_user_func_array(array($stmt, 'bind_param'), $a_params);
  }

  $stmt->execute();

  $stmt->bind_result($id, $lat, $lng, $address, $classification);

  while($stmt->fetch()){

     $classification = ucwords(str_replace("_", " ", $classification));

     $batCall = new stdClass();
     $batCall->id = $id;
     $batCall->address = $address;
     $batCall->lat = $lat;
     $batCall->lng = $lng;
     $batCall->species = $classification;
     array_push($batCalls, $batCall);

  }

  $output = new stdClass();
  $output->calls = $batCalls;

  echo json_encode($output);

?>
