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
use Twig\TwigFilter; 

class TextTwigExtension extends \Twig_Extension {
  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'pespsibam.texttwig_extension';
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('truncateText', 
        array($this, 'truncateText'),
        array('is_safe' => array('html')
      )),
      new \Twig_SimpleFunction('twig_explode', 
        array($this, 'twig_explode'),
        array('is_safe' => array('html')
      )),
    );
  }

  // public function getFilters()
  // {
  //       return array(
  //           // 'debug_var' => new Twig_Filter_Method($this, 'debugVar'),
  //           new TwigFilter('debug_var', array($this, 'debugVar'))
  //       );
  // }

  // public function debugVar($var){
  //   // return json_encode(get_object_vars($var));
  //   \Drupal::logger('debug')->info(" debuged " . json_encode(array_keys($var) ), []);
  //   return "debugged var";
  // }
  public function twig_explode( $text, $separator) {
     $text = strtolower(rtrim(trim($text), '|') );
     $arr_claims = explode($separator, $text);
     foreach ($arr_claims as $key => $value) {
       $arr_claims[$key] = str_replace(' ', '_', trim($value) );
     }

     $available_claims = [
        'made_in_canada' => 0,
        'gluten_free' => 0,
        'kosher' => 0,
        'no_artifical_flavours' => 0,
        'no_artifical_colours' => 0,
     ];

     foreach ($arr_claims as $key => $claim) {
       if ($available_claims[$claim] === 0){
         $available_claims[$claim] = 1;
       }
     }

     return $available_claims;
  }

  /**
   * The php function to load a given block
   */
  public function truncateText( $text, $max_count = 100) {
  	// $max_count = 100;
  	if (strlen($text) < $max_count) return $text;
  	$last_character = $text[$max_count];
  	$result = "";


  	if ($last_character != ' ') {
  		// If the last character is part of a word, cut from the space before that word
  		$words = explode(" ", $text);
  		array_pop($words);
  		$result = implode(" ", $words);
  	} else {
  		$result = substr($text, 0, $max_count);
  	}
    return $this->closetags($result) . "...";
  }

  function closetags($html) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
  }     
}