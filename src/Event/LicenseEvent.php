<?php

namespace Drupal\commerce_license\Event;

use Drupal\commerce_license\Entity\LicenseInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the license event.
 *
 * @see \Drupal\commerce_license\Event\LicenseEvents
 */
class LicenseEvent extends Event {

  /**
   * The license.
   *
   * @var \Drupal\commerce_license\Entity\LicenseInterface
   */
  protected $license;

  /**
   * Constructs a new LicenseEvent.
   *
   * @param \Drupal\commerce_license\Entity\LicenseInterface $license
   *   The license.
   */
  public function __construct(LicenseInterface $license) {
    $this->license = $license;
  }

  /**
   * Gets the license.
   *
   * @return \Drupal\commerce_license\Entity\LicenseInterface
   *   The license.
   */
  public function getLicense() {
    return $this->license;
  }

}
