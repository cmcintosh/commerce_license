<?php

namespace Drupal\commerce_license\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures license variation SKU uniqueness.
 *
 * @Constraint(
 *   id = "LicenseVariationSku",
 *   label = @Translation("The SKU of the license variation.", context = "Validation")
 * )
 */
class LicenseVariationSkuConstraint extends Constraint {

  public $message = 'The SKU %sku is already in use and must be unique.';

}
