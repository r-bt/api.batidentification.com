<?php

  //HEADER: Import the neccessary files
  require_once("server.php");
  require_once("../libraries/dbconnect.php");
  require_once("../libraries/MapsApi.php");

  //Check if access token has been submited
  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    echo('{"error": "access_token", "error_description":"The access token provided is invalid"}');
    $server->getResponse()->send();
    die;
  }

  //HEADER: Functions to validate the uploaded data

  function formatInput($input){
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
  }

  function isWav($tmpname){

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mtype = finfo_file($finfo, $tmpname);
    finfo_close($finfo);
    if($mtype == "audio/x-wav"){
      return TRUE;
    }
    return TRUE;

  }

  function validateDate($date){

    $corrFormat = DateTime::createFromFormat("Y-m-d G:i:s", $date) === FALSE ? FALSE : TRUE;

    return $corrFormat;

  }

  function validateLat($lat){

      return preg_match("/^-?([1-8]\d?\.\d+|90)$/", $lat);

  }

  function validateLon($lon){

    return preg_match("/^-?([0-1]?[0-7]\d?\.\d+|180)$/", $lon);

  }

  //HEADER: Validate all the data

  if(!isset($_FILES["bat_call"]) || !isset($_POST['date_recorded']) || !isset($_POST['lat']) || !isset($_POST['lon'])){

    http_response_code(400);
    die('{"error": "insufficent_data", "error_description": "Sorry, some data was missing please try again"}');

  }

  if(!validateDate($_POST['date_recorded'])){

    http_response_code(400);
    die('{"error": "invalid_data", "error_description": "Please submit the date in the format YYYY-MM-DD HH:MM:SS"}');

  }

  if(!validateLat($_POST['lat']) || !validateLon($_POST['lon'])){

    http_response_code(400);
    die('{"error": "invalid_data", "error_description": "Please insert a valid latitude and longitude pair"}');

  }

  if(!isWav($_FILES["bat_call"]["tmp_name"])){

    http_response_code(415);
    die('{"error": "invalid_format", "error_description": "Sorry only .wav files can be uploaded"}');

  }

  //HEADER: Get address form Lat / Lon pair

  $mapsAPI = new MapsAPI();
  try{
    $address = $mapsAPI->addressFromCords($_POST['lat'], $_POST['lon']);
  }catch (Exception $e){
    $address = "Unknown";
  }

  //HEADER: Upload Call

  $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
  $date_recorded = formatInput($_POST['date_recorded']);

  $folderName = uniqid();
  $uploadDir = $config["bat_calls"] . $folderName . '/';
  while (file_exists($uploadDir) || is_dir($uploadDir)) {
    $folderName = uniqid();
    $uploadDir = $config["bat_calls"] . $folderName . '/';
  }

  $new_file = $uploadDir . 'original.wav';

  var_dump($new_file);

  if(mkdir($uploadDir, 0777, true) && move_uploaded_file($_FILES["bat_call"]["tmp_name"], $new_file)){

    $stmt = $connection->prepare("INSERT INTO bat_calls (call_url, user_id, date_added, date_recorded, lat, lng, address) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
    $folderDir = "bat_calls/" . $folderName . '/';
    var_dump($connection->error);
    $stmt->bind_param("sisiis", $folderDir, $token['user_id'], $date_recorded, $_POST['lat'], $_POST['lon'], $address);
    $stmt->execute();

    $sql = "INSERT INTO bat_classifications (common_pipistrelle, nathusius_pipistrelle, soprano_pipistrelle, daubentons_bat, natterers_bat, whiskered_bat, brown_long_eared, lesser_Horseshoe, leislers_bat) VALUES (0,0,0,0,0,0,0,0,0)";
    $connection->query($sql);

    die('{"success": true}');

  }else{

    http_response_code(500);
    die('{"error": "upload_failed", "error_description": "Sorry, there was an error uploading your file"}');

  }


?>
