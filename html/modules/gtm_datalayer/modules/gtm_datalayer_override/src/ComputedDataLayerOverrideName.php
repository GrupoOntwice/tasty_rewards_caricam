<?php

namespace Drupal\gtm_datalayer_override;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface;

/**
 * Represents the GTM dataLayer Override.
 */
class ComputedDataLayerOverrideName extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    /** @var \Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface $entity */
    $entity = $this->getEntity();
    if ($entity instanceof DataLayerOverrideInterface) {
      $related_entity = $entity->getRelatedEntity();

      $this->list[0] = $this->createItem(0, $related_entity->label());
    }

    return NULL;
  }

}
