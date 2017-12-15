<?php

namespace Defiant\Model;

class Query {
  protected $database;
  protected $model;
  protected $filter;
  protected $offset;
  protected $limit;

  public function __construct(\Defiant\Database $database, $model) {
    $this->filter = [];
    $this->database = $database;
    $this->setModel($model);
  }

  public function setModel($model) {
    $this->model = $model;
  }

  public function all() {
    return $this->map($this->select()->fetchAll());
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
    $item = $this->limit(0, 1)->select()->fetch(PDO::FETCH_ASSOC);
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
    $item = new $this->model($data, $this->database);
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

  protected function select() {
    $columns = $this->model::getFieldNames();
    $query = [
      'SELECT',
      implode(', ', $columns),
      'FROM',
      $this->model::getTableName(),
    ];
    $queryParams = [];

    if ($this->filter) {
      $filterStatement = [];

      foreach ($this->filter as $field => $value) {
        $filterStatement[] = "`$field` = :$field";
        $queryParams[$field] = $value;
      }
    }

    if (sizeof($filterStatement) > 0) {
      $query[] = 'WHERE';
      $query[] = implode(' AND ', $filterStatement);
    }

    return $this->database->query(implode(' ', $query), $queryParams);
  }
}
