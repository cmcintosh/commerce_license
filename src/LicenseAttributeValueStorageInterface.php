<?php

namespace Drupal\commerce_license;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for license attribute value storage.
 */
interface LicenseAttributeValueStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads license attribute values for the given license attribute.
   *
   * @param string $attribute_id
   *   The license attribute ID.
   *
   * @return \Drupal\commerce_license\Entity\LicenseAttributeValueInterface[]
   *   The license attribute values, indexed by id, ordered by weight.
   */
  public function loadByAttribute($attribute_id);

}
