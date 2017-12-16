<?php

namespace Defiant;

define('MODEL_INSERT', 'MODEL_INSERT');
define('MODEL_UPDATE', 'MODEL_UPDATE');

abstract class Model {
  protected static $connectors = [];
  protected static $databaseName = null;
  protected static $fields = [];
  protected static $fieldTypes;
  protected $id;
  protected $comesFromDb;
  protected $changed = false;

  public function __construct(array $data = []) {
    if ($data) {
      $this->setState($data);
    }
    $this->changed = false;
  }

  public function __get($fieldName) {
    return $this->getFieldValue($fieldName);
  }

  public function __set($fieldName, $value) {
    return $this->setValue($fieldName, $value);
  }

  public static function getAllModels() {
    $classes = get_declared_classes();
    $models = [];

    foreach ($classes as $class) {
      if (is_subclass_of($class, get_class())) {
        $models[] = $class;
      }
    }

    return $models;
  }

  public static function getConnector() {
    $class = get_called_class();
    return isset(static::$connectors[$class]) ? static::$connectors[$class] : null;
  }

  public static function getDatabaseName() {
    return static::$databaseName;
  }

  public static function getFields() {
    return static::getFieldsFromDefinition(static::$fields, false);
  }

  public static function getExpandedFields() {
    $fields = array_merge([
      new Model\IntegerField('id', [
        "isPrimary" => true,
        "isAutoincrement" => true,
      ]),
    ], static::getFieldsFromDefinition(static::$fields, true));

    $fields[] = new Model\DatetimeField('createdAt', [
      "setNowOnInsert" => true,
      "isNull" => true,
    ]);
    $fields[] = new Model\DatetimeField('updatedAt', [
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

  public static function getTableName() {
    return str_replace(['\\', 'Model'], '', lcfirst(get_called_class()));
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

  public static function setConnector($connector) {
    return static::$connectors[get_called_class()] = $connector;
  }

  public function getFieldValue($fieldName) {
    $field = static::getField($fieldName);

    if (!$field) {
      throw new Error(sprintf('Field "%s" does not exist on model "%s"', $fieldName, get_called_class()));
    }

    return $field->getValue($this, isset($this->$fieldName) ?
      $this->$fieldName :
      null
    );
  }

  public function setComesFromDb() {
    $this->comesFromDb = true;
    return $this;
  }

  public function setState(array $data) {
    foreach ($data as $field => $value) {
      $this->setValue($field, $value);
    }
    return $this;
  }

  public function setUnchanged() {
    $this->changed = false;
    return $this;
  }

  public function setValue($field, $value) {
    if ($this->hasField($field)) {
      if ($this->$field !== $value) {
        $this->$field = $value;
        $this->changed = true;
      }
    } else {
      throw new Error(sprintf('Model "%s" does not have field "%s"', get_called_class(), $field));
    }
    return $this;
  }

  public function getDatabase() {
    return $this::getConnector()->getDatabase();
  }

  public function isStored() {
    return $this->comesFromDb;
  }

  public function save() {
    $isStored = $this->isStored();
    $data = $this->toDbObject($isStored ? MODEL_UPDATE : MODEL_INSERT);
    $tableName = $this::getTableName();
    $database = $this::getConnector()->getDatabase();

    if ($isStored) {
      if ($this->changed) {
        $database->update($tableName, $this->id, $data);
      } else {
        var_dump('not changed');
      }
    } else {
      $this->id = $database->insert($tableName, $data);
    }
    return $this;
  }

  public function toDbObject($opportunity = null) {
    $array = [];
    $fields = $this::getExpandedFields();
    foreach ($fields as $field) {
      if (!$field::dbType) {
        continue;
      }
      $fieldName = $field->getName();
      $value = $field->serialize($this->$fieldName, $opportunity);
      if (isset($value)) {
        $array[$fieldName] = $value;
      }
    }
    return $array;
  }
}
