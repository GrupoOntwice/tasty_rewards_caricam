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
 *   id = "bam_rest_api_cron_sync_contest",
 *   label = @Translation("TR Cron Rest API"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/v1.0/cron/sync_contest",
 *     "https://www.drupal.org/link-relations/create" = "/api/v1.0/cron/sync_contest"
 *   }
 * )
 */
class RestResourceCronSyncContest extends ResourceBase {
   /**
   * Responds to GET requests.
   */
    public function get(Request $request){
        
        //$data = Json::decode($request->getContent());
        $data = \Drupal::request()->query->get('limit');

        reSyncContestInfo($limit = 250);

        $status = 200;
        $return = array(
            "status"=>"400",
            "errorMessage"=>'No credentials were provided'
        );

        return new JsonResponse($data,$status);

    }
    


}
