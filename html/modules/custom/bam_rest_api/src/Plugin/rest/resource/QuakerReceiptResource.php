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
 *   id = "bam_rest_api_quaker_receipt",
 *   label = @Translation("Quaker Receipt REST api "),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/receipt/process",
 *     "create" = "/api/receipt/process",
 *   }
 * )
 */
class QuakerReceiptResource extends ResourceBase {
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
            
            $request_content = $request->getContent();
            log_var($request_content, "Request content ", "3tierLogic");
            $data = Json::decode($request_content);
            log_var($data, "3Tier logic api ", "3tierLogic");
            $headers = $request->headers->all();
            log_var($headers, "Headers 3Tier ", "3tierLogic");

            // Accept array structure
            /*3Tier logic api Array ( [Screen Shot 2021-07-08 at 2.03.42 PM.png] => Array ( [data] => Array ( [store] => [products] => Array ( [0] => Array ( [upcCode] => 055577105269 [quantity] => 1 [description] => Quaker Life Chocolate * [productAmount] => 0 ) ) [amount] => 0 [date] => [qualifyingAmount] => 0 ) [declineReason] => [UUID] => 5f98ee2d-39d1-4b60-86ff-5a8cf5e083d7 [submissionDate] => 1625767483476 [rawData] => ﻿ Walmart HOW DID WE DO TODAY? Complete cm sheet customer survey ai SURVEY.WALMART.CA fori monthly chance^ WIN lot 3 $1000 GIFT CARDS 5ee approved ) ) */

            // Reject array structure
            /*
            3Tier logic api Array ( [Screen Shot 2021-07-08 at 2.07.20 PM.png] => Array ( [data] => Array ( [store] => [products] => Array ( ) [amount] => 0 [date] => [qualifyingAmount] => 0 ) [declineReason] => invalid receipt [UUID] => ac814ea1-e4a0-4d83-ba83-34764c6c3ab3 [submissionDate] => 1625767738911 [rawData] => [status] => rejected ) ) 
             */
            
            $return = ['success' => 1];
            $status = 200;

            if (!empty($data) && is_array($data) ){
              $keys = array_keys($data);
              $filename = $keys[0];

              $verified = 0;
              if ($data[$filename]['status'] == 'approved'){
                $verified = 1;
              } elseif(trim($data[$filename]['status'] ) == 'rejected'){
                $verified = 0;
              } else {
                // empty status 
                $status = 400;
                $return = [
                  'success' => 0,
                  'error' => 'Bad Request: status is missing',
                ];

                return new JsonResponse($return,$status);
              }

              $obj_entry = fetch_receipt_entry($filename); 
              if (!empty($obj_entry)){
                $email = $obj_entry->email;
                $lang = $obj_entry->language;
                $nb_email = 0;

                if (!$verified){
                  send_email_receipt($email, $verified, $lang, $obj_entry->firstname);
                } else {
                  $nb_products_total = get_total_quaker_products($data[$filename]['data']['products']);
                  if ($nb_products_total <= 3){
                    $nb_email = 1;
                    send_email_receipt($email, $verified, $lang, $obj_entry->firstname);
                  } elseif ($nb_products_total > 3 && $nb_products_total <6){
                    $nb_email = 2;
                    // We send 2 emails
                    for ($x = 0; $x < $nb_email; $x++) {
                      send_email_receipt($email, $verified, $lang, $obj_entry->firstname);
                    }
                  } elseif ($nb_products_total > 5 && $nb_products_total < 8){
                    $nb_email = 3;
                    for ($x = 0; $x < $nb_email; $x++) {
                      send_email_receipt($email, $verified, $lang, $obj_entry->firstname);
                    }

                  } elseif ($nb_products_total > 7){
                    $nb_email = 4;
                    for ($x = 0; $x < $nb_email; $x++) {
                      send_email_receipt($email, $verified, $lang, $obj_entry->firstname);
                    }
                  }
                }


                $values = [
                  'status' => $data[$filename]['status'],
                  'store' => $data[$filename]['data']['store'],
                  'products' => serialize($data[$filename]['data']['products']),
                  'decline_reason' => $data[$filename]['declineReason'],
                  'uuid' => $data[$filename]['UUID'],
                  'amount' => $data[$filename]['data']['amount'],
                  'filename' => $filename,
                  'email' => $email,
                  'nb_email_sent' => $nb_email,
                ];
                update_receipt_entry($values);
                
              } else {
                // Data not in the expected format
                $return = [
                  'success' => 0,
                  'error' => 'Bad Request: ',
                ];

                  if (!is_array($data)){
                    $return['error'] .= 'data is in the wrong format';
                  }
              }


            } else {
              $return = [
                'success' => 0,
                'error' => 'Bad Request: ',
              ];

              if (empty($data)){
                $return['error'] .= 'No payload was sent';
              }

              $status = 400;
            }

            // @TODO: keep track of receipts approved, declined, decline reasons.
            
            



        return new JsonResponse($return,$status);

    }
    


}

