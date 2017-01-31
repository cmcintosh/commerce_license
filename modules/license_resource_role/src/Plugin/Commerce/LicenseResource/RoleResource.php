<?php
namespace Drupal\license_resource_role\Plugin\Commerce\LicenseResource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
* @file
* - Provides a user role resource for licenses.
*
* @CommerceLicenseResource(
*  id = "resource_role",
*  label = "User Role",
*  display_label = "User Role"
* )
*/
class RoleResource extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'commerce_product_variation' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Select a role to award a user with on completion of the order.');
  }

  public function evaluate() { }

  public function execute() { }

  /**
   * {@inheritdoc}
   */
   public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

     // @TODO: see if there is a cleaner way to retrieve this.
     $default = isset($this->configuration['resource_role']) ? $this->configuration['resource_role'] : -1;

     $form['resource_role'] = [
       '#type' => 'select',
       '#title' => t('Role'),
       '#options' => $this->getRoles(),
       '#default_value' => $default,

     ];

     return $form;
   }

   /**
   * {@inheritdoc}
   */
   public function submitConfigurationForm(array &$form = null, FormStateInterface $form_state) {

    $this->configuration['negate'] = $form_state->getValue('negate');
    if ($form_state->hasValue('context_mapping')) {
      $this->setContextMapping($form_state->getValue('context_mapping'));
    }

   }

   /**
   * Private helper function to get all defined system roles.
   */
   private function getRoles() {
     $roles = [
       -1 => t('Please select a role.')
     ];

     $entities = user_roles(TRUE);

     foreach($entities as $id => $role) {
       $roles[$id] = $id;
     }

     return $roles;
   }

}
