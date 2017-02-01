<?php
namespace Drupal\license_resource_fields\Plugin\Commerce\LicenseResource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
/**
* @file
* - Provides user access to an existing entity's field.
*
* @CommerceLicenseResource(
*  id = "resource_existing_entity_field",
*  label = "Field Access (Existing Entity)",
*  display_label = "Field Access (Existing Entity)"
* )
*/
class ExistingEntityField extends ConditionPluginBase {

  /**
  * {@inheritdoc}
  */
  public function defaultConfiguration() {
    return [
      'resource_entity' => NULL,
      'resource_entity_type' => 'node',
      'resource_entity_bundle' => NULL,
      'resource_entity_field' => NULL,
    ];
  }

  /**
  * {@inheritdoc}
  */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $default_entity_type = isset($this->configuration['resource_entity_type']) ? $this->configuration['resource_entity_type'] : 'node';
    $default_entity_bundle = isset($this->configuration['resource_entity_bundle']) ? $this->configuration['resource_entity_bundle'] : -1;
    $default_entity_field = isset($this->configuration['resource_entity_field']) ? $this->configuration['resource_entity_field'] : -1;
    $default_entity_id = isset($this->configuration['resource_entity']) ? $this->configuration['resource_entity'] : -1;

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
      '#title' => t('Entity Bundle'),
      '#options' => [
        'node' => t('Node')
      ],
      '#default_value' => $default_entity_type,
    ];

    $form['resource_entity_field'] = [
      '#type' => 'select',
      '#title' =>t('Field Name'),
      '#options' => $this->getEntityFields($entity_type, $bundle),
      '#default_value' => t('Select the Field to limit access to. Only administers and users who purchase this license will have access to view this field.')
    ];

    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
  * Helper function to get the entity types.
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
  * Helper function to get the fields defined.
  */
  public function getEntityFields($entity_type, $bundle) {
    $entityTypeManage = \Drupal::entityTypeManager()
      ->getStorage($entity_type);

    $options = [];

    return $options;
  }

}
