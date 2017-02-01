<?php

namespace Drupal\commerce_license\Plugin\Commerce\LicenseCondition;

abstract class LicenseConditionBase implements LicenseConditionInterface {

  public function getTargetEntityType() {
    return 'license_variation';
  }

  public function getTargetEntity() {
    return $this->getContextValue($this->getTargetEntityType());
  }

  public function execute() {
    $result = $this->evaluate();
    return $this->isNegated() ? !$result : $result;
  }

}
