<?php

namespace Defiant\Model;

class PasswordField extends VarcharField {
  public function hashValue($value) {
    $model = $this->carrier;
    $connector = $model::getConnector();
    $salt = $connector->getRunner()->getSecretKey();
    return sha1($salt.$value);
  }

  public function formatValue($value) {
    return $this->hashValue($value);
  }
}
