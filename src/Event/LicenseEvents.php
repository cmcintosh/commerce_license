<?php

namespace Drupal\commerce_license\Event;

final class LicenseEvents {

  /**
   * Name of the event fired after loading a license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_LOAD = 'commerce_license.commerce_license.load';

  /**
   * Name of the event fired after creating a new license.
   *
   * Fired before the license is saved.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_CREATE = 'commerce_license.commerce_license.create';

  /**
   * Name of the event fired before saving a license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_PRESAVE = 'commerce_license.commerce_license.presave';

  /**
   * Name of the event fired after saving a new license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_INSERT = 'commerce_license.commerce_license.insert';

  /**
   * Name of the event fired after saving an existing license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_UPDATE = 'commerce_license.commerce_license.update';

  /**
   * Name of the event fired before deleting a license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_PREDELETE = 'commerce_license.commerce_license.predelete';

  /**
   * Name of the event fired after deleting a license.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_DELETE = 'commerce_license.commerce_license.delete';

  /**
   * Name of the event fired after saving a new license translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_TRANSLATION_INSERT = 'commerce_license.commerce_license.translation_insert';

  /**
   * Name of the event fired after deleting a license translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseEvent
   */
  const LICENSE_TRANSLATION_DELETE = 'commerce_license.commerce_license.translation_delete';

  /**
   * Name of the event fired after changing the license variation via ajax.
   *
   * Allows modules to add arbitrary ajax commands to the response returned by
   * the add to cart form #ajax callback.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationAjaxChangeEvent
   */
  const LICENSE_VARIATION_AJAX_CHANGE = 'commerce_license.commerce_license_variation.ajax_change';

  /**
   * Name of the event fired after loading a license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_LOAD = 'commerce_license.commerce_license_variation.load';

  /**
   * Name of the event fired after creating a new license variation.
   *
   * Fired before the license variation is saved.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_CREATE = 'commerce_license.commerce_license_variation.create';

  /**
   * Name of the event fired before saving a license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_PRESAVE = 'commerce_license.commerce_license_variation.presave';

  /**
   * Name of the event fired after saving a new license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_INSERT = 'commerce_license.commerce_license_variation.insert';

  /**
   * Name of the event fired after saving an existing license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_UPDATE = 'commerce_license.commerce_license_variation.update';

  /**
   * Name of the event fired before deleting a license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_PREDELETE = 'commerce_license.commerce_license_variation.predelete';

  /**
   * Name of the event fired after deleting a license variation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_DELETE = 'commerce_license.commerce_license_variation.delete';

  /**
   * Name of the event fired after saving a new license variation translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_TRANSLATION_INSERT = 'commerce_license.commerce_license_variation.translation_insert';

  /**
   * Name of the event fired after deleting a license variation translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\LicenseVariationEvent
   */
  const LICENSE_VARIATION_TRANSLATION_DELETE = 'commerce_license.commerce_license_variation.translation_delete';

  /**
   * Name of the event fired when filtering variations.
   *
   * @Event
   *
   * @see \Drupal\commerce_license\Event\FilterVariationsEvent
   */
  const FILTER_VARIATIONS = "commerce_license.filter_variations";

  /**
  * Name of the event fired when a license expires.
  *
  * @Event
  *
  * @see \Drual\commerce_license\Event\LicenseExpiredEvent
  */
  const LICENSE_EXPIRED = "commerce_license.license_expired";

  /**
  * Create an event for when commerce orders have been paid in full, so that our resource modules can react.
  */
  const LICENSE_ORDER_PAID_IN_FULL = "commerce_license.order_paid_in_full";

  /**
  * Create an event for cancelled orders.
  */
  const LICENSE_ORDER_CANCELLED = "commerce_license.order_cancelled";

  /**
  * Create an event for refunded orders.
  */
  const LICENSE_ORDER_REFUNDED = "commerce_license.order_refunded";

  /**
  * Create a event that returns the resource info.
  */
  const LICENSE_RESOURCE_INFO = "commerce_license.resource_info";

  /**
  * Create a event that gets the condition info.
  */
  const LICENSE_CONDITION_INFO = "commerce_license.condition_info";
}
