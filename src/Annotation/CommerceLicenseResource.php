<?php

namespace Drupal\commerce_license\Annotation;

use Drupal\Core\Condition\Annotation\Condition;
use Drupal\Component\Annotation\Plugin;

/**
 * Defines a condition plugin annotation object.
 * For our use we will be setting what type of resource the user gets when
 * a license is active.
 *
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class CommerceLicenseResource extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The resource label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The resource display label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $display_label;

  /**
  * Constructes a new LicenseResource objcet.
  */
  public function __construct(array $values) {
    parent::__construct($values);
  }
}
