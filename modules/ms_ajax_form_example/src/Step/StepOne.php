<?php

namespace Drupal\ms_ajax_form_example\Step;

use Drupal\ms_ajax_form_example\Button\StepOneNextButton;
use Drupal\ms_ajax_form_example\Validator\EmailCheckMaropost;
use Drupal\ms_ajax_form_example\Validator\ValidatorEmail;
use Drupal\ms_ajax_form_example\Validator\ValidatorRequired;
use Egulias\EmailValidator\EmailValidator;

/**
 * Class StepOne.
 *
 * @package Drupal\ms_ajax_form_example\Step
 */
class StepOne extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      [
        '#type' => 'submit',
        '#key' => 'next',
        '#value' => t('Subscribe'),
        '#goto_step' => StepsEnum::STEP_TWO,
        '#submit_handler' => 'emailSubmitToMaropost',
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() {
    $form['mail_subscriber_email'] = [
      '#type' => 'email',
      '#title' => t("Email"),
      '#required' => true,
      '#default_value' => '',
      '#submit_handler' => 'emailSubmitToMaropost',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    return [
      'mail_subscriber_email',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators() {
    return [
      'mail_subscriber_email' => [
        new ValidatorEmail('The email address is not valid.'),
      ],
    ];
  }
}
