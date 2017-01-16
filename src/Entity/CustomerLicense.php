<?php

namespace Drupal\commerce_license\Entity;

use Drupal\user\UserInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the license entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_customer_license",
 *   label = @Translation("Customer License"),
 *   label_singular = @Translation("license"),
 *   label_plural = @Translation("licenses"),
 *   label_count = @PluralTranslation(
 *     singular = "@count license",
 *     plural = "@count licenses",
 *   ),
 *   bundle_label = @Translation("License type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "view_builder" = "Drupal\commerce_license\CustomerLicenseViewBuilder",
 *     "list_builder" = "Drupal\commerce_license\CustomerLicenseListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_license\Form\LicenseForm",
 *       "add" = "Drupal\commerce_license\Form\LicenseForm",
 *       "edit" = "Drupal\commerce_license\Form\LicenseForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\commerce_license\LicenseTranslationHandler"
 *   },
 *   admin_permission = "administer commerce_license",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   base_table = "commerce_license",
 *   data_table = "commerce_license_field_data",
 *   entity_keys = {
 *     "id" = "license_id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/user/{user}/license/{commerce_license}",
 *     "collection" = "/admin/commerce/licenses"
 *   },

 * )
 */

class CustomerLicense extends ContentEntityBase {

  use EntityChangedTrait;

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
  public function isExpired() {
    return (bool) $this->getEntityKey('expired');

  }

  /**
  * {@inheritdoc}
  */
  public function setPublished($expired) {
    $this->set('expired', (bool) $expired);
    if (true) {
      $this->setExpirationTime(time());
    }
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
  * Gets the expiration datetime.
  */
  public function getExpirationTime() {
    return $this->get('expiration')->value;
  }

  /**
  * Sets the expiration datetime.
  */
  public function setExpirationTime($timestamp) {
    $this->set('expiration', $timestamp);
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
  public function getVariationId() {
    $variation_ids = [];
    foreach ($this->get('variation') as $field_item) {
      $variation_ids[] = $field_item->target_id;
    }
    return $variation_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariation() {
    $variations = $this->get('variation')->referencedEntities();
    return $this->ensureTranslations($variations);
  }

  /**
   * {@inheritdoc}
   */
  public function setVariation(array $variations) {
    $this->set('variation', $variations);
    return $this;
  }

  /**
   * Ensures that the provided entities are in the current entity's language.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   The entities to process.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   The processed entities.
   */
  protected function ensureTranslations(array $entities) {
    $langcode = $this->language()->getId();
    foreach ($entities as $index => $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if ($entity->hasTranslation($langcode)) {
        $entities[$index] = $entity->getTranslation($langcode);
      }
    }

    return $entities;
  }

  /**
  * {@inheritdoc}
  */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The license author.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\commerce_license\Entity\License::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['expired'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDescription(t('Whether the license is published.'))
      ->setDefaultValue(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the license was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the license was last edited.'))
      ->setTranslatable(TRUE);

    $fields['expiration'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the license was last edited.'))
      ->setTranslatable(TRUE);

    $fields['data'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of additional data.'));

    $fields['variation'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('License'))
      ->setDescription(t('Details for this issued license.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setSettings(array(
        'target_type' => 'commerce_license_variation',
        'default_value' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

      $fields['order'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Order'))
        ->setDescription(t('The order that this license was purchased with.'))
        ->setRequired(TRUE)
        ->setDisplayOptions('form', [
          'type' => 'entity_reference_autocomplete',
          'weight' => -1,
          'settings' => [
            'match_operator' => 'CONTAINS',
            'size' => '60',
            'placeholder' => '',
          ],
        ])
        ->setSettings(array(
          'target_type' => 'commerce_order',
          'default_value' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

        $fields['order_item'] = BaseFieldDefinition::create('entity_reference')
          ->setLabel(t('Order'))
          ->setDescription(t('The order item that this license was purchased with.'))
          ->setRequired(TRUE)
          ->setDisplayOptions('form', [
            'type' => 'entity_reference_autocomplete',
            'weight' => -1,
            'settings' => [
              'match_operator' => 'CONTAINS',
              'size' => '60',
              'placeholder' => '',
            ],
          ])
          ->setSettings(array(
            'target_type' => 'commerce_order_item',
            'default_value' => 0,
          ))
          ->setDisplayConfigurable('form', TRUE)
          ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  public function getCurrentUserId() {
    return \Drupal::currentUser()->id();
  }

  public function getCurrentUser() {
    return \Drupal::currentUser();
  }
}
