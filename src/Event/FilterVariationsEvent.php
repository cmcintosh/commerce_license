<?php

namespace Drupal\commerce_license\Event;

use Drupal\commerce_license\Entity\LicenseInterface;
use Symfony\Component\EventDispatcher\Event;

class FilterVariationsEvent extends Event {

  /**
   * The parent license.
   *
   * @var \Drupal\commerce_license\Entity\LicenseInterface
   */
  protected $license;

  /**
   * The enabled variations.
   *
   * @var array
   */
  protected $variations;

  /**
   * Constructs a new FilterVariationsEvent object.
   *
   * @param \Drupal\commerce_license\Entity\LicenseInterface $license
   *   The license.
   * @param array $variations
   *   The enabled variations.
   */
  public function __construct(LicenseInterface $license, array $variations) {
    $this->license = $license;
    $this->variations = $variations;
  }

  /**
   * Sets the enabled variations.
   *
   * @param array $variations
   *   The enabled variations.
   */
  public function setVariations(array $variations) {
    $this->variations = $variations;
  }

  /**
   * Gets the enabled variations.
   *
   * @return array
   *   The enabled variations.
   */
  public function getVariations() {
    return $this->variations;
  }

}
