<?php

namespace Drupal\commerce_license\Event;

use Drupal\commerce_license\Entity\LicenseAttributeValueInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the license attribute value event.
 *
 * @see \Drupal\commerce_license\Event\LicenseEvents
 */
class LicenseAttributeValueEvent extends Event {

  /**
   * The license attribute value.
   *
   * @var \Drupal\commerce_license\Entity\LicenseAttributeValueInterface
   */
  protected $attributeValue;

  /**
   * Constructs a new LicenseAttributeValueEvent.
   *
   * @param \Drupal\commerce_license\Entity\LicenseAttributeValueInterface $attribute_value
   *   The license attribute value.
   */
  public function __construct(LicenseAttributeValueInterface $attribute_value) {
    $this->attributeValue = $attribute_value;
  }

  /**
   * Gets the license attribute value.
   *
   * @return \Drupal\commerce_license\Entity\LicenseAttributeValueInterface
   *   The license attribute value.
   */
  public function getAttributeValue() {
    return $this->attributeValue;
  }

}
