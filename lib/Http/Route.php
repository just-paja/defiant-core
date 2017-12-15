<?php

namespace Defiant\Http;

class Route {
  public $method;
  public $path;
  public $viewCallback;

  public function __construct($method, $path, $viewCallback) {
    $this->method = $method;
    $this->path = $path;
    $this->viewCallback = $viewCallback;
  }
}
