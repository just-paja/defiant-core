<?php

namespace Defiant\Model;

class EmailField extends VarcharField {
  public function validateValue($value) {
    if ($value) {
      if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
        throw new \Defiant\View\ValidationError(sprintf(
          'Provided value "%s" is not a valid e-mail address.',
          $value
        ), $this->name);
      }
    }
    return true;
  }
}
