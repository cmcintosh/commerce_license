<?php

namespace Drupal\commerce_license\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LicenseTypeForm extends BundleEntityFormBase {

  /**
   * The variation type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $variationTypeStorage;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Creates a new LicenseTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->variationTypeStorage = $entity_type_manager->getStorage('commerce_license_variation_type');
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_license\Entity\LicenseTypeInterface $license_type */
    $license_type = $this->entity;
    $variation_types = $this->variationTypeStorage->loadMultiple();
    $variation_types = array_map(function ($variation_type) {
      return $variation_type->label();
    }, $variation_types);
    // Create an empty license to get the default status value.
    // @todo Clean up once https://www.drupal.org/node/2318187 is fixed.
    if ($this->operation == 'add') {
      $license = $this->entityTypeManager->getStorage('commerce_license')->create(['type' => $license_type->uuid()]);
    }
    else {
      $license = $this->entityTypeManager->getStorage('commerce_license')->create(['type' => $license_type->id()]);
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $license_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $license_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_license\Entity\LicenseType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add license</em> page.'),
      '#default_value' => $license_type->getDescription(),
    ];
    $form['variationType'] = [
      '#type' => 'select',
      '#title' => $this->t('License variation type'),
      '#default_value' => $license_type->getVariationTypeId(),
      '#options' => $variation_types,
      '#required' => TRUE,
      '#disabled' => !$license_type->isNew(),
    ];
    $form['injectVariationFields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inject license variation fields into the rendered license.'),
      '#default_value' => $license_type->shouldInjectVariationFields(),
    ];
    $form['license_status'] = [
      '#type' => 'checkbox',
      '#title' => t('Publish new licenses of this type by default.'),
      '#default_value' => $license->isPublished(),
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
        '#group' => 'additional_settings',
      ];
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'commerce_license',
          'bundle' => $license_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('commerce_license', $license_type->id()),
      ];
      $form['#submit'][] = 'language_configuration_element_submit';
    }

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    // Update the default value of the status field.
    $license = $this->entityTypeManager->getStorage('commerce_license')->create(['type' => $this->entity->id()]);
    $value = (bool) $form_state->getValue('license_status');
    if ($license->status->value != $value) {
      $fields = $this->entityFieldManager->getFieldDefinitions('commerce_license', $this->entity->id());
      $fields['status']->getConfig($this->entity->id())->setDefaultValue($value)->save();
      $this->entityFieldManager->clearCachedFieldDefinitions();
    }

    drupal_set_message($this->t('The license type %label has been successfully saved.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.commerce_license_type.collection');
    if ($status == SAVED_NEW) {
      commerce_license_add_stores_field($this->entity);
      commerce_license_add_body_field($this->entity);
      commerce_license_add_variations_field($this->entity);
    }
  }

}
