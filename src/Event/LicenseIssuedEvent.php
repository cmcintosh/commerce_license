<?php

namespace Drupal\commerce_license\Event;

use Symfony\Component\EventDispatcher\Event;

class LicenseIssuedEvent extends Event {

  private $license_variation;
  private $customer_license;
  private $resources;

  /**
  * Constuction function.
  */
  public function __construct($variation, $customer_license) {
    $this->license_variation = $variation;
    $this->customer_license = $customer_license;
  }


  /**
  * Returns the defined license variation for this event.
  */
  public function getLicenseVariation() {
    return $this->license_variation;
  }

  public function getCustomerLicense() {
    return $this->customer_license;
  }

}
