<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\ssoservices;

use Drupal\Core\Site\Settings;
use Carbon\Carbon;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\UserAgent;

class SSOServices {

    protected $oauth_token; 
    protected $sso_config;
    protected $errormsg;
    protected $errorKey;
    protected $rnduts;
    protected $encstr;
    protected $sessionId;
    


    public function __construct() {
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if ($language == 'en-us' || $language == 'es-us'){
          $this->sso_config =  \Drupal::config('ssoconfig_us.adminsettings');
        }
        else{
          $this->sso_config =  \Drupal::config('ssoconfig.adminsettings');
        }
        

        $this->errormsg = "";
        $this->errorKey = "";
    }

    public function get_auth_token(){
      if (isset($this->oauth_token['token_value']) && isset($this->oauth_token['expires_in'])) {
        if ($this->oauth_token['token_value'] && time() < $this->oauth_token['expires_in']){
          return $this->oauth_token['token_value'];
        }
      }

      $url = rtrim($this->sso_config->get('access_token_url'));
      
      $data = array(
        "grant_type" => "client_credentials", 
        "client_id" => $this->sso_config->get('client_id'), 
        "client_secret" => $this->sso_config->get('client_secret'),  
        "scope" => $this->sso_config->get('scope') 
      );

      $payload = http_build_query($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_POST, 1);

      $headers = array();
      $headers[] = 'Content-Type: application/x-www-form-urlencoded';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
        $result = json_decode($curl_result);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /*
        $message = "Result Auth token httpcode:  %error";
        $context = ['%error' => $httpcode];
        \Drupal::logger('sso call httpcode')->info($message, $context);

        $message = "Result Auth token Result:  %error";
        $context = ['%error' => serialize($result)];
        \Drupal::logger('sso call result')->info($message, $context);

        $message = "Result Auth token Payload:  %error";
        $context = ['%error' => $payload];
        \Drupal::logger('sso call payload')->info($message, $context);
        */

        if ($httpcode != 200){
          $message = "Exception SSO:  %error";
          $context = ['%error' => $result->errorCode];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          $message = "Auht token OKTA Call:  %error";
          $context = ['%error' => $result->access_token];
          \Drupal::logger('curt fail')->info($message, $context);

          $this->oauth_token = array(
            'token_value' => $result->access_token, 
            'expires_in' => time() + $result->expires_in, 
          );
          curl_close ($ch);
          return  $result->access_token;
        }
      }
      curl_close ($ch);
      return false;
    }

    public function get_consumer_token(){

      $env = Settings::get('environment', NULL);

      /*  
      if ($env == 'dev'){ // Artifice to simulate the session
        $this->temptesttoken(); // CReate session with hardcoded token
      }
      */

      $session = \Drupal::request()->getSession();

      //SAving session both Consumer accessToken and  idtoken
      $accesstoken = $session->get('accessToken');

      if (!$accesstoken) return false;

      $tokenParts = explode('.', $accesstoken);
      $header     = base64_decode($tokenParts[0]);
      $payload    = base64_decode($tokenParts[1]);
      $signatureProvided = $tokenParts[2];
      $pay = json_decode($payload);
      $exp = $pay->exp;

      // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
      $expiration = Carbon::createFromTimestamp($exp);
      $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

      if ($tokenExpired) {
        // check if token can be renewed
        return false; 
      }

      return $accesstoken;
    }

    public function get_och_accountId(){

      // och_accountId is coming as part to the Accesstoken

      $session = \Drupal::request()->getSession();
      //SAving session both Consumer accessToken and  idtoken
      $accesstoken = $session->get('accessToken');

      // split the token (we need the token for API calls + get the accountId or och_accountId)
      $tokenParts = explode('.', $accesstoken);
      $tokenpayload    = base64_decode($tokenParts[1]);
      $tokenpayload = json_decode($tokenpayload);
      return $tokenpayload->accountId; 
    }

    public function CreateOchUser($user){

       $token = $this->get_auth_token();
       $this->errormsg = "";

       $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

       $payload = json_encode($this->OchUserCreationFormat($user));

       $url = $this->sso_config->get('och_userendpoint') . '/users/';

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_URL, $url);

       curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
       curl_setopt($ch, CURLOPT_POST, 1);

       $headers = array();
       $headers[] = 'Authorization: Bearer ' . $token;
       $headers[] = 'Content-Type: application/json';
       $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

       $curl_result = curl_exec($ch);
       if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curl fail')->info($message, $context);
       }
       else{
          $result = json_decode($curl_result);
          $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          /*
          $message = "SSO User Create (PAYLOAD):  %error";
          $context = ['%error' => $payload];
          \Drupal::logger('sso call error')->info($message, $context);

          $message = "SSO User Create (httpcode):  %error";
          $context = ['%error' => $httpcode];
          \Drupal::logger('sso call error')->info($message, $context);

          $message = "SSO User Create (RESULT):  %error";
          $context = ['%error' => serialize($result)];
          \Drupal::logger('sso call error')->info($message, $context);
          */

          if ($httpcode != 200){
            $title = $result->title;
            $title =  str_replace("source=TastyRewards","",str_replace("source=TastyRewardsUS","",$title));
            if ($this->isJSON($title)){
              $title = json_decode($title);
              $msg = $title->errorCauses[0];
              $cleanmsg = $msg->errorSummary;
            }
            else{
              $cleanmsg = $title;
            }
            $this->errormsg = $cleanmsg;
            if (isset($result->errorKey)){
              $this->errorKey = $result->errorKey;
            }

            $message = "Exception SSO User Create:  %error";
            $context = ['%error' => $cleanmsg];
            \Drupal::logger('sso call error')->info($message, $context);
          }
          else{
            curl_close ($ch);
            return  $result->id; // Returd Id of the new user
          }
       }

       curl_close ($ch);
       return false;
     
    }    

    public function GetOchUser(){

      $token = $this->get_consumer_token();

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();

      //https://api-na.dev.mypepsico.com/it/och/cussvc/api/account?accountId=8a7f0ed0730128e3017305b5b499001b
      $url = $this->sso_config->get('och_userendpoint') . '/account?accountId=' . $ochaccountid; 

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_HTTPGET, 1);

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call get:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200){
          $message = "Exception SSO User get:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
          if (isset($result['id'])){
            // We need to keep the values fetched and set back in the OCH update API call
            $tempstore = \Drupal::service('tempstore.private')->get('ssoservices');
            $tempstore->set('ochuser', $result);
            return $result;
          }
          return false;
        }
      }

      curl_close ($ch);
      return false;
    } 


    /**
     * Update API
     * Eg: https://api-na.dev.mypepsico.com/it/och/cussvc/api/account-contact?accountId=9c80ff46791d37e501791d38e9700000
     * 
     {
          "id": "9c80ff46791d37e501791d38e9aa0002",
            "contactType": "CONSUMER",
            "firstName": "Update111",
            "lastName": "12345111",
            "middleInit": null,
            "email": "test.deleteuser5@gmail.com",
            "phone": "",
            "phone2": null,
            "gender": "M",
            "source": "TastyRewards",
            "login": "Snacksca.test.deleteuser5@gmail.com",
            "birthDate": null,
            "birthMonth": null,
            "birthDay": null,
            "birthYear": null,
            "languageCode": "EN",
            "languageName": null,
            "occupation": null,
            "optins": "100024307_PEPSI_NEWSLETTER_PEPSICANADATASTYREWARDS_20160614",
            "optin2": null,
            "optOuts": "",
            "createdDt": "2021-04-29T10:42:19.602Z",
            "updatedDt": "2021-04-29T10:42:19.602Z",
          "account": {
              "id":"9c80ff46791d37e501791d38e9700000"
              }
     }
     * 
     * 
     */


    public function UpdateOchUser($user){

      $token = $this->get_consumer_token();

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();

      $payload = json_encode($this->OchUserUpdateFormat($user));

      $url = $this->sso_config->get('och_userendpoint') . '/account-contact?accountId=' . $ochaccountid; 

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200){
          $message = "Exception SSO User update:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
          if (isset($result['id'])){
            return true;
          }
          return false;
        }
      }

      curl_close ($ch);
      return false;
    } 

    /**
     * {
            "id": "8a7f0e7478feed7a0178ff1cae610001",
            "addressType": "SHIPPING_ADDRESS",
            "name": " test 220420231111111",
            "addressLine1": "",
            "addressLine2": null,
            "city": "Belleville",
            "state": "ON",
            "zip": "K8N 0P8",
            "country": "CA",
            "effectiveDt": "2021-04-29T10:24:10Z",
            "effectiveEndDt": null,
            "primaryAddr": true,
            "createdDt": "2021-04-29T10:24:10Z",
            "updatedDt": "2021-04-29T10:24:10Z",
     "account": {
        "id":" 8a7f0e7478feed7a0178ff1cae3f0000"
          }

      }
     * 
     */
    public function UpdateAddressOchUser($user, $address = null){

      $token = $this->get_consumer_token();

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();

      $payload = json_encode($this->OchUserAddressUpdateFormat($user,$address));

      $url = $this->sso_config->get('och_userendpoint') . '/account-address?accountId=' . $ochaccountid; 

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200){
          $message = "Exception SSO User update:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
          if (isset($result['id'])){
            return true;
          }
          return false;
        }
      }

      curl_close ($ch);
      return false;
    } 

    /**
     * {
    "addressType": "BILLING_ADDRESS",
    "name": "VIDHYASHANKAR",
    "addressLine1": "760 FAIRWAY DR",
    "city": "NAPERVILLE",
    "state": "ILLINOIS",
    "zip": "60563",
    "country": "IN",
    "primaryAddr": "true",
    "effectiveDt": "2020-07-06T13:40:33.336Z",
    "account": {
        "id": "8a7f29ac73474030017347c18bec0010"
      }
    }
     * 
     */
    public function AddAddressOchUser($user, $address = null){

      //$token = $this->get_consumer_token();
      $token = $this->get_auth_token(); // In the creation address we call using auth token and not consumer token

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();

      $payload = json_encode($this->OchUserAddressAddFormat($user,$address));

      $url = $this->sso_config->get('och_userendpoint') . '/account-addresses'; 

      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_ENCODING, '');
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	  
	  
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');  

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 201){
          $message = "Exception SSO User update:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
          if (isset($result['id'])){
            return true;
          }
          return false;
        }
      }
          
      curl_close ($ch);
      return false;
    }     

    /**
     * {
            "id": "8a7f0e7478feed7a0178ff1cae610001",
            "addressType": "SHIPPING_ADDRESS",
            "name": " test 220420231111111",
            "addressLine1": "",
            "addressLine2": null,
            "city": "Belleville",
            "state": "ON",
            "zip": "K8N 0P8",
            "country": "CA",
            "effectiveDt": "2021-04-29T10:24:10Z",
            "effectiveEndDt": null,
            "primaryAddr": true,
            "createdDt": "2021-04-29T10:24:10Z",
            "updatedDt": "2021-04-29T10:24:10Z",
     "account": {
        "id":" 8a7f0e7478feed7a0178ff1cae3f0000"
          }

      }
     * 
     */
    public function RemoveAddressOchUser($addressId){

      $token = $this->get_consumer_token();

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();

      
      //https://api-na.dev.mypepsico.com/it/och/cussvc/api/account-addresses?accountId=8a7f8d0a72ec7f0a0172eca3fc030000&addressId=8a7f8d0a72ec7f0a0172eca447c20003

      $url = $this->sso_config->get('och_userendpoint') . '/account-address?accountId=' . $ochaccountid . '&addressId=' . $addressId; 

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 204){
          $message = "Exception SSO User update:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
            return true;
        }
      }

      curl_close ($ch);
      return false;
    } 


    public function DeleteOchUser(){

      $token = $this->get_consumer_token();

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $ochaccountid = $this->get_och_accountId();
      
      $source = ($language == 'en-us' || $language == 'es-us') ? 'TastyRewards':'TastyRewards';  
      
      //https://api-na.dev.mypepsico.com/it/och/cussvc/api/account?accountId=8a7f3a8678faa7f00178fab2e1600003&source=TastyRewards

      $url = $this->sso_config->get('och_userendpoint') . '/account?accountId=' . $ochaccountid . '&source=' .$source; 

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

      $headers = array();
      $headers[] = 'Authorization: Bearer ' . $token;
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Accept-Language: '. $language=='es-us'?'es':$language;
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      if (curl_errno($ch)) {
        $message = "Exception SSO Call:  %error";
        $context = ['%error' => curl_error($ch)];
        \Drupal::logger('curl fail')->info($message, $context);
      }
      else{
        $result = json_decode($curl_result,true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200){
          $message = "Exception SSO User update:  %error";
          $context = ['%error' => isset($result['detail'])?$result['detail'] :'No description' ];
          \Drupal::logger('sso call error')->info($message, $context);
        }
        else{
          curl_close ($ch);
            return true;
        }
      }

      curl_close ($ch);
      return false;
    } 



    public function LoginUser($accessToken,$idtoken){

      $session = \Drupal::request()->getSession();
      //SAving session both Consumer accessToken and  idtoken
      $session->set('accessToken', $accessToken);
      $session->set('idtoken', $idtoken);

      $message = "accessToken:  %error";
      $context = ['%error' =>  $accessToken];
      \Drupal::logger('sso call error')->info($message, $context);
      $message = "idtoken:  %error";
      $context = ['%error' =>  $idtoken];
      
      \Drupal::logger('sso call error')->info($message, $context);

      // split the token (we need the token for API calls + get the accountId or och_accountId)
      $tokenParts = explode('.', $accessToken);
      $header     = base64_decode($tokenParts[0]);
      $tokenpayload    = base64_decode($tokenParts[1]);
      $signatureProvided = $tokenParts[2];
      $tokenpayload = json_decode($tokenpayload);

      $ochaccountid      = $tokenpayload->accountId;
      $group      = $tokenpayload->groups[0];

      // split the idtoken (we need it to get the user profile )
      $idtokenParts    = explode('.', $idtoken);
      $idtokenpayload  = base64_decode($idtokenParts[1]);
      $idtokenpayload = json_decode($idtokenpayload);

      /* checking the country based on the $group */



      //$exp = $pay->exp;
      $email      = $idtokenpayload->email;
      $firstname  = $idtokenpayload->field_firstname;
      $lastname   = $idtokenpayload->field_lastname;
      $postalcode = $idtokenpayload->field_postalcode;
      $langcode   = strtolower($idtokenpayload->preferred_langcode);

      if ($email == "miguel80@bam.com"){
        $langcode   = 'en-us';
      }


      if ($langcode == 'es') { //convert es to 'es-us'
        $langcode = 'es-us';
      }

      $social     = $idtokenpayload->social;

      if ($social){ //have to save in session.
        $session = \Drupal::request()->getSession();
        //SAving session both Consumer accessToken and  idtoken
        $session->set('socialsource', $social);
      }

      if ($langcode != 'en' && $langcode != 'fr' && $langcode != 'en-us' && $langcode != 'es-us' ) $langcode = 'en';

      $tmpoptin = $idtokenpayload->field_optin;

      $tmpoptin2 = isset($idtokenpayload->field_optin2)?$idtokenpayload->field_optin2:null; 
      $optin  = false;
      $optin2 = false;
      $optin3 = false;
      $optin4 = false;
      $optin5 = false;
      $optin6 = false;


      if ($langcode == 'en-us' || $langcode == 'es-us'){

        $optins_us = $this->getSubscriptionIdsFlags($tmpoptin);

        $optin  = $optins_us[1];
        $optin2 = $optins_us[2];
        $optin3 = $optins_us[3];
        $optin4 = $optins_us[4];
        $optin5 = $optins_us[5];
        $optin6 = $optins_us[6];
  
      }
      else{
        if ($tmpoptin == 'true') $optin  = true;
        if ($tmpoptin2 == 'true') $optin2  = true;
      }

      if (!$langcode){
        $langcode = 'en';
      }
      /*
      "field_firstname": "Migffour",
      "field_optin2": "false",
      "social": "",
      "field_lastname": "pin",
      "field_postalcode": "H3E1B5",
      "preferred_langcode": "FR",
      "field_optin": "false" 
      */



      //check if Email exist on the Drupal
      $user = user_load_by_mail($email);

      if (!$user){
        $user = User::create([
          'name' => $email,
          'mail' => $email,
          'status' => 1, // Always active for USA - What about CA?
          'field_firstname' => $firstname,
          'field_lastname' => $lastname,
          'field_postalcode' => $postalcode,
          'field_marketoid' => $ochaccountid, // We use this field since we need this field to make the OCH get customer API call.
          'field_optin' => $optin,
          'field_optin2' => $optin2,
          'field_optin3' => $optin3,
          'field_optin4' => $optin4,
          'field_optin5' => $optin5,
          'field_optin6' => $optin6,
          'field_optin_old' => $optin,
          'field_optin_old2' => $optin2,
          'field_optin_old3' => $optin3,
          'field_optin_old4' => $optin4,
          'field_optin_old5' => $optin5,
          'field_optin_old6' => $optin6,          
        ]);
        $user->set("langcode", $langcode);
        $user->set("preferred_langcode", $langcode);

        $user->setPassword($this->generatePassword());
        $user->save();
      }
      else{
        $user->set('field_firstname', $firstname);
        $user->set('field_lastname', $lastname);
        $user->set('field_postalcode', $postalcode);
        $user->set('field_marketoid', $ochaccountid);
        $user->set('field_optin', $optin);
        $user->set('field_optin2', $optin2);
        $user->set('field_optin3', $optin3);
        $user->set('field_optin4', $optin4);
        $user->set('field_optin5', $optin5);
        $user->set('field_optin6', $optin6);
        $user->set('field_optin_old', $optin);
        $user->set('field_optin_old2', $optin2);
        $user->set('field_optin_old3', $optin3);
        $user->set('field_optin_old4', $optin4);
        $user->set('field_optin_old5', $optin5);
        $user->set('field_optin_old6', $optin6);
        $user->set("langcode", $langcode);
        $user->set("preferred_langcode", $langcode);
        $user->setPassword($this->generatePassword());
        $user->save();
      }
      //If user variable exist
      if($user) {
        user_login_finalize($user);
      }

      if (\Drupal::currentUser()->id() > 0){
        //Call the getallprofile info from OCH just for USA members
        //if ($langcode == 'en-us' || $langcode == 'es-us'){

          $ochuser =  $this->GetOchUser();
          if (!$ochuser) {
              throw new NotFoundHttpException();
          }
          $user = $this->updateOchToTR($user,$ochuser);          
        //}
        return true;
      }

      return false;

      // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
      //$expiration = Carbon::createFromTimestamp($exp);
      //$tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

    }

    public function OchUserCreationFormat($post){
      /*
      ** Values coming from registration form.
      [
      $post['email'],
      $post['email'],
      $post['password']
      $post['firstname'],
      $post['lastname'],
      $post['city'],
      $post['province'],
      $post['zipcode']['main'],
      $post['zipcode']['extension'],
      $post['gender'],
      $post['bday'],
      $post['fbid'],
      $post['optin'],
      $post['source'],
      $post['ccid'],
      $post['ipaddress'],
      $post['useragent'],
      $post['language'],
      $post['date'],
      $post['casl']
      ];

    {
      "profile": {
        "firstName": "test",  user firstname
        "lastName": "22042021",  user lastname
        "email": "testuser220420210445@yahoo.co.in", email id
        "login": "Snacksca.testuser220420210445@yahoo.co.in", -> prefix with Snacksca
        "gender": "M",  gender
        "dateOfBirth": "1986-01-01",  gender
        "source" : "TastyRewards",  source
        "city":"Belleville",  city
        "state":"ON",  state
        "zipCode":"K8N 0P8",  zip code
        "countryCode":"CA",  countryCode
        "social":"",  Value should be GOOGLE for signup with Google
        "optin":"TESTOPTIN1",  Subject to change
        "optin2":"TESTOPTIN2",  Subject to change
        "optin_date" : "2021-04-04"  optin date
      },
      "credentials": {
      "password" : { "value": "U2VwdEAxOTg4" }  Base64 encrypted
      }
    }

      */
      $user_prefix  = $this->sso_config->get('signin_prefix');
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($language == 'en-us' ||  $language == 'es-us'){
        $country = "US";
        $source = 'TastyRewardsUS';
        
        if ($post['optin']){
          $arroptins[1] = '1';
        } 
        if ($post['optin3']){
          $arroptins[3] = '1';
        } 
  
        $optin = $this->getSubscriptionIdsStringOptIn($arroptins);
        $optin2 = '';
        //optouts,  
        $optOuts = ''; // It is empty, but since the user is a new user.
  
      }
      else{
        $country = "CA";
        $source = 'TastyRewards';
        $optin = false;
        if ($post['optin']) $optin = true;
  
        $optin2 = false;
        if ($post['optin2']) $optin2 = true;
  
      }
      


      $social = isset($post['socialsource'])?$post['socialsource']:"";

      $gender = isset($post['gender'])?$post['gender']:"O";

      $gender = empty($gender)?"O":$gender;

      $password = !empty($post['password'])? base64_encode($post['password']):'';

      if ($social > '') $password = '';
      

      $user_formatted = [];
      $user_formatted["profile"] = [
        "firstName"=> $post['firstname'],
        "lastName"=>$post['lastname'],
        "email"=> $post['email'],
        "login"=> $user_prefix . $post['email'],
        "gender"=> $gender,
        "dateOfBirth"=> $post['bday'],
        "source" => $source,
        "city" => $post['city'],
        "state" => $post['province'],
        "zipCode" => $post['zipcode']['main'],
        "countryCode"=> $country,
        "social"=>$social,
        "preferredLanguage" => strtoupper( isset($post['language'])?$post['language']:""),
        "optin" => $optin,
        "optin2" => $optin2,
        "optin_date" => $post['date'],
        "optin_casl" => isset($post['casl'])?$post['casl']:'',
        "sourceID" => $post['sourceid']
      ];
      
      if ($optin) {
        $user_formatted["profile"]["optin_date"] = $post['date'];
      }
      $user_formatted["credentials"] = [
        "password"=> ["value" => $password ],
      ];
      //$user_formatted["groupIds"] = [$groupId];
  
      return $user_formatted;
   }

   public function OchUserUpdateFormat($user){
    
    /*    
      {
                "id": "9c80ff46791d37e501791d38e9aa0002",
                  "contactType": "CONSUMER",
                  "firstName": "Update111",
                  "lastName": "12345111",
                  "middleInit": null,
                  "email": "test.deleteuser5@gmail.com",
                  "phone": "",
                  "phone2": null,
                  "gender": "M",
                  "source": "TastyRewards",
                  "login": "Snacksca.test.deleteuser5@gmail.com",
                  "birthDate": null,
                  "birthMonth": null,
                  "birthDay": null,
                  "birthYear": null,
                  "languageCode": "EN",
                  "languageName": null,
                  "occupation": null,
                  "optins": "100024307_PEPSI_NEWSLETTER_PEPSICANADATASTYREWARDS_20160614",
                  "optin2": null,
                  "optOuts": "",
                  "createdDt": "2021-04-29T10:42:19.602Z",
                  "updatedDt": "2021-04-29T10:42:19.602Z",
                "account": {
                    "id":"9c80ff46791d37e501791d38e9700000"
                    }
          }
    */

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
      //Recover the original values from private user session
      $tempstore = \Drupal::service('tempstore.private')->get('ssoservices');
      $ochuser = $tempstore->get('ochuser');

      /*
      $data['email'] = $user->get('mail')->value;
      $data['firstname'] = $user->get('field_firstname')->value;
      $data['lastname'] = $user->get('field_lastname')->value;
      $data['postalcode'] = $user->get('field_postalcode')->value;
      $data['gender'] = $user->get('field_gender')->value;
      $data['bday'] = $user->get('field_bday')->value;
      $data['optin'] = $user->get('field_optin')->value;
      $data['optin2'] = $user->get('field_optin2')->value;

      $user->set('field_marketoid' , $ochaccountid);  // OCH Account ID Needed to get / update the OCH apicalls
      $user->set('field_marketocookie', $post['id']);  // OCH Contact ID Needed to update the OCH apicalls
      */

      $accountContacts = $ochuser["accountContacts"][0];
      $accountUsers    = $ochuser["accountUsers"][0];


      if ($language == 'en-us' ||  $language == 'es-us'){
        //Optins are send SubscriptionID values as comma separated.
        $arroptins[1] = $user->get('field_optin')->value;
        $arroptins[2] = $user->get('field_optin2')->value;
        $arroptins[3] = $user->get('field_optin3')->value;
        $arroptins[4] = $user->get('field_optin4')->value;
        $arroptins[5] = $user->get('field_optin5')->value;
        $arroptins[6] = $user->get('field_optin6')->value;

        $optin = $this->getSubscriptionIdsStringOptIn($arroptins);

        //optouts
        
        if( ($user->get('field_optin')->value != $user->get('field_optin_old')->value) &&  ($user->get('field_optin')->value == false || $user->get('field_optin')->value == 0))  {
          $arroptouts[1] = 0;
        }

        if( ($user->get('field_optin3')->value != $user->get('field_optin_old3')->value) &&  ($user->get('field_optin3')->value == false || $user->get('field_optin3')->value == 0)) {
          $arroptouts[3] = 0;
        }

        if( ($user->get('field_optin4')->value != $user->get('field_optin_old4')->value) &&  ($user->get('field_optin4')->value == false || $user->get('field_optin4')->value == 0)) {
          $arroptouts[4] = 0;
        }

        if( ($user->get('field_optin5')->value != $user->get('field_optin_old5')->value) &&  ($user->get('field_optin5')->value == false || $user->get('field_optin5')->value == 0)) {
          $arroptouts[5] = 0;
        }

        if( ($user->get('field_optin6')->value != $user->get('field_optin_old6')->value) &&  ($user->get('field_optin6')->value == false || $user->get('field_optin6')->value == 0)) {
          $arroptouts[6] = 0;
        }

        $optOuts = '';
        if (isset($arroptouts)){
          $optOuts = $this->getSubscriptionIdsStringOptOut($arroptouts);
        }

      }
      else{
        $optin = false;
        $new_optin = $user->get('field_optin')->value;
        $new_optin2 = $user->get('field_optin2')->value;
        if ($new_optin) $optin = true;

        $optin2 = false;
        if ($new_optin2) $optin2 = true;
        $optOuts = $accountContacts["optOuts"];
      }

      $date = new \DateTime('NOW');
      $ochdate = date_format($date, 'c');

      $langtoOCH = $user->get('preferred_langcode')->value;
      if ($langtoOCH == 'es-us'){
        $langtoOCH == 'es';
      }

      $ochuser_new = [
      "id"           => $accountContacts["id"],  //OCH Contact ID
      "contactType"  => $accountContacts["contactType"],
      "firstName"    => $user->get('field_firstname')->value,
      "lastName"     => $user->get('field_lastname')->value,
      "middleInit"   => $accountContacts["middleInit"],
      "email"        => $user->get('mail')->value,
      "phone"        => $accountContacts["phone"],
      "phone2"       => $accountContacts["phone2"],
      "gender"       => $user->get('field_gender')->value,
      "source"       => $accountContacts["source"],
      "login"        => $accountContacts["login"],
      "birthDate"    => $user->get('field_bday')->value, 
      "birthMonth"   => $accountContacts["birthMonth"],
      "birthDay"     => $accountContacts["birthDay"],
      "birthYear"    => $accountContacts["birthYear"],
      "languageCode" => $langtoOCH,
      "languageName" => $langtoOCH,
      "occupation"   => $accountContacts["occupation"],
      "optins"       => $optin,
      "optin2"       => $optin2,
      "optOuts"      => $optOuts,
      "createdDt"    => $accountContacts["createdDt"],
      "updatedDt"    => $accountContacts["updatedDt"],
        "account" =>  [
            "id" => $ochuser["id"]
        ]
      ];

      return $ochuser_new ;
    }   

    public function OchUserAddressUpdateFormat($user,$address = null){
    
      /* if  $address  is null, mean we only update one address "PROFILE_ADDRESS" type ("PUT" method)
         if $address is an array and address-id is not null, then we need to update that especific address ("PUT" method)
         if $address is an array and address-id is null, then we need to create a new address, ("POST" method)

        $address['id']
        $address['firstname']
        $address['lastname']
        $address['address1']
        $address['address2']
        $address['city']
        $address['state']
        $address['zip']

      /*    
        {
            "id": "8a7f0e7478feed7a0178ff1cae610001",
            "addressType": "SHIPPING_ADDRESS",
            "name": " test 220420231111111",
            "addressLine1": "",
            "addressLine2": null,
            "city": "Belleville",
            "state": "ON",
            "zip": "K8N 0P8",
            "country": "CA",
            "effectiveDt": "2021-04-29T10:24:10Z",
            "effectiveEndDt": null,
            "primaryAddr": true,
            "createdDt": "2021-04-29T10:24:10Z",
            "updatedDt": "2021-04-29T10:24:10Z",
            "account": {
                "id":" 8a7f0e7478feed7a0178ff1cae3f0000"
            }

        }
      */
  
        //Recover the original values from private user session
        $tempstore = \Drupal::service('tempstore.private')->get('ssoservices');
        $ochuser = $tempstore->get('ochuser');
  
  
        $accountContacts  = $ochuser["accountContacts"][0];
        $accountUsers     = $ochuser["accountUsers"][0];
        // $accountAddresses = $ochuser["accountAddresses"][0];

        if (!$address) { //Update the profile address
          $accountAddresses = $this->getProfileAddress($ochuser["accountAddresses"]);

          $ochuser_new = [
            "id"             => $accountAddresses["id"],  //OCH Contact ID
            "addressType"    => $accountAddresses["addressType"],
            "name"           => $accountAddresses["name"],
            "addressLine1"   => $accountAddresses["addressLine1"],
            "addressLine2"   => $accountAddresses["addressLine2"],
            "city"           => $user->get('field_city')->value,
            "state"          => $user->get('field_province')->value,
            "zip"            => $user->get('field_postalcode')->value,
            "country"        => $accountAddresses["country"],
            "effectiveDt"    => $accountAddresses["effectiveDt"],
            "effectiveEndDt" => $accountAddresses["effectiveEndDt"],
            "primaryAddr"    => $accountAddresses["primaryAddr"],
            "createdDt"      => $accountAddresses["createdDt"],
            "updatedDt"      => $accountAddresses["updatedDt"],
              "account"      =>  [
                  "id" => $ochuser["id"]
              ]
            ];

        }
        else{
          if ( isset($address['id']) && $address['id'] != 'new' ) { //update an especific address
            $accountAddresses = $this->getAddressById($ochuser["accountAddresses"],$address['id']);
            if ($address['primaryAddr'] == "1") { // Only this field is updated.
              $ochuser_new = [
                "id"             => $accountAddresses["id"],  //OCH Contact ID
                "addressType"    => $accountAddresses["addressType"],
                "name"           => $accountAddresses["name"],
                "addressLine1"   => $accountAddresses["addressLine1"],
                "addressLine2"   => $accountAddresses["addressLine2"],
                "city"           => $accountAddresses["city"],
                "state"          => $accountAddresses["state"],
                "zip"            => $accountAddresses["zip"],
                "country"        => $accountAddresses["country"],
                "effectiveDt"    => $accountAddresses["effectiveDt"],
                "effectiveEndDt" => $accountAddresses["effectiveEndDt"],
                "primaryAddr"    => true,
                "createdDt"      => $accountAddresses["createdDt"],
                "updatedDt"      => $accountAddresses["updatedDt"],
                  "account"      =>  [
                      "id" => $ochuser["id"]
                  ]
                ];
            }
            else {
            $ochuser_new = [
              "id"             => $accountAddresses["id"],  //OCH Contact ID
              "addressType"    => $accountAddresses["addressType"],
              "name"           => $address['firstname'] . " " . $address['lastname'],
              "addressLine1"   => $address['address1'],
              "addressLine2"   => $address['address2'],
              "city"           => $address['city'],
              "state"          => $address['state'],
              "zip"            => $address['zip'],
              "country"        => $accountAddresses["country"],
              "effectiveDt"    => $accountAddresses["effectiveDt"],
              "effectiveEndDt" => $accountAddresses["effectiveEndDt"],
              "primaryAddr"    => $accountAddresses["primaryAddr"], 
              "createdDt"      => $accountAddresses["createdDt"],
              "updatedDt"      => $accountAddresses["updatedDt"],
                "account"      =>  [
                    "id" => $ochuser["id"]
                ]
              ];
            }
          }
          else{ //Create a new Shipping address
            $profileaddress = $this->getProfileAddress($ochuser["accountAddresses"]); //Since we don't pass the country from the form, we can get it from the profile address

            $ochuser_new = [
              "addressType"    => "SHIPPING_ADDRESS",
              "name"           => $address['firstname'] . " " . $address['lastname'],
              "addressLine1"   => $address['address1'],
              "addressLine2"   => $address['address2'],
              "city"           => $address['city'],
              "state"          => $address['state'],
              "zip"            => $address['zip'],
              "country"        => $profileaddress["country"],
              "account"      =>  [
                    "id" => $ochuser["id"]
                ]
              ];

          }
        }
        
  
        
  
        return $ochuser_new ;
    }


    public function OchUserAddressAddFormat($user,$address = null){
    
      /* if $address is an array and address-id is null, then we need to create a new address, ("POST" method)

        $address['id']
        $address['firstname']
        $address['lastname']
        $address['address1']
        $address['address2']
        $address['city']
        $address['state']
        $address['zip']

      /*    
        {

          "addressType": "BILLING_ADDRESS",
          "name": "VIDHYASHANKAR",
          "addressLine1": "760 FAIRWAY DR",
          "city": "NAPERVILLE",
          "state": "ILLINOIS",
          "zip": "60563",
          "country": "IN",
          "primaryAddr": "true",
          "effectiveDt": "2020-07-06T13:40:33.336Z",
          "account": {
              "id": "8a7f29ac73474030017347c18bec0010"
          }

      }
      */
  
        //Recover the original values from private user session
        $tempstore = \Drupal::service('tempstore.private')->get('ssoservices');
        $ochuser = $tempstore->get('ochuser');
  
  
        $accountContacts  = $ochuser["accountContacts"][0];
        $accountUsers     = $ochuser["accountUsers"][0];
        // $accountAddresses = $ochuser["accountAddresses"][0];

        $accountAddresses = $this->getProfileAddress($ochuser["accountAddresses"]);


        $profileaddress = $this->getProfileAddress($ochuser["accountAddresses"]); //Since we don't pass the country from the form, we can get it from the profile address

        $ochuser_new = [
              "addressType"    => "SHIPPING_ADDRESS",
              "name"           => $address['firstname'] . " " . $address['lastname'],
              "addressLine1"   => $address['address1'],
              "addressLine2"   => $address['address2'],
              "city"           => $address['city'],
              "state"          => $address['state'],
              "zip"            => $address['zip'],
              "country"        => $profileaddress["country"],
              "account"      =>  [
                    "id" => $ochuser["id"]
                ]
        ];

        /*
        "addressType": "BILLING_ADDRESS",
        "name": "VIDHYASHANKAR",
        "addressLine1": "760 FAIRWAY DR",
        "city": "NAPERVILLE",
        "state": "ILLINOIS",
        "zip": "60563",
        "country": "IN",
        "primaryAddr": "true",
        "effectiveDt": "2020-07-06T13:40:33.336Z",
        "account": {
            "id": "8a7f29ac73474030017347c18bec0010"
        }
        */        

        return $ochuser_new ;
    }


    private function generatePassword ( $length = 8 ) {
      	// start with a blank password	
        $password = "";	
        // define possible characters	
        $possible = "0123456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ!@#$%^&*";	
        // set up a counter	
        $i = 0;	
        // add random characters to $password until $length is reached	
        while ($i < $length) {	
          // pick a random character from the possible ones	
          $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);	
          // we don’t want this character if it’s already in the password	
          if (!strstr($password, $char)) {	
              $password .= $char;	
              $i++;	
          }	
        }	// done!	
        return $password;	
    }


    /***
     * 
     * {
          "id": "8a7f4e6b78feee900178ff4d6e930004",
          "accountType": "D2C_CANADA",
          "createdDt": "2021-04-23T15:16:08Z",
          "updatedDt": "2021-04-23T15:16:08Z",
          "status": "ACTIVE",
          "accountContacts": [
              {
                  "id": "8a7f4e6b78feee900178ff4d6e940006",
                  "contactType": "CONSUMER",
                  "firstName": "Miguel",
                  "lastName": "Pino",
                  "middleInit": null,
                  "email": "miguel20.bam@bam.com",
                  "phone": "",
                  "phone2": null,
                  "gender": "M",
                  "source": "TastyRewards",
                  "login": "Snacksca.miguel20.bam@bam.com",
                  "birthDate": null,
                  "birthMonth": null,
                  "birthDay": null,
                  "birthYear": null,
                  "languageCode": null,
                  "languageName": null,
                  "occupation": null,
                  "optins": "",
                  "optin2": null,
                  "optOuts": "100024307_PEPSI_NEWSLETTER_PEPSICANADATASTYREWARDS_20160614",
                  "createdDt": "2021-04-23T15:16:08Z",
                  "updatedDt": "2021-04-23T15:16:08Z"
              }
          ],
          "accountUsers": [
              {
                  "id": "8a7f4e6b78feee900178ff4d73580007",
                  "userId": "miguel20.bam@bam.com",
                  "source": "TastyRewards",
                  "social": null,
                  "createdDt": "2021-04-23T15:16:09Z",
                  "updatedDt": "2021-04-23T15:16:09Z"
              }
          ],
          "accountAddresses": [
              {
                  "id": "8a7f4e6b78feee900178ff4d6e940005",
                  "addressType": "BILLING_ADDRESS",
                  "name": "Miguel Pino",
                  "addressLine1": "",
                  "addressLine2": null,
                  "city": "Montreal",
                  "state": "QC",
                  "zip": "H3E1B5",
                  "country": "CA",
                  "effectiveDt": "2021-04-23T15:16:08Z",
                  "effectiveEndDt": null,
                  "primaryAddr": true,
                  "createdDt": "2021-04-23T15:16:08Z",
                  "updatedDt": "2021-04-23T15:16:08Z"
              }
          ]
      }
     * 
     */
    public function updateOchToTR($user, $newUser){

        //check if Email exist on the Drupal
        $ochaccountid = $newUser['id'];
        $post    = $newUser["accountContacts"][0];
        $account = $newUser["accountUsers"][0];
        $addr    = $this->getProfileAddress($newUser["accountAddresses"]);

        $language = isset( $post['languageCode']) ? ($post['languageCode']=='es')?'es-us':$post['languageCode']:'en';
        $language = strtolower($language);

        if ($user->get('mail')->value == "miguel80@bam.com"){
          $language   = 'en-us';
        }

        $optin  = false;
        $optin2 = false;
        $optin3 = false;
        $optin4 = false;
        $optin5 = false;
        $optin6 = false;


        if ($language == 'en-us' || $language == 'es-us'){
          $optins = isset($post['optins'])?$post['optins']:"";
          $optins_us = $this->getSubscriptionIdsFlags($optins);
  
          $optin  = $optins_us[1];
          $optin2 = $optins_us[2];
          $optin3 = $optins_us[3];
          $optin4 = $optins_us[4];
          $optin5 = $optins_us[5];
          $optin6 = $optins_us[6];
  
        }
        else{
          if (isset($post['optins']) && $post['optins'] ) $optin = true;
          if (isset($post['optin2']) && ($post['optin2'] == 'true' || $post['optin2'] == '1' )) $optin2 = true;
        }

        

        

        $user->set("status", 1);
        $user->set('field_firstname', $post['firstName']);
        $user->set('field_lastname' , $post['lastName']);
        $user->set('field_postalcode', $addr['zip']);
        $user->set('field_gender' , $post['gender']);
        $user->set('field_bday' , $post['birthDate']);
        $user->set('field_optin' , $optin);
        $user->set('field_optin2' , $optin2); 
        $user->set('field_optin3' , $optin3); 
        $user->set('field_optin4' , $optin4); 
        $user->set('field_optin5' , $optin5); 
        $user->set('field_optin6' , $optin6); 
        $user->set('field_optin_old' , $optin);
        $user->set('field_optin_old2' , $optin2); 
        $user->set('field_optin_old3' , $optin3); 
        $user->set('field_optin_old4' , $optin4); 
        $user->set('field_optin_old5' , $optin5); 
        $user->set('field_optin_old6' , $optin6); 
        $user->set('field_source' , $account['source']);
        $user->set('field_marketoid' , $ochaccountid);  // OCH Account ID Needed to get / update the OCH apicalls
        $user->set('field_marketocookie', $post['id']);  // OCH Contact ID Needed to update the OCH apicalls
        $user->set("langcode", $language);
        $user->set("preferred_langcode", $language);
        $user->set("preferred_admin_langcode", $language);

        $user->save();
        return $user;
    }


    


    public function temptesttoken(){

      $accessToken = "eyJraWQiOiJHNWZSb0ktTlhMNXdoR1l6VjhFaUIxSXhIcnA4X2hLZjl6a1lfZ3V6STJJIiwiYWxnIjoiUlMyNTYifQ.eyJ2ZXIiOjEsImp0aSI6IkFULl9UOTdMS3JFM0tRNEdpYjRhdk5rajZBa0N2bUJkSjR6LU10XzRjU0FVRTgiLCJpc3MiOiJodHRwczovL2NvbnN1bWVyLXBlcHNpY28ub2t0YXByZXZpZXcuY29tL29hdXRoMi9hdXNybG94b2FicTlKRW1hZjBoNyIsImF1ZCI6IlNuYWNrcy5jb20iLCJpYXQiOjE2MTk5OTkxMTEsImV4cCI6MTYyMDAwNjMxMSwiY2lkIjoiMG9heWp5bG00bHRMWHNYRUgwaDciLCJ1aWQiOiIwMHV5eXIwN2V6b2VsZ2lkNTBoNyIsInNjcCI6WyJwcm9maWxlIiwib3BlbmlkIiwiU25hY2tzLmNhIiwiZW1haWwiXSwic3ViIjoiU25hY2tzY2EubWlndWVsNTRAYmFtLmNvbSIsImFjY291bnRJZCI6IjhhN2ZhYWEwNzkxZTEyZGQwMTc5MmY3Nzg1NzEwMDUzIiwiZ3JvdXBzIjpbIlNuYWNrcy5jYSJdfQ.nXSk6h7EWLlwHJaeHIiE9FXORNtshEiSUwwuzpjN_Dzp3VOoiVD8-7fhs4WERtg2ZB5nPmoHA6gAbek-1WbDBS5QCjSzrcW05UXpNlLLVnhjJT20lKj21jZ7iJ6sNz4hzJqXjqnoDEtm-SpHE-HdyP_FZAmziXP2azCyAb8x_znpWcFAZOG5OnG6xA4XGaR3ZCqq8U561FGHKhM1FvNsGi9KqVRmydsFxlLVXj4sXEQ3x2KG7xoP8uq_3sX8_Z_MpuUor2BdBloFpjkuK5rQzgSldR1Q8QmnCbzvi-rGleZW-HRH4h70dcz27JduNBPv2HhoXZkOkRhwyUHdJQOM-Q";
      $idtoken = "eyJraWQiOiJHNWZSb0ktTlhMNXdoR1l6VjhFaUIxSXhIcnA4X2hLZjl6a1lfZ3V6STJJIiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiIwMHV5eXIwN2V6b2VsZ2lkNTBoNyIsIm5hbWUiOiJNaWdmZm91ciBwaW4iLCJlbWFpbCI6Im1pZ3VlbDU0QGJhbS5jb20iLCJ2ZXIiOjEsImlzcyI6Imh0dHBzOi8vY29uc3VtZXItcGVwc2ljby5va3RhcHJldmlldy5jb20vb2F1dGgyL2F1c3Jsb3hvYWJxOUpFbWFmMGg3IiwiYXVkIjoiMG9heWp5bG00bHRMWHNYRUgwaDciLCJpYXQiOjE2MTk5OTkxMTEsImV4cCI6MTYyMDAwMjcxMSwianRpIjoiSUQud0JZejQtcWVrcF9KaHZwQkh3RzJ5ZnRvMkktN2JDUGdtU2o0c0FRNzBURSIsImFtciI6WyJwd2QiXSwiaWRwIjoiMDBvcmlmMDE5YmhpYlhYOVEwaDciLCJub25jZSI6Imo0RzNiNEd1VGV5UjljOU5nWkdXUDVHZGFIMmV1TXlScEhNUzhobkR1TUR4cTE4eHdSMWk2TXJmU01WZmUwOEgiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJTbmFja3NjYS5taWd1ZWw1NEBiYW0uY29tIiwiYXV0aF90aW1lIjoxNjE5OTk5MDkxLCJhdF9oYXNoIjoieVBpVTRUMzBIdXVCbFlsMnY4bnJaZyIsImZpZWxkX2ZpcnN0bmFtZSI6Ik1pZ2Zmb3VyIiwiZmllbGRfb3B0aW4yIjoiZmFsc2UiLCJzb2NpYWwiOiIiLCJmaWVsZF9sYXN0bmFtZSI6InBpbiIsImZpZWxkX3Bvc3RhbGNvZGUiOiJIM0UxQjUiLCJwcmVmZXJyZWRfbGFuZ2NvZGUiOiJGUiIsImZpZWxkX29wdGluIjoiZmFsc2UifQ.iGVRhcQRTTRh-lQvbsXFFCa16vxeajV8cot9HYLRISFLfA73W5jORxpKFwBiwFnlG6xIkoMtlE040pYpAuuxSyLWDOAsOE5sTt-NZcgRxu5g6T30h71kBOaj7LXZtaSD29pz5I5qPHp2iPBfzeuH6YujGBNZa0SzMW_GqY8RPc_rL-ef8L6D4P7NKUMB-CtzD-IMuT0BCNup3fltR3POvDcMv4Wb5kPtFUoXyO4q18lSoeParXfkKQup9bF98RGNKAko46flxzNoviH6x6_-i4RYJK2ElBMFasxhG37CVGvWfwpQ-Sakupt78sCB1wjjJD0URREaz6_2lszt3EC-Vw";

      $session = \Drupal::request()->getSession();
      //SAving session both Consumer accessToken and  idtoken
      $session->set('accessToken', $accessToken);
      $session->set('idtoken', $idtoken);

    }

    private function isJSON($string){
      return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function getErrorMsg(){
       return $this->errormsg;
    }

    public function getErrorKey(){
      return $this->errorKey;
   }


    public function getProfileAddress($accountAddresses){
      $profileaddress = null; 
      foreach ($accountAddresses as $accountAddress) {
        if ($accountAddress['addressType'] == "PROFILE_ADDRESS"){
          $profileaddress = $accountAddress;
          break;
        }
      }
      return $profileaddress;
    }

    public function getAddressById($accountAddresses, $id){
      $address = null; 
      foreach ($accountAddresses as $accountAddress) {
        if ($accountAddress['addressType'] == "SHIPPING_ADDRESS" && $accountAddress['id'] == $id ){
          $address = $accountAddress;
          break;
        }
      }
      return $address;
    }


    function getSubscriptionIdsFlags($och_optins){
      $mapping = $this->getSubscriptionIdsValues();
      $optin[1] = strpos($och_optins, $mapping[1]) === false?0:1;
      $optin[2] = 0;
      $optin[3] = strpos($och_optins, $mapping[3]) === false?0:1;
      $optin[4] = strpos($och_optins, $mapping[4]) === false?0:1;
      $optin[5] = strpos($och_optins, $mapping[5]) === false?0:1;
      $optin[6] = strpos($och_optins, $mapping[6]) === false?0:1;

      return $optin;
    }

    function getSubscriptionIdsStringOptIn($optins){

      $mapping = $this->getSubscriptionIdsValues();
      
      $optin = [];
      foreach ($optins as $k => $value) {
          if ($value === true || $value == "1" ){
            $optin[$k]  = $mapping[$k];
          }
      }

      return implode(";", $optin);
    }

    function getSubscriptionIdsStringOptOut($optins){

      $mapping = $this->getSubscriptionIdsValues();
      
      $optin = [];
      foreach ($optins as $k => $value) {
          if ($value == false){
            $optin[$k]  = $mapping[$k];
          }
      }

      return implode(";", $optin);
    }

    function getSubscriptionIdsValues(){
      /*
        Tasty Rewards (US) : SubscriptionID = "100022221_PTR_NEWSLETTER_PEPSICOTRNEWS_20200819"; (1)
        Tasty Makers (US) : SubscriptionID = "100022227_PEPSI_NEWSLETTER_PEPSITASTEMAKERSEMAILOPTIN_20140320"; (3)
        Pepsi Emails (US) : SubscriptionID = "100022227_PEPSI_NEWSLETTER_PURELYPEPSINEWSLETTER_20140320"; (4)
        MTN DEW emails (US) : SubscriptionID = "100022222_MOUNTAINDEW_NEWSLETTER_DEWSLETTERNEWSLETTER_20140320"; (5)
        Snacks.com US (not active yet) : SubscriptionID = "100022221_PTR_SNACKSCOM_SNACKSNEWSLETTEROPTIN_20211201"  (6)
      */
    $mapping[1] = "100022221_PTR_NEWSLETTER_PEPSICOTRNEWS_20200819";
    $mapping[2] = "";
    $mapping[3] = "Newsletter"; //"100022227_PEPSI_NEWSLETTER_PEPSITASTEMAKERSEMAILOPTIN_20140320";
    $mapping[4] = "100022227_PEPSI_NEWSLETTER_PURELYPEPSINEWSLETTER_20140320";
    $mapping[5] = "100022222_MOUNTAINDEW_NEWSLETTER_DEWSLETTERNEWSLETTER_20140320";
    $mapping[6] = "100022221_PTR_SNACKSCOM_SNACKSNEWSLETTEROPTIN_20211201";
    
    return $mapping;

    } 
    
    function ConvertFormErrors(){
      $error = [];
      $pwderr = strpos($this->errormsg,"password:");
      //$pwderr = strpos("ddd","password:");

      if(is_numeric($pwderr)){
          $error['password'] = t("Password must be at least 8 characters, a lowercase letter, an uppercase letter, a number, and a symbol.");
      }
      elseif($this->errorKey == "userIdexists"){
        $error['email'] = t("You’ve already registered to Tasty Rewards.");
      }
      else{
          $error['form'] = $this->errormsg;
      }

      return $error;
    }

    
    public function handshake(){
      // generate a random number for the sessionID
      // $url = "https://ospep-develop.azurefd.net/webservices/handshaking";


      // checking if values already exist, otherwise has to make an API call
      /*
      if ($this->hasDoneHandshake()){
          return true;
      }*/

      $sso_us_config =  \Drupal::config('ssoconfig_us.adminsettings');
      $endpoint = $sso_us_config->get('pimcore_endpoint');
      $url = rtrim($endpoint, "/") . "/handshaking";
      

      // sessionID should be a random number, we can just
      // create it based on the unique access token
      //$this->sessionId = md5(substr($this->get_consumer_token(), 0, 20) );
      $this->sessionId = md5(base64_encode(random_bytes(20)));
      

      $data = array(
          "sessionId" => $this->sessionId, 
      );

      $payload = json_encode($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_POST, 1);

      $headers = array();
      // $headers[] = 'Content-Type: application/x-www-form-urlencoded';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
          $result = json_decode($curl_result);
          if ($result->success == true){
              // TODO get the rnduts and encstr values
              $this->rnduts = $result->data->rnduts;
              $this->encstr = $result->data->encstr;
              $this->saveHandshakeValues();
              return true;
          }
      }

      return false;

  }

  public function saveHandshakeValues(){
      $session = \Drupal::request()->getSession();
      $session->set('rnduts', $this->rnduts);
      $session->set('encstr', $this->encstr);
      $session->set('sessionId', $this->sessionId);
  }

  public function hasDoneHandshake(){
      
      $session = \Drupal::request()->getSession();
      if (!$session->get('rnduts') || !$session->get('encstr') || !$session->get('sessionId') ){
          return false;
      }

      $this->rnduts = $session->get('rnduts');
      $this->encstr = $session->get('encstr');
      // $this->accessToken = $session->get('pimcore_token');
      $this->sessionId = $session->get('sessionId');

      return  true;
  }

  /*
  public function refreshHandshake(){
      $session = \Drupal::request()->getSession();

      if (!$session->get('rnduts') || !$session->get('encstr') || !$session->get('sessionId') ){
          $session = \Drupal::request()->getSession();
          $_token = $session->get('accessToken');
          return $this->handshake($_token);
      }

      $this->rnduts = $session->get('rnduts');
      $this->encstr = $session->get('encstr');
      $this->sessionId = $session->get('sessionId');
      $this->accessToken = $session->get('pimcore_token');

  }
  */

  public function getCommonHeaderParams(){
      
      $session = \Drupal::request()->getSession();

      return [
          'Content-Type: application/json',
          'sessionId: ' . $session->get('sessionId'),
          'rnduts: ' . $session->get('rnduts'),
          'encstr: ' . $session->get('encstr'),
          // @TODO: get this value from okta accessToken
          'x-auth-token: ' . $this->get_consumer_token(), 
      ];

  }

  public function getCustomer(){
      // $url = "https://ospep-develop.azurefd.net/webservices/getcustomer";
      $sso_us_config =  \Drupal::config('ssoconfig_us.adminsettings');
      $endpoint = $sso_us_config->get('pimcore_endpoint');
      $url = rtrim($endpoint, "/") . "/getcustomer";

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      

      $headers = $this->getCommonHeaderParams();
      
      // $headers[] = 'Content-Type: application/x-www-form-urlencoded';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);
      $custom_info = [];
      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
          $response = json_decode($curl_result);
          if ($response->success == true){
              $cust_data = $response->data;
              $addresses = !empty($cust_data->accountAddresses)? $cust_data->accountAddresses: null;
              $custom_info = [
                  'id' => $cust_data->id,
                  'status' => $cust_data->status,
                  'accountType' => $cust_data->accountType,
                  'accountContacts' => $cust_data->accountContacts,
                  'accountAddresses' => $addresses,
                  'shippingZipcode' => $cust_data->shippingZipcode,
              ];
          }

      }

      return $custom_info;
  }

  public function hasExistingAddress($customer_info){
      $addresses = $customer_info['accountAddresses'];
      if ( !empty($addresses)  ){
          foreach ($addresses as $key => $address) {
              if ( property_exists($address, 'addressType') 
                      && $address->addressType == 'SHIPPING_ADDRESS'){
                  return true;
              }
          }
          // && property_exists($address, 'addressType')
          // return true;
      }

      return false;
  }

  public function isNewShippingAddress($customer_info, $shipping_id){
      $addresses = $customer_info['accountAddresses'];
      if (!empty($addresses)){
          foreach ($addresses as $key => $address) {
              if ( $address->addressType == 'SHIPPING_ADDRESS' && $address->id == $shipping_id){
                  return false;
              }
          }

      }

      return true;
  }

  public function getShippingAddress(){

    if (!$this->handshake())
     return [];

          $customer_info = $this->getCustomer();
          $customer_id = $customer_info['id'];
          $addresses = $customer_info['accountAddresses'];
          foreach ($addresses as $key => $addr) {
              if ($addr->addressType == 'PROFILE_ADDRESS'){
                  unset($addresses[$key]);
              }
          }
          return $addresses;
          
      
      ;

  }

  public function pimcoreCreateOrUpdateAddress($post){
      // fields: id, firstname, lastname, address1, address2, city, state, zip, primaryAddr ( ""), language (en-us)
      $this->handshake();
      //if ( $this->handshake() ){
          $customer_info = $this->getCustomer();
          $customer_id = $customer_info['id'];
          // if ( !$this->hasExistingAddress($customer_info)  ){
          if ( $this->isNewShippingAddress($customer_info, $post['id'] )  ){
              $success = $this->pimcoreCreateAddress($post);
          } else {
              $success = $this->pimcoreUpdateAddress($post);
          }
          return $success;
      //}
      
      //return false;
      
  }

  public function pimcoreDeleteAddress($address_id){

      // if ( !$this->handshake($this->accessToken) ){
      //     return false;
      // }
      
      // $this->refreshHandshake();
      $this->handshake();

      
      $sso_us_config =  \Drupal::config('ssoconfig_us.adminsettings');
      $endpoint = $sso_us_config->get('pimcore_endpoint');
      $url = rtrim($endpoint, "/") . "/deleteaddress";
      // $customer_info = $this->getCustomer();

      $data =  [
          'id' => $address_id
      ];

      $payload = json_encode($data);
      $headers = $this->getCommonHeaderParams();

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      // curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
          $response = json_decode($curl_result);
          if ($response->success == true){
              \Drupal::logger('PIM Call Delete')->notice("Delete Address call sucess");
              return true;
          }
      }

      return false;

  }
  
  public function pimcoreUpdateAddress($post){
      // $url = "https://ospep-develop.azurefd.net/webservices/updateaddress";

      $sso_us_config =  \Drupal::config('ssoconfig_us.adminsettings');
      $endpoint = $sso_us_config->get('pimcore_endpoint');
      $url = rtrim($endpoint, "/") . "/updateaddress";
      
      
      $customer_info = $this->getCustomer();
      // fields: id, firstname, lastname, address1, address2, city, state, zip, primaryAddr ( ""), language (en-us)
      // return false;
      $country = in_array($post['language'], ['en-us', 'es-us']) ? 'US' : 'CA';

      $data =  [
          "customer" => [
              "id" => $customer_info['id'],
          ],
          "shipping" => [
              'id' => $post['id'], // shipping ID
              'shippingFirstname' => $post['firstname'],
              'shippingLastname' => $post['lastname'],
              'shippingAddressLine1' => $post['address1'],
              'shippingAddressLine2' => $post['address2'],
              'shippingZipCode' => $post['zip'],
              'shippingState' => $post['state'],
              'shippingCountry' => $country,
              'shippingCity' => $post['city'],
              'isPrimary' => empty($post['primaryAddr']) ?  false: true,
              'isViewShipping' => false,
              // 'isPrimary' => true,
              // 'isViewShipping' => true,
          ], 

      ];

      $payload = json_encode($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_POST, 1);

      $headers = $this->getCommonHeaderParams();
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
          $response = json_decode($curl_result);
          if ($response->success == true){
              \Drupal::logger('PIM Call Update')->notice("Update Address call sucess");
              return true;
          }
      }

      return false;

  }

  public function pimcoreCreateAddress($post){
      // $url = "https://ospep-develop.azurefd.net/webservices/createaddress";

      $sso_us_config =  \Drupal::config('ssoconfig_us.adminsettings');
      $endpoint = $sso_us_config->get('pimcore_endpoint');
      $url = rtrim($endpoint, "/") . "/createaddress";

      $customer_info = $this->getCustomer();
      $country = in_array($post['language'], ['en-us', 'es-us']) ? 'US' : 'CA';
      
      $data =  [
          "customer" => [
              "id" => $customer_info['id'],
          ],
          "shipping" => [
              'id' => $customer_info['id'],
              'shippingFirstname' => $post['firstname'],
              'shippingLastname' => $post['lastname'],
              'shippingAddressLine1' => $post['address1'],
              'shippingAddressLine2' => $post['address2'],
              'shippingZipCode' => $post['zip'],
              'shippingState' => $post['state'],
              'shippingCountry' => $country,
              'shippingCity' => $post['city'],
              'isPrimary' => empty($post['primaryAddr']) ?  false: true,
              'isViewShipping' => false,
              // 'isPrimary' => true,
              // 'isViewShipping' => true,
              'isUpsAddress' => true,
          ], 

      ];

      $payload = json_encode($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_POST, 1);

      $headers = $this->getCommonHeaderParams();
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception SSO Call:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('curt fail')->info($message, $context);
      } else {
          $response = json_decode($curl_result);
          if ($response->success == true){
              \Drupal::logger('PIM Call Create')->notice("Create Address call sucess");
              return true;
          }
      }

      return false;
      
  }





}
