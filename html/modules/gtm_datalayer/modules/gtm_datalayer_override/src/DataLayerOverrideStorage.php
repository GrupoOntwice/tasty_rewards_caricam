<?php

namespace Drupal\gtm_datalayer_override;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the entity storage for GTM dataLayer Override.
 */
class DataLayerOverrideStorage extends SqlContentEntityStorage implements DataLayerOverrideStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadByMultipleRelatedEntity(EntityInterface $entity) {
    return $this->loadByProperties([
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ]);
  }

}
