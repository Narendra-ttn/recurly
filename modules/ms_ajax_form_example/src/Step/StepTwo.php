<?php

namespace Drupal\ms_ajax_form_example\Step;

/**
 * Class StepTwo.
 *
 * @package Drupal\ms_ajax_form_example\Step
 */
class StepTwo extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return StepsEnum::STEP_TWO;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      [
        '#type' => 'submit',
        '#key' => 'Finish',
        '#value' => t('Submit'),
        '#goto_step' => StepsEnum::STEP_FINALIZE,
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() {
    $form['subscriber_age'] = [
      '#type' => 'checkboxes',
      '#title' => t('Select the age(s) you want in your newsletter.'),
      '#options' => ['0-6-years' => '0-6 years', '7-11-years' => '7-11 years', '12-18-years' => '12-18 years'],
      '#default_value' => [],
      '#required' => true,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    return [
      'subscriber_age',
    ];
  }
}
