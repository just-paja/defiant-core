<?php

namespace Defiant\Model;

class BooleanField extends Field {
  const dbType = 'TINYINT';
  const isUnsigned = true;

  public function getValue($instance, $value) {
    return !!$value;
  }
}
