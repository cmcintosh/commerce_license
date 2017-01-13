<?php

namespace Drupal\commerce_license\Event;

use Drupal\commerce_license\Entity\LicenseVariationInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the license variation ajax change event.
 *
 * @see \Drupal\commerce_license\Event\LicenseEvents
 */
class LicenseVariationAjaxChangeEvent extends Event {

  /**
   * The license variation.
   *
   * @var \Drupal\commerce_license\Entity\LicenseVariationInterface
   */
  protected $licenseVariation;

  /**
   * The ajax response.
   *
   * @var \Drupal\Core\Ajax\AjaxResponse
   */
  protected $response;

  /**
   * The view mode.
   *
   * @var string
   */
  protected $viewMode;

  /**
   * Constructs a new LicenseVariationAjaxChangeEvent.
   *
   * @param \Drupal\commerce_license\Entity\LicenseVariationInterface $license_variation
   *   The license variation.
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response.
   * @param string $view_mode
   *   The view mode used to render the license variation.
   */
  public function __construct(LicenseVariationInterface $license_variation, AjaxResponse $response, $view_mode = 'default') {
    $this->licenseVariation = $license_variation;
    $this->response = $response;
    $this->viewMode = $view_mode;
  }

  /**
   * The license variation.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationInterface
   *   The license variation.
   */
  public function getLicenseVariation() {
    return $this->licenseVariation;
  }

  /**
   * The ajax response.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax reponse.
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * The view mode used to render the license variation.
   *
   * @return string
   *   The view mode.
   */
  public function getViewMode() {
    return $this->viewMode;
  }

}
