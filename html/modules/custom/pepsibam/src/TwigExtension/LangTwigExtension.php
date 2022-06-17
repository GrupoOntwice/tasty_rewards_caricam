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
namespace Drupal\pepsibam\TwigExtension;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

class LangTwigExtension extends \Twig_Extension {
  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'pespsibam.twig_extension';
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('convertLangUrl', 
        array($this, 'convertLangUrl'),
        array('is_safe' => array('html')
      )),
    );
  }

  /**
   * The php function to load a given block
   */
  public function convertLangUrl( $path ,$parameters = [], $options = [], $langcode = '') {
    //$block = \Drupal\block\Entity\Block::load($block_id);
    //$paths = \Drupal::request()->getRequestUri();
    //$paths = \Drupal::service('path.current')->getPath();
    //$url = Url::fromUri($path);
    //$url = Url::fromUri($path);
    
    //$path = \Drupal::service('path.current')->getPath();
    
    //\Doctrine\Common\Util\Debug::dump($link);
    //\Doctrine\Common\Util\Debug::dump("XXXXXXXXXXXX");
      
    $current_path = \Drupal::service('path.current')->getPath();
    $url = Url::fromUri('internal:'.$current_path);
    
    $internal = $url->getRouteName();
    /*\Doctrine\Common\Util\Debug::dump($current_path);
        
        $lmanager = \Drupal::service('path.alias_manager');
        $result = $lmanager->getAliasByPath('/node/171','fr');
        
     */
    \Doctrine\Common\Util\Debug::dump($url);
    $mylanguage = \Drupal::languageManager()->getCurrentLanguage()->getId()=='fr'?'en':'fr';
    
    $lmanager = \Drupal::service('path_alias.manager');
    $result = $lmanager->getAliasByPath($current_path,$mylanguage);
    
    
    
    return $internal;  //\Drupal::entityManager()->getViewBuilder('block')->view($block);
  }    
}