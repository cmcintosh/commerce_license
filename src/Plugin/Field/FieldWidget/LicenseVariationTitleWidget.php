<?php

namespace Drupal\commerce_license\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'commerce_license_variation_title' widget.
 *
 * @FieldWidget(
 *   id = "commerce_license_variation_title",
 *   label = @Translation("License variation title"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class LicenseVariationTitleWidget extends LicenseVariationWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label_display' => TRUE,
      'label_text' => 'Please select',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['label_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display label'),
      '#default_value' => $this->getSetting('label_display'),
    ];
    $element['label_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label text'),
      '#default_value' => $this->getSetting('label_text'),
      '#description' => $this->t('The label will be available to screen readers even if it is not displayed.'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Label: "@text" (@visible)', [
      '@text' => $this->getSetting('label_text'),
      '@visible' => $this->getSetting('label_display') ? $this->t('visible') : $this->t('hidden'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $license */
    $license = $form_state->get('license');
    /** @var \Drupal\commerce_license\Entity\LicenseVariationInterface[] $variations */
    $variations = $this->variationStorage->loadEnabled($license);
    if (count($variations) === 0) {
      // Nothing to purchase, tell the parent form to hide itself.
      $form_state->set('hide_form', TRUE);
      $element['variation'] = [
        '#type' => 'value',
        '#value' => 0,
      ];
      return $element;
    }
    elseif (count($variations) === 1) {
      /** @var \Drupal\commerce_license\Entity\LicenseVariationInterface $selected_variation */
      $selected_variation = reset($variations);
      $element['variation'] = [
        '#type' => 'value',
        '#value' => $selected_variation->id(),
      ];
      return $element;
    }

    // Build the variation options form.
    $wrapper_id = Html::getUniqueId('commerce-license-add-to-cart-form');
    $form += [
      '#wrapper_id' => $wrapper_id,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];
    $parents = array_merge($element['#field_parents'], [$items->getName(), $delta]);
    $user_input = (array) NestedArray::getValue($form_state->getUserInput(), $parents);
    if (!empty($user_input)) {
      $selected_variation = $this->selectVariationFromUserInput($variations, $user_input);
    }
    else {
      $selected_variation = $this->variationStorage->loadFromContext($license);
      // The returned variation must also be enabled.
      if (!in_array($selected_variation, $variations)) {
        $selected_variation = reset($variations);
      }
    }

    // Set the selected variation in the form state for our AJAX callback.
    $form_state->set('selected_variation', $selected_variation->id());

    $variation_options = [];
    foreach ($variations as $option) {
      $variation_options[$option->id()] = $option->label();
    }
    $element['variation'] = [
      '#type' => 'select',
      '#title' => $this->getSetting('label_text'),
      '#options' => $variation_options,
      '#required' => TRUE,
      '#default_value' => $selected_variation->id(),
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxRefresh'],
        'wrapper' => $form['#wrapper_id'],
      ],
    ];
    if ($this->getSetting('label_display') == FALSE) {
      $element['variation']['#title_display'] = 'invisible';
    }

    return $element;
  }

  /**
   * Selects a license variation from user input.
   *
   * If there's no user input (form viewed for the first time), the default
   * variation is returned.
   *
   * @param \Drupal\commerce_license\Entity\LicenseVariationInterface[] $variations
   *   An array of license variations.
   * @param array $user_input
   *   The user input.
   *
   * @return \Drupal\commerce_license\Entity\LicenseVariationInterface
   *   The selected variation.
   */
  protected function selectVariationFromUserInput(array $variations, array $user_input) {
    $current_variation = NULL;
    if (!empty($user_input['variation']) && $variations[$user_input['variation']]) {
      $current_variation = $variations[$user_input['variation']];
    }

    return $current_variation;
  }

}
