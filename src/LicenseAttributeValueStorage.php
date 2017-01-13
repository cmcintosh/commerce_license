<?php

namespace Drupal\commerce_license;

use Drupal\commerce\CommerceContentEntityStorage;

/**
 * Defines the license attribute value storage.
 */
class LicenseAttributeValueStorage extends CommerceContentEntityStorage implements LicenseAttributeValueStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadByAttribute($attribute_id) {
    $entity_query = $this->getQuery();
    $entity_query->condition('attribute', $attribute_id);
    $entity_query->sort('weight');
    $entity_query->sort('name');
    $result = $entity_query->execute();
    return $result ? $this->loadMultiple($result) : [];
  }

}
