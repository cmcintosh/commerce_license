<?php


/**
* @file contains event handler for license_resource_entity
*/

namespace Drupal\license_resource_entity\EventSubscriber;
use Drupal\Core\Database\Database;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_license\Entity\LicenseInterface;
use Drupal\commerce_license\Entity\LicenseVariationInterface;

use Drupal\commerce\Event\CommerceEvents;
use Drupal\commerce_license\Event\LicenseEvents;
use Drupal\commerce\Event\ReferenceablePluginTypesEvent;
use Symfony\Component\HttpKernal\KernalEvents;
use Drupal\commerce_license\Event\LicenseResourceInfoEvent;
use Drupal\commerce_license\Event\LicenseIssuedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventHandler implements EventSubscriberInterface{

  public static function getSubscribedEvents() {
    $events[LicenseEvents::LICENSE_ORDER_PAID_IN_FULL] = ['onOrderPaid'];
    $events[LicenseEvents::LICENSE_ORDER_CANCELLED]    = ['onOrderCancelled'];
    $events[LicenseEvents::LICENSE_ORDER_REFUNDED]     = ['onOrderRefunded'];
    $events[LicenseEvents::LICENSE_RESOURCE_INFO]      = ['onResourceInfo'];
    $events[LicenseEvents::LICENSE_ISSUED]             = ['onLicenseIssued'];
    return $events;
  }

  public function onOrderPaid(OrderInterface $order) {

  }

  public function onOrderCancelled(OrderInterface $order) { }

  public function onOrderRefunded(OrderInterface $order) { }

  public function onResourceInfo(LicenseResourceInfoEvent $event) {
    $license_variation = $event->getLicenseVariation();
    $sku = $license_variation->getSKU();


    if ( $sku ) {
      // check for newest entity resource first.
      $query = \Drupal::database()
        ->select('resource_newest_entity', 're');
      $query->condition('re.sku', $sku, '=' );
      $query->addfield('re', 'sku');
      $query->addField('re', 'bundle');
      $result = $query->execute()->fetchAllAssoc('sku');

      if (isset($result[$sku])) {
        $event->setInfo('resource_newest_entity', [
          'bundle' => $result[$sku]->bundle
        ]);
      }

      // check for the new entity resource next.
      $query = \Drupal::database()
        ->select('resource_new_entity', 're');
      $query->condition('re.sku', $sku, '=' );
      $query->addfield('re', 'sku');
      $query->addField('re', 'bundle');
      $query->addField('re', 'quantity');
      $result = $query->execute()->fetchAllAssoc('sku');
      if (isset($result[$sku])) {
        $event->setInfo('resource_new_entity', [
          'bundle' => $result[$sku]->bundle,
          'quantity' => $result[$sku]->quantity
        ]);
      }

      // check for existing resource last.
      $query = \Drupal::database()
        ->select('resource_existing_entity', 're');

      $query->condition('re.sku', $sku, '=' );

      $query->addfield('re', 'sku');
      $query->addField('re', 'entity_id');
      $result = $query->execute()->fetchAllAssoc('sku');
      if (isset($result[$sku])) {
        $event->setInfo('resource_existing_entity', [
          'entity_id' => $result[$sku]->entity_id
        ]);
      }
    }
  }

  /**
  * Called when a license is issued.
  */
  public function onLicenseIssued(LicenseIssuedEvent $event) {

      $customer = $event->getCustomer();
      $license = $event->getLicenseVariation();

      // We need to issue the license to the customer.
      foreach($license->resources as $delta => $resource) {
        // Handle for existing entity
        $data = $resource->target_plugin_configuration;
        if ($resource->target_plugin_configuration['id'] == 'resource_existing_entity') {
          $acl_id = acl_get_id_by_name('resource_entity', "resource_entity_{$data['resource_entity_type']}_{$data['resource_entity']}");
          acl_add_user($acl_id, $customer->id());

          if ($data['resource_entity_type'] == 'node') {
            $acl_id = null;
            if (! ($acl_id = acl_get_id_by_name('content_access', 'view_' . $data['resource_entity'] ) ) ) {
                acl_create_acl( 'content_access', 'view_' . $data['resource_entity']);
            }
            $acl_id = acl_get_id_by_name('content_access', 'view_' . $data['resource_entity'] );

            db_merge('acl_user')
              ->key(['acl_id' => $acl_id, 'uid' => $customer->id()])
              ->fields([
                'acl_id' => $acl_id,
                'uid' => $customer->id()
              ]);
            $node = node_load($data['resource_entity']);
            \Drupal::entityManager()->getAccessControlHandler('node')->writeGrants($node);
          }

        }
        // Handle for newest entity
        else if ($resource->target_plugin_configuration['id'] == 'resource_newest_entity') {
          $data = $resource->target_plugin_configuration;

          $query = \Drupal::entityQuery('node');
          $query->condition('type', $data['resource_entity_bundle']);
          $query->condition('status', 1);
          $query->sort('created', 'desc');
          $query->accessCheck(FALSE);
          $nids = $query->execute();
          $entity_id = array_shift($nids);

          $acl_id = acl_get_id_by_name('content_access', 'view_' . $entity_id );
          db_merge('acl_user')
            ->key(['acl_id' => $acl_id, 'uid' => $customer->id()])
            ->fields([
              'acl_id' => $acl_id,
              'uid' => $customer->id()
            ])->execute();
          $node = node_load($entity_id);
          \Drupal::entityManager()->getAccessControlHandler('node')->writeGrants($node);
        }

        // No handling for new entities here.
      }

  }

}
