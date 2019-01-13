<?php

  require_once("server.php");
  require_once("../libraries/dbconnect.php");
  require_once("../libraries/environment.php");

  $dir = $baridentification . 'bat_calls/';

  if(!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())){
    $server->getResponse()->send();
    die;
  }

  function isWAV($tmpname){
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mtype = finfo_file($finfo, $tmpname);
    finfo_close($finfo);
    if($mtype == "audio/x-wav"){
      return TRUE;
    }
    return FALSE;
  }

  function isImage($tmpname){

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mtype = finfo_file($finfo, $tmpname);
    finfo_close($finfo);
    if($mtype == ("image/jpeg" || "image/png")){
      return TRUE;
    }
    return FALSE;

  }

  if(isset($_FILES['spectrogram']) && isset($_FILES['time_expansion']) && isset($_POST['analysing_id'])){

    if(isImage($_FILES['spectrogram']['tmp_name'])){

      if(isWAV($_FILES['time_expansion']['tmp_name'])){

        $stmt = $connection->prepare("SELECT call_url FROM bat_calls WHERE analysing_id = ?");
        $stmt->bind_param("s", $_POST['analysing_id']);
        $stmt->execute();
        $stmt->bind_result($call_url);
        $stmt->fetch();
        $stmt->close();

        if($call_url != NULL){

          $newSpectrogram = $batidentification . $call_url . '/spectrogram.' . pathinfo($_FILES['spectrogram']['name'], PATHINFO_EXTENSION);
          $newTimeExpansion = $batidentification . $call_url . '/time_expansion.' . pathinfo($_FILES['time_expansion']['name'], PATHINFO_EXTENSION);

          if(move_uploaded_file($_FILES['spectrogram']['tmp_name'], $newSpectrogram)){

            if(move_uploaded_file($_FILES['time_expansion']['tmp_name'], $newTimeExpansion)){

                $stmt = $connection->prepare("UPDATE bat_calls SET analysing_id = NULL, analyzed = true WHERE analysing_id = ?");
                $stmt->bind_param("s", $_POST['analysing_id']);
                $stmt->execute();
                $stmt->close();

                echo('{"success": true}');

            }else{

              http_response_code(500);
              echo('{"error": "File Upload", "description": "Something went wrong with uploading the spectrogram file"}');

            }

          }else{

            http_response_code(500);
            echo('{"error": "File Upload", "description": "Something went wrong with uploading the spectrogram file"}');

          }

        }else{

          http_response_code(400);
          echo('{"error": "Invalid value", "description": "The analysing id provided was invalid"}');

        }

      }else{

        http_response_code(415);
          echo('{"error": "Invaid file", "description": "Only wavs can be uploaded"}');

      }

    }else{

      http_response_code(415);
      echo('{"error": "Invaid file", "description": "Only JPEGs or PNGs can be uploaded"}');

    }

  }else{

    http_response_code(400);
    echo('{"error": "Insufficent data", "description": "Some data was missing"}');

  }

?>
