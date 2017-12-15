<?php

namespace Defiant\Console;

class Module {
  protected $runner;

  public function renderValue($value) {
    if ($value === null) {
      return 'null';
    } elseif ($value === false) {
      return 'false';
    } elseif ($value === true) {
      return 'true';
    }
    return "'$value'";
  }

  public function configure(array $config) {
    $this->runner = new \Defiant\Runner($config);
  }
}
