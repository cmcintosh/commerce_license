<?php

namespace Drupal\commerce_license;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the list builder for license variation types.
 */
class LicenseVariationTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('License variation type');
    $header['type'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    $row['name'] = $entity->label();
    $row['type'] = $entity->id();

    return $row + parent::buildRow($entity);
  }

}
