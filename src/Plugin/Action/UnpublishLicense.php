<?php

namespace Drupal\commerce_license\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Unpublishes a license.
 *
 * @Action(
 *   id = "commerce_unpublish_license",
 *   label = @Translation("Unpublish selected license"),
 *   type = "commerce_license"
 * )
 */
class UnpublishLicense extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $entity */
    $entity->setPublished(FALSE);
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $object */
    $access = $object
      ->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
