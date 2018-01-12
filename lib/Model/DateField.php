<?php

namespace Defiant\Model;

class DateField extends Field {
  const dbType = 'DATE';

  public function serialize($value, $opportunity = null) {
    if ($value instanceof \DateTime) {
      $value = $value->format('YYYY-MM-DD');
    }
    return $value;
  }

  public function validateValue($value) {
    if ($value) {
      if (!preg_match('/^[0-9]{4}\-[01]\d\-[0123]\d$/', $value)) {
        throw new \Defiant\View\ValidationError('Value must be a valid ISO-8601 date.', $this->name);
      }
    }
    return true;
  }
}
