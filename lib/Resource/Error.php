<?php

namespace Defiant\Resource;

class Error extends \Exception {
  public function __construct($message, $params = null) {
    $this->message = $message;
    $this->params = $params;
  }

  public function getParams() {
    return $this->params;
  }
}
