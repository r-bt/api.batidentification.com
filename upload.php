<?php

  require_once("server.php");
  require_once("libraries/dbconnect.php");
  require_once("libraries/MapsApi.php");

  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    echo('{"error": "access_token", "error_description":"The access token provided is invalid"}');
    $server->getResponse()->send();
    die;
  }

  $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

  $folderName = uniqid();
  $uploadDir = $config["bat_calls"] . $folderName . '/';

  while (file_exists($uploadDir) || is_dir($uploadDir)) {
    $uploadDir = $config["bat_calls"] . uniqid() . '/';
  }

  function isMP3($tmpname){
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mtype = finfo_file($finfo, $tmpname);
    finfo_close($finfo);
    //if($mtype == ("audio/mpeg" || "audio/wav")){
    if($mtype == "audio/x-wav"){
      return TRUE;
    }
    return FALSE;
  }

  function validateInput($input){
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
  }

  if(isset($_FILES["bat_call"]) && isset($_POST['date_recorded']) && isset($_POST['location'])){

    $location = validateInput($_POST['location']);
    $date_recorded = validateInput($_POST['date_recorded']);

    if(DateTime::createFromFormat("Y-m-d G:i:s", $date_recorded) !== FALSE){

      if(preg_match("/^-?([1-8]\d?\.\d+|90), -?([0-1]?[0-7]\d?\.\d+|180)$/", $location) != FALSE){

        // We need to also convert the geocode to an address
        $mapsAPI = new MapsAPI();
        try{
          $address = $mapsAPI->addressFromCords($location);
        }catch (Exception $e){
          $address = "Unknown";
        }

        if(isMP3($_FILES["bat_call"]["tmp_name"])){

          $new_file = $uploadDir . 'original.' . pathinfo($_FILES["bat_call"]["name"], PATHINFO_EXTENSION);
          $old = umask(0);

          if (mkdir($uploadDir, 0777, true) && move_uploaded_file($_FILES["bat_call"]["tmp_name"], $new_file)) {
              umask($old);

              $stmt = $connection->prepare("INSERT INTO bat_calls (call_url, user_id, date_added, date_recorded, location, address) VALUES (?, ?, NOW(), ?, ?, ?)");
              $folderDir = "bat_calls/" . $folderName . '/';
              $stmt->bind_param("sisss", $folderDir, $token['user_id'], $date_recorded, $location, $address);
              $stmt->execute();
              $sql = "INSERT INTO bat_classifications (common_pipistrelle, nathusius_pipistrelle, soprano_pipistrelle, daubentons_bat, natterers_bat, whiskered_bat, brown_long_eared, lesser_Horseshoe, leislers_bat) VALUES (0,0,0,0,0,0,0,0,0)";
              $connection->query($sql);
              echo('{"success": true}');
          } else {
             umask($old);
             http_response_code(500);
             echo('{"error": "upload_failed", "error_description": "Sorry, there was an error uploading your file"}');
          }

        }else{

          http_response_code(415);
          echo('{"error": "invalid_format", "error_description": "Sorry only .wav files can be uploaded"}');

        }

      }else{

        http_response_code(400);
        echo('{"error": "invalid_data", "error_description": "Please insert a valid latitude and longitude pair"}');

      }

    }else{
      http_response_code(400);
      echo('{"error": "invalid_data", "error_description": "Please submit the date in the format YYYY-MM-DD HH:MM:SS"}');
    }

  }else{

    http_response_code(400);
    echo('{"error": "insufficent_data", "error_description": "Sorry, some data was missing please try again"}');

  }


?>
