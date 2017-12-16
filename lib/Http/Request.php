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
    $this->protocol = $this->isSsl($server) ? 'https' : 'http';
  }

  protected function isSsl($server) {
    if (isset($server['HTTPS'])) {
      if ('on' == strtolower($server['HTTPS']) || '1' == $server['HTTPS']) {
        return true;
      }
    } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
      return true;
    }
    return false;
  }
}
