<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
* Defines the interface for license attributes.
*/
interface LicenseAttributeInterface extends ConfigEntityInterface {

  /**
   * Gets the attribute values.
   *
   * @return \Drupal\commerce_license\Entity\LicenseAttributeValueInterface[]
   *   The attribute values.
   */
  public function getValues();

  /**
   * Gets the attribute element type.
   *
   * @return string
   *   The element type name.
   */
  public function getElementType();

}
