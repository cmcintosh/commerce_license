<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the product type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_license_type",
 *   label = @Translation("License type"),
 *   label_singular = @Translation("license type"),
 *   label_plural = @Translation("license types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count license type",
 *     plural = "@count license types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_license\LicenseTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_license\Form\LicenseTypeForm",
 *       "edit" = "Drupal\commerce_license\Form\LicenseTypeForm",
 *       "delete" = "Drupal\commerce_license\Form\LicenseTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_license_type",
 *   admin_permission = "administer commerce_license_type",
 *   bundle_of = "commerce_license",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "variationType",
 *     "injectVariationFields",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/license-types/add",
 *     "edit-form" = "/admin/commerce/config/license-types/{commerce_license_type}/edit",
 *     "delete-form" = "/admin/commerce/config/license-types/{commerce_license_type}/delete",
 *     "collection" = "/admin/commerce/config/license-types"
 *   }
 * )
 */
class LicenseType extends ConfigEntityBundleBase implements LicenseTypeInterface {

  /**
   * The product type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The product type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The product type description.
   *
   * @var string
   */
  protected $description;

  /**
   * The variation type ID.
   *
   * @var string
   */
  protected $variationType;

  /**
   * Indicates if variation fields should be injected.
   *
   * @var bool
   */
  protected $injectVariationFields = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariationTypeId() {
    return $this->variationType;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariationTypeId($variation_type_id) {
    $this->variationType = $variation_type_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldInjectVariationFields() {
    return $this->injectVariationFields;
  }

  /**
   * {@inheritdoc}
   */
  public function setInjectVariationFields($inject) {
    $this->injectVariationFields = (bool) $inject;
    return $this;
  }

}
