<?php

  // The upload endpoint for calls to be sent to.
  // Requires the following params:
  //   -> A file called bat_call
  //   -> A date date_recorded conforming to Y-m-d G:i:s
  //   -> A lat coordinate
  //   -> A lng coordinate

  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: authorization");

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
    return FALSE;

  }

  function validateDate($date){

    $corrFormat = DateTime::createFromFormat("Y-m-d G:i:s", $date) === FALSE ? FALSE : TRUE;

    return $corrFormat;

  }

  function validateLat($lat){

      return preg_match("/^-?([1-8]?\d?\.?\d+|90)$/", $lat);

  }

  function validateLng($lng){

    return preg_match("/^-?([0-1]?[0-7]?\d?\.?\d+|180)$$/", $lng);

  }

  //HEADER: Validate all the data

  if(!isset($_FILES["bat_call"]) || !isset($_POST['date_recorded']) || !isset($_POST['lat']) || !isset($_POST['lng'])){

    http_response_code(400);
    die('{"error": "insufficent_data", "error_description": "Sorry, some data was missing please try again"}');

  }

  if(!validateDate($_POST['date_recorded'])){

    http_response_code(400);
    die('{"error": "invalid_data", "error_description": "Please submit the date in the format YYYY-MM-DD HH:MM:SS"}');

  }

  if(!validateLat($_POST['lat']) || !validateLng($_POST['lng'])){

    http_response_code(400);
    die('{"error": "invalid_data", "error_description": "Please insert a valid latitude and longitude pair"}');

  }

  if(!isWav($_FILES["bat_call"]["tmp_name"])){

    http_response_code(415);
    die('{"error": "invalid_format", "error_description": "Sorry only .wav files can be uploaded"}');

  }

  //HEADER: Get address form Lat / lng pair

  $mapsAPI = new MapsAPI();
  try{
    $address = $mapsAPI->addressFromCords($_POST['lat'], $_POST['lng']);
  }catch (Exception $e){
    $address = "Unknown";
  }

  //HEADER: Upload Call

  $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
  $date_recorded = formatInput($_POST['date_recorded']);

  $rootDir = explode('api.batidentification', __DIR__)[0];
  $folderName = uniqid();
  $uploadDir = $rootDir . $config['bat_calls'] . "bat_calls/" . $folderName . '/';
  while (file_exists($uploadDir) || is_dir($uploadDir)) {
    $folderName = uniqid();
    $uploadDir = $rootDir . $config["bat_calls"] . $folderName . '/';
  }

  $new_file = $uploadDir . 'original.wav';

  if(mkdir($uploadDir, 0777, true) && move_uploaded_file($_FILES["bat_call"]["tmp_name"], $new_file)){

    $stmt = $connection->prepare("INSERT INTO bat_calls (call_url, user_id, date_added, date_recorded, lat, lng, address) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
    $folderDir = "bat_calls/" . $folderName . '/';
    $stmt->bind_param("sisdds", $folderDir, $token['user_id'], $date_recorded, $_POST['lat'], $_POST['lng'], $address);
    $stmt->execute();
    $stmt->close();

    $sql = "INSERT INTO bat_classifications (common_pipistrelle, nathusius_pipistrelle, soprano_pipistrelle, myotis, leislers_bat, brown_long_eared, lesser_horseshoe, unknown) VALUES (0,0,0,0,0,0,0,0)";
    $connection->query($sql);

    die('{"success": true}');

  }else{

    http_response_code(500);
    die('{"error": "upload_failed", "error_description": "Sorry, there was an error uploading your file"}');

  }


?>
