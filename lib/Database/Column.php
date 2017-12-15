<?php

namespace Defiant\Database;

class Column {
  protected $connection;
  protected $default;
  protected $drop = false;
  protected $exists = null;
  protected $field;
  protected $isAutoincrement = false;
  protected $isIndex = false;
  protected $isNull = false;
  protected $isPrimary = false;
  protected $isUnique = false;
  protected $isUnsigned = false;
  protected $length = null;
  protected $name;
  protected $onChange;
  protected $previous;
  protected $renamed = false;
  protected $table;
  protected $type;

  public function __construct(
    Connection $connection,
    \Defiant\Database\Table $table,
    \Defiant\Model\Field $field,
    Column $previous = null,
    Callable $onChange = null
  ) {
    $this->connection = $connection;
    $this->previous = $previous;
    $this->field = $field;
    $this->name  = $field->getName();
    $this->table = $table;
    $this->onChange = $onChange;

    $this->isAutoincrement = $this->field->isAutoincrement();
    $this->isNull = $this->field->isNull();
    $this->isPrimary = $this->field->isPrimary();
    $this->isUnsigned = $this->field::isUnsigned;
    $this->length = $this->field->getLength();
    $this->type = $this->field::dbType;
  }

  public function equals(Column $column) {
    return sizeof($this->whatChanged($column)) > 0;
  }

  public function exists() {
    return !!$this->previous;
  }

  public function getDefault() {
    return $this->default;
  }

  public function getChanges(Column $column = null) {
    $changes = [];
    if (!$column || $this->getLength() !== $column->getLength()) {
      $changes['length'] = new Change($column ? $column->getLength() : null, $this->getLength());
    }
    if (!$column || $this->getName() !== $column->getName()) {
      $changes['name'] = new Change($column ? $column->getName() : null, $this->getName());
    }
    if (!$column || $this->getType() !== $column->getType()) {
      $changes['type'] = new Change($column ? $column->getType() : null, $this->getType());
    }
    if (!$column || $this->isAutoincrement() !== $column->isAutoincrement()) {
      $changes['isAutoincrement'] = new Change($column ? $column->isAutoincrement() : null, $this->isAutoincrement());
    }
    if (!$column || $this->isNull() !== $column->isNull()) {
      $changes['isNull'] = new Change($column ? $column->isNull() : null, $this->isNull());
    }
    if (!$column || $this->isPrimary() !== $column->isPrimary()) {
      $changes['isPrimary'] = new Change($column ? $column->isPrimary() : null, $this->isPrimary());
    }
    if (!$column || $this->isUnsigned() !== $column->isUnsigned()) {
      $changes['isUnsigned'] = new Change($column ? $column->isUnsigned() : null, $this->isUnsigned());
    }
    return $changes;
  }

  public function getField() {
    return $this->field;
  }

  public function getIdent() {
    return $this->table->getName() . '.' . $this->name;
  }

  public function getName() {
    return $this->name;
  }

  public function getLength() {
    return $this->length;
  }

  public function getPrevious() {
    return $this->previous;
  }

  public function getType() {
    return $this->type;
  }

  public function getSaveQuery() {
    if (!$this->isChanged()) {
      return null;
    }
    return $this->getSaveQuerySqlite();
  }

  public function getSaveQuerySqlite() {
    if ($this->exists()) {
      $front = 'CHANGE `'.$this->previous->getName().'` `'.$this->name.'`';
    } elseif ($this->table->exists()) {
      $front = 'ADD `'.$this->name.'`';
    } else {
      $front = '`'.$this->name.'`';
    }

    $sq = implode(' ', array_filter(array(
      $front,
      $this->type . ($this->length !== null ? '('.$this->length.')' : ''),
      !empty($this->isUnsigned()) ? 'UNSIGNED' : '',
      $this->isNull() ? 'NULL' : 'NOT NULL',
      $this->isPrimary() && (!$this->previous || !$this->previous->isPrimary()) ? 'PRIMARY KEY':'',
      $this->isUnique() && !$this->isPrimary() ? 'UNIQUE':'',
      $this->isAutoincrement() ? 'AUTOINCREMENT' : '',
      $this->default ? 'DEFAULT '.$this->default : '',
    )));

    return trim($sq);
  }

  public function getSaveQueryMysql() {
    if ($this->exists()) {
      $front = 'CHANGE `'.$this->previous->getName().'` `'.$this->name.'`';
    } elseif ($this->table->exists()) {
      $front = 'ADD `'.$this->name.'`';
    } else {
      $front = '`'.$this->name.'`';
    }

    $sq = implode(' ', array_filter(array(
      $front,
      $this->type . ($this->length !== null ? '('.$this->length.')' : ''),
      !empty($this->isUnsigned()) ? 'UNSIGNED' : '',
      $this->isNull() ? 'NULL' : 'NOT NULL',
      $this->default ? 'DEFAULT '.$this->default : '',
      $this->isAutoincrement() ? 'AUTO_INCREMENT' : '',
      $this->isUnique() && !$this->isPrimary() ? 'UNIQUE':'',
      $this->isPrimary() && (!$this->previous || !$this->previous->isPrimary()) ? 'PRIMARY KEY':'',
    )));

    return trim($sq);
  }

  public function isAutoincrement() {
    return $this->isAutoincrement;
  }

  public function isChanged() {
    return !$this->exists() || sizeof($this->whatChanged()) > 0;
  }

  public function isNull() {
    return !!$this->isNull;
  }

  public function isPrimary() {
    return !!$this->isPrimary;
  }

  public function isUnique() {
    return !!$this->isUnique;
  }

  public function isUnsigned() {
    return !!$this->isUnsigned;
  }

  public function triggerChange($changeType, $name, $onChange = null) {
    if ($this->onChange) {
      call_user_func($this->onChange, $changeType, $name, $onChange);
    }
  }

  public function whatChanged() {
    return $this->getChanges($this->previous);
  }
}
