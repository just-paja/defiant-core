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

  public function __get($resource) {
    if (isset($this->resources[$resource])) {
      return $this->resources[$resource];
    }

    if ($resource) {
      throw new Error(sprintf('Resource %s does not exist in %s', $resource, get_class($this)));
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
