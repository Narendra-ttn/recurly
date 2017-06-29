<?php

namespace Drupal\lazy_load\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LazyLoadConfigForm.
 *
 * @package Drupal\lazy_load\Form
 */
class LazyLoadConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'lazy_load_configuration.image_data',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lazy_load_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    // Get config.
    $config = $this->config('lazy_load_configuration.image_data');

    // Google Chrome Messaging.
    $form['image_data'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Remove Images from Lazy Load.'),
      '#description' => $this->t('Enter the Images URL from that want to remove Lazy Loading.'),
    ];

    $form['image_data']['lazy_loading_image_urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Image URLs'),
      '#description' => "Enter Image Url by Line separator.",
      '#default_value' => $config->get('lazy_loading_image_urls'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Store GCM config.
    $config_gpn = $this->config('lazy_load_configuration.image_data');
    $config_gpn->set('lazy_loading_image_urls', $form_state->getValue('lazy_loading_image_urls'));
    $config_gpn->save();
    drupal_set_message($this->t('Lazy Loading setting is saved.'));
  }

}