<?php

namespace Defiant\Model;

class Collection extends \Defiant\Resource\Collection {
  protected $databases;

  public function __construct(
    \Defiant\Database\Collection $databases,
    $resources = null
  ) {
    $this->databases = $databases;
    parent::__construct($resources);
  }

  public function __get($model) {
    try {
      return parent::__get($model);
    } catch(\Defiant\Resource\Error $exception) {
      var_dump(array_keys($this->resources));
      throw new Error(sprintf('Model %s does not exist', $model));
    }
  }

  public function replace($resources) {
    foreach ($resources as $model) {
      $databaseName = $model::getDatabaseName();
      $this->resources[$model] = Connector::createFor(
        $model,
        $this->databases->$databaseName
      );
    }
  }
}