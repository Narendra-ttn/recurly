<?php

namespace Drupal\ms_ajax_form_example\Validator;

use Egulias\EmailValidator\EmailValidator;

/**
 * Class ValidatorEmail.
 *
 * @package Drupal\ms_ajax_form_example\Validator
 */
class ValidatorEmail extends BaseValidator {

  protected $error_message;

  /**
   * ValidatorEmail constructor.
   *
   * @param string $error_message
   *   Error message.
   */
  public function __construct($error_message) {
    $this->error_message = $error_message;
  }

  /**
   * {@inheritdoc}
   */
  public function validates($value) {
    $emailValidator = new EmailValidator();
    return !$emailValidator->isValid($value) ? $this->error_message : true;
  }

}
