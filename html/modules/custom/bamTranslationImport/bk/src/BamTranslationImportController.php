<?php
/**
 * @file
 * Contains \Drupal\bamTranslationImport\BamTranslationImportController.
 */

namespace Drupal\bamTranslationImport;
use Drupal\Core\Site\Settings;
use Drupal\Core\Controller\ControllerBase;

class BamTranslationImportController extends ControllerBase {
    public function content() {
        return array(
            '#markup' => 'aaa',
        );
    }
}

//class EpsilonServices {
//
//  protected $client;
//  protected $token;
//  protected $transactionID;
//  protected $client_id;
//  protected $client_secret;
//  protected $endpoint;
//  protected $env;
//
//
//  public function __construct() {
//
//    $this->epsilonservices = $this->epsilonApi();
//
//  }
//
//
//
//
//}
