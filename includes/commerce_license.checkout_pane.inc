<?php
/**
 * @file
 * Checkout pane callbacks.
 */

/**
 * Checkout pane callback: License edit.
 */
function commerce_license_information_checkout_form($form, &$form_state, $checkout_pane, $order) {
  $pane_form = array();
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Add a fieldset for each found license.
  foreach ($wrapper->commerce_line_items as $line_item_wrapper) {
    $product = $line_item_wrapper->commerce_product->value();
    $license_line_item = isset($line_item_wrapper->commerce_license);
    $license_product = !empty($product->commerce_license_type);
    if (!$license_line_item || !$license_product) {
      // The line item can't contain licenses, or the product is not licensable.
      continue;
    }

    $license = $line_item_wrapper->commerce_license->value();
    if (!$license) {
      // No license found, create a new one and save it to get its id.
      $type = $line_item_wrapper->commerce_product->commerce_license_type->value();
      $license = entity_create('commerce_license', array('type' => $type));
      $license->product_id = $line_item_wrapper->commerce_product->product_id->value();
      $license->save();
      // Save the license back to the line item.
      $line_item_wrapper->commerce_license = $license;
      $line_item_wrapper->save();
    }

    $license_id = $license->license_id;
    $title_arguments = array(
      '@product_title' => $line_item_wrapper->commerce_product->title->value(),
    );
    $pane_form[$license_id] = array(
      '#type' => 'fieldset',
      '#title' => t('License information - @product_title', $title_arguments),
      '#parents' => array($license_id),
      '#tree' => TRUE,
    );
    $license->form($pane_form[$license_id], $form_state);
  }

  return $pane_form;
}

/**
 * Checkout pane callback: License edit pane validate.
 */
function commerce_license_information_checkout_form_validate($form, &$form_state, $checkout_pane, $order) {
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Validate the form of each found license.
  foreach ($wrapper->commerce_line_items as $line_item_wrapper) {
    $product = $line_item_wrapper->commerce_product->value();
    $license_line_item = isset($line_item_wrapper->commerce_license);
    $license_product = !empty($product->commerce_license_type);
    if (!$license_line_item || !$license_product) {
      // The line item can't contain licenses, or the product is not licensable.
      continue;
    }
    $license = $line_item_wrapper->commerce_license->value();
    if (!$license) {
      // No license found (should never happen).
      continue;
    }

    $license_form = $form[$checkout_pane['pane_id']][$license->license_id];
    $license->formValidate($license_form, $form_state);
  }

  $form_errors = &drupal_static('form_set_error', array());
  if (empty($form_state['errors']) && empty($form_errors)) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Checkout pane callback: license edit pane submit callback.
 */
function commerce_license_information_checkout_form_submit($form, &$form_state, $checkout_pane, $order) {
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Submit the form of each found license.
  foreach ($wrapper->commerce_line_items as $line_item_wrapper) {
    $product = $line_item_wrapper->commerce_product->value();
    $license_line_item = isset($line_item_wrapper->commerce_license);
    $license_product = !empty($product->commerce_license_type);
    if (!$license_line_item || !$license_product) {
      // The line item can't contain licenses, or the product is not licensable.
      continue;
    }
    $license = $line_item_wrapper->commerce_license->value();
    if (!$license) {
      // No license found (should never happen).
      continue;
    }

    $license_form = $form[$checkout_pane['pane_id']][$license->license_id];
    $license->formSubmit($license_form, $form_state);
    $license->save();
  }
}

/**
 * Checkout pane callback: presents a completion license on the complete page.
 */
function commerce_license_complete_checkout_form($form, &$form_state, $checkout_pane, $order) {
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Submit the form of each found license.
  foreach ($wrapper->commerce_line_items as $line_item_wrapper) {
    if (!isset($line_item_wrapper->commerce_license)) {
      // The line item can't contain licenses.
      continue;
    }
    $license = $line_item_wrapper->commerce_license->value();
    if (!$license) {
      // No license found, nothing to display.
      continue;
    }

    $title_arguments = array(
      '@product_title' => $line_item_wrapper->commerce_product->title->value(),
    );
    $pane_form[$license->license_id] = array(
      '#type' => 'fieldset',
      '#title' => t('License information - @product_title', $title_arguments),
      'output' => array(
        '#markup' => $license->successText(),
      ),
    );
  }

  return $pane_form;
}
