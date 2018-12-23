<?php

  header("Content-Type: application/json");
  header("Access-Control-Allow-Origin: *");

  require_once("server.php");
  require_once("libraries/dbconnect.php");

  $modified = false;

  function modifySQL(string $inital, string $append, array $params){

    global $paramType, $a_params, $modified;
    $modified = true;
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

  /////HEADER: Parameters for API

      //If the user has set a range
  if(isset($_GET['range'])){

    list($start, $end) = explode(" - ", $_GET['range']);

    $sql = modifySQL($sql, "date(date_recorded) >= ? AND date(date_recorded) <= ?", [$start, $end]);

  }

      //Get if specific species have been set
  if(isset($_GET['bat_species'])){

    $species = array_map('trim', explode(',', $_GET['bat_species']));

    $sql = modifySQL($sql, "classification IN ({%1%})", $species);

  }

    //If location bounds set
  if(isset($_GET['lat']) && isset($_GET['lon']) && isset($_GET['radius'])){

    $preparedParams = [$_GET['lat'], $_GET['lon'], $_GET['lat'], $_GET['radius']];

    for($i = 0; $i < count($preparedParams); $i++){
      $preparedParams[$i] = floatval($preparedParams[$i]);
      if(!$preparedParams[$i]){
         echo('{"error": "invalid_params", "error_description": "Latitude, longitude and radius all need to be integers"}');
      }
    }

    $sql = modifySQL($sql, "
        ( 3959
          * acos( cos( radians(?) )
                  * cos(  radians( lat )   )
                  * cos(  radians( lng ) - radians(?) )
                + sin( radians(?) )
                  * sin( radians( lat ) )
                )
        ) < ?", $preparedParams);

  }

  //HEADER: Submit query

  $stmt = $connection->prepare($sql);

  if($modified){
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
