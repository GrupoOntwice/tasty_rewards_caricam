<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RemoveXFrameOptionsSubscriber
 *
 * @author miguel.pino
 */
namespace Drupal\pepsibam\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

  public function RemoveXFrameOptions(FilterResponseEvent $event) {
    $request = $event->getRequest();
    $response = $event->getResponse();
    /*echo "<pre>";
    \Doctrine\Common\Util\Debug::dump();
    echo "</pre>";
     * 
     */
    if ($request->get('_allowiframe')){
        $response->headers->remove('X-Frame-Options');
    }
        
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('RemoveXFrameOptions', -10);
    return $events;
  }
}