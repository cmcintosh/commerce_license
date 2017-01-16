<?php

namespace Drupal\commerce_license\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\commerce_license\Event\LicenseEvents;
use Drupal\commerce_license\Event\LicenseResourceInfoEvent;
use Drupal\commerce_license\Event\LicenseConditionInfoEvent;

/**
 * Defines the license variation entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_license_variation",
 *   label = @Translation("License variation"),
 *   label_singular = @Translation("license variation"),
 *   label_plural = @Translation("license variations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count license variation",
 *     plural = "@count license variations",
 *   ),
 *   bundle_label = @Translation("License variation type"),
 *   handlers = {
 *     "event" = "Drupal\commerce_license\Event\LicenseVariationEvent",
 *     "storage" = "Drupal\commerce_license\LicenseVariationStorage",
 *     "access" = "Drupal\commerce\EmbeddedEntityAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *     },
 *     "inline_form" = "Drupal\commerce_license\Form\LicenseVariationInlineForm",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   admin_permission = "administer commerce_license",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   content_translation_ui_skip = TRUE,
 *   base_table = "commerce_license_variation",
 *   data_table = "commerce_license_variation_field_data",
 *   entity_keys = {
 *     "id" = "variation_id",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "status" = "status",
 *   },
 *   bundle_entity_type = "commerce_license_variation_type",
 *   field_ui_base_route = "entity.commerce_license_variation_type.edit_form",
 * )
 */
class LicenseVariation extends ContentEntityBase implements LicenseVariationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    if ($rel == 'canonical') {
      $route_name = 'entity.commerce_license.canonical';
      $route_parameters = [
        'commerce_license' => $this->getLicenseId(),
      ];
      $options = [
        'query' => [
          'v' => $this->id(),
        ],
        'entity_type' => 'commerce_license',
        'entity' => $this->getLicense(),
        // Display links by default based on the current language.
        'language' => $this->language(),
      ];
      return new Url($route_name, $route_parameters, $options);
    }
    else {
      return parent::toUrl($rel, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLicense() {
    return $this->get('license_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getLicenseId() {
    return $this->get('license_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getSku() {
    return $this->get('sku')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSku($sku) {
    $this->set('sku', $sku);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    if (!$this->get('price')->isEmpty()) {
      return $this->get('price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('price', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', (bool) $active);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    $license = $this->getLicense();
    return $license ? $license->getStores() : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTypeId() {
    // We actually need to find an order item, that accepts license variation entities.
    // for now hardcode, it and get with bojan about a correct method for this

    // The order item type is a bundle-level setting.
    $type_storage = $this->entityTypeManager()->getStorage('commerce_license_variation_type');
    $type_entity = $type_storage->load($this->bundle());

    /**
    * @TODO: get with bojang so we can get correct method.
    */
    return 'license';
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTitle() {
    $label = $this->label();
    if (!$label) {
      $label = $this->generateTitle();
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValueIds() {
    $attribute_ids = [];
    foreach ($this->getAttributeFieldNames() as $field_name) {
      $field = $this->get($field_name);
      if (!$field->isEmpty()) {
        $attribute_ids[$field_name] = $field->target_id;
      }
    }

    return $attribute_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValueId($field_name) {
    $attribute_field_names = $this->getAttributeFieldNames();
    if (!in_array($field_name, $attribute_field_names)) {
      throw new \InvalidArgumentException(sprintf('Unknown attribute field name "%s".', $field_name));
    }
    $attribute_id = NULL;
    $field = $this->get($field_name);
    if (!$field->isEmpty()) {
      $attribute_id = $field->target_id;
    }

    return $attribute_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValues() {
    $attribute_values = [];
    foreach ($this->getAttributeFieldNames() as $field_name) {
      $field = $this->get($field_name);
      if (!$field->isEmpty()) {
        $attribute_values[$field_name] = $field->entity;
      }
    }

    return $attribute_values;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValue($field_name) {
    $attribute_field_names = $this->getAttributeFieldNames();
    if (!in_array($field_name, $attribute_field_names)) {
      throw new \InvalidArgumentException(sprintf('Unknown attribute field name "%s".', $field_name));
    }
    $attribute_value = NULL;
    $field = $this->get($field_name);
    if (!$field->isEmpty()) {
      $attribute_value = $field->entity;
    }

    return $attribute_value;
  }

  /**
   * Gets the names of the entity's attribute fields.
   *
   * @return string[]
   *   The attribute field names.
   */
  protected function getAttributeFieldNames() {
    $attribute_field_manager = \Drupal::service('commerce_license.attribute_field_manager');
    $field_map = $attribute_field_manager->getFieldMap($this->bundle());
    return array_column($field_map, 'field_name');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    $tags = parent::getCacheTagsToInvalidate();
    // Invalidate the variations view builder and license caches.
    return Cache::mergeTags($tags, [
      'commerce_license:' . $this->getLicenseId(),
      'commerce_license_variation_view',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    /** @var \Drupal\commerce_license\Entity\LicenseVariationTypeInterface $variation_type */
    $variation_type = $this->entityTypeManager()
      ->getStorage('commerce_license_variation_type')
      ->load($this->bundle());

    if ($variation_type->shouldGenerateTitle()) {
      $title = $this->generateTitle();
      $this->setTitle($title);
    }
  }

  /**
   * Generates the variation title based on attribute values.
   *
   * @return string
   *   The generated value.
   */
  protected function generateTitle() {
    if (!$this->getLicenseId()) {
      // Title generation is not possible before the parent license is known.
      return '';
    }

    $license_title = $this->getLicense()->getTitle();
    if ($attribute_values = $this->getAttributeValues()) {
      $attribute_labels = array_map(function ($attribute_value) {
        return $attribute_value->label();
      }, $attribute_values);

      $title = $license_title . ' - ' . implode(', ', $attribute_labels);
    }
    else {
      // When there are no attribute fields, there's only one variation.
      $title = $license_title;
    }

    return $title;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The variation author.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\commerce_license\Entity\LicenseVariation::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    // The license backreference, populated by License::postSave().
    $fields['license_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('License'))
      ->setDescription(t('The parent license.'))
      ->setSetting('target_type', 'commerce_license')
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['sku'] = BaseFieldDefinition::create('string')
      ->setLabel(t('SKU'))
      ->setDescription(t('The unique, machine-readable identifier for a variation.'))
      ->setRequired(TRUE)
      ->addConstraint('LicenseVariationSku')
      ->setSetting('display_description', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The variation title.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage (there might be a price per currency, role, country, etc).
    $fields['price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Price'))
      ->setDescription(t('The variation price'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['resources'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_license_resource')
      ->setLabel(t('Resource'))
      ->setDescription(t('Enter one or more resource awarded when this license is purchased.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_select',
        'weight' => 1,
      ]);

    $fields['conditions'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_license_condition')
      ->setLabel(t('Conditions'))
      ->setDescription(t('Enter in conditions for this license. This could be an expiration date, number of uses, etc.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_select',
        'weight' => 2,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDescription(t('Whether the variation is active.'))
      ->setDefaultValue(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 99,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the variation was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the variation was last edited.'))
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = [];
    /** @var \Drupal\commerce_license\Entity\LicenseVariationTypeInterface $variation_type */
    $variation_type = LicenseVariationType::load($bundle);
    // $variation_type could be NULL if the method is invoked during uninstall.
    if ($variation_type && $variation_type->shouldGenerateTitle()) {
      // The title is always generated, the field needs to be hidden.
      // The widget is hidden in commerce_license_field_widget_form_alter()
      // since setDisplayOptions() can't affect existing form displays.
      $fields['title'] = clone $base_field_definitions['title'];
      $fields['title']->setRequired(FALSE);
      $fields['title']->setDisplayConfigurable('form', FALSE);
    }

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
  * Will return specific information from the License Resource plugin.
  * - for now lets return an array to test check out.
  */
  public function getResourceInfo() {
    // We need to use events here to pull in the keyed information.
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new LicenseResourceInfoEvent($this);
    $dispatcher->dispatch(LicenseEvents::LICENSE_RESOURCE_INFO, $event);

    return $event->getInfo();
  }

  /**
  * Will return specific information from the License Condition plugin(s).
  */
  public function getConditionInfo() {
    // We need to use events here to pull in the keyed information.
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new LicenseConditionInfoEvent($this);
    $dispatcher->dispatch(LicenseEvents::LICENSE_CONDITION_INFO, $event);

    return $event->getInfo();
  }

}
