<?php

namespace Drupal\gtm_datalayer_override\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a GTM dataLayer Override entity have valid values.
 *
 * @Constraint(
 *   id = "ValidDataLayerOverrideConstraint",
 *   label = @Translation("Valid entity value constraint", context = "Validation"),
 *   type = { "entity" }
 * )
 */
class ValidDataLayerOverrideConstraint extends Constraint {

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\gtm_datalayer_override\Plugin\Validation\Constraint\DataLayerOverrideValidator';
  }

}
