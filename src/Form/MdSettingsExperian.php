<?php

namespace Drupal\md_experian\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MdSettingsExperian.
 *
 * @package Drupal\md_experian\Form
 */
class MdSettingsExperian extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'experian.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'experian_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('experian.settings');
    $form['experianPhone'] = [
      '#type' => 'details',
      '#title' => $this->t('Experian Phone number API settings'),
      '#open' => TRUE,
    ];

    $form['experianPhone']['expPhoneEndPoint'] = [
      '#type' => 'url',
      '#title' => $this->t('Endpoint'),
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('expPhoneEndPoint')) ? $config->get('expPhoneEndPoint') : '',
      '#description' => $this->t('Enter the experian endpoint for phone number.'),
    ];
    $form['experianPhone']['expPhoneToken'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token'),
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('expPhoneToken')) ? $config->get('expPhoneToken') : '',
      '#description' => $this->t('Enter the experian API Token form phone number.'),
    ];
    $form['experianEmail'] = [
      '#type' => 'details',
      '#title' => $this->t('Experian email address API settings'),
      '#open' => TRUE,
    ];
    $form['experianEmail']['expEmailEndPoint'] = [
      '#type' => 'url',
      '#title' => $this->t('End Point'),
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('expEmailEndPoint')) ? $config->get('expEmailEndPoint') : '',
      '#description' => $this->t('Enter the experian endpoint for email address.'),
    ];
    $form['experianEmail']['expEmailToken'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token'),
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('expEmailToken')) ? $config->get('expEmailToken') : '',
      '#description' => $this->t('Enter the experian API Token form email address.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('experian.settings')->set('expPhoneEndPoint', $values['expPhoneEndPoint'])->save();
    $this->config('experian.settings')->set('expPhoneToken', $values['expPhoneToken'])->save();
    $this->config('experian.settings')->set('expEmailEndPoint', $values['expEmailEndPoint'])->save();
    $this->config('experian.settings')->set('expEmailToken', $values['expEmailToken'])->save();
    parent::submitForm($form, $form_state);
  }

}
