<?php

namespace Drupal\commerce_license\Event;

use Drupal\commerce_license\Entity\LicenseVariationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the license variation event.
 *
 * @see \Drupal\commerce_license\Event\LicenseEvents
 */
class LicenseVariationEvent extends Event {

  /**
   * The license variation.
   *
   * @var \Drupal\commerce_license\Entity\LicenseVariationInterface
   */
  protected $licenseVariation;

  /**
   * Constructs a new LicenseVariationEvent.
   *
   * @param \Drupal\commerce_license\Entity\LicenseVariationInterface $license_variation
   *   The license variation.
   */
  public function __construct(LicenseVariationInterface $license_variation) {
    $this->licenseVariation = $license_variation;
  }

  /**
   * Gets the license variation.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationInterface
   *   The license variation.
   */
  public function getLicenseVariation() {
    return $this->licenseVariation;
  }

}
