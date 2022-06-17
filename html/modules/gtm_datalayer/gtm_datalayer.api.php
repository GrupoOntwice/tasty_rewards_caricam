<?php

/**
 * @file
 * Hooks provided by the GTM dataLayer module.
 */

use Drupal\gtm_datalayer\Controller\DataLayerControllerInterface;

/**
 * Alter the order, order item and webform submission on the webform handler.
 *
 * @param array $tags
 *   The rendered dataLayer tags to alter.
 * @param \Drupal\gtm_datalayer\Controller\DataLayerControllerInterface $controller
 *   The GTM dataLayer controller.
 *
 * @ingroup gtm_datalayer_api
 */
function hook_gtm_datalayer_tags_alter(array &$tags, DataLayerControllerInterface $controller) {
  // Add some tags.
  if ($tags['entity_type'] == 'node' && $tags['entity_id'] == 1) {
    $tags['event'] = 'myCustomEvent';
  }
}
