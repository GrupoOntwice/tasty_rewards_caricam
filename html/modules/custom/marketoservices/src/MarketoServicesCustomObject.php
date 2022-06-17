<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\marketoservices;

class MarketoServicesCustomObject{
    
    private $host = "CHANGE ME";
    private $clientId = "CHANGE ME";
    private $clientSecret = "CHANGE ME";
    public $input;//array of custom objects, required
    public $action;//action to take, createOnly, updateOnly, createOrUpdate, default createOrUpdate
    public $dedupeBy;//dedupefields, or idField, see describe ca;
    public $name; //name of custom object

    public function __construct() {
        $marketoconf = \Drupal::config('marketoservices.settings')->get('marketo');

        $this->clientId = $marketoconf['client_id'];
        $this->clientSecret = $marketoconf['client_secret'];
        $this->host = 'https://777-OAA-137.mktorest.com';
        //$this->token = $this->getToken();
    }
    
    private function bodyBuilder(){
            $requestBody = new \stdClass();
            if (isset($this->action)){
                    $requestBody->action = $this->action;
            }
            if (isset($this->dedupeBy)){
                    $requestBody->dedupeBy = $this->dedupeBy;
            }
            $requestBody->input = $this->input;
            $json = json_encode($requestBody);

            return $json;
    }
    
    public function postData(){
            $url = $this->host . "/rest/v1/customobjects/" . $this->name . ".json?access_token=" . $this->getToken();
            $ch = curl_init($url);
            $requestBody = $this->bodyBuilder();
            curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json','Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
            curl_getinfo($ch);
            $response = curl_exec($ch);
            return $response;
    }

    private function getToken(){
            $ch = curl_init($this->host . "/identity/oauth/token?grant_type=client_credentials&client_id=" . $this->clientId . "&client_secret=" . $this->clientSecret);
            curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
            $response = json_decode(curl_exec($ch));
            curl_close($ch);
            $token = $response->access_token;
            return $token;
    }	
}