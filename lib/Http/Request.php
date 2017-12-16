<?php

namespace Defiant\Http;

class Request {
  public $method;
  public $path;
  public $host;
  protected $params = [];
  protected $query = [];

  public function __construct($server) {
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->host = $server['HTTP_HOST'];
    $this->protocol = $this->isSsl($server) ? 'https' : 'http';
    $pathSplit = explode('?', $server['REQUEST_URI']);
    $this->path = $pathSplit[0];
    if (isset($pathSplit[1])) {
      $this->parseQueryString($pathSplit[1]);
    }
  }

  public function getParams() {
    return $this->params;
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

  public function parseQueryString($queryString) {
    $arguments = explode('&', $queryString);
    foreach ($arguments as $argument) {
      $argumentSplit = explode('=', $argument);
      $param = $argumentSplit[0];
      $value = isset($argumentSplit[1]) ? $argumentSplit[1] : null;
      $this->query[$param] = $value;
    }
  }

  public function query($key, $defaultValue) {
    if (isset($this->query[$key]) && (string) $this->query[$key] !== '') {
      return $this->query[$key];
    }
    return $defaultValue;
  }

  public function setParams(array $params) {
    $this->params = $params;
  }
}
