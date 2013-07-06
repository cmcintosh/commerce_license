<?php

/**
 * @file
 * Commerce License rules integration.
 */

/**
 * Implements hook_rules_event_info().
 */
function commerce_license_rules_event_info() {
  $events = array();
  $events['commerce_license_synchronize'] = array(
    'label' => t('After synchronizing a license'),
    'group' => t('Commerce License'),
    'variables' => array(
      'commerce_license' => array(
        'type' => 'commerce_license',
        'label' => t('A synchronized license'),
      ),
    ),
  );

  return $events;
}
