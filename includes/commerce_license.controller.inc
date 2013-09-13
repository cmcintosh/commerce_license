<?php

/**
 * @file
 * Controller for the commerce_license entity type.
 */

class CommerceLicenseEntityController extends EntityBundlePluginEntityController {

  /**
   * Overrides EntityBundlePluginEntityController::saveRevision().
   *
   * Maintains the revision_created and revision_ended timestamps.
   */
  protected function saveRevision($entity) {
    $entity->is_new_revision = !empty($entity->is_new_revision) || !empty($entity->revision) || $entity->is_new;

    if ($entity->is_new_revision) {
      $entity->revision_created = REQUEST_TIME;
      $entity->revision_ended = 0;

      // A previous revision exists, close it.
      if (!empty($entity->revision_id)) {
        $previous_revision = entity_revision_load('commerce_license', $entity->revision_id);
        $previous_revision->revision_ended = REQUEST_TIME - 1;
        $previous_revision->save();
      }
    }
    else {
      // If the revision being saved has no end timestamp, it's default.
      if (empty($entity->revision_ended)) {
        $entity->default_revision = TRUE;
      }
    }

    parent::saveRevision($entity);
  }
}
