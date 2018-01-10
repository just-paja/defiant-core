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
}
