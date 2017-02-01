<?php

namespace Drupal\commerce_license;

use Drupal\commerce_license\Entity\LicenseInterface;

/**
 * Defines the interface for license variation storage.
 */
interface LicenseVariationStorageInterface {

  /**
   * Loads the variation from context.
   *
   * Uses the variation specified in the URL (?v=) if it's active and
   * belongs to the current license.
   *
   * Note: The returned variation is not guaranteed to be enabled, the caller
   * needs to check it against the list from loadEnabled().
   *
   * @param \Drupal\commerce_license\Entity\LicenseInterface $license
   *   The current license.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationInterface
   *   The license variation.
   */
  public function loadFromContext(LicenseInterface $license);

  /**
   * Loads the enabled variations for the given license.
   *
   * Enabled variations are active variations that have been filtered through
   * the FILTER_VARIATIONS event.
   *
   * @param \Drupal\commerce_license\Entity\LicenseInterface $license
   *   The license.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationInterface[]
   *   The enabled variations.
   */
  public function loadEnabled(LicenseInterface $license);

}
