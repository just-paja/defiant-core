<?php

namespace Defiant\Http;

class Request {
  public $method;
  public $path;
  public $host;

  public function __construct($server) {
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->path = $server['REQUEST_URI'];
    $this->host = $server['HTTP_HOST'];
  }
}
