<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the interface for license variation types.
 */
interface LicenseVariationTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the license variation type's order item type ID.
   *
   * Used for finding/creating the appropriate order item when purchasing a
   * license (adding it to an order).
   *
   * @return string
   *   The order item type ID.
   */
  public function getOrderItemTypeId();

  /**
   * Sets the license variation type's order item type ID.
   *
   * @param string $order_item_type_id
   *   The order item type ID.
   *
   * @return $this
   */
  public function setOrderItemTypeId($order_item_type_id);

  /**
   * Gets whether the license variation title should be automatically generated.
   *
   * @return bool
   *   Whether the license variation title should be automatically generated.
   */
  public function shouldGenerateTitle();

  /**
   * Sets whether the license variation title should be automatically generated.
   *
   * @param bool $generate_title
   *   Whether the license variation title should be automatically generated.
   *
   * @return $this
   */
  public function setGenerateTitle($generate_title);

}
