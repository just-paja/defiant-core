<?php

namespace Defiant\Http;

class Route {
  public $method;
  public $path;
  public $viewCallback;
  protected $params = [];
  protected $pathPattern;
  protected $paramNames = [];

  public function __construct($method, $path, $viewCallback) {
    $this->method = $method;
    $this->path = $path === '/' ? '/' : rtrim($path, '/');
    $this->viewCallback = $viewCallback;
  }

  public function matches($path, $method = null) {
    if ($method && $this->method !== $method) {
      return false;
    }
    $matches = [];
    if (!preg_match($this->getPathPattern(), $path, $matches)) {
      return false;
    }
    foreach ($this->paramNames as $index => $param) {
      $this->params[$param] = isset($matches[$index + 1]) ? $matches[$index + 1] : null;
    }
    return true;
  }

  public function getParams() {
    return $this->params;
  }

  public function getPathPattern() {
    if (!$this->pathPattern) {
      $pattern = preg_replace('/\//', '\\/', $this->path);
      $pattern = preg_replace_callback(
        '/\:[a-zA-Z0-9]+/',
        function($matches) {
          $this->paramNames[] = ltrim($matches[0], ':');
          return '([^\/]+)';
        },
        $pattern
      );
      $this->pathPattern = '/^'.$pattern.'$/';
    }
    return $this->pathPattern;
  }
}
