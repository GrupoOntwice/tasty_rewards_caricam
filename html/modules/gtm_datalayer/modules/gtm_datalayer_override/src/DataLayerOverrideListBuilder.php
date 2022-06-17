<?php

namespace Drupal\gtm_datalayer_override;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder for GTM dataLayer Overrides.
 */
class DataLayerOverrideListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['entity_type'] = $this->t('Related entity type');
    $header['entity_id'] = $this->t('Related entity id');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface $entity */
    $related_entity = $entity->getRelatedEntity();

    $row['name']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl('edit-form')->toRenderArray();
    $row['entity_type'] = $related_entity->getEntityTypeId();
    $row['entity_id'] = $related_entity->id();

    return $row + parent::buildRow($entity);
  }

}
