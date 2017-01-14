<?php

/**
* @file
* Contains EventHandler for commerce_license.
* - Defines two new referenceable plugin types for the plugin_type field to use.
*/

namespace Drupal\commerce_license\EventSubscriber;

use Drupal\commerce\Event\CommerceEvents;
use Drupal\commerce\Event\ReferenceablePluginTypesEvent;
use Symfony\Component\HttpKernal\KernalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventHandler implements EventSubscriberInterface {

  /**
  * - Defines the events this handler will respond to.
  */
  public static function getSubscribedEvents() {
    drupal_set_message('Get Subscribed Events');
    $events[CommerceEvents::REFERENCEABLE_PLUGIN_TYPES][] = ['onReferenceablePluginTypes'];
    return $events;
  }

  public function onReferenceablePluginTypes(ReferenceablePluginTypesEvent $event) {
      $plugin_types = $event->getPluginTypes();
      $plugin_types['commerce_license_resource']  = t('License Resource');
      $plugin_types['commerce_license_condition'] = t('License Condition');
      $event->setPluginTypes( $plugin_types );
  }

}
