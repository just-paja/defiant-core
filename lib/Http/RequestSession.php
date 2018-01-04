<?php

namespace Defiant\Http;

class RequestSession {
  protected $data = [];

  public function __construct(&$data = []) {
    $this->data = &$data;
  }

  public function get($key, $default = null) {
    if (isset($this->data[$key])) {
      return $this->data[$key];
    }
    return $default;
  }

  public function set($key, $value) {
    $this->data[$key] = $value;
  }

  public function unset($key) {
    unset($this->data[$key]);
  }
}
