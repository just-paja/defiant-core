<?php

namespace Defiant\Resource;

abstract class ClassCollector {
  public static function getAncestors() {
    $called = get_called_class();
    $allClasses = get_declared_classes();
    $followers = [];

    foreach ($allClasses as $class) {
      $reflect = new \ReflectionClass($class);
      if ($reflect->isSubclassOf($called) && $reflect->isInstantiable()) {
        $followers[] = $class;
      }
    }

    return $followers;
  }
}
