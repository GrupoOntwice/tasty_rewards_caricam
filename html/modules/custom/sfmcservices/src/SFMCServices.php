<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\sfmcservices;

use Drupal\Core\Site\Settings;
use FuelSdk\ET_Client;
use FuelSdk\ET_TriggeredSend;
use FuelSdk\ET_DataExtension_Row;
use FuelSdk\ET_POST;
use FuelSdk\ET_GET;

class SFMCServices {

    protected $client;
    protected $country;
    protected $oauth_token; 
    protected $primary_key = "SubscriberKey";
    protected $sfmc_config;


    public function __construct() {
        $this->sfmc_config =  \Drupal::config('sfmcservices.adminsettings');
    }


    public function init_api($country_code, $isOAuth_v1 = false){
        $this->country = $country_code;
        $this->sfmcservices = $this->sfmcApi($this->country, $isOAuth_v1);
        $this->init_token();
    }

    private function init_token(){
      $session = \Drupal::service('session');

      $this->oauth_token = array(
          'token_value' => !empty($session->get('oauth_token'))? $session->get('oauth_token'): '', 
          'expires_in' => !empty($session->get('oauth_token_expiry'))? $session->get('oauth_token_expiry'): 0, 
        );
    }

    private function sfmcApi($country_code, $isOAuth_v1) {
      $config_prefix = $this->get_config_prefix($country_code);
        $this->country = $country_code;
        //$MID = $this->sfmc_config->get('sfmcservices_mid'); 
      if ($isOAuth_v1 === false){
         return;
      }

        $params = array(
            'trace' => 1,
            'appsignature' => 'none',
            'clientid' =>  $this->sfmc_config->get($config_prefix . 'sfmcservices_clientID') , // usa_sfmcservices_clientID for USA
            'clientsecret' => $this->sfmc_config->get($config_prefix . 'sfmcservices_clientSecret'), // usa_sfmcservices_clientSecret for USA
            'defaultwsdl' => 'https://webservice.exacttarget.com/etframework.wsdl',
            'baseUrl' => $this->sfmc_config->get('sfmcservices_baseurl'), //'https://mcmqczd118jdz8xqdfsbxdwh96f0.rest.marketingcloudapis.com/',
            'baseAuthUrl' => $this->sfmc_config->get('sfmcservices_base_auth_url') , //'https://mcmqczd118jdz8xqdfsbxdwh96f0.auth.marketingcloudapis.com/', 
            'baseSoapUrl' => $this->sfmc_config->get('sfmcservices_endpoint'), //'https://mcmqczd118jdz8xqdfsbxdwh96f0.soap.marketingcloudapis.com/Service.asmx ',
        );
        try {
            $this->client = new ET_Client(true, true, $params);
            $this->country = $country_code;
        } catch (Exception $ex) {
            // Do something useful.
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage()];
            \Drupal::logger('general')->info($message, $context);
        }
    }

    private function get_config_prefix($country_code = 'ca'){
      return $country_code == 'ca'? '': 'usa_';
    }

    public function get_auth_token($is_trigger_email = false){
      if ($this->oauth_token['token_value'] && time() < $this->oauth_token['expires_in']){
        return $this->oauth_token['token_value'];
      }

      $url = rtrim( $this->sfmc_config->get('sfmcservices_base_auth_url'), '/') . "/v2/token";
      
      $config_prefix = $this->country == 'ca'? '': 'usa_';
      // email_read email_write email_send
      $scope = !$is_trigger_email? "data_extensions_read data_extensions_write": "email_read email_write email_send";
      $data = array(
        "grant_type" => "client_credentials", 
        "client_id" => $this->sfmc_config->get($config_prefix . 'sfmcservices_clientID_2'), 
        "client_secret" => $this->sfmc_config->get($config_prefix . 'sfmcservices_clientSecret_2'), 
        "scope" => $scope, 
        "account_id" => $this->sfmc_config->get($config_prefix . 'sfmcservices_mid'), 
      );

      $payload = json_encode($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
      curl_setopt($ch, CURLOPT_POST, 1);

      $headers = array();
      $headers[] = 'Content-Type: application/json';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $curl_result = curl_exec($ch);

      if (curl_errno($ch)) {
          $message = "Exception:  %error";
          $context = ['%error' => curl_error($ch)];
          \Drupal::logger('general')->info($message, $context);
      } else {
        $result = json_decode($curl_result);
        $session = \Drupal::service('session');
        $session->set('oauth_token', $result->access_token);
        $session->set('oauth_token_expiry', time() + $result->expires_in);

        curl_close ($ch);
        return  $result->access_token;
      }
      curl_close ($ch);
      return false;
    }

    /**
     * Send a triggeredsend Email,
     * receive as parameter, 
     *     $props: array with CustomerKey (Id of the triggeredsend item
     *     $subscriber : array with info related to the subscriber,
     *     Example: 
     *     $props = ['CustomerKey' => 'Welcome3'];
     *     $subscriber = ["EmailAddress" => "email@domain.com", "SubscriberKey" => "email@domain.com" ,
     *                     "Attributes" => ["Name"=>"FirstName", "Value"=> "miguel" ]
     *                   ];
     */
    public function sendTriggeredEmail($key, $subscriber, $country_code) {
        
        //Call the Key value for an speciofic mail
        $ck = $this->sfmc_config->get($key);
        $props = ['CustomerKey' => $ck];
        $sendTrigger = new ET_TriggeredSend();
        $sendTrigger->props = $props;
        $sendTrigger->subscribers = [$subscriber];
        
        // $this->setBusinessUnitByCountry($country_code);
        $sendTrigger->authStub = $this->client;
        $sendResult = $sendTrigger->send();
        /*
          print_r('Send Status: '.($sendResult->status ? 'true' : 'false')."\n");
          print 'Code: '.$sendResult->code."\n";
          print 'Message: '.$sendResult->message."\n";
          print 'Results: '."\n";
          print_r($sendResult->results);
          print "\n---------------\n";
        */
        return $sendResult->status;
        
    }


    public function sendTriggeredEmail_V2($key, $subscriber, $country_code) {

        \Drupal::logger('sfmcservices')->info(" trigger email " . print_r($subscriber, 1), []);
        // return ;

        $ck = $this->sfmc_config->get($key);
        $config_prefix = $this->get_config_prefix($country_code);


        $token = $this->get_auth_token($is_trigger_email = true);
        \Drupal::logger('sfmcservices')->info(" Access Token " . print_r($token, 1), []);

        $payload = json_encode($subscriber);
        // /messaging/v1/messageDefinitionSends/{triggeredSendDefinitionId}/send
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/messaging/v1/messageDefinitionSends/key:" . $ck . "/send";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            print_r($context);
            \Drupal::logger('general')->info($message, $context);
            // return false;
        }
        \Drupal::logger('sfmcservices')->info(" URL   " . $url, []);
        \Drupal::logger('sfmcservices')->info(" Payload  " . print_r($subscriber, 1), []);
        \Drupal::logger('sfmcservices')->info(" Results  " . print_r($result, 1), []);

        return $result;
        
    }

    public function setBusinessUnitByCountry($country_code){
      if (!empty($country_code) && $country_code != $this->country){
        $this->sfmcservices = $this->sfmcApi($country_code);
      }
    }

    public function syncPoll($poll_data, $country_code){
       $config_prefix = $this->get_config_prefix($country_code);


        $token = $this->get_auth_token();
        // sfmcservices_data_extension_key
        // $data_extension = $this->sfmc_config->get($config_prefix . 'sfmcservices_data_extension_key');
        // $data_extension = 'CF_Poll_Vote'; //@TODO: hardcoded for now but need to get this from the config form
        $data_extension = $this->sfmc_config->get($config_prefix .'sfmcservices_poll_data_extension_key');

        $payload = json_encode($poll_data);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            print_r($context);
            \Drupal::logger('general')->info($message, $context);
            // return false;
        }

        return $result;
      
    }

    public function sendRequestV1($payload, $data_extension, $country_code){
       $config_prefix = $this->get_config_prefix($country_code);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";
        $token = $this->get_auth_token();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);

        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            print_r($context);
            \Drupal::logger('general')->info($message, $context);
            return false;
        }

        return $result;

    }

    public function syncDrupalIDs($user_info, $country_code){

       $config_prefix = $this->get_config_prefix($country_code);
        $data_extension = $this->sfmc_config->get($config_prefix .'sfmcservices_mapping_email_uid');
        $payload = json_encode($user_info);
        $result = $this->sendRequestV1($payload, $data_extension, $country_code);
        return $result;
      
    }

    public function syncContest($contest_data, $country_code){
       $config_prefix = $this->get_config_prefix($country_code);


        $token = $this->get_auth_token();
        $data_extension = $this->sfmc_config->get($config_prefix .'sfmcservices_contest_data_extension_key');
        $payload = json_encode($contest_data);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            print_r($context);
            \Drupal::logger('general')->info($message, $context);
            // return false;
        }

        return $result;
      
    }

    public function syncContestWinners(array $leads, $country_code){
        // $this->setBusinessUnitByCountry($country_code);
        $config_prefix = $this->get_config_prefix($country_code);
        
        $token = $this->get_auth_token();
        // sfmcservices_data_extension_key
        $data_extension = $this->sfmc_config->get($config_prefix . 'sfmcservices_winners_data_extension_key');

        $payload = json_encode($leads);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            \Drupal::logger('general')->info($message, $context);
            // return false;
        }

        return $result;
    }

    public function createOrUpdateLeads(array $leads, $country_code = 'ca') {
        // $this->setBusinessUnitByCountry($country_code);
        $config_prefix = $this->get_config_prefix($country_code);

        // $leads[0]['keys']['SourceID'] = $this->sfmc_config->get($config_prefix . 'sfmcservices_sourceID');
        $MID = $this->sfmc_config->get($config_prefix . 'sfmcservices_mid'); // sfmcservices_mid
        
        // $Opt_value = $MID . '_' . $this->sfmc_config->get($config_prefix . 'sfmcservices_nl_label');
        $Opt_value = $leads[0]['values']['optinsValue'];
        if ($leads[0]['values']['unsubscribed']){
          $leads[0]['values']['OptOuts'] = $Opt_value;
          $leads[0]['values']['OptIns'] = "";
        } else {
          $leads[0]['values']['OptIns'] = $Opt_value;
          $leads[0]['values']['OptOuts'] = "";
        }
        unset($leads[0]['values']['optinsValue']);

        $token = $this->get_auth_token();
        // sfmcservices_data_extension_key
        $data_extension = $this->sfmc_config->get($config_prefix . 'sfmcservices_data_extension_key');

        $payload = json_encode($leads);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            \Drupal::logger('general')->info($message, $context);
            // return false;
        }

        return $result;
    }

    public function setContestsStatus($contest_status , $coupon_status, $country_code = 'ca'){
      $config_prefix = $this->get_config_prefix($country_code);

      $leads = array();
      $leads[0]['keys']['SourceID'] = $this->sfmc_config->get($config_prefix . 'sfmcservices_sourceID'); //"CA_FRITOLAY_GENERALPRODUCTS_LOYALTY_20190508";
      $leads[0]['values']['flag_contest'] =  $contest_status ;
      $leads[0]['values']['flag_coupon'] =  $coupon_status;

        $token = $this->get_auth_token();
        // sfmcservices_data_extension_key
        // $data_extension = "active-coupons";
        $data_extension = $this->sfmc_config->get($config_prefix . 'sfmcservices_contest_status_DE'); //;
        if (empty($data_extension)){
           \Drupal::logger('general')->info("Contest Status data extension is empty " . $country_code, []);
        }

        $payload = json_encode($leads);
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/hub/v1/dataevents/key:" . $data_extension . "/rowset";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);
        if (curl_errno($ch)) {
            $message = "Exception:  %error";
            $context = ['%error' => curl_error($ch)];
            \Drupal::logger('general')->info($message, $context);
            // return false;
        } 
        if (empty($result->errorcode) ) {
          \Drupal::logger('general')->info(" Contest status API call successful for " . $country_code, []);
        }

        return $result;
    }



    /**
     * Retrieves a lead profile if it exists
     * @param  [email] $key a unique identifier for the lead profile
     * @return boolean
     */
    public function find_lead_by_key($key) {

        $data_extension_row = new ET_DataExtension_Row();
        $data_extension_row->authStub = $this->get_client();

        $data_extension_row->CustomerKey = $this->sfmc_config->get('sfmcservices_data_extension_key');
        $data_extension_row->props = array(
            $this->primary_key => "",
                // "FirstName" => "",
                // "LastName" => "",
                // "Team" => "",
        );
        // Here "Email" is used as primary key
        $data_extension_row->filter = array('Property' => $this->primary_key, 'SimpleOperator' => 'equals', 'Value' => $key);
        $response = $data_extension_row->get();
        if (empty($response->results)) {
            return false;
        } else {
            return true;
        }
    }

    public function get_unsubscribed_emails($country_code = 'ca'){
        $config_prefix = $this->get_config_prefix($country_code);

        // --------
        
        $token = $this->get_auth_token();
        // sfmcservices_data_extension_key
        $data_extension = $this->sfmc_config->get($config_prefix . 'sfmcservices_customerkey_unsubscribed');
        $filter = "";
        $url = rtrim( $this->sfmc_config->get($config_prefix . 'sfmcservices_baseurl'), '/') . "/data/v1/customobjectdata/key/" . $data_extension . "/rowset" . $filter;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);


        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        $result = json_decode($curl_result);

                
        if (empty($result) || intval($result->count) === 0) {
            return false;
        } else {

          $all_email_addresses = [];
          try{
            // foreach ($response->results as $key => $objProfile) {
            foreach ($result->items as $key => $objProfile) {
              // $all_subscriberKeys[] = $objProfile->values->subscriberkey;
              if (empty($objProfile->values->emailaddress)){
                if (filter_var($objProfile->keys->subscriberkey, FILTER_VALIDATE_EMAIL) ){
                   $all_email_addresses[] = $objProfile->keys->subscriberkey;
                } 
              }
              else {
                 $all_email_addresses[] = $objProfile->values->emailaddress;
              }
            }
            return array_unique($all_email_addresses);
          } catch (Exception $e) {
            // Do something useful.
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage()];
            \Drupal::logger('general')->info($message, $context);
            return null;
          }
        }

    }

    public function find_email_by_subscriberKey($subscriberKeys, $country_code = 'ca'){
      $config_prefix = $this->get_config_prefix($country_code);
    
      $data_extension_row = new ET_DataExtension_Row();
        $data_extension_row->authStub = $this->get_client();

        $data_extension_row->CustomerKey = $this->sfmc_config->get($config_prefix . 'sfmcservices_mapping_email_subscriberkey');
        // $data_extension_row->CustomerKey =  "mapping_email_subscriberkey"; // $this->sfmc_config->get('usa_sfmcservices_data_extension_key_fb_ad');
        $data_extension_row->props = array(
           // "SubscriberKey" => "",
           "EmailAddress" => "EmailAddress",
        );
        
        $data_extension_row->filter = array('Property' => "SubscriberKey", 'SimpleOperator' => 'IN', 'Value' => $subscriberKeys);
              
        $response = $data_extension_row->get();
        $all_emails = [];
        
        if (empty($response->results)) {
            return false;
        } else {
          try{
            foreach ($response->results as $key => $objProfile) {
              $all_emails[] = $objProfile->Properties->Property->Value;
            }
          } catch (Exception $e) {
            // Do something useful.
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage()];
            \Drupal::logger('general')->info($message, $context);
            return null;
        }

        return $all_emails;

        }
    }

    /**
     * Retrieves a lead profile if it exists
     * @param  [email] $key a unique identifier for the lead profile
     * @return boolean
     */
    public function find_email_fb_list($email) {

        $data_extension_row = new ET_DataExtension_Row();
        $data_extension_row->authStub = $this->get_client();

        $data_extension_row->CustomerKey =  $this->sfmc_config->get('usa_sfmcservices_data_extension_key_fb_ad');
        $data_extension_row->props = array(
           "email" => "Email",
           "first_name" => "FirstName",
           "last_name" => "LasttName",
           "zip_code" => "ZipCode",
           "Date Collected" => "Date Collected",
                // "Team" => "",
        );
        // Here "Email" is used as primary key
        $data_extension_row->filter = array('Property' => "email", 'SimpleOperator' => 'equals', 'Value' => $email);
        $response = $data_extension_row->get();
        
        if (empty($response->results)) {
            return false;
        } else {
          try{
            
            $user_properties = $response->results[0]->Properties->Property;
            $user_profile = array(
              'email' => $user_properties[0]->Value,
              'first_name' => $user_properties[1]->Value,
              'last_name' => $user_properties[2]->Value,
              'zip_code' => $user_properties[3]->Value,
            );
            
            return $user_profile;

          } catch (Exception $e) {
            // Do something useful.
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage()];
            \Drupal::logger('general')->info($message, $context);
            return null;
        }



        }
    }

    public function get_client() {
        return $this->client;
    }

    public function get_config() {
        return $this->sfmc_config;
    }

}
