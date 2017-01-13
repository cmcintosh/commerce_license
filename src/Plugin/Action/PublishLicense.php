<?php

namespace Drupal\commerce_license\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Publishes a license.
 *
 * @Action(
 *   id = "commerce_publish_license",
 *   label = @Translation("Publish selected license"),
 *   type = "commerce_license"
 * )
 */
class PublishLicense extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $entity */
    $entity->setPublished(TRUE);
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $object */
    $result = $object
      ->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
