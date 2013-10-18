<?php

/**
 * @file
 * Controller for the commerce_license entity type.
 */

class CommerceLicenseEntityController extends EntityBundlePluginEntityController {

  /**
   * Overrides EntityBundlePluginEntityController::save().
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->is_new_revision = !empty($entity->is_new_revision) || !empty($entity->revision) || !empty($entity->is_new);
    // New revisions are always set as default.
    if ($entity->is_new_revision) {
      $entity->default_revision = TRUE;
    }
    parent::save($entity, $transaction);
  }

  /**
   * Overrides EntityBundlePluginEntityController::saveRevision().
   *
   * Maintains the revision_created and revision_ended timestamps.
   */
  protected function saveRevision($entity) {
    if ($entity->is_new_revision) {
      $current_time = commerce_license_get_time();
      $entity->revision_created = $current_time;
      $entity->revision_ended = 0;

      // A previous revision exists, close it.
      if (!empty($entity->revision_id)) {
        db_update($this->revisionTable)
          ->fields(array('revision_ended'  => $current_time - 1))
          ->condition('revision_id', $entity->revision_id)
          ->execute();
      }
    }

    parent::saveRevision($entity);
  }
}
