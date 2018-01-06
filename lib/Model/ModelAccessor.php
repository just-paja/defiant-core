<?php

namespace Defiant\Model;

define('MODEL_INSERT', 'MODEL_INSERT');
define('MODEL_UPDATE', 'MODEL_UPDATE');

abstract class ModelAccessor extends \Defiant\Resource\ClassCollector {
  protected static $fields = [];
  protected static $fieldTypes;
  protected $data;
  protected $changed = false;

  public function __construct(array $data = []) {
    $this->data = [];
    if ($data) {
      $this->setState($data);
    }
    $this->changed = false;
  }

  public function __get($fieldName) {
    return $this->getFieldValue($fieldName);
  }

  public function __isset($fieldName) {
    return $this->hasField($fieldName);
  }

  public function __set($fieldName, $value) {
    return $this->setValue($fieldName, $value);
  }

  public function __unset($fieldName) {
    unset($this->data[$fieldName]);
  }

  public static function getFields() {
    return array_merge([
      new IntegerField('id', [
        "isPrimary" => true,
        "isAutoincrement" => true,
      ]),
    ], static::getFieldsFromDefinition(static::$fields, false), [
      new DatetimeField('createdAt', [
        "setNowOnInsert" => true,
        "isNull" => true,
      ]),
      new DatetimeField('updatedAt', [
        "setNowOnUpdate" => true,
        "isNull" => true,
      ]),
    ]);
  }

  public static function getDefinedFields() {
    return static::getFieldsFromDefinition(static::$fields, false);
  }

  public static function getExpandedFields() {
    $fields = array_merge([
      new IntegerField('id', [
        "isPrimary" => true,
        "isAutoincrement" => true,
      ]),
    ], static::getFieldsFromDefinition(static::$fields, true));

    $fields[] = new DatetimeField('createdAt', [
      "setNowOnInsert" => true,
      "isNull" => true,
    ]);
    $fields[] = new DatetimeField('updatedAt', [
      "setNowOnUpdate" => true,
      "isNull" => true,
    ]);
    return $fields;
  }

  public static function getFieldFromCollection($collection, $fieldName) {
    foreach ($collection as $field) {
      if ($field->getName() == $fieldName) {
        return $field;
      }
    }
    return null;
  }

  public static function getField($fieldName) {
    $field = static::getFieldFromCollection(static::getFields(), $fieldName);
    if (!$field) {
      $field = static::getFieldFromCollection(static::getExpandedFields(), $fieldName);
    }
    return $field;
  }

  public static function getFieldNames() {
    $fields = static::getExpandedFields();
    $names = [];
    foreach ($fields as $field) {
      if ($field::dbType) {
        $names[] = $field->getName();
      }
    }
    return $names;
  }

  public static function getFieldsFromDefinition($fieldDef, $expand = false) {
    $fields = [];

    foreach ($fieldDef as $fieldName => $fieldType) {
      $field = static::getFieldFromType($fieldName, $fieldType);
      if ($expand && is_subclass_of($field, '\Defiant\Model\FieldSet')) {
        $fields = array_merge($fields, static::getFieldsFromDefinition($field->expandFields()));
      } else {
        $fields[] = $field;
      }
    }
    return $fields;
  }

  public static function getFieldFromType($fieldName, $fieldType, array $fieldDef = array()) {
    if (is_array($fieldType)) {
      return static::getFieldFromType($fieldName, $fieldType['type'], $fieldType);
    }
    return new $fieldType($fieldName, $fieldDef);
  }

  public static function hasField($fieldName) {
    $fields = static::getExpandedFields();
    foreach ($fields as $field) {
      if ($field->getName() === $fieldName) {
        return true;
      }
    }
    return false;
  }

  public function hasValue($fieldName) {
    return isset($this->data[$fieldName]);
  }

  public function getFieldValue($fieldName) {
    $field = static::getField($fieldName);

    if (!$field) {
      throw new Error(sprintf('Field "%s" does not exist on model "%s"', $fieldName, get_called_class()));
    }

    return $field->getValue($this, $this->hasValue($fieldName) ?
      $this->data[$fieldName] :
      null
    );
  }

  public function getFieldValueDirect($fieldName) {
    if (!static::hasField($fieldName)) {
      throw new Error(sprintf('Field "%s" does not exist on model "%s"', $fieldName, get_called_class()));
    }

    return $this->hasValue($fieldName) ?
      $this->data[$fieldName] :
      null;
  }

  public function setState(array $data) {
    foreach ($data as $fieldName => $value) {
      $this->setValue($fieldName, $value, true);
    }
    return $this;
  }

  public function setUnchanged() {
    $this->changed = false;
    return $this;
  }

  public function setValue($fieldName, $value, $noFormat = false) {
    $field = $this->getField($fieldName);
    if ($field) {
      if (!$this->hasValue($fieldName) || $this->data[$fieldName] !== $value) {
        if ($noFormat) {
          $this->data[$fieldName] = $value;
        } else {
          $this->data[$fieldName] = $field->formatValue($value);
        }
        $this->changed = true;
      }
    } else {
      throw new Error(sprintf('Model "%s" does not have field "%s"', get_called_class(), $fieldName));
    }
    return $this;
  }
}

require_once 'fields.php';
