<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the license variation type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_license_variation_type",
 *   label = @Translation("License variation type"),
 *   label_singular = @Translation("license variation type"),
 *   label_plural = @Translation("license variation types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count license variation type",
 *     plural = "@count license variation types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_license\LicenseVariationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_license\Form\LicenseVariationTypeForm",
 *       "edit" = "Drupal\commerce_license\Form\LicenseVariationTypeForm",
 *       "delete" = "Drupal\commerce_license\Form\LicenseVariationTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_license_variation_type",
 *   admin_permission = "administer commerce_license_type",
 *   bundle_of = "commerce_license_variation",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "orderItemType",
 *     "generateTitle",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/license-variation-types/add",
 *     "edit-form" = "/admin/commerce/config/license-variation-types/{commerce_license_variation_type}/edit",
 *     "delete-form" = "/admin/commerce/config/license-variation-types/{commerce_license_variation_type}/delete",
 *     "collection" =  "/admin/commerce/config/license-variation-types"
 *   }
 * )
 */
class LicenseVariationType extends ConfigEntityBundleBase implements LicenseVariationTypeInterface {

  /**
   * The license variation type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The order item type ID.
   *
   * @var string
   */
  protected $orderItemType;

  /**
   * Whether the license variation title should be automatically generated.
   *
   * @var bool
   */
  protected $generateTitle;

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTypeId() {
    return $this->orderItemType;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderItemTypeId($order_item_type_id) {
    $this->orderItemType = $order_item_type_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldGenerateTitle() {
    return (bool) $this->generateTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function setGenerateTitle($generate_title) {
    $this->generateTitle = $generate_title;
  }

}
