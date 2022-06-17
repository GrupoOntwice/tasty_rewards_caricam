<?php

/**
 * @file
 * Contains \Drupal\my_event_subscriber\EventSubscriber\MyEventSubscriber.
 */

namespace Drupal\bammaintenance200\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;



/**
 * Event Subscriber MyEventSubscriber.
 */
class MaintenanceEventSubscriber implements EventSubscriberInterface {

    
    public function onKernelResponse(FilterResponseEvent $event) {
        $admin_context = \Drupal::service('router.admin_context');
        $status_code = 200;
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $state = \Drupal::state()->get('system.maintenance_mode');
        if ($state === 1 && !$admin_context->isAdminRoute()) {
            if (is_numeric($status_code)) {
                $response->setStatusCode($status_code);
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents() {
        $events[KernelEvents::RESPONSE][] = array('onKernelResponse', 31);
        return $events;
    }

}
