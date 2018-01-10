<?php

namespace Defiant\Model;

class Collection extends \Defiant\Resource\Collection {
  const SYSTEM_MODELS = [
    '\Defiant\Model\CsrfToken',
  ];

  protected $databases;
  protected $runner;

  public function __construct(
    \Defiant\Database\Collection $databases,
    \Defiant\Runner $runner,
    $resources = null
  ) {
    $this->databases = $databases;
    $this->runner = $runner;
    parent::__construct($resources);
  }

  public function __get($model) {
    try {
      return parent::__get($model);
    } catch(\Defiant\Resource\Error $exception) {

      throw new Error(sprintf('Model %s does not exist', $model));
    }
  }

  public function getByClassName($className) {
    foreach ($this->resources as $resource) {
      if ($resource->getModel() === $className) {
        return $resource;
      }
    }
    return null;
  }

  public function replace($resources) {
    foreach ($resources as $model) {
      $databaseName = $model::getDatabaseName();
      $this->resources[$model] = Connector::createFor(
        $model,
        $this->databases->$databaseName,
        $this->runner
      );
    }
  }
}
