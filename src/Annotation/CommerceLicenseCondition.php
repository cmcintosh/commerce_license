<?php

namespace Drupal\commerce_license\Annotation;

use Drupal\Core\Condition\Annotation\Condition;

/**
 * Defines a condition plugin annotation object.
 * For our use we will be setting what the conditions are for an valid
 * license to remain valid.
 *
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class CommerceLicenseCondition extends Condition {

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
   * The payment gateway forms.
   *
   * An array of form classes keyed by operation.
   * For example:
   * <code>
   *   'configuration-form'      => "buildConfigurationForm",
   *    'license-attribute-form' =>  "buildLicenseAttributeForm",
   *   'add-to-cart-form'        => "buildAddToCartForm",
   * </code>
   *
   * @var array
   */
  public $forms = [];


  /**
  * Constructes a new LicenseResource objcet.
  */
  public function __construct(array $values) {

    parent::__construct($values);
  }
}
