<?php
namespace Drupal\license_resource_entity\Plugin\Commerce\LicenseResource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Condition\ConditionPluginBase;

/**
* @file
* - Provides a user access to an existing entity.
*
* CommerceLicenseResource(
*   id = 'resource_existing_entity',
*   label = 'Exsiting Entity Access',
*   display_label = 'Existing Entity Access',
* )
*/

class ExistingEntityResource extends ConditionPluginBase {

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['test'] = [
      '#type' => 'markup',
      '#markup' => t('Hello world')
    ];

    return $form;
  }

  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {

  }

  public function submitConfigurationForm(array $form, FormStateInterface $form_state) {

  }

}
