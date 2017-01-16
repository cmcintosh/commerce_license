<?php


/**
* @file contains event handler for license_resource_entity
*/

namespace Drupal\license_resource_entity\EventSubscriber;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_license\Entity\LicenseInterface;
use Drupal\commerce_license\Entity\LicenseVariationInterface;

use Drupal\commerce\Event\CommerceEvents;
use Drupal\commerce_license\Event\LicenseEvents;
use Drupal\commerce\Event\ReferenceablePluginTypesEvent;
use Symfony\Component\HttpKernal\KernalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventHandler extends EventSubscriberInterface{

  public static function getSubscribedEvents() {
    $events[LicenseEvents::LICENSE_ORDER_PAID_IN_FULL] = ['onOrderPaid'];
    $events[LicenseEvents::LICENSE_ORDER_CANCELLED] = ['onOrderCancelled'];
    $events[LicenseEvents::LICENSE_ORDER_REFUNDED] = ['onOrderRefunded'];

    return $events;
  }

  public function onOrderPaid(OrderInterface $order) { }

  public function onOrderCancelled(OrderInterface $order) { }

  public function onOrderRefunded(OrderInterface $order) { }

}
