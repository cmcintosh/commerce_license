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

  public function onLicenseIssued(LicenseIssuedEvent $event) {

      $license_variation = $event->getLicenseVariation();
      $customer_license = $event->getCustomerLicense();

      // We need to get our customer license id from the database.
      $query = \Drupal::database()
        ->select('commerce_license_customer_license', 're');
      $query->condition('re.order_id', $customer_license['order_id'], '=' );
      $query->condition('re.variation_id', $license_variation->get('variation_id')->value, '=');

      $query->addField('re', 'id');
      $result = $query->execute()->fetchAllAssoc('id');


      // For our purpose here we only need to respond if this license needs the newest entity.
      if (isset($customer_license['data']['resource']['resource_newest_entity'])) {

        // Entity query to get newest entity.
        $query = \Drupal::entityQuery('node');
        $query->condition('type', $customer_license['data']['resource']['resource_newest_entity']['bundle']);
        $query->condition('status', 1);
        $query->sort('created', 'desc');
        $nids = $query->execute();
        $entity_id = array_shift($nids);

        $data = [
          'customer_license_id' => $customer_license['customer_license'],
          'uid' => $customer_license['uid'],
          'entity_type' => 'node',
          'id' => $entity_id,
          'op' => 'view'
        ];


        $conn = Database::getConnection();
        $conn->insert('resource_entity_access')->fields($data)->execute();
      }

      // or an existing entity.
      if (isset($customer_license['data']['resource']['resource_existing_entity'])) {

        $data = [
          'customer_license_id' => $customer_license['customer_license'],
          'uid' => $customer_license['uid'],
          'entity_type' => 'node',
          'id' => $customer_license['data']['resource']['resource_existing_entity']['entity_id'],
          'op' => 'view'
        ];
        

        $conn = Database::getConnection();
        $conn->insert('resource_entity_access')->fields($data)->execute();
      }

  }

}
