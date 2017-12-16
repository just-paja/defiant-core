<?php

namespace Defiant\Model;

class Connector {
  protected $database;
  protected $fields;
  protected $model;

  public static function createFor($model, \Defiant\Database $database) {
    $connector = $model::getConnector();

    if (!$connector) {
      $connector = new static($model, $database);
      $model::setConnector($connector);
    }

    return $connector;
  }

  public function __construct($model, \Defiant\Database $database = null) {
    $this->database = $database;
    $this->model = $model;
    $this->fields = $model::getExpandedFields();
  }

  public function __get($attr) {
    if ($attr === 'objects') {
      return $this->getQuery();
    }
    return $this->$attr;
  }

  public function create($data) {
    $model = get_called_class();
    $item = new $this->model($data);
    return $item;
  }

  public function fetchAndUpdate($data) {
    $item = $this->getQuery()->find($data['id']);
    if ($item) {
      return $item->setState($data);
    }
    return $this->create($data);
  }

  public function getDatabase() {
    return $this->database;
  }

  public function getQuery() {
    return new Query(
      $this->database,
      $this->model
    );
  }
}
