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
*   id = "resource_newest_entity",
*   label = "Newest Entity Access",
*   display_label = "Newest Entity Access"
* )
*
*/
class NewestEntityResource extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'resource_entity_type' => 'node',
      'resource_entity_bundle' => NULL
    ];
  }

  public function summary() {
    return t('This will give the customer access to the newest created entity.');
  }

  public function evaluate() {
    return true;
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $default_entity_type = isset($this->configuration['resource_entity_type']) ? $this->configuration['resource_entity_type'] : 'node';
    $default_entity_bundle = isset($this->configuration['resource_entity_bundle']) ? $this->configuration['resource_entity_bundle'] : -1;


    $entity_type = 'node';
    $form['resource_entity_type'] = [
      '#type' => 'select',
      '#title' => t('Entity Type'),
      '#options' => [
        'node' => t('Node')
      ],
      '#default_value' => $default_entity_type,
    ];

    $form['resource_entity_bundle'] = [
      '#type' => 'select',
      '#title' => t('Bundle'),
      '#description' => t('Select the bundle or sub type to use when awarding access.'),
      '#options' => $this->getEntityBundles($default_entity_type),
      '#prefix' => '<div id="bundle-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $default_entity_bundle
    ];
    return $form;
  }

  public function submitConfigurationForm(array &$form = null, FormStateInterface $form_state) {
    $data = $this->configuration;
    if ($form_state->hasValue('context_mapping')) {
      $this->setContextMapping($form_state->getValue('context_mapping'));
    }
    $resource_id = "resource_entity_{$data['resource_entity_type']}_{$data['resource_entity_bundle']}";

    if ( !(acl_get_id_by_name('resource_entity', $resource_id)) ) {
      acl_create_acl( 'resource_entity', $resource_id);
    }

    // We dont immediately award anything here. We do it later when the license is issued.
  }

  public function entityTypeSelectAjax(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(true);
    $values = $form_state->getValues();


    $form['bundle']['#options'] = NewestEntityResource::getEntityBundles( $form_state->getValue('resource_entity_type') );
    return $form['bundle'];
  }

  /**
  * Helper function that returns all the defined ContentEntity types in the system.
  */
  public function getEntityTypes() {
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

  /**
  * Helper function that returns all bundles for a given Entity type.
  */
  public function getEntityBundles($entity_type = NULL) {
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
