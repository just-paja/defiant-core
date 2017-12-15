<?php

namespace Defiant\Database;

class Error extends \Defiant\Error {
  public function __construct($code, $message, $query, $params = null) {
    $this->code = $code;
    $this->message = $message;
    $this->params = $params;
    $this->query = $query;
  }

  public function getParams() {
    return $this->params;
  }

  public function getQuery() {
    return $this->query;
  }
}
