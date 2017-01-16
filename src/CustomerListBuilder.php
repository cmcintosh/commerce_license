<?php

namespace Drupal\commerce_license;

use Drupal\commerce_license\Entity\LicenseType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder for licenses.
 */
class CustomerLicenseListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Title');
    $header['created'] = t('Issued');
    $header['status'] = t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $entity */


    $row['title']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();
    $row['issued'] = date('m/d/Y h:i', $entity->created);
    $row['status'] = $entity->isExpired() ? $this->t('Expired') : $this->t('Active');

    return $row + parent::buildRow($entity);
  }

}
