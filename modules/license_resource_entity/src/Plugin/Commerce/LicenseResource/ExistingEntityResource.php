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

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'resource_entity' => NULL,
      'resource_entity_type' => 'node',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Select a entity type, entity, and action to provide access for.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // parent::buildConfigurationForm($form, $form_state);

    $default_entity_type = isset($this->configuration['resource_entity_type']) ? $this->configuration['resource_entity_type'] : 'node';
    $default_entity_id = isset($this->configuration['resource_entity']) ? $this->configuration['resource_entity'] : -1;

    $form['resource_entity_type'] = [
      '#type' => 'select',
      '#title' => t('Entity Type'),
      '#options' => [
        'node' => t('Node')
      ],
      '#default_value' => $default_entity_type,
    ];

    // Get the Entity's storage controller.
    $defaultEntity = \Drupal::entityTypeManager()
      ->getStorage($default_entity_type)
      ->load($default_entity_id);

    // Maybe better to have autocomplete here?
    $form['resource_entity'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => $default_entity_type,
      '#title' => t('Entity'),
      '#description' => t('Select the entity you wish to provide access for.'),
      '#default_value' => $defaultEntity,
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

    $data = $this->configuration;
    if ( !(acl_get_id_by_name('resource_entity', "resource_entity_{$data['resource_entity_type']}_{$data['resource_entity']}")) ) {
      acl_create_acl( 'resource_entity', "resource_entity_{$data['resource_entity_type']}_{$data['resource_entity']}");
    }

  }

  public function ajaxUpdateEntityType(array $form, FormStateInterface $form_state) {

    $default_entity_type = isset($this->configuration['resource_entity_type']) ? $this->configuration['resource_entity_type'] : 'node';
    $default_entity_id = isset($this->configuration['resource_entity']) ? $this->configuration['resource_entity'] : -1;
    $default_entity = NULL;

    if ($default_entity_type == $form_state->getValue('resource_entity_type')) {
      $defaultEntity = \Drupal::entityTypeManager()
        ->getStorage($default_entity_type)
        ->load($default_entity_id);
    }

    $form['resource_entity_type']['#default_value'] = $form_state->getValue('resource_entity_type');
    $form['resource_entity']['#default_value'] = $default_entity;

    return $form;
  }


  public function evaluate() {
    return true;
  }

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

}
