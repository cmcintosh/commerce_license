<?php

/**
 * @file
 * Abstract and interface plugin implementation.
 */

/**
 * Provide a separate Exception so it can be caught separately.
 */
class CommerceLicenseException extends Exception { }

/**
 * Ensures basic required behavior for a license.
 *
 * EntityBundlePluginProvideFieldsInterface also mandates a fields() method.
 */
interface CommerceLicenseInterface extends EntityBundlePluginProvideFieldsInterface {

  /**
   * Returns an html representation of the access details.
   *
   * The information contained within should be the required minimum
   * for the customer to access the resource he purchased the rights to.
   *
   * If the customer purchased a file license, this method would output
   * the link to that file.
   * If the customer purchased a software subscription and the service
   * returned access credentials, this method would return those
   * access credentials.
   *
   * @return
   *   An html string with the access details.
   */
  public function accessDetails();

  /**
   * Returns whether a license is configurable.
   *
   * Configurable licenses are editable by the customer, either through
   * the add to cart form (via Inline Entity Form) or through a checkout pane.
   *
   * The output of this method determines whether form(), formValidate()
   * and formSubmit() will be called.
   *
   * @return
   *   TRUE if the license is configurable, FALSE otherwise.
   */
  public function isConfigurable();

  /**
   * Provides the license form.
   *
   * @param $form
   *   The license form. Might be embedded in another form through
   *   Inline Entity Form.
   * @param $form_state
   *   The form state of the complete form.
   *   Always use drupal_array_get_nested_value() instead of accessing
   *   $form_state['values'] directly.
   */
  public function form(&$form, &$form_state);

  /**
   * Validates the license form.
   *
   * @param $form
   *   The license form. Might be embedded in another form through
   *   Inline Entity Form, so form['#parents'] needs to be taken into account
   *   when fetching values and setting errors.
   * @param $form_state
   *   The form state of the complete form.
   *   Always use drupal_array_get_nested_value() instead of accessing
   *   $form_state['values'] directly.
   */
  public function formValidate($form, &$form_state);

  /**
   * Submits the license form.
   *
   * @param $form
   *   The license form. Might be embedded in another form through
   *   Inline Entity Form, so form['#parents'] needs to be taken into account
   *   when fetching values and setting errors.
   * @param $form_state
   *   The form state of the complete form.
   *   Always use drupal_array_get_nested_value() instead of accessing
   *   $form_state['values'] directly.
   */
  public function formSubmit(&$form, $form_state);

  /**
   * Returns the html message to be shown to the customer on checkout complete.
   *
   * Called by the commerce_license_checkout_complete checkout pane.
   *
   * @return
   *   The html message to be shown to the customer.
   */
  public function successText();

}

/**
 * License base class.
 *
 * Remote license types should inherit CommerceLicenseRemoteBase instead.
 */
abstract class CommerceLicenseBase extends Entity implements CommerceLicenseInterface, EntityBundlePluginValidableInterface {

  /**
   * License metadata wrapper.
   *
   * @var EntityDrupalWrapper
   */
  public $wrapper;

  /**
   * Constructor.
   *
   * @see Entity::__construct()
   */
  public function __construct(array $values) {
    // Provide default values.
    $values += array(
      'uid' => 0,
      'product_id' => 0,
      'status' => COMMERCE_LICENSE_CREATED,
      'granted' => 0,
      'expires' => 0,
    );

    parent::__construct($values, 'commerce_license');
    $this->wrapper = entity_metadata_wrapper($this->entityType, $this);
  }

  /**
   * Implements EntityBundlePluginProvideFieldsInterface::fields().
   */
  static function fields() {
    return array();
  }

  /**
   * Implements CommerceLicenseInterface::accessDetails().
   */
  public function accessDetails() {}

  /**
   * Implements CommerceLicenseInterface::isConfigurable().
   */
  public function isConfigurable() {
    return FALSE;
  }

  /**
   * Implements CommerceLicenseInterface::form().
   */
  public function form(&$form, &$form_state) {
    field_attach_form('commerce_license', $this, $form, $form_state, LANGUAGE_NONE);
  }

  /**
   * Implements CommerceLicenseInterface::formValidate().
   */
  public function formValidate($form, &$form_state) {
    field_attach_form_validate('commerce_license', $this, $form, $form_state);
  }

  /**
   * Implements CommerceLicenseInterface::formSubmit().
   */
  public function formSubmit(&$form, $form_state) {
    field_attach_submit('commerce_license', $this, $form, $form_state);
  }

  /**
   * Implements CommerceLicenseInterface::successText().
   */
  public function successText() {}

  /**
   * Overrides Entity::save().
   */
  public function save() {
    if (!empty($this->license_id) && !isset($this->original)) {
      $this->original = entity_load_unchanged('commerce_license', $this->license_id);
      $status_changed = ($this->status > $this->original->status);
      $current_status_active = ($this->status == COMMERCE_LICENSE_ACTIVE);
      if ($status_changed && $current_status_active) {
        // The license was just activated, set the granted timestamp.
        $this->granted = REQUEST_TIME;
        // Calculate the expiration timestamp.
        $duration = $this->wrapper->product->commerce_license_duration->value();
        if ($duration == 0) {
          // Duration is set to 'unlimited'.
          $this->expires = 0;
        }
        else {
          $this->expires = REQUEST_TIME + $duration;
        }
      }
    }

    parent::save();
  }

  /**
   * Returns whether the type plugin is valid.
   */
  public static function isValid() {
    return TRUE;
  }
}

/**
 * Broken implementation of a type plugin.
 */
class CommerceLicenseBroken extends Entity implements CommerceLicenseInterface, EntityBundlePluginValidableInterface  {

  /**
   * Throw an exception.
   */
  public function __construct($values = array()) {
    throw new CommerceLicenseException(t('Attempt to instantiate broken type plugin'));
  }

  /**
   * Implements EntityBundlePluginProvideFieldsInterface::fields().
   */
  static function fields() {
    return array();
  }

  /**
   * Implements CommerceLicenseInterface::accessDetails().
   */
  public function accessDetails() {}

  /**
   * Implements CommerceLicenseInterface::isConfigurable().
   */
  public function isConfigurable() {}

  /**
   * Implements CommerceLicenseInterface::form().
   */
  public function form(&$form, &$form_state) {}

  /**
   * Implements CommerceLicenseInterface::formValidate().
   */
  public function formValidate($form, &$form_state) {}

  /**
   * Implements CommerceLicenseInterface::formSubmit().
   */
  public function formSubmit(&$form, $form_state) {}

  /**
   * Implements CommerceLicenseInterface::successText().
   */
  public function successText() {}

  /**
   * This plugin is broken, so return FALSE.
   */
  public static function isValid() {
    return FALSE;
  }
}
