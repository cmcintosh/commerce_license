<?php

namespace Drupal\commerce_license\Form;

use Drupal\commerce_license\Entity\LicenseInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the license add/edit form.
 *
 * Uses a two-column layout, optimized for an admin theme.
 */
class LicenseForm extends ContentEntityForm {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new LicenseForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(EntityManagerInterface $entity_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_manager);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Skip building the form if there are no available stores.
    $store_query = $this->entityManager->getStorage('commerce_store')->getQuery();
    if ($store_query->count()->execute() == 0) {
      $link = Link::createFromRoute('Add a new store.', 'entity.commerce_store.add_page');
      $form['warning'] = [
        '#markup' => t("Licenses can't be created until a store has been added. @link", ['@link' => $link->toString()]),
      ];
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\commerce_license\Entity\License $license */
    $license = $this->entity;
    $form = parent::form($form, $form_state);

    $form['#tree'] = TRUE;
    $form['#theme'] = ['commerce_license_form'];
    $form['#attached']['library'][] = 'commerce_license/form';
    $form['#entity_builders']['update_status'] = [get_class($this), 'updateStatus'];
    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $license->getChangedTime(),
    ];

    $last_saved = t('Not saved yet');
    if (!$license->isNew()) {
      $last_saved = $this->dateFormatter->format($license->getChangedTime(), 'short');
    }
    $form['meta'] = [
      '#attributes' => ['class' => ['entity-meta__header']],
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -100,
      'published' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $license->isPublished() ? $this->t('Published') : $this->t('Not published'),
        '#access' => !$license->isNew(),
        '#attributes' => [
          'class' => 'entity-meta__title',
        ],
      ],
      'changed' => [
        '#type' => 'item',
        '#wrapper_attributes' => [
          'class' => ['entity-meta__last-saved', 'container-inline'],
        ],
        '#markup' => '<h4 class="label inline">' . $this->t('Last saved') . '</h4> ' . $last_saved,
      ],
      'author' => [
        '#type' => 'item',
        '#wrapper_attributes' => [
          'class' => ['author', 'container-inline'],
        ],
        '#markup' => '<h4 class="label inline">' . $this->t('Author') . '</h4> ' . $license->getOwner()->getDisplayName(),
      ],
    ];
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];
    $form['visibility_settings'] = [
      '#type' => 'details',
      '#title' => t('Visibility settings'),
      '#open' => TRUE,
      '#group' => 'advanced',
      '#access' => !empty($form['stores']['#access']),
      '#attributes' => [
        'class' => ['license-visibility-settings'],
      ],
      '#weight' => 30,
    ];
    $form['path_settings'] = [
      '#type' => 'details',
      '#title' => t('URL path settings'),
      '#open' => !empty($form['path']['widget'][0]['alias']['#value']),
      '#group' => 'advanced',
      '#access' => !empty($form['path']['#access']) && $license->get('path')->access('edit'),
      '#attributes' => [
        'class' => ['path-form'],
      ],
      '#attached' => [
        'library' => ['path/drupal.path'],
      ],
      '#weight' => 60,
    ];
    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['license-form-author'],
      ],
      '#attached' => [
        'library' => ['commerce_license/drupal.commerce_license'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];
    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }
    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }
    if (isset($form['path'])) {
      $form['path']['#group'] = 'path_settings';
    }
    if (isset($form['stores'])) {
      $form['stores']['#group'] = 'visibility_settings';
      $form['#after_build'][] = [get_class($this), 'hideEmptyVisibilitySettings'];
    }

    return $form;
  }

  /**
   * Hides the visibility settings if the stores widget is a hidden element.
   *
   * @param array $form
   *   The form.
   *
   * @return array
   *   The modified visibility_settings element.
   */
  public static function hideEmptyVisibilitySettings(array $form) {
    if (isset($form['stores']['widget']['target_id'])) {
      $stores_element = $form['stores']['widget']['target_id'];
      if (!Element::getVisibleChildren($stores_element)) {
        $form['visibility_settings']['#printed'] = TRUE;
        // Move the stores widget out of the visibility_settings group to
        // ensure that its hidden element is still present in the HTML.
        unset($form['stores']['#group']);
      }
    }

    return $form;
  }

  /**
   * Entity builder: updates the license status with the submitted value.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Drupal\commerce_license\Entity\LicenseInterface $license
   *   The license updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\node\NodeForm::form()
   */
  public static function updateStatus($entity_type, LicenseInterface $license, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $license->setPublished($element['#published_status']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $license */
    $license = $this->entity;

    $element['delete']['#access'] = $license->access('delete');
    $element['delete']['#weight'] = 100;
    // Add a "Publish" button.
    $element['publish'] = $element['submit'];
    $element['publish']['#published_status'] = TRUE;
    $element['publish']['#dropbutton'] = 'save';
    $element['publish']['#weight'] = 0;
    // Add an "Unpublish" button.
    $element['unpublish'] = $element['submit'];
    $element['unpublish']['#published_status'] = FALSE;
    $element['unpublish']['#dropbutton'] = 'save';
    $element['unpublish']['#weight'] = 10;
    // isNew | prev status » primary   & publish label             & unpublish label
    // 1     | 1           » publish   & Save and publish          & Save as unpublished
    // 1     | 0           » unpublish & Save and publish          & Save as unpublished
    // 0     | 1           » publish   & Save and keep published   & Save and unpublish
    // 0     | 0           » unpublish & Save and keep unpublished & Save and publish.
    if ($license->isNew()) {
      $element['publish']['#value'] = $this->t('Save and publish');
      $element['unpublish']['#value'] = $this->t('Save as unpublished');
    }
    else {
      $element['publish']['#value'] = $license->isPublished() ? $this->t('Save and keep published') : $this->t('Save and publish');
      $element['unpublish']['#value'] = !$license->isPublished() ? $this->t('Save and keep unpublished') : $this->t('Save and unpublish');
    }
    // Set the primary button based on the published status.
    if ($license->isPublished()) {
      unset($element['unpublish']['#button_type']);
    }
    else {
      unset($element['publish']['#button_type']);
      $element['unpublish']['#weight'] = -10;
    }
    // Hide the now unneeded "Save" button.
    $element['submit']['#access'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_license\Entity\LicenseInterface $license */
    $license = $this->getEntity();
    $license->save();
    drupal_set_message($this->t('The license %label has been successfully saved.', ['%label' => $license->label()]));
    $form_state->setRedirect('entity.commerce_license.canonical', ['commerce_license' => $license->id()]);
  }

}
