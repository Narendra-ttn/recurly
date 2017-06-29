<?php

namespace Drupal\ms_ajax_form_example\Validator;

/**
 * Class EmailCheckMaropost.
 *
 * @package Drupal\ms_ajax_form_example\Validator
 */
class EmailCheckMaropost extends BaseValidator {

  protected $error_message;

  /**
   * ValidatorEmail constructor.
   *
   * @param string $error_message
   *   Error message.
   */
  public function __construct($error_message) {
    parent::__construct($error_message);
    $this->error_message = $error_message;
  }

  /**
   * {@inheritdoc}
   */
  public function validates($value) {
    return false;
  }

}
