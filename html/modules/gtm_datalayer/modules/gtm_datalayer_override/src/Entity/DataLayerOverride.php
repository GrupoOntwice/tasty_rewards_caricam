<?php

namespace Drupal\gtm_datalayer_override\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\gtm_datalayer_override\ComputedDataLayerOverrideName;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the GTM dataLayer Override entity class.
 *
 * @ContentEntityType(
 *   id = "gtm_datalayer_override",
 *   label = @Translation("GTM dataLayer Override"),
 *   label_collection = @Translation("GTM dataLayer Override"),
 *   label_singular = @Translation("GTM dataLayer Override"),
 *   label_plural = @Translation("GTM dataLayer Override"),
 *   label_count = @PluralTranslation(
 *     singular = "@count GTM dataLayer Override",
 *     plural = "@count GTM dataLayer Overrides",
 *   ),
 *   bundle_label = @Translation("GTM dataLayer Override"),
 *   handlers = {
 *     "storage" = "Drupal\gtm_datalayer_override\DataLayerOverrideStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gtm_datalayer_override\DataLayerOverrideListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\gtm_datalayer_override\Form\DataLayerOverrideForm",
 *       "add" = "Drupal\gtm_datalayer_override\Form\DataLayerOverrideForm",
 *       "edit" = "Drupal\gtm_datalayer_override\Form\DataLayerOverrideForm",
 *       "duplicate" = "Drupal\gtm_datalayer_override\Form\DataLayerOverrideForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "gtm_datalayer_override",
 *   data_table = "gtm_datalayer_override_field_data",
 *   admin_permission = "administer gtm datalayer overrides",
 *   translatable = TRUE,
 *   constraints = {
 *     "ValidDataLayerOverrideConstraint" = {}
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "label" = "name",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/datalayers/overrides/add",
 *     "edit-form" = "/admin/structure/datalayers/overrides/{gtm_datalayer_override}/edit",
 *     "duplicate-form" = "/admin/structure/datalayers/overrides/{gtm_datalayer_override}/duplicate",
 *     "delete-form" = "/admin/structure/datalayers/overrides/{gtm_datalayer_override}/delete",
 *     "delete-multiple-form" = "/admin/structure/datalayers/overrides/delete",
 *     "collection" = "/admin/structure/datalayers/override",
 *   },
 *   field_ui_base_route = "entity.gtm_datalayer_override.collection",
 * )
 */
class DataLayerOverride extends ContentEntityBase implements DataLayerOverrideInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntityId() {
    return $this->get('entity_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRelatedEntityId($entity_id) {
    $this->set('entity_id', $entity_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntityType() {
    return $this->get('entity_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRelatedEntityType($entity_type) {
    $this->set('entity_type', $entity_type);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTags($string = TRUE) {
    return $string ?
      $this->get('tags')->value :
      Yaml::decode($this->get('tags')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function setTags($message) {
    $this->set('tags', $message);
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntity($translate = FALSE) {
    if (!$this->get('entity_type')->isEmpty() && !$this->get('entity_id')->isEmpty()) {
      $entity_type = $this->get('entity_type')->value;
      $entity_id = $this->get('entity_id')->value;
      $source_entity = $this->entityTypeManager()->getStorage($entity_type)->load($entity_id);

      // If translated is set, get the translated source entity.
      if ($translate && $source_entity instanceof ContentEntityInterface) {
        $langcode = $this->language()->getId();
        if ($source_entity->hasTranslation($langcode)) {
          $source_entity = $source_entity->getTranslation($langcode);
        }
      }

      return $source_entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setRelatedEntity(EntityInterface $entity) {
    $this->setRelatedEntityId($entity->id());
    $this->setRelatedEntityType($entity->getEntityTypeId());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('Owner'))
      ->setDescription(t('The GTM dataLayer Override owner.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The GTM dataLayer Override name.'))
      ->setComputed(TRUE)
      ->setClass(ComputedDataLayerOverrideName::class)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['tags'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Tags'))
      ->setDescription(t('The tags to override.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 0,
        'settings' => [
          'rows' => 12,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Related entity type'))
      ->setDescription(t('The entity type of the related entity.'))
      ->setRequired(TRUE)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -20,
      ]);

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Related entity ID'))
      ->setDescription(t('The entity ID of the related entity.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ]);

    return $fields;
  }

}
