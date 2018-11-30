<?php

  class MapsAPI{

    function __construct(){
      $this->base_url = "http://dev.virtualearth.net/REST/v1/Locations/";
      $this->key = "An4gVlk9Jm3GbYcxcYw8pBj19n9n_EamCf8HLP7HdCgqeadvW2Q-7M60rEQZrgqL";
    }

    function addressFromCords($coordinates){

      $coordinates = str_replace(" ", "", $coordinates);

      $queryURL = $this->base_url . "{$coordinates}?o=json&key={$this->key}";

      $response = file_get_contents($queryURL);

      $decoded_response = json_decode($response);

      $formatted = $decoded_response->resourceSets[0]->resources[0]->name;

      if($formatted != NULL){
        return $formatted;
      }else{
        throw new Exception("Failed to get address");
      }

    }

  }

?>
