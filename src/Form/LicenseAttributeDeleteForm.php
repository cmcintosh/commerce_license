<?php

namespace Drupal\commerce_license\Form;

use Drupal\Core\Entity\EntityDeleteForm;

/**
 * Builds the form to delete a license attribute.
 */
class LicenseAttributeDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a license attribute will delete all of its values. This action cannot be undone.');
  }

}
