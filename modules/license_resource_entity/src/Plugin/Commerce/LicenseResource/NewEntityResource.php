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
*   id = "resource_new_entity",
*   label = "New Entity Access",
*   display_label = "New Entity Access"
* )
*
*/
class NewEntityResource extends ConditionPluginBase {

  public function summary() {
    return t('This will give the customer access to the newly published entities.');
  }

  public function evaluate() {
    return true;
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    parent::buildConfigurationForm($form, $form_state);

    $values = $form_state->getValues();
    $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
    $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];
    $defaults = null;
    if ( $sku ) {
      // Check if we have settings for this sku.
      $query = \Drupal::database()
        ->select('resource_new_entity', 're');

      $query->condition('re.sku', $sku, '=' );

      $query->addfield('re', 'sku');
      $query->addField('re', 'bundle');
      $query->addField('re', 'quantity');
      $result = $query->execute()->fetchAllAssoc('sku');
      if (isset($result[$sku])) {
        $defaults = $result[$sku];
      }
    }

    $entity_type = 'node';
    $form['resource_bundle'] = [
      '#type' => 'select',
      '#title' => t('Bundle'),
      '#description' => t('Select the bundle or sub type to use when awarding access.'),
      '#options' => $this->getEntityBundles($entity_type),
      '#prefix' => '<div id="bundle-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => isset($defaults->bundle) ? $defaults->bundle : -1,
    ];

    $form['resource_entity_quantity'] = [
      '#type' => 'textfield',
      '#title' => t('Number to Award'),
      '#description' => t('Enter the number of new entities to award. This will decrease for the customer each time a new entity is published.'),
      '#default_value' => isset($defaults->quantity) ? $defaults->quantity : 1,
    ];

    return $form;
  }


  public function submitConfigurationForm(array &$form = null, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $resources = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['resources'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['resources'];
    $sku = isset($values['variations']['form']['inline_entity_form']['resources']) ? $values['variations']['form']['inline_entity_form']['sku'][0]['value'] : $values['variations']['form']['inline_entity_form']['entities'][0]['form']['sku'][0]['value'];

    foreach($resources as $resource) {

      if (is_array($resource)){

        if ($resource['target_plugin_id'] == 'resource_new_entity') {
          $data = [
            'sku' => $sku,
            'entity_type' => 'node',
            'bundle' => $resource['target_plugin_configuration']['resource_bundle'],
            'op' => 'view',
            'quantity' => $resource['target_plugin_configuration']['resource_entity_quantity']
          ];
          db_merge('resource_new_entity')
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
        $options[$bundle] = $info['label'];
      }
    }
    return $options;
  }

}
