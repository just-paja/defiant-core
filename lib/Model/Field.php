<?php

namespace Defiant\Model;

class Field {
  const dbType = null;
  const isUnsigned = null;
  protected $default = null;
  protected $isAutoincrement = false;
  protected $isNull = false;
  protected $isPrimary = false;
  protected $isUnique = false;
  protected $length = null;
  protected $name;

  public static function createFromDef($name, $def) {
    $fieldType = static::getFieldTypeByDef($def);
    if (!$fieldType) {
      throw new Error('Could not find model by definition', $def);
    }
    return new $fieldType($name, $def);
  }

  public static function getFieldTypeByDef($def) {
    $classes = get_declared_classes();
    $types = [];

    foreach ($classes as $class) {
      if (is_subclass_of($class, 'Defiant\Model\Field')) {
        if ($class::dbType == $def['dbType']) {
          if (isset($def['isUnsigned'])) {
            if ($class::isUnsigned === $def['isUnsigned']) {
              $types[] = $class;
            }
          } else {
            $types[] = $class;
          }
        }
      }
    }

    if (sizeof($types) === 1) {
      return $types[0];
    } else if (sizeof($types) > 1) {
      return static::getBestMatch($types);
    }

    return null;
  }

  public static function getBestMatch($types) {
    $max = null;
    $match = null;
    foreach ($types as $type) {
      if ($max === null || $type->hintDb() > $max) {
        $match = $type;
      }
    }

    return $match;
  }


  public function __construct($name, array $args = array()) {
    $this->name = $name;

    if ($args) {
      foreach ($args as $arg=>$value) {
        $this->$arg = $value;
      }
    }
  }

  public function serialize($value, $opportunity = null) {
    return $value;
  }

  public function getDefault() {
    return $this->default;
  }

  public function getName() {
    return $this->name;
  }

  public function getDbName() {
    return $this->name;
  }

  public function getLength() {
    return $this->length;
  }

  public function getValue($instance, $value) {
    return $value;
  }

  public function hintDb() {
    return 1;
  }

  public function isAutoincrement() {
    return $this->isAutoincrement;
  }

  public function isNull() {
    return $this->isNull;
  }

  public function isPrimary() {
    return $this->isPrimary;
  }

  public function isUnique() {
    return $this->isUnique;
  }

  public function formatValue($value) {
    return $value;
  }
}

require_once 'DatetimeField.php';
require_once 'EmailField.php';
require_once 'ForeignKeyField.php';
require_once 'IntegerField.php';
require_once 'PasswordField.php';
require_once 'PositiveIntegerField.php';
require_once 'TextField.php';
require_once 'VarcharField.php';
