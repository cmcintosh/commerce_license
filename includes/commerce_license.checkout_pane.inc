<?php
/**
 * @file
 * Checkout pane callbacks.
 */

/**
 * Checkout pane callback: License edit.
 *
 * Outputs a pane for each found license.
 */
function commerce_license_information_checkout_form($form, &$form_state, $checkout_pane, $order) {
  $pane_form = array();
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Add a pane for each found license.
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
 * Checkout pane callback: License completion message.
 *
 * Outputs a pane for each found license.
 * Synchronizable licenses are shown in a pane that refreshes itself
 * periodically, allowing the customer to follow the synchronization process.
 * If the license doesn't get synchronized within 30 seconds, the refresh
 * process is stopped, and the license synchronization is marked as failed.
 */
function commerce_license_complete_checkout_form($form, &$form_state, $checkout_pane, $order) {
  $pane_form = array();
  $balance = commerce_payment_order_balance($order);
  $order_paid = ($balance && $balance['amount'] <= 0);
  $js_attached = FALSE;

  // Show a pane for each found license.
  $wrapper = entity_metadata_wrapper('commerce_order', $order);
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

    // If this is a synchronizable license, attach the JS for the refresh.
    $refresh_rate = NULL;
    if (!$js_attached && $license instanceof CommerceLicenseSynchronizableInterface) {
      $pane_form['#attached']['library'][] = array('system', 'drupal.ajax');
      $pane_form['#attached']['js'] = array(
        drupal_get_path('module', 'commerce_license') . '/commerce_license.js',
      );
      $js_attached = TRUE;
      $refresh_rate = COMMERCE_LICENSE_CHECK_REFRESH_RATE;
    }
    // Get the message to show.
    $message = t('Your license will be activated once your payment has been processed.');
    if ($order_paid) {
      $message = commerce_license_checkout_competion_message($license, $refresh_rate);
    }

    $title_arguments = array(
      '@product_title' => $line_item_wrapper->commerce_product->title->value(),
    );
    $pane_form[$license->license_id] = array(
      '#type' => 'fieldset',
      '#title' => t('License information - @product_title', $title_arguments),
      'output' => array(
        '#markup' => $message,
      ),
    );
  }

  return $pane_form;
}

/**
 * Ajax callback for the license completion message checkout pane.
 *
 * Called once every second, until the synchronization is marked
 * as successful / failed, or until it timeouts (within 30 seconds).
 */
function commerce_license_complete_checkout_ajax_callback($license) {
  // Get the refresh delay, initialize it if empty.
  $session_key = 'commerce_license_refresh_rate_' . $license->license_id;
  if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = COMMERCE_LICENSE_CHECK_REFRESH_RATE;
  }
  $refresh_delay = $_SESSION[$session_key] = $_SESSION[$session_key] + 1000;
  // Check if the refresh has timed out.
  if ($refresh_delay > COMMERCE_LICENSE_CHECK_REFRESH_TIMEOUT) {
    $license->wrapper->sync_status = COMMERCE_LICENSE_SYNC_FAILED;
    $license->save();
  }
  // Stop the refresh if synchronization has ended (succeeded, failed).
  $sync_status = $license->wrapper->sync_status->value();
  if (in_array($sync_status, array(COMMERCE_LICENSE_SYNCED, COMMERCE_LICENSE_SYNC_FAILED))) {
    unset($_SESSION[$session_key]);
    $refresh_delay = FALSE;
  }

  // Get the message.
  $message = commerce_license_checkout_competion_message($license, $refresh_delay);
  return array(
    '#type' => 'ajax',
    '#commands' => array(
      ajax_command_replace('#commerce-license-checkout-pane-' . $license->license_id, $message),
    ),
  );
}

/**
 * Returns the license checkout completion message.
 *
 * @param $license
 *   The license entity.
 * @param $refresh_delay
 *   The refresh delay to be added to the div, in miliseconds.
 *   If NULL, it is assumed that the refresh has been stopped.
 *
 * @return
 *   A string containing the success text.
 */
function commerce_license_checkout_competion_message($license, $refresh_delay = NULL) {
  $element = array(
    '#type' => 'container',
    '#attributes' => array(
      'id' => 'commerce-license-checkout-pane-' . $license->license_id,
      'class' => array(
        'commerce-license-checkout',
      ),
    ),
    'output' => array(
      '#markup' => $license->successText(),
    ),
  );
  // Add data attributes for the refresh process.
  if ($refresh_delay) {
    $element['#attributes'] += array(
      'data-refresh-url' => base_path() . 'ajax/commerce_license/' . $license->license_id,
      'data-refresh-delay' => $refresh_delay,
    );
  }

  return drupal_render($element);
}
