<?php

namespace Drupal\commerce_license\PluginManager;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
* Manages discovery and instantiation of resource type plugins.
*
* @see \Drupal\commerce_license\Annotation\LicenseCondition
* @see plugin_api
*/
class LicenseConditionManager extends DefaultPluginManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LicenseResourceManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {

    parent::__construct(
      'Plugin/Commerce/LicenseCondition',
      $namespaces,
      $module_handler,
      'Drupal\commerce_license\Plugin\Commerce\LicenseCondition\LicenseConditionInterface',
      'Drupal\commerce_license\Annotation\CommerceLicenseCondition'
    );

    $this->alterInfo('commerce_license_condition_info');
    $this->setCacheBackend($cache_backend, 'commerce_license_condition_plugins');
    
  }

}
