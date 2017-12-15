<?php

namespace Defiant\Model;

class NullField extends Field {
  public function getValue($instance, $value) {
    if ($this->bindTrough) {
      return $this->bindTrough->getValue($instance, $value);
    }
    return null;
  }
}
