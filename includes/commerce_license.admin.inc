<?php

/**
 * @file
 * Contains admin menu callbacks for the Commerce License module.
 */

/**
 * Settings form callback.
 */
function commerce_license_settings_form($form, &$form_state) {
  $form['help'] = array(
    '#markup' => t('This form allows you to enable licensing for your product and line item types.'),
  );
  $form['commerce_license_product_types'] = array(
    '#title' => t('Product types'),
    '#type' => 'checkboxes',
    '#default_value' => variable_get('commerce_license_product_types', array()),
    '#options' => commerce_product_type_options_list(),
  );
  $line_item_types = array();
  foreach (commerce_line_item_types() as $type => $line_item_type) {
    // Only allow product line item types to be selected.
    if (!empty($line_item_type['product'])) {
      $line_item_types[$type] = $line_item_type['name'];
    }
  }
  $form['commerce_license_line_item_types'] = array(
    '#title' => t('Line item types'),
    '#type' => 'checkboxes',
    '#default_value' => variable_get('commerce_license_line_item_types', array_keys($line_item_types)),
    '#options' => $line_item_types,
  );

  $form = system_settings_form($form);
  $form['#submit'][] = 'commerce_license_settings_form_submit';
  return $form;
}

/**
 * Submit callback of the license settings form.
 */
function commerce_license_settings_form_submit($form, &$form_state) {
  // This will create the required fields on any newly selected types,
  // and remove them from any newly deselected types.
  commerce_license_flush_caches();
}
