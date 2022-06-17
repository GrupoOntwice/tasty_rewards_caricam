<?php

namespace Drupal\gtm_datalayer_override;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines an interface for GTM dataLayer Override entity storage.
 */
interface DataLayerOverrideStorageInterface extends EntityStorageInterface {

  /**
   * Loads the given entity's GTM dataLayer Overrides.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The related entity.
   *
   * @return \Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface[]
   *   An array of loaded GTM dataLayer Override entities.
   */
  public function loadByMultipleRelatedEntity(EntityInterface $entity);

}
