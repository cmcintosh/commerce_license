<?php

namespace Drupal\commerce_license\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the product attribute entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_license_attribute",
 *   label = @Translation("License attribute"),
 *   label_singular = @Translation("license attribute"),
 *   label_plural = @Translation("license attributes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count license attribute",
 *     plural = "@count license attributes",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "list_builder" = "Drupal\commerce_license\LicenseAttributeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_license\Form\LicenseAttributeForm",
 *       "edit" = "Drupal\commerce_license\Form\LicenseAttributeForm",
 *       "delete" = "Drupal\commerce_license\Form\LicenseAttributeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_license_attribute",
 *   admin_permission = "administer commerce_license_attribute",
 *   bundle_of = "commerce_license_attribute_value",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "elementType"
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/license-attributes/add",
 *     "edit-form" = "/admin/commerce/license-attributes/manage/{commerce_license_attribute}",
 *     "delete-form" = "/admin/commerce/license-attributes/manage/{commerce_license_attribute}/delete",
 *     "collection" =  "/admin/commerce/license-attributes",
 *   }
 * )
 */
class LicenseAttribute extends ConfigEntityBundleBase implements LicenseAttributeInterface {

  /**
   * The attribute ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The attribute label.
   *
   * @var string
   */
  protected $label;

  /**
   * The attribute element type.
   *
   * @var string
   */
  protected $elementType = 'select';

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    $storage = $this->entityTypeManager()->getStorage('commerce_license_attribute_value');
    return $storage->loadByAttribute($this->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getElementType() {
    return $this->elementType;
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    /** @var \Drupal\commerce_license\Entity\LicenseAttributeInterface[] $entities */
    parent::postDelete($storage, $entities);

    // Delete all associated values.
    $values = [];
    foreach ($entities as $entity) {
      foreach ($entity->getValues() as $value) {
        $values[$value->id()] = $value;
      }
    }
    /** @var \Drupal\Core\Entity\EntityStorageInterface $value_storage */
    $value_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_license_attribute_value');
    $value_storage->delete($values);
  }

}
