<?php

namespace Drupal\commerce_license\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LicenseVariationSku constraint.
 */
class LicenseVariationSkuConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $sku = $items->first()->value;
    if (isset($sku) && $sku !== '') {
      $sku_exists = (bool) \Drupal::entityQuery('commerce_license_variation')
        ->condition('sku', $sku)
        ->condition('variation_id', (int) $items->getEntity()->id(), '<>')
        ->range(0, 1)
        ->count()
        ->execute();

      if ($sku_exists) {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%sku', $this->formatValue($sku))
          ->addViolation();
      }
    }
  }

}
