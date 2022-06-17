<?php

namespace Drupal\gtm_datalayer_views\Entity;

use Drupal\gtm_datalayer\Entity\DataLayerInterface;

/**
 * Provides an interface defining a gtm_datalayer_view entity.
 */
interface DataLayerViewInterface extends DataLayerInterface {

  /**
   * Sets the view ID for the GTM dataLayer.
   *
   * @param string $view
   *   The view ID for the GTM dataLayer.
   *
   * @return $this
   */
  public function setView($view);

  /**
   * Returns the view ID for the GTM dataLayer.
   *
   * @return string
   *   The view ID for the GTM dataLayer.
   */
  public function getView();

  /**
   * Returns the dataLayer Processor instance.
   *
   * @return \Drupal\gtm_datalayer_views\Plugin\DataLayerProcessorViewBaseInterface
   *   The GTM dataLayer Processor plugin instance for this GTM dataLayer.
   */
  public function getDataLayerProcessor();

}
