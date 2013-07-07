<?php

/**
 * Remote example license type.
 */
class CommerceLicenseRemoteExample extends CommerceLicenseRemoteBase  {

  /**
   * Implements EntityBundlePluginProvideFieldsInterface::fields().
   */
  static function fields() {
    $fields = parent::fields();

    // This field stores the api key returned by the remote service.
    // Such a field shouldn't be editable by the customer, of course.
    // Since this license type is not configurable, it's not a problem because
    // there's no form. However, if the license type was configurable,
    // this field instance would need to use the field_extrawidgets_hidden
    // widget provided by the field_extrawidgets module, or set
    // $form['cle_api_key']['#access'] = FALSE in form().
    $fields['cle_api_key']['field'] = array(
      'type' => 'text',
      'cardinality' => 1,
    );
    $fields['cle_api_key']['instance'] = array(
      'label' => t('API Key'),
      'required' => 1,
      'settings' => array(
        'text_processing' => '0',
      ),
    );

    return $fields;
  }

  /**
   * Implements CommerceLicenseInterface::resourceDetails().
   */
  public function accessDetails() {
    $output = field_view_field('commerce_license', $this, 'cle_api_key');
    return drupal_render($output);
  }

  /**
   * Implements CommerceLicenseInterface::isConfigurable().
   */
  public function isConfigurable() {
    return FALSE;
  }

  /**
   * Implements CommerceLicenseSynchronizableInterface::synchronize().
   */
  public function synchronize() {
    // Simulate a 2s delay in synchronization, as if the service call was done.
    sleep(2);
    // Imagine that the service call returned an api key. Set it.
    $this->wrapper->cle_api_key = sha1(REQUEST_TIME);

    return TRUE;
  }
}
