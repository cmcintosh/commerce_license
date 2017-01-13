<?php

namespace Drupal/commerce_license/Entity;

use Drupal\user\UserInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
* Defines the license entity class
*
* @ContentEntityType(
*   id = "commerce_license",
*   label = @Translation('License'),
*   label_singular = @Translation("license"),
*   label_plural = @Translation("licenses"),
*   label_count = @PluralTranslation(
*    singular = "@count license",
*    plural = "@count licenses",
*  ),
*  bundle_label = @Translation("License Type"),
*  handlers = {
*    "event" = "Drupal\commerce_license\Event\LicenseEvent",
*    "storage" = "Drupal\commerce\CommerceContentEntityStorage",
*    "access" = "Drupal\commerce_license\EntityAccessControlHandler",
*    "permission_provider" = "Drupal\commerce_license\EntityPermissionProvider",
*    "view_builder" = "Drupal\commerce_license\LicenseViewBuilder",
*    "list_builder" = "Drupal\commerce_license\LicenseListBuilder",
*    "views_data" = "Drupal\views\EntityViewsData",
*    "form" = {
*      "default" = "Drupal\commerce_license\Form\LicenseForm",
*      "add" = "Drupal\commerce_license\Form\LicenseForm",
*      "edit" = "Drupal\commerce_license\Form\LicenseForm",
*      "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
*    },
*    "route_provider" = {
*      "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
*      "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
*    },
*    "translation" = "Drupal\commerce_license\LicenseTranslationHandler",
*  },
*  admin_permission = "administer commerce_license",
*  permission_granularity = "bundle",
*  fieldable = TRUE,
*  translatable = TRUE,
*  base_table = "commerce_license",
*  data_table = "commerce_license_field_data",
*  entity_keys = {
*    "id" = "license_id",
*    "bundle" = "type",
*    "label" = "title",
*    "langcode" = "langcode",
*    "uuid" = "uuid",
*    "status" = "status",
*  },
*  links = {
*    "canonical" = "/license/{commerce_license}",
*    "add-page" = "/license/add",
*    "edit-form" = "/license/{commerce_license}/edit",
*    "delete-form" = "/license/{commerce_license}/delete",
*    "delete-multiple-form" = "/admin/commerce/licenses/delete",
*    "collection" = "/admin/commerce/licenses"
*  },
*  bundle_entity_type = "commerce_license_type",
*  field_ui_base_route = "entity.commerce_license_type.edit_form"
* )
*/

class License extends ContentEntity implements LicenseInterface {

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
  public function isPublished() {
    return (bool) $this->getEntityKey('status');

  }

  /**
  * {@inheritdoc}
  */
  public function setPublished($published) {
    $this->set('status', (bool) $published);
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
  public function getStores() {
    $stores = $this->get('stores')->referencedEntities();
    return $this->ensureTranslations($stores);
  }

  /**
  * {@inheritdoc}
  */
  public function setStores(array $stores) {
    $this->set('stores', $stores);
    return $this;
  }

  /**
  * {@inheritdoc}
  */
  public function getStoreIds() {
    $store_ids = [];
    foreach ($this->get('stores') as $store_item) {
      $store_ids[] = $store_item->target_id;
    }
    return $store_ids;
  }

  /**
  * {@inheritdoc}
  */
  public function setStoreIds(array $store_ids) {
    $this->set('stores', $store_ids);
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
  public function getVariationIds() {
    $variation_ids = [];
    foreach ($this->get('variations') as $field_item) {
      $variation_ids[] = $field_item->target_id;
    }
    return $variation_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariations() {
    $variations = $this->get('variations')->referencedEntities();
    return $this->ensureTranslations($variations);
  }

  /**
   * {@inheritdoc}
   */
  public function setVariations(array $variations) {
    $this->set('variations', $variations);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariations() {
    return !$this->get('variations')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addVariation(LicenseVariationInterface $variation) {
    if (!$this->hasVariation($variation)) {
      $this->get('variations')->appendItem($variation);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariation(LicenseVariationInterface $variation) {
    $index = $this->getVariationIndex($variation);
    if ($index !== FALSE) {
      $this->get('variations')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariation(LicenseVariationInterface $variation) {
    return in_array($variation->id(), $this->getVariationIds());
  }

  /**
   * Gets the index of the given variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return int|bool
   *   The index of the given variation, or FALSE if not found.
   */
  protected function getVariationIndex(LicenseVariationInterface $variation) {
    return array_search($variation->id(), $this->getVariationIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultVariation() {
    foreach ($this->getVariations() as $variation) {
      // Return the first active variation.
      if ($variation->isActive() && $variation->access('view')) {
        return $variation;
      }
    }
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
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Ensure there's a back-reference on each product variation.
    foreach ($this->variations as $item) {
      $variation = $item->entity;
      if ($variation->product_id->isEmpty()) {
        $variation->product_id = $this->id();
        $variation->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    // Delete the product variations of a deleted product.
    $variations = [];
    foreach ($entities as $entity) {
      if (empty($entity->variations)) {
        continue;
      }
      foreach ($entity->variations as $item) {
        $variations[$item->target_id] = $item->entity;
      }
    }
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_license_variation');
    $variation_storage->delete($variations);
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

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The license title.'))
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
      ->setDisplayConfigurable('form', TRUE);

    $fields['path'] = BaseFieldDefinition::create('path')
      ->setLabel(t('URL alias'))
      ->setDescription(t('The license URL alias.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'path',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setCustomStorage(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
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

    return $fields;
  }

}
