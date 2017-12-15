<?php

namespace Defiant\Resource;

class Change {
  public $currentValue;
  public $previousValue;

  public function __construct($previousValue, $currentValue) {
    $this->previousValue = $previousValue;
    $this->currentValue = $currentValue;
  }
}
