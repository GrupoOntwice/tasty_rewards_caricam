<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RouteSubscriber
 *
 * @author miguel.pino
 */

namespace Drupal\pepsibam\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    
    $user = \Drupal::currentUser()->getRoles();
    /*if(!in_array("administrator", $user)) {
    
        // Change path '/user/login' to '/login'.
        if ($route = $collection->get('user.register')) {
          $route->setPath('/form/register');
        }

        if ($route = $collection->get('entity.user.edit_form')) {
          $route->setRequirement('_access', 'FALSE');
        }
        
        
    }*/  
      
    // Change path '/user/login' to '/login'.
    if ($route = $collection->get('user.register')) {
        $route->setPath('/form/register');
    }
//if(!in_array("administrator", $user)) {
//    if ($route = $collection->get('entity.user.edit_form')) {
//          $route->setRequirement('_access', 'FALSE');
//    }
//}


    
     
    // Always deny access to '/user/logout'.
    // Note that the second parameter of setRequirement() is a string.
    /*if ($route = $collection->get('user.logout')) {
      $route->setRequirement('_access', 'FALSE');
    }*/
            
  }

}