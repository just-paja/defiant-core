<?php

namespace Defiant\Model;

class Query {
  protected $mode = 'select';
  protected $database;
  protected $filter;
  protected $jumps;
  protected $limit;
  protected $model;
  protected $offset;

  public function __construct(\Defiant\Database $database, $model) {
    $this->filter = [];
    $this->jumps = [];
    $this->database = $database;
    $this->setModel($model);
  }

  public function setModel($model) {
    $this->model = $model;
  }

  public function all() {
    return $this->map($this->select()->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function count() {
    $this->mode = 'count';
    $data = $this->select()->fetch(\PDO::FETCH_ASSOC);
    return $data['count'];
  }

  public function filter(array $conds) {
    $this->filter = array_merge($this->filter, $conds);
    return $this;
  }

  public function find($id) {
    return $this
      ->filter([ "id" => $id ])
      ->first();
  }

  public function first() {
    $item = $this->limit(0, 1)->select()->fetch(\PDO::FETCH_ASSOC);
    if ($item) {
      return $this->extend($item);
    }
    return null;
  }

  public function limit($offset, $limit) {
    $this->offset = $offset;
    $this->limit = $limit;
    return $this;
  }

  protected function extend(array $data) {
    $lastJump = $this->getLastJump();
    if ($lastJump) {
      $item = new $lastJump['model']($data, $this->database);
    } else {
      $item = new $this->model($data, $this->database);
    }
    $item->setComesFromDb();
    return $item;
  }

  protected function map(array $data) {
    $items = [];
    foreach ($data as $item) {
      $items[] = $this->extend($item);
    }
    return $items;
  }

  protected function getLastJump() {
    $lastJumpIndex = sizeof($this->jumps) - 1;
    return $lastJumpIndex === -1 ? null : $this->jumps[$lastJumpIndex];
  }

  protected function mapColumnWithTableName($table, $columns) {
    $mapped = [];
    foreach ($columns as $column) {
      $mapped[] = '`'.$table.'`.'.'`'.$column.'`';
    }
    return $mapped;
  }

  protected function getJumpJoinCond($jump) {
    $srcTable = $this->model::getTableName();
    $cond = [];
    $cond[] = '`'.$srcTable.'`.`'.$jump['fk'].'`';
    $cond[] = '=';
    $cond[] = '`'.$jump['model']::getTableName().'`.`id`';
    return implode(' ', $cond);
  }

  protected function select() {
    $baseTableName = $this->model::getTableName();
    $jump = sizeof($this->jumps) > 0;
    if ($jump) {
      $lastJump = $this->getLastJump();
      $columns = $this->mapColumnWithTableName(
        $lastJump['model']::getTableName(),
        $lastJump['model']::getFieldNames()
      );
    } else {
      $columns = $this->mapColumnWithTableName(
        $baseTableName,
        $this->model::getFieldNames()
      );
    }

    $query = ['SELECT'];

    if ($this->mode === 'count') {
      $query[] = 'COUNT(*) as count';
    } else {
      $query[] = implode(', ', $columns);
    }

    $query[] = 'FROM';
    $query[] = $baseTableName;

    foreach ($this->jumps as $jump) {
      $query[] = 'JOIN';
      $query[] = $jump['model']::getTableName();
      $query[] = 'ON('.$this->getJumpJoinCond($jump).')';
    }

    $queryParams = [];
    $filterStatement = [];

    if ($this->filter) {
      foreach ($this->filter as $field => $value) {
        $filterStatement[] = "`$baseTableName`.`$field` = :$field";
        $queryParams[$field] = $value;
      }
    }

    if (sizeof($filterStatement) > 0) {
      $query[] = 'WHERE';
      $query[] = implode(' AND ', $filterStatement);
    }

    if ($this->offset || $this->limit) {
      $query[] = 'LIMIT';
      $query[] = "$this->offset, $this->limit";
    }

    return $this->database->query(implode(' ', $query), $queryParams);
  }

  public function jumpToModelViaForeignKey($model, $fk) {
    $this->jumps[] = [
      "model" => $model,
      "fk" => $fk,
    ];
    return $this;
  }
}
