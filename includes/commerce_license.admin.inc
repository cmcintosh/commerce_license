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
    '#default_value' => variable_get('commerce_license_line_item_types', array()),
    '#options' => $line_item_types,
  );

  $form = system_settings_form($form);
  // commerce_license_settings_form_submit() will run first and call
  // system_settings_form_submit() when it's done.
  unset($form['#submit']);
  return $form;
}

/**
 * Submit callback of the order settings form.
 */
function commerce_license_settings_form_submit($form, &$form_state) {
  $old = array(
    'product_types' => variable_get('commerce_license_product_types', array()),
    'line_item_types' => variable_get('commerce_license_line_item_types', array()),
  );
  $new = array(
    'product_types' => $form_state['values']['commerce_license_product_types'],
    'line_item_types' => $form_state['values']['commerce_license_line_item_types'],
  );
  // Filter out disabled types.
  $old = array_map('array_filter', $old);
  $new = array_map('array_filter', $new);

  // Create license fields on the newly enabled types.
  $added_product_types = array_diff($new['product_types'], $old['product_types']);
  $added_line_item_types = array_diff($new['line_item_types'], $old['line_item_types']);
  commerce_license_configure_product_types($added_product_types);
  commerce_license_configure_line_item_types($added_line_item_types);

  // Remove license fields from the newly disabled types.
  $removed_product_types = array_diff($old['product_types'], $new['product_types']);
  $removed_line_item_types = array_diff($old['line_item_types'], $new['line_item_types']);
  foreach ($removed_product_types as $type) {
    $type_instance = field_info_instance('commerce_product', 'commerce_license_type', $type);
    $duration_instance = field_info_instance('commerce_product', 'commerce_license_duration', $type);
    field_delete_instance($type_instance);
    field_delete_instance($duration_instance);
  }
  foreach ($removed_line_item_types as $type) {
    $instance = field_info_instance('commerce_line_item', 'commerce_license', $type);
    field_delete_instance($instance);
  }

  system_settings_form_submit($form, $form_state);
}
