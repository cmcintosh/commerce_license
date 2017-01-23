<?php

namespace Drupal\commerce_license\Event;

use Symfony\Component\EventDispatcher\Event;

class LicenseConditionInfoEvent extends Event {

  private $license_variation;
  private $conditions;

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
    return $this->conditions;
  }

  /**
  * Sets info for this event.
  */
  public function setCondition($condition_plugin, $info) {
    $this->resources[$condition_plugin] = $info;
  }

}
