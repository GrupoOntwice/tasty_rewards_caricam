<?php

namespace Drupal\bam_rest_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "bam_rest_api_lead",
 *   label = @Translation("TR Login Rest API"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/v1.0/user/login",
 *     "create" = "/api/v1.0/user/login",
 *   }
 * )
 */
class RestResourceLead extends ResourceBase {
 /*
  * 
  {
    "username": "email@mail.com",
    "password": "Mybeautypass"
}
}
  */
    
   /**
   * Responds to POST requests.
   */
    public function post(Request $request){
        
            $data = Json::decode($request->getContent());

            /*            
            $err = isset($data['data']['context']['credential']['username'])?$data['data']['context']['credential']['username']:'';
            $message = "User:  %error";
            $context = ['%error' => json_encode($data)];
            \Drupal::logger('LOGIN API CALL')->info($message, $context);
            */

            $status = 404;
            
            if(isset($data['data']['context']['credential']['username']) && $data['data']['context']['credential']['password'] ){

                $user = $data['data']['context']['credential']['username'];
                $pass = $data['data']['context']['credential']['password'];
                
                //remove "Snacksca." prefix for the user name
                $count = 1;
                $user = str_ireplace("snacksca.", "", $user, $count);
                
                $uid = \Drupal::service('user.auth')->authenticate($user, $pass);

                if ($uid > 0){
                    $status = 200;
                    /*
                    {
                        "commands":[
                                    { "type":"com.okta.action.update",
                                       "value":{
                                        "credential":"VERIFIED"
                                        }
                                    }
                                    ]
                    }*/
                    $return = array(
                        "commands"=>array((array("type"=>"com.okta.action.update","value"=>array("credential"=>"VERIFIED")))
                        )
                    );

                    /*
                    $return = array(
                        "tokenSuccess"=>true,
                        "migrationRequired"=>false
                    );*/
                }
                else{
                    $status = 403;


                    $return = array(
                        "commands"=>array((array("type"=>"com.okta.action.update","value"=>array("credential"=>"UNVERIFIED")))
                        )
                    );
                }
            }
            else{
                $status = 400;
                $return = array(
                    "status"=>"400",
                    "errorMessage"=>'No credentials were provided'
                );
            }

            /*    
            $message = "Status:  %error";
            $context = ['%error' => json_encode($return)];
            \Drupal::logger('LOGIN API CALL')->info($message, $context);
            */


        return new JsonResponse($return,$status);

    }
    


}
