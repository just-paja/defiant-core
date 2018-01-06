<?php

namespace Defiant\Model;

class JsonField extends TextField {
  public function formatValue($value) {
    return json_encode($value);
  }

  public function getValue($instance, $value) {
    return $value ? json_decode($value, true) : null;
  }
}
