<?php

namespace Defiant\Database;

class Collection extends \Defiant\Resource\Collection {
  protected $onChange = null;

  public function __get($databaseName = null) {
    if (!$databaseName) {
      $database = $this->getDefault();
      if ($database) {
        return $database;
      }
    }
    return parent::__get($databaseName);
  }

  public function replace($resources) {
    foreach ($resources as $name=>$config) {
      $db = \Defiant\Database::fromConfig($config, $this->onChange);
      $db->connect();
      $this->resources[$name] = $db;
    }
  }

  public function getDefault() {
    if (sizeof($this->resources) === 1) {
      return reset($this->resources);
    }
    return null;
  }

  public function setOnChange(callable $onChange = null) {
    $this->onChange = $onChange;
  }
}
