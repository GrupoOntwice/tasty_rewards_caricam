<?php

namespace Drupal\gtm_datalayer_views\Controller;

use Drupal\gtm_datalayer\Controller\DataLayerControllerInterface;
use Drupal\views\ViewExecutable;

/**
 * Defines the interface for GTM dataLayer Views Controller.
 */
interface DataLayerViewsControllerInterface extends DataLayerControllerInterface {

  /**
   * Builds Google Tag Manager dataLayer pusher (script).
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view object.
   */
  public function buildGtmPusherScript(ViewExecutable $view);

}
