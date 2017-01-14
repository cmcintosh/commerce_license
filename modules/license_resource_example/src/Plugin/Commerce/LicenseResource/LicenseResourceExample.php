<?php

namespace Drupal\license_resource_example\Plugin\Commerce\LicenseResource;

/*
* @CommerceLicenseResource(
*    id = 'example_resource',
*    label = 'License Resource Example',
*    display_label = 'Example',
*  )
*/

class LicenseResourceExample {

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['hello'] = [
      '#type' => 'markup',
      '#markup' => 'hello world'
    ];
    return $form;
  }

  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {}

  public function submitConfigurationForm(array $form, FormStateInterface $form_state) {}

  public function licenseStart() { }

  public function licenseExpire() { }
}
