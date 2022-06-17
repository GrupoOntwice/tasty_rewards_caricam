<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\epsilonservices;
use Drupal\Core\Site\Settings;

class EpsilonServices {

  protected $client;
  protected $token;
  protected $transactionID;
  protected $client_id;
  protected $client_secret;
  protected $endpoint;
  protected $env;
  

  public function __construct() {
    
    $this->epsilonservices = $this->epsilonApi();
    
  }
  
  public function getToken() {
      return $this->token;
  }
          
  function epsilonApi() {
        
        $this->env = Settings::get('environment', NULL);
        $epsilonconf = \Drupal::config('epsilonservices.settings')->get('epsilon');
        $this->client_id = $epsilonconf['client_id'];
        $this->client_secret = $epsilonconf['client_secret'];
        $this->endpoint = $epsilonconf['endpoint_'.$this->env];
        
        try {

            //Creating Client Object pointing to Epsilon end point (prod or dev depending if environment)
            $client = new \GuzzleHttp\Client(['base_uri' => $this->endpoint]);

            //Preparing parameters to call Autorize method
            $query = array('grant_type' => 'vendor', 'client_id' => $this->client_id, 'client_secret' => $this->client_secret);
            
            //Make a POST API Call
            $request = $client->request('POST', '/CPGWebServices/OAuth2/AuthenticateVendor/Authorize', ['query' => $query]);
            
            //Getting response and converting to Json 
            //normally we should  receive something like below
            /*
            {
                "AccessToken": "e8d31efb8609467f85097540d8f8fbda",
                "ApplicationId": "",
                "Expires": 86368,
                "RefreshToken": "682cb2eeef45401eb312a1937c9fe39d",
                "RequireSsl": true,
                "Success": true
             }
            */
            
            $response = json_decode($request->getBody()->getContents());
            
            if ($response->Success) { // 
                $this->client = $client;
                $this->token = $response->AccessToken;
               
                return true;
            }
            return false;
            
        }
        catch (\GuzzleHttp\Exception\BadResponseException $e) {
            dpm('BadResponseException');
            dpm($e->getMessage());
            $message = "BadResponseException:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }
        catch (\GuzzleHttp\Exception\CurlException $e) {
            dpm('CurlException');
            dpm($e->getMessage());
            $message = "CurlException:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }
        catch (\GuzzleHttp\Exception\Exception $e) {
        // Do something useful.
            dpm('Exception');
            dpm("Guzzle\Http\Exception\Exception");
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }       
        \Drupal::logger('general')->info($message, $context);
        return false;
  }  
  

 /*
  * 
  */  
  public function getTransactionID() {
      return $this->transactionID;
  }
 
  /*
   * 
   */
  public function createTransactionID() {
      $this->transactionID =  $client->request('POST', '/Transaction/GetTransactionID/data');
  }

  /*
   * Return null if There us CURL err, we can see the error log in drupal admin
   * Return True if User exist
   * Return False if User doesn't exist
   */
  public function VerifyUser($email) {
      
      $uri= '/CPGWebServices/Profile/VerifyUser/data'; 
      $data = array('ClientID'=> $this->client_id, 'UserID'=>$email, 'ExternalInfo'=>'','CultureInfo'=>''); 
      
      $response = $this->curlrequest($uri,$data);
      
      $answ = json_decode($response,true);

      return $this->getApiResponse($answ);
  }
  
  /*
   * Return null if There us CURL err, we can see the error log in drupal admin
   * Return True if User exist
   * Return False if User doesn't exist
   * 
   * Json to send
   * {
        "ClientID": "BAM",
        "ProfileID": "",
        "UserID": "pattyklinedale080701@hotmail.com",
        "SocialProvider": "",
        "SocialUID": "",
        "ExternalInfo": "",
        "CultureInfo": ""
        }
   */
  
  public function GetProfileDetails($profileid) {
      
      $uri= '/CPGWebServices/Profile/GetProfileDetails/data'; 
      $data = array(
                   "ClientID" => 'BAM',
                   "ProfileID" => $profileid,
                   "UserID" => '',
                   "SocialProvider" => '',
                   "SocialUID" => '',
                   "ExternalInfo" => '',
                   "CultureInfo" => ''
      ); 
      
      $response = $this->curlrequest($uri,$data);
      $answ = json_decode($response,true);

      return $this->getApiResponse($answ);
  }
  
  public function AddProfile($user) {
      
      $uri= '/CPGWebServices/Profile/AddProfile/data'; 
      
      $response = $this->curlrequest($uri,$user);
      
      $answ = json_decode($response,true);
      
      return $this->getApiResponse($answ);
  }

  public function UpdateProfile($user) {
      
      $uri= '/CPGWebServices/Profile/UpdateProfile/data'; 
      
      $response = $this->curlrequest($uri,$user);
      
      $answ = json_decode($response,true);

      return $this->getApiResponse($answ);
  }
  
  public function AddPromotion($promotion) {
      
      $uri= '/CPGWebServices/Promotion/AddPromotionResponses/data'; 

      $response = $this->curlrequest($uri,$promotion);
      
      $answ = json_decode($response,true);

      return $this->getApiResponse($answ);
  }
  
  public function AddSurveyProfile($survey) {
      
      $uri= '/CPGWebServices/Survey/AddSurveyProfileResponses/data'; 

      $response = $this->curlrequest($uri,$survey);
      
      $answ = json_decode($response,true);

      return $this->getApiResponse($answ);
  }
  
  
  public function getApiResponse($answ) {
      
      $method = debug_backtrace(); //to get the function/method who is calling this current method 
      
      if (!$answ) {
          $context = ['%error' => "Unexpected error" ];     
          $message = "Epsilon error in " . $method[1]['function'] . " method:  %error";
          \Drupal::logger('general')->info($message, $context);
          return null; //If unexpected error  then return null
      }
      
      if (isset($answ["Type"]) && $answ["Type"] == 'PROCESSED') 
          return $answ;
      else{
          if (isset($answ["Type"]) && $answ["Type"] == 'ERROR' ) {
            $context = ['%error' => "Err no: " . $answ["ErrorDesc"]["Number"] . " | Err Type: " . $answ["ErrorDesc"]["Type"] . " | Err Value: " . $answ["ErrorDesc"]["Value"] ];
          }
          else {
            $context = ['%error' => "There is no description for this error" ];              
          }
         $message = "Epsilon error in " . $method[1]['function'] . " method:  %error";
         
         \Drupal::logger('general')->info($message, $context);
      }
      return false;
  } 
  
  
  
  
  
  public function curlrequest($uri, $data, $header = array()){
        
    if (count($header) == 0 ){
        $header = array(
            "Authorization: Vendor " . $this->token,
            "cache-control: no-cache",
            "content-type: application/json"               
        );
    }
      
    $url = $this->endpoint . $uri;
    $data = json_encode($data); 
    
    $curl = curl_init();              
              
    curl_setopt_array($curl, array(
          CURLOPT_URL => $url , //"https://wsc-uat.peps.epsilon.com/CPGWebServices/Profile/GetProfileDetails/data",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => $header,
        ));
    $response = curl_exec($curl);
        
    $err = curl_error($curl);
    curl_close($curl);

    if (!$response || $err) {
        $message = "Exception:  %error";
        $context = ['%error' => $err ];
        \Drupal::logger('general')->info($message, $context);
        return false;
    } else {
        return $response;
    }
  }


  public function send_curl_request($email) {

       $url = $this->endpoint . '/CPGWebServices/Profile/VerifyUser/data';
       
       $data = json_encode(array('ClientID'=> $this->client_id, 'UserID'=>$email, 'ExternalInfo'=>'','CultureInfo'=>'')); 
       $curl = curl_init();
       
       curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "authorization: Vendor ". $this->token,
            "cache-control: no-cache",
            "content-type: application/json"
          ),
        ));
       
        

        $json_response = curl_exec($curl);
        /*echo "<pre>";
        var_dump(curl_error ($curl));
        echo "<br>";
        var_dump(curl_errno ($curl));
        echo "<br>";
        var_dump(curl_getinfo($curl, CURLINFO_HTTP_CODE));
        echo "<br>";
        var_dump(curl_getinfo($curl));
        echo "<br>";
        var_dump(curl_error($curl));
        echo "<br>";
       
        echo "</pre>";*/
        
        curl_close($curl);
        
        return $json_response;
  }
  
}
/*
function a() {
{
     "MTServerName":null, 
     "Type":"PROCESSED", 
     "ProfileDetails" : {
                         "ProfileID":"3263172", 
                         "Status":"A", 
                         "FirstName":"MIGTHREE", 
                         "LastName":"PING", 
                         "MiddleName":"", 
                         "Birthdate":"1980-01-01", 
                         "Gender":"M", 
                         "NamePrefix":"", 
                         "NameSuffix":"", 
                         "Address": {
                                        "AddressID":"3199173", 
                                        "AddressLine1":"", 
                                        "AddressLine2":"", 
                                        "CarrierRoute":"", 
                                        "ChannelCode":"DM", 
                                        "ChannelDesc":"Direct Mail", 
                                        "City":"VERDUN", 
                                        "CountryCode":"CA", 
                                        "DeliveryPointBarCode":"", 
                                        "GlobalAddressID":"0", 
                                        "Latitude":"", 
                                        "Location":"P", 
                                        "LocationDesc":"Primary", 
                                        "Longitude":"", 
                                        "MailScore":"Y", 
                                        "MultiTypeCode":"", 
                                        "PostalCode":"H3E 1B5", 
                                        "PreferredInd":"Y", 
                                        "StateCode":"QC", 
                                        "Status":"A" 
                                     }, 
                         "Emails": [ {
                                        "ChannelCode":"EM", 
                                        "ChannelDesc":"Email", 
                                        "ContactScore":"0", 
                                        "DeliveryStatus":"G", 
                                        "EmailAddress":"bam3@bam.com", 
                                        "EmailID":"3024340", 
                                        "Location":"P", 
                                        "LocationDesc":"Primary", 
                                        "PreferredInd":"Y"
                                            
                                    }],
                         "Phones":[], 
                         "ProfileSubscriptions":[{
                                                   "ChannelCode":"EM", 
                                                   "OptStatus":"Y", 
                                                   "SubscriptionDesc":null, 
                                                   "SubscriptionID":"50022", 
                                                   "SubscriptionName":"Pepsi Canada Tasty Rewards"
                                                 }, 
                                                   {
                                                    "ChannelCode":"WB", 
                                                    "OptStatus":"Y", 
                                                    "SubscriptionDesc":null, 
                                                    "SubscriptionID":"50024", 
                                                    "SubscriptionName":"Pepsi Canada Tasty Rewards Website"
                                                   }
                                                 ], 
                         "SocialProfiles":[], "ProfileCredentials":[]}, "Additional":"", "ExternalInfo":""}" 
                                                 }
}
 * 
 */