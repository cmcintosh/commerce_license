<?php

namespace Drupal\commerce_license;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides #lazy_builder callbacks.
 */
class LicenseLazyBuilders {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs a new CartLazyBuilders object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFormBuilderInterface $entity_form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * Builds the add to cart form.
   *
   * @param string $license_id
   *   The license ID.
   * @param string $view_mode
   *   The view mode used to render the license.
   * @param bool $combine
   *   TRUE to combine order items containing the same license variation.
   *
   * @return array
   *   A renderable array containing the cart form.
   */
  public function addToCartForm($license_id, $view_mode, $combine) {
    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');

    /** @var \Drupal\commerce_license\Entity\LicenseInterface $license */
    $license = $this->entityTypeManager->getStorage('commerce_license')->load($license_id);
    $order_item = $order_item_storage->createFromPurchasableEntity($license->getDefaultVariation());

    $form_state_additions = [
      'license' => $license,
      'view_mode' => $view_mode,
      'settings' => [
        'combine' => $combine,
      ],
    ];
    return $this->entityFormBuilder->getForm($order_item, 'add_to_cart', $form_state_additions);
  }

}
