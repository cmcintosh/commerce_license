<?php

/**
 * @file
 * Provides Entity metadata integration.
 */

class CommerceLicenseMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    $properties['status']['options list'] = 'commerce_license_status_options_list';
    $properties['status']['setter callback'] = 'entity_property_verbatim_set';
    $properties['granted']['type'] = 'date';
    $properties['expires']['type'] = 'date';

    // This ensures functioning Views relationships.
    unset($properties['uid']);
    $properties['owner'] = array(
      'type' => 'user',
      'label' => t("Owner"),
      'description' => t("The owner of the license."),
      'setter callback' => 'entity_property_verbatim_set',
      'required' => TRUE,
      'schema field' => 'uid',
    );
    unset($properties['product_id']);
    $properties['product'] = array(
      'type' => 'commerce_product',
      'label' => t("Product"),
      'description' => t("The licensed product."),
      'setter callback' => 'entity_property_verbatim_set',
      'required' => TRUE,
      'schema field' => 'product_id',
    );

    // entity_token uses this to provide [commerce_license:access-details].
    $properties['access_details'] = array(
      'label' => t("Access details"),
      'description' => t("The access details of an activated license."),
      'getter callback' => 'commerce_license_get_access_details',
    );

    return $info;
  }
}
