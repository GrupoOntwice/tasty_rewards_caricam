<?php

/**
 * @file
 * Contains \Drupal\fancy_login\Controller\FancyLoginController.
 */

namespace Drupal\pepsibam\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* Class CustomRestController.
*/
class ApiController extends ControllerBase {

 /**
 * Entity query factory.
 *
 * @var \Drupal\Core\Entity\Query\QueryFactory
 */
 protected $entityQuery;

 /**
 * Constructs a new CustomRestController object.

 * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
 * The entity query factory.
 */
 public function __construct(QueryFactory $entity_query) {
   $this->entityQuery = $entity_query;
 }

 /**
 * {@inheritdoc}
 */
 public static function create(ContainerInterface $container) {
   return new static(
     $container->get('entity_type.manager')
   );
 }

 /**
 * Return the 10 most recently updated nodes in a formatted JSON response.
 *
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 * The formatted JSON response.
 */
 public function queueAction() {

    
     
    $response['data'] = 'Some test data to return';
    $response['method'] = 'GET';

    return new JsonResponse( $response ); 
     
    /*
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
     
     
   // Initialize the response array.
   $response_array = [];
   
   // Load the 10 most recently updated nodes and build an array of titles to be
   // returned in the JSON response.
   // NOTE: Entity Queries will automatically honor site content permissions when
   // determining whether or not to return nodes. If this is not desired, adding
   // accessCheck(FALSE) to the query will bypass these permissions checks.
   // USE WITH CAUTION.
   $node_query = $this->entityQuery->get('node')
     ->condition('status', 1)
     ->sort('changed', 'DESC')
     ->range(0, 10)
     ->execute();
   if ($node_query) {
     $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
     foreach ($nodes as $node) {
       $response_array[] = [
         'title' => $node->title->value,
       ];
     }
   }
   else {
     // Set the default response to be returned if no results can be found.
     $response_array = ['message' => 'No new nodes.'];
   }

   // Add the node_list cache tag so the endpoint results will update when nodes are
   // updated.
   $cache_metadata = new CacheableMetadata();
   $cache_metadata->setCacheTags(['node_list']);

   // Create the JSON response object and add the cache metadata.
   $response = new CacheableJsonResponse($response_array);
   $response->addCacheableDependency($cache_metadata);

   return $response;*/
 }

}