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
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectUsEnglishContent implements EventSubscriberInterface {

  public function RedirectUsEnglish(){
	// $current_path = \Drupal::service('path.current')->getPath();
	$prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $current_language = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;

 //    $current_user = \Drupal::currentUser();
	// $roles = $current_user->getRoles();

	// if ($current_language == "en-us" &&
	// 		!in_array("administrator", $roles) &&
	// 		!in_array("editor", $roles) )
	// {
	// 	$response = new RedirectResponse('/en', 302);
	// 	$response->send();
	// }

  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('RedirectUsEnglish');
    return $events;
  }
}