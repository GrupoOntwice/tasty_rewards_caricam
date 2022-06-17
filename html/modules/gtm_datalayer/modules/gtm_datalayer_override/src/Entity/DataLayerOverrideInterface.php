<?php

namespace Drupal\gtm_datalayer_override\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines the interface for GTM dataLayer Overrides.
 */
interface DataLayerOverrideInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the GTM dataLayer Override name.
   *
   * @return string
   *   The GTM dataLayer Override name.
   */
  public function getName();

  /**
   * Returns the related entity ID.
   *
   * @return int|null
   *   The related entity type ID, or NULL in case the field has not been set on
   *   the entity.
   */
  public function getRelatedEntityId();

  /**
   * Sets the related entity ID.
   *
   * @param int $entity_id
   *   The related entity id.
   *
   * @return $this
   */
  public function setRelatedEntityId($entity_id);

  /**
   * Returns the related entity type ID.
   *
   * @return string|null
   *   The related entity type ID, or NULL in case the field has not been set on
   *   the entity.
   */
  public function getRelatedEntityType();

  /**
   * Sets the related entity type ID.
   *
   * @param string $entity_type
   *   The related entity type ID.
   *
   * @return $this
   */
  public function setRelatedEntityType($entity_type);

  /**
   * Gets the GTM dataLayer Override's related entity.
   *
   * @param bool $translate
   *   (optional) If TRUE the related entity will be translated.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The related entity.
   */
  public function getRelatedEntity($translate = FALSE);

  /**
   * Sets the GTM dataLayer Override's related entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return $this
   */
  public function setRelatedEntity(EntityInterface $entity);

  /**
   * Returns the overridden tags.
   *
   * @param bool $string
   *   (optional) If TRUE the tags will be returned as string, otherwise as
   *   array.
   *
   * @return string|array
   *   The overridden tags.
   */
  public function getTags($string = TRUE);

  /**
   * Sets the overridden tags.
   *
   * @param string $tags
   *   The overridden tags.
   */
  public function setTags($tags);

}
