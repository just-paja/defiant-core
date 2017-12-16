<?php

namespace Defiant\Resource;

class Collection {
  protected $resources;

  public function __construct($resources = null) {
    $this->resources = [];
    if ($resources) {
      $this->replace($resources);
    }
  }

  public function __get($resourceName) {
    if (isset($this->resources[$resourceName])) {
      return $this->resources[$resourceName];
    }

    foreach ($this->resources as $key => $resource) {
      $keyArr = explode('\\', $key);
      $lowerKey = strtolower($keyArr[sizeof($keyArr) - 1]);
      $lowerResourceName = strtolower($resourceName);
      if ($lowerKey === $lowerResourceName) {
        return $resource;
      }
    }

    if ($resource) {
      throw new Error(sprintf('Resource %s does not exist in %s', $resourceName, get_class($this)));
    }
    throw new Error(sprintf('Could not determine default resource in %s', get_class($this)));

  }

  public function all() {
    return $this->resources;
  }

  public function replace($data) {
    $this->resources = $resources;
  }
}
