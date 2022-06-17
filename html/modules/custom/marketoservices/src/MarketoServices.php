<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\marketoservices;
use CSD\Marketo\Client;

class MarketoServices {

  protected $marketoservices;

  public function __construct() {
    
    $this->marketoservices = $this->marketoApi();
    
  }
  
  public function createOrUpdateLeads($lead) {

      return  $this->marketoservices->createOrUpdateLeads($lead);

  }
  
  
  public function associateLead($id, $cookie = null, $args = array(), $returnRaw = false)  {
        
      //return  $this->marketoservices->associateLead($id, $cookie , $args = array(), $returnRaw = false);
      return  $this->marketoservices->associateLead($id, $cookie , $args = array(), $returnRaw = false);
      
      /*
      $rpta = new \CSD\Marketo\Response\AssociateLeadResponse;
      
      $rpta->getError()
      $rpta->getRequestId()
      $rpta->isSuccess()
       * 
       */
  }
  
  
  
  function marketoApi() {
        
        $marketoconf = \Drupal::config('marketoservices.settings')->get('marketo');
        try {
            $marketoapi = Client::factory(array(
                    'client_id' => $marketoconf['client_id'],
                    'client_secret' => $marketoconf['client_secret'],
                    'munchkin_id' => $marketoconf['munchkin_id'] // alternatively, you can supply the full URL, e.g. 'url' => 'https://100-AEK-913.mktorest.com'
             ));
             return $marketoapi;
        }
        catch (Guzzle\Http\Exception\BadResponseException $e) {
            dpm($e->getMessage());
            $message = "BadResponseException:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }
        catch (Guzzle\Http\Exception\CurlException $e) {
            dpm($e->getMessage());
            $message = "CurlException:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }
        catch (Guzzle\Http\Exception\Exception $e) {
        // Do something useful.
            dpm("Guzzle\Http\Exception\Exception");
            $message = "Exception:  %error";
            $context = ['%error' => $e->getMessage() ];
            
        }       
        \Drupal::logger('general')->info($message, $context);
        return false;
}  
  
  
  

}