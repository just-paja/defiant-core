<?php

namespace Defiant\Model;

class Error extends \Defiant\Error {
  public function __construct($message, $params = null) {
    $this->message = $message;
    $this->params = $params;
  }

  public function getParams() {
    return $this->params;
  }
}
