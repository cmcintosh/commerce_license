<?php

namespace Drupal\commerce_license\ConfigTranslation;

use Drupal\config_translation\ConfigEntityMapper;

/**
 * Provides a configuration mapper for license attributes.
 */
class LicenseAttributeMapper extends ConfigEntityMapper {

  /**
   * {@inheritdoc}
   */
  public function getAddRoute() {
    $route = parent::getAddRoute();
    $route->setDefault('_form', '\Drupal\commerce_license\Form\LicenseAttributeTranslationAddForm');
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditRoute() {
    $route = parent::getEditRoute();
    $route->setDefault('_form', '\Drupal\commerce_license\Form\LicenseAttributeTranslationEditForm');
    return $route;
  }

}
