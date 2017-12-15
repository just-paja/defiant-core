<?php

namespace Defiant\Database;

abstract class Table {
  protected $columns = [];
  protected $columnsAdd = [];
  protected $columnsChange = [];
  protected $columnsKeep = [];
  protected $columnsRemove = [];
  protected $connection;
  protected $db;
  protected $exists = null;
  protected $name;
  protected $onChange = null;


  public function __construct(Connection $connection, \Defiant\Database $db, $name, $onChange = null) {
    $this->connection = $connection;
    $this->db = $db;
    $this->name = $name;
    $this->onChange = $onChange;
  }

  abstract protected function loadColumns();

  abstract public function getSaveQuery();

  public static function exceptionContains($exception, $msg) {
    $message = strtolower($exception->getMessage());
    return strPos($exception->getMessage(), $msg) !== false;
  }

  public function exists($db_ident = null) {
    if ($this->exists === null) {
      $this->exists = $this->queryExists();
    }
    return $this->exists;
  }

  protected function queryExists() {
    $db_name = $this->connection->getDb();
    try {
      $result = $this->connection->query(
        "SELECT id from $this->name"
      );
    } catch(Error $exception) {
      if (static::exceptionContains($exception, 'such table')) {
        return false;
      } elseif (static::exceptionContains($exception, 'such column')) {
        return true;
      } else {
        throw $exception;
      }
    }
    return true;
  }

  public function loadModelFields(): void {
    $model = $this->name;
    $this->fields = $model::getFields();
  }

  public function findFieldByName($name) {
    foreach ($this->fields as $field) {
      if ($field->getName() === $name) {
        return $field;
      }
    }

    return null;
  }

  public function findColumnByName($name) {
    foreach ($this->columns as $column) {
      if ($column->getName() === $name) {
        return $column;
      }
    }

    return null;
  }

  public function getColumns() {
    $columns = [];
    foreach ($this->fields as $field) {
      if ($field::dbType) {
        $existingColumn = $this->findColumnByName($field->getName());
        $columns[] = new DatabaseColumn($this->connection, $this, $field, $existingColumn, $this->onChange);
      }
    }
    return $columns;
  }

  public function getColumnsUnused() {
    $columns = [];
    foreach ($this->columns as $column) {
      $existingColumn = $this->findFieldByName($column->getName());
      if (!$existingColumn) {
        $columns[] = $column;
      }
    }
    return $columns;
  }

  public function getDb(): string {
    return $this->db;
  }

  public function getName() {
    return $this->name;
  }

  protected function columnAdd(DatabaseColumn $col) {
    $this->columnsAdd[] = $col;
    $this->triggerChange('TABLE_COLUMN_ADD', $col->getIdent());
  }

  protected function columnChange(DatabaseColumn $col, $changes) {
    $this->columnsChange[] = $col;
    $this->triggerChange('TABLE_COLUMN_CHANGED', $col->getIdent(), $changes);
  }

  protected function columnKeep(DatabaseColumn $col) {
    $this->columnsKeep[] = $col;
  }

  protected function columnRemove(DatabaseColumn $col) {
    $this->columnsRemove[] = $col;
    $this->triggerChange('TABLE_COLUMN_REMOVED', $col->getIdent());
  }

  protected function readChanges() {
    $existing = $this->getColumns();
    $removed = $this->getColumnsUnused();

    foreach ($existing as $col) {
      if ($col->isChanged()) {
        if ($col->exists()) {
          $this->columnChange($col, $col->whatChanged());
        } else {
          $this->columnAdd($col);
        }
      } else {
        $this->columnKeep($col);
      }
    }

    foreach ($removed as $col) {
      $this->columnRemove($col);
    }
  }

  public function save() {
    $this->triggerChange('TABLE_SYNC_STARTED', $this->name);
    $this->readChanges();
    $query = $this->getSaveQuery();
    if ($query) {
      $this->connection->transaction($query);
    }
    $this->triggerChange('TABLE_SYNC_FINISHED', $this->name);
    return null;
  }

  public function synchronize() {
    if ($this->exists()) {
      $this->loadColumns();
    }
    $this->loadModelFields();
    $this->save();
  }

  public function triggerChange($changeType, $name, $params = null) {
    if ($this->onChange) {
      call_user_func($this->onChange, $changeType, $name, $params);
    }
  }
}
