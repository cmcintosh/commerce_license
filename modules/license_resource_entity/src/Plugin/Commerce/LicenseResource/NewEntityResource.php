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
class NewEntityResource extends ConditionPluginBase {

  public function summary() {
    return t('This will give the customer access to the newly published entities.');
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    parent::buildConfigurationForm($form, $form_state);

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => t('Entity Type'),
      '#description' => t('Select the entity type to award to the customer.'),
      '#options' => $this->getEntityTypes(),
      '#ajax' => [
        'wrapper' => 'bundle-wrapper',
        'method' => 'replace',
        'callback' => [$this, 'entityTypeSelectAjax']
      ]
    ];


    if ($entity_type = $form_state->getValue('entity_type')) {
      $form['bundle'] = [
        '#type' => 'select',
        '#title' => t('Bundle'),
        '#description' => t('Select the bundle or sub type to use when awarding access.'),
        '#options' => $this->getEntityBundles($entity_type),
        '#prefix' => '<div id="bundle-wrapper">',
        '#suffix' => '</div>'
      ];
    }
    else {
      $form['bundle'] = [
        '#type' => 'markup',
        '#markup' => t('Please select a entity type to continue.'),
        '#prefix' => '<div id="bundle-wrapper">',
        '#suffix' => '</div>'
      ];
    }

    return $form;
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
        $options[$bundle] = $bundle;
      }
    }
    return $options;
  }

}
