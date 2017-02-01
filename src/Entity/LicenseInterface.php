<?php

namespace Drupal\commerce_license\Entity;

use Drupal\commerce_store\Entity\EntityStoresInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
* Defines the interface for licenses.
*/
interface LicenseInterface extends ContentEntityInterface {

  /**
  * Gets the license title.
  *
  * @return string
  *   The license title
  */
  public function getTitle();

  /**
  * Sets the license title.
  *
  * @param string $title.
  *  The license title.
  */
  public function setTitle($title);

  /**
  * Get whether the product is published.
  *
  * Unpublished licenses are only visible to their owners.
  *
  * @return bool
  *   TRUE if the license is published, FALSE otherwise.
  */
  public function isPublished();

  /**
  * Sets whether the license is published.
  *
  * @param bool $published.
  *   Whether the license is published.
  *
  * @return $this
  */
  public function setPublished($published);

  /**
  * Gets the license creation timestamp.
  *
  * @return int
  *   The license creation timestamp.
  */
  public function getCreatedTime();

  /**
  * Sets the license craetion timestamp.
  *
  * @param int $timestamp
  *   The license creation timestamp.
  *
  * @return $this
  */
  public function setCreatedTime($timestamp);

  /**
  * Sets the variation.
  *
  * @param \Drupal\commerce_license\Entity\LicenseVariationInterface[] $variations
  *   The variations.
  * @return $this
  */
  public function setVariations(array $variations);

  /**
  * Gets whether the license has variations.
  *
  * A license must always have at least one variation, but a newly initialized
  * (or invalid) license entity might not have any.
  *
  * @return bool
  *   TRUE if the license has variations, FALSE otherwize.
  */
  public function hasVariations();

  /**
  * Add a variation.
  *
  * @param \Drupal\commerce_license\Entity\LicenseVariationInterface $variation.
  *   The variation.
  *
  * @return $this
  */
  public function addVariation(LicenseVariationInterface $variation);

  /**
  * Removes a variation.
  *
  * @param \Drupal\commerce_license\Entity\LicenseVariationInterface $variatioin
  *   The variation
  *
  * @return $this
  */
  public function removeVariation(LicenseVariationInterface $variation);

  /**
  * Checks whether the license has a given variation.
  *
  * @param \Drupal\commerce_license\Entity\LicenseVariationInterface $variation
  *   The variation
  *
  * @return bool
  *  TRUE if the variation was found, FALSE otherwise.
  */
  public function hasVariation(LicenseVariationInterface $variation);

  /**
  * Gets the default variation.
  *
  * @return \Drupal\commerce_variation\Entity\LicenseVariationInterface $variation
  *  The default variation.
  *
  */
  public function getDefaultVariation();

}
