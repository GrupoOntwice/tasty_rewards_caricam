<?php

namespace Drupal\gtm_datalayer_views\Plugin;

use Drupal\gtm_datalayer\Plugin\DataLayerProcessorInterface;
use Drupal\views\ViewExecutable;

/**
 * Defines the interface for GTM dataLayer Form Processors.
 */
interface DataLayerProcessorViewBaseInterface extends DataLayerProcessorInterface {

  /**
   * Configures dataLayer view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view object.
   * @param string $view_id
   *   The view ID.
   *
   * @return $this
   */
  public function configure(ViewExecutable $view, string $view_id);

}
