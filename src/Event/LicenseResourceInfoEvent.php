<?php

namespace Drupal\commerce_license\Event;

use Symfony\Component\EventDispatcher\Event;

class LicenseResourceInfoEvent extends Event {

  private $license_variation;
  private $resources;

  /**
  * Constuction function.
  */
  public function __construct($variation) {
    $this->license_variation = $variation;
  }


  /**
  * Returns the defined license variation for this event.
  */
  public function getLicenseVariation() {
    return $this->license_variation;
  }

  /**
  * Returns the info for all of the attached resource plugins.
  */
  public function getInfo() {
    return $this->resources;
  }

  /**
  * Sets info for this event.
  */
  public function setInfo($resource_plugin, $info) {
    $this->resources[$resource_plugin] = $info;
  }

}
