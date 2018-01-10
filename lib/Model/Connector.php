<?php

namespace Defiant\Model;

class Connector {
  protected $database;
  protected $fields;
  protected $model;
  protected $runner;

  public static function createFor($model, \Defiant\Database $database, \Defiant\Runner $runner) {
    try {
     return $model::getConnector();
   } catch(\Defiant\Model\ConnectorError $e) {
      $connector = new static($model, $database, $runner);
      $model::setConnector($connector);
      return $connector;
    }
  }

  public function __construct($model, \Defiant\Database $database = null, \Defiant\Runner $runner = null) {
    $this->database = $database;
    $this->model = $model;
    $this->fields = $model::getExpandedFields();
    $this->runner = $runner;
  }

  public function __get($attr) {
    if ($attr === 'objects') {
      return $this->getQuery();
    }
    return $this->$attr;
  }

  public function create(array $data = []) {
    $model = get_called_class();
    $item = new $this->model($data);
    return $item;
  }

  public function fetchAndUpdate($data) {
    $item = $this->getQuery()->find($data['id']);
    if (!$item) {
      $item = $this->create();
    }
    return $item->setState($data, false);
  }

  public function getModel() {
    return $this->model;
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

  public function getRunner() {
    return $this->runner;
  }
}
