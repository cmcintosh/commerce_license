<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Defines the interface for product types.
 */
interface LicenseTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Gets the product type's matching variation type ID.
   *
   * @return string
   *   The variation type ID.
   */
  public function getVariationTypeId();

  /**
   * Sets the product type's matching variation type ID.
   *
   * @param string $variation_type_id
   *   The variation type ID.
   *
   * @return $this
   */
  public function setVariationTypeId($variation_type_id);

  /**
   * Gets whether variation fields should be injected into the rendered product.
   *
   * @return bool
   *   TRUE if the variation fields should be injected into the rendered
   *   product, FALSE otherwise.
   */
  public function shouldInjectVariationFields();

  /**
   * Sets whether variation fields should be injected into the rendered product.
   *
   * @param bool $inject
   *   Whether variation fields should be injected into the rendered product.
   *
   * @return $this
   */
  public function setInjectVariationFields($inject);

}
