<?php

namespace Drupal\commerce_license\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Defines the inline form for license variations.
 */
class LicenseVariationInlineForm extends EntityInlineForm {

  /**
   * The loaded variation types.
   *
   * @var \Drupal\commerce_license\Entity\LicenseVariationTypeInterface[]
   */
  protected $variationTypes;

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeLabels() {
    $labels = [
      'singular' => t('variation'),
      'plural' => t('variations'),
    ];
    return $labels;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['label']['label'] = t('Title');
    $fields['price'] = [
      'type' => 'field',
      'label' => t('Price'),
      'weight' => 10,
    ];
    $fields['status'] = [
      'type' => 'field',
      'label' => t('Status'),
      'weight' => 100,
      'display_options' => [
        'settings' => [
          'format' => 'custom',
          'format_custom_true' => t('Active'),
          'format_custom_false' => t('Inactive'),
        ],
      ],
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLabel(EntityInterface $entity) {
    /** @var \Drupal\commerce_license\Entity\LicenseVariationInterface $entity */
    $variation_type = $this->loadVariationType($entity->bundle());
    if (!$variation_type->shouldGenerateTitle()) {
      return $entity->label();
    }

    // The generated variation title includes the license title, which isn't
    // relevant in this context, the user only needs to see the attribute part.
    if ($attribute_values = $entity->getAttributeValues()) {
      $attribute_labels = array_map(function ($attribute_value) {
        return $attribute_value->label();
      }, $attribute_values);

      $label = implode(', ', $attribute_labels);
    }
    else {
      // @todo Replace the Complex widget with the Simple one when there
      // are no attributes, indicating there should only be one variation.
      $label = t('N/A');
    }

    return $label;
  }

  /**
   * Loads and returns a license variation type with the given ID.
   *
   * @param string $variation_type_id
   *   The variation type ID.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationTypeInterface
   *   The loaded license variation type.
   */
  protected function loadVariationType($variation_type_id) {
    if (!isset($this->variationTypes[$variation_type_id])) {
      $storage = $this->entityTypeManager->getStorage('commerce_license_variation_type');
      $this->variationTypes[$variation_type_id] = $storage->load($variation_type_id);
    }

    return $this->variationTypes[$variation_type_id];
  }

}
