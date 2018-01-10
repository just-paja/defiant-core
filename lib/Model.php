<?php

namespace Defiant;

abstract class Model extends \Defiant\Model\ModelAccessor {
  protected static $connectors = [];
  protected static $databaseName = null;
  protected $comesFromDb;


  public static function getConnector() {
    $class = get_called_class();
    if (isset(static::$connectors[$class])) {
      return static::$connectors[$class];
    }
    throw new \Defiant\Model\ConnectorError(sprintf('Undefined connector for model %s', $class));
  }

  public static function getDatabaseName() {
    return static::$databaseName;
  }

  public static function getTableName() {
    return str_replace(['\\', 'Model'], '', lcfirst(get_called_class()));
  }

  public static function setConnector($connector) {
    return static::$connectors[get_called_class()] = $connector;
  }

  public function setComesFromDb() {
    $this->comesFromDb = true;
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
        $this->saveRelations();
      }
    } else {
      $this->id = $database->insert($tableName, $data);
      $this->saveRelations();
    }
    return $this;
  }

  public function delete() {
    $isStored = $this->isStored();
    $tableName = $this::getTableName();
    $database = $this::getConnector()->getDatabase();

    if ($isStored) {
        $database->delete($tableName, $this->id);
    }
    return $this;
  }

  public function getCustomSaveFields() {
    $fields = $this->getFields();
    $customSave = [];
    foreach ($fields as $field) {
      if ($field instanceof \Defiant\Model\CustomSaveField) {
        $customSave[] = $field;
      }
    }
    return $customSave;
  }

  public function saveRelations() {
    $fields = $this->getCustomSaveFields();
    foreach ($fields as $field) {
      $field->saveValue($this);
    }
  }

  public function toDbObject($opportunity = null) {
    $array = [];
    $fields = $this::getExpandedFields();
    foreach ($fields as $field) {
      if (!$field::dbType) {
        continue;
      }
      $fieldName = $field->getName();
      $value = $field->serialize($this->getFieldValue($fieldName), $opportunity);
      if (isset($value)) {
        $array[$fieldName] = $value;
      }
    }
    return $array;
  }

  public function serializeFields($fieldNames) {
    $values = [];
    foreach ($fieldNames as $fieldName) {
      $field = $this->getField($fieldName);
      $values[$fieldName] = $field->serialize($this->$fieldName);
    }
    return $values;
  }
}
