<?php

  header("Content-Type: application/json");
  header("Access-Control-Allow-Origin: *");

  require_once("server.php");
  require_once("libraries/dbconnect.php");

  // if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
  //   $server->getResponse()->send();
  //   die;
  // }

  $batCalls = array();
  $a_params = array();
  $paramType = "";

  $sql = "Select id, location, address, classification FROM bat_calls";

  //If the user has set a range
  if(isset($_POST['range'])){

    list($start, $end) = explode(" - ", $_POST['range']);

    $sql .= " WHERE date(date_recorded) >= ? AND date(date_recorded) <= ?";

    $paramType = "ss";

    $a_params[] = &$paramType;
    $a_params[] = &$start;
    $a_params[] = &$end;

  }

  //Get if specific species have been set
  if(isset($_POST['bat_species'])){

    $classParams = "?";
    $paramType .= "s";

    for($i = 1; $i < count($_POST['bat_species']); $i++){

      $classParams .= ", ?";
      $paramType .= "s";

    }

    for($i = 0; $i < count($_POST['bat_species']); $i++){

      $a_params[] = &$_POST['bat_species'][$i];

    }

    if(isset($_POST['range'])){

      $sql .= " AND classification IN ({$classParams})";

    }else{

      $sql .= " WHERE classification IN ({$classParams})";

    }

  }

  $stmt = $connection->prepare($sql);

  if(isset($_POST['range']) || isset($_POST['bat_species'])){
    call_user_func_array(array($stmt, 'bind_param'), $a_params);
  }

  $stmt->execute();

  $stmt->bind_result($id, $location, $address, $classification);

  while($stmt->fetch()){

     list($lat, $lng) = explode(", ", $location);

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
