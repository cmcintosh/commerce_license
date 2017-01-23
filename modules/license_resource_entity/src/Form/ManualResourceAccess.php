<?php

namespace Drupal\license_resource_entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;

class ManualResourceAccess extends FormBase {

  public function getFormId() {
    return 'ManualResourceAccess';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['wrapper'] = [
      '#type' => 'fieldset',
      '#description' => t('You can give manual access to individual users or to specific emails.  If no user exists for an email address one will be created and then the system will send the welcome email to that address.'),
    ];

    $form['wrapper']['email'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
    ];

    $form['wrapper']['user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('User'),
      '#prefix' => '<div class="clearfix">' . t('or') . '</div>',
      '#target_type' => 'user',
    ];

    $form['wrapper']['entity'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Content'),
      '#target_type' => 'node',
      '#description' => t('Enter a restricted access item you wish to provide access to.')
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Give Access')
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ( $values['email'] == '' && $values['user'] < 1) {
      drupal_set_message( t('You have to select either an email or user to give access to.') );
      return;
    }

    if ( $values['entity'] == NULL ) {
      drupal_set_message( t('You have to select content to give access to.') );
      return;
    }

    if ($values['email'] != '') {
      if ($account = user_load_by_mail($values['email'])) {
        $data = [
          'customer_license_id' => 0,
          'uid' => $account->id(),
          'entity_type' => 'node',
          'id' => $values['entity'],
          'op' => 'view'
        ];

        $conn = Database::getConnection();
        $conn->insert('resource_entity_access')->fields($data)->execute();
      }
      else {
        // we need to create the account first and then add it
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $account = \Drupal\user\Entity\User::create();

        $account->setPassword('password');
        $account->enforceIsNew();
        $account->setEmail($values['email']);
        $account->setUsername($values['email']);
        $account->activate();
        $res = $account->save();
        $data = [
          'customer_license_id' => 0,
          'uid' => $account->id(),
          'entity_type' => 'node',
          'id' => $values['entity'],
          'op' => 'view'
        ];
        $conn = Database::getConnection();
        $conn->insert('resource_entity_access')->fields($data)->execute();
      }
    }

    if ( $values['user'] ) {
      $data = [
        'customer_license_id' => 0,
        'uid' => $values['user'],
        'entity_type' => 'node',
        'id' => $values['entity'],
        'op' => 'view'
      ];

      $conn = Database::getConnection();
      $conn->insert('resource_entity_access')->fields($data)->execute();
    }



    ksm($values);
  }
}
