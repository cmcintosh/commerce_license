<?php

namespace Drupal\commerce_license;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_license\Entity\LicenseInterface;
use Drupal\commerce_license\Event\FilterVariationsEvent;
use Drupal\commerce_license\Event\LicenseEvents;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the license variation storage.
 */
class LicenseVariationStorage extends CommerceContentEntityStorage implements LicenseVariationStorageInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new LicenseVariationStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, EventDispatcherInterface $event_dispatcher, RequestStack $request_stack) {
    parent::__construct($entity_type, $database, $entity_manager, $cache, $language_manager, $event_dispatcher);

    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('event_dispatcher'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadFromContext(LicenseInterface $license) {
    $current_request = $this->requestStack->getCurrentRequest();
    if ($variation_id = $current_request->query->get('v')) {
      if (in_array($variation_id, $license->getVariationIds())) {
        /** @var \Drupal\commerce_license\Entity\LicenseVariationInterface $variation */
        $variation = $this->load($variation_id);
        if ($variation->isActive()) {
          return $variation;
        }
      }
    }
    return $license->getDefaultVariation();
  }

  /**
   * {@inheritdoc}
   */
  public function loadEnabled(LicenseInterface $license) {
    $ids = [];
    foreach ($license->variations as $variation) {
      $ids[$variation->target_id] = $variation->target_id;
    }
    // Speed up loading by filtering out the IDs of disabled variations.
    $query = $this->getQuery()
      ->condition('status', TRUE)
      ->condition('variation_id', $ids, 'IN');
    $result = $query->execute();
    if (empty($result)) {
      return [];
    }
    // Restore the original sort order.
    $result = array_intersect_key($ids, $result);

    $enabled_variations = $this->loadMultiple($result);
    // Allow modules to apply own filtering (based on date, stock, etc).
    $event = new FilterVariationsEvent($license, $enabled_variations);
    $this->eventDispatcher->dispatch(LicenseEvents::FILTER_VARIATIONS, $event);
    $enabled_variations = $event->getVariations();

    return $enabled_variations;
  }

}
