<?php

namespace Drupal\license_resource_role\EventSubscriber;

use Drupal\commerce\Event\CommerceEvents;
use Drupal\commerce\Event\ReferenceablePluginTypesEvent;
use Symfony\Component\HttpKernal\KernalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_license\Event\LicenseEvents;
use Drupal\commerce_license\Event\LicenseIssuedEvent;

class EventHandler implements EventSubscriberInterface {


  public static function getSubscribedEvents() {
    $events[LicenseEvents::LICENSE_ISSUED] = ['onLicenseIssued'];
    return $events;
  }

  public function onLicenseIssued(LicenseIssuedEvent $event) {
    
    $order = $event->getOrder();

    // I want to award the customer the role here.
    $customer = $event->getCustomer();


    $license = $event->getLicenseVariation();
    if (count($license->resources) >0) {
      foreach($license->resources as $delta => $resource) {
        if ($resource->target_plugin_configuration['id'] == 'resource_role') {
          $customer->addRole($resource->target_plugin_configuration['resource_role']);
          $customer->save();
        }
      }
    }


  }

}
