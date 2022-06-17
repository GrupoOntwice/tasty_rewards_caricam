<?php

namespace Drupal\gtm_datalayer_override\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a GTM dataLayer Override entity has valid values.
 */
class DataLayerOverrideValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityUntranslatableFieldsConstraintValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (empty($entity)) {
      return;
    }

    $values = NULL;
    if ($entity instanceof DataLayerOverrideInterface) {
      $tags = Yaml::decode($entity->getTags());
      if (!is_array($tags)) {
        $this->context
          ->buildViolation('The tags to override has an invalid value.')
          ->addViolation();
      }
    }

    if (empty($entity->getRelatedEntity())) {
      $this->context
        ->buildViolation('The related entity does not exists.')
        ->addViolation();

      return;
    }

    $entities = $this->entityTypeManager
      ->getStorage('gtm_datalayer_override')
      ->loadByMultipleRelatedEntity($entity->getRelatedEntity());

    $is_unique = TRUE;
    if ($entity->isNew() && !empty($entities)) {
      $is_unique = FALSE;
    }
    elseif (!$entity->isNew()) {
      foreach ($entities as $related_entity) {
        if ($entity->id() != $related_entity->id()) {
          $is_unique = FALSE;
          break;
        }
      }
    }

    if (!$is_unique) {
      $this->context
        ->buildViolation('An entity can only has one override.')
        ->addViolation();
    }
  }

}
