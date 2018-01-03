<?php

namespace Defiant\Model;

class PasswordField extends VarcharField {
  public static function hashValue($value) {
    return sha1($value);
  }
}
