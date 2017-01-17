<?php
namespace Drupal\license_resource_entity\Plugin\Commerce\LicenseResource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
* @file
* - Provides a user access to an existing entity.
*
* @CommerceLicenseResource(
*   id = "resource_existing_entity",
*   label = "Existing Entity Access",
*   display_label = "Existing Entity Access"
* )
*
*/
class ExistingEntityResource extends ConditionPluginBase {

  public function summary() {
    return t('Select a entity type, entity, and action to provide access for.');
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    parent::buildConfigurationForm($form, $form_state);

    $values = $form_state->getValues();
    $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
    $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];
    $defaults = null;
    $default_entity = null;
    if ( $sku ) {
      // Check if we have settings for this sku.
      $query = \Drupal::database()
        ->select('resource_existing_entity', 're');

      $query->condition('re.sku', $sku, '=' );

      $query->addfield('re', 'sku');
      $query->addField('re', 'entity_id');
      $result = $query->execute()->fetchAllAssoc('sku');

      if (isset($result[$sku])) {
        $defaults = $result[$sku];
        $default_entity = node_load($defaults->entity_id);
      }
    }

    // Maybe better to have autocomplete here?
    $form['resource_entity'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => t('Entity'),
      '#description' => t('Select the entity you wish to provide access for.'),
      '#default_value' => isset($default_entity) ? $default_entity : null
    ];

    $form['#submit'][] = [this, 'submitConfigurationForm'];

    return $form;
  }

  public function validateConfigurationForm(array &$form = null, FormStateInterface $form_state) {

  }

  public function submitConfigurationForm(array &$form = null, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
    $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];

    foreach($resources as $resource) {
      if (is_array($resource)){
        if ($resource['target_plugin_id'] == 'resource_existing_entity') {
          $data = [
            'sku' => $sku,
            'entity_type' => 'node',
            'entity_id' => $resource['target_plugin_configuration']['resource_entity'],
            'op' => 'view'
          ];
          db_merge('resource_existing_entity')
            ->key(array('sku' => $data['sku']))
            ->fields($data)
            ->execute();
        }
      }

    }
  }

  public function entityTypeSelectAjax(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(true);

    $form['bundle']['#options'] = $this->getEntityBundles( $form_state->getValue('entity_type') );
    return $form['bundle'];
  }

  public function evaluate() {
    return true;
  }

  private function getEntityTypes() {
    $options = [
      -1 => t('Select an Entity type')
    ];

    $definitions = \Drupal::entityManager()->getDefinitions();

    foreach($definitions as $entity_type => $definition) {
      if (!is_a($definition, 'Drupal\Core\Config\Entity\ConfigEntityType')) {
        $options[$entity_type] = $definition->getLabel();
      }
    }

    return $options;
  }

  private function getEntityBundles($entity_type = NULL) {
    $options =[
      -1 => t('Select a Bundle')
    ];

    if ($entity_type !== NULL) {
      $bundles = entity_get_bundles($entity_type);
      foreach($bundles as $bundle => $info) {
        $options[$bundle] = $bundle;
      }
    }
    return $options;
  }

}
