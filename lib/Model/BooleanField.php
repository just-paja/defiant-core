<?php

namespace Defiant\Model;

class BooleanField extends Field {
  const dbType = 'TINYINT';

  public function getValue($instance, $value) {
    return !!$value;
  }
}
