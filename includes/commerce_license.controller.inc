<?php

/**
 * @file
 * Controller for the commerce_license entity type.
 */

class CommerceLicenseEntityController extends EntityBundlePluginEntityController {

  /**
    * Stores our transaction object, necessary for pessimistic locking to work.
    */
   protected $controllerTransaction = NULL;

   /**
    * Stores the ids of locked entities, necessary for knowing when to release a
    * lock by committing the transaction.
    */
   protected $lockedEntities = array();

   /**
    * Override of DrupalDefaultEntityController::buildQuery().
    *
    * Handle pessimistic locking.
    */
   protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
     $query = parent::buildQuery($ids, $conditions, $revision_id);

     if (isset($this->entityInfo['locking mode']) && $this->entityInfo['locking mode'] == 'pessimistic') {
       // In pessimistic locking mode, we issue the load query with a FOR UPDATE
       // clause. This will block all other load queries to the loaded objects
       // but requires us to start a transaction.
       if (empty($this->controllerTransaction)) {
         $this->controllerTransaction = db_transaction();
       }

       $query->forUpdate();

       // Store the ids of the entities in the lockedEntities array for later
       // tracking, flipped for easier management via unset() below.
       if (is_array($ids)) {
         $this->lockedEntities += array_flip($ids);
       }
     }

     return $query;
   }

   public function resetCache(array $ids = NULL) {
     parent::resetCache($ids);

     // Maintain the list of locked entities, so that the releaseLock() method
     // can know when it's time to commit the transaction.
     if (!empty($this->lockedEntities)) {
       if (isset($ids)) {
         foreach ($ids as $id) {
           unset($this->lockedEntities[$id]);
         }
       }
       else {
         $this->lockedEntities = array();
       }
     }

     // Try to release the lock, if possible.
     $this->releaseLock();
   }

   /**
    * Checks the list of tracked locked entities, and if it's empty, commits
    * the transaction in order to remove the acquired locks.
    *
    * The transaction is not necessarily committed immediately. Drupal will
    * commit it as soon as possible given the state of the transaction stack.
    */
   protected function releaseLock() {
     if (isset($this->entityInfo['locking mode']) && $this->entityInfo['locking mode'] == 'pessimistic') {
       if (empty($this->lockedEntities)) {
         unset($this->controllerTransaction);
       }
     }
   }

  /**
   * Create a default license.
   *
   * @param array $values
   *   An array of values to set, keyed by property name.
   * @return
   *   A product object with all default fields initialized.
   */
  public function create(array $values = array()) {
    $values += array(
      'license_id' => NULL,
      'is_new' => TRUE,
      'uid' => '',
      'status' => COMMERCE_LICENSE_CREATED,
      'granted' => 0,
      'expires' => 0,
    );
    if (!isset($values['type'])) {
      $types = commerce_license_get_plugins();
      $values['type'] = key($types);
    }
    return parent::create($values);
  }

  /**
   * Overrides EntityAPIController::save().
   *
   * @see EntityAPIController::save()
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    if (!isset($transaction)) {
      $transaction = db_transaction();
      $started_transaction = TRUE;
    }
    try {
      $return = parent::save($entity, $transaction);
      // Update the static cache so that the next entity_load() will return this
      // newly saved entity.
      $this->entityCache[$entity->{$this->idKey}] = $entity;

      // Maintain the list of locked entities and release the lock if possible.
      unset($this->lockedEntities[$entity->{$this->idKey}]);
      $this->releaseLock();
      return $return;
    }
    catch (Exception $e) {
      if (!empty($started_transaction)) {
        if (Database::getConnection()->inTransaction()) {
          $transaction->rollback();
        }
        else {
          drupal_set_message('Transaction already cancelled.', 'warning');
        }
        watchdog_exception($this->entityType, $e);
      }
      throw $e;
    }
  }
}
