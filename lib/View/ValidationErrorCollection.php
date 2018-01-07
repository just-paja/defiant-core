<?php

namespace Defiant\View;

class ValidationErrorCollection extends \Defiant\View\Error {
  protected $rawData;
  protected $errors;

  public function __construct(array $errors, array $rawData = []) {
    $this->message = 'Form validation failed';
    $this->errors = $errors;
    $this->rawData = $rawData;
  }

  public function getRawData() {
    return $this->rawData;
  }

  public function toErrorList() {
    $list = [];

    foreach ($this->errors as $error) {
      $list[$error->getFieldName()] = $error->getMessage();
    }

    return $list;
  }
}
