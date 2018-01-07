<?php

namespace Defiant\View;

class ValidationError extends \Defiant\View\Error {
  protected $fieldName;

  public function __construct($message, $fieldName) {
    $this->message = $message;
    $this->fieldName = $fieldName;
  }

  public function getFieldName() {
    return $this->fieldName;
  }
}
