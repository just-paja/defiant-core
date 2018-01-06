<?php

namespace Defiant\Model;

class JsonField extends TextField {
  public function serialize($value, $opportunity = null) {
    return json_encode($value);
  }

  public function getValue($instance, $value) {
    return $value && is_string($value) ? json_decode($value, true) : $value;
  }
}
