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
    } elseif (is_int($value)) {
      return $value;
    }
    return "'$value'";
  }

  public function configure(array $config) {
    $this->runner = new \Defiant\Runner($config);
  }

  public static function getAllConsoleModules() {
    $classes = get_declared_classes();
    $modules = [];

    foreach ($classes as $class) {
      if (is_subclass_of($class, get_class())) {
        $modules[] = $class;
      }
    }

    return $modules;
  }
}
