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
  public function summary() {
    return t('Select a role to award a user with on completion of the order.');
  }

  /**
   * {@inheritdoc}
   */
   public function buildConfiurationForm(array $form, FormStateInterface $form_state) {
     parent::buildConfigurationForm($form, $form_state);

     // @TODO: see if there is a cleaner way to retrieve this.
     $values = $form_state->getValues();
     $default = $this->getDefaultValue($values);

     $form['resource_role'] = [
       '#type' => 'select',
       '#title' => t('Role'),
       '#options' => $this->getRoles(),
       '#default_value' => isset($default) ? $defaulte : -1,

     ];

     $form['#submit'][] = [this, 'submitConfigurationForm'];

     return $form;
   }

   /**
   * {@inheritdoc}
   */
   public function submitConfigurationForm(array $form = null, FormStateInterface $form_state) {
     $values = $form_state->getValues();
     $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
     $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];

     // Loop and update our resource pluin settings
     foreach($resources as $resource) {
       if (is_array($resource)){
         if ($resource['target_plugin_id'] == 'resource_role') {
           $data = [
             'sku' => $sku,
             'role' => $resource['target_plugin_configuration']['resource_role'],
           ];
           db_merge('license_resource_role')
             ->key(array('sku' => $data['sku']))
             ->fields($data)
             ->execute();
         }
       }
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
       $roles[$id] = $role->name;
     }

     return $roles;
   }

   /**
   * Helper function that returns the default value for a sku.
   */
   private function getDefaultValue($values) {
     $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
     $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];
     $defaults = null;
     $default_entity = null;

     if ( $sku ) {
       $query = \Drupal::database()
         ->select('resource_role', 'rr');

       $query->condition('rr.sku', $sku, '=' );

       $query->addfield('rr', 'sku');
       $query->addField('rr', 'role');
       $result = $query->execute()->fetchAllAssoc('sku');
       return isset($result[$sku]) ? $result[$sku]->role : -1;
     }
     return -1;
   }

}
