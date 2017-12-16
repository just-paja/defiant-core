<?php

namespace Defiant\View;

abstract class Resource extends \Defiant\View\Api {
  public function __construct(\Defiant\Runner $runner = null, \Defiant\Http\Request $request = null) {
    parent::__construct($runner, $request);
    $this->addLink('self', $this->path);
  }

  abstract public function getResource();

  public function view() {
    $resource = $this->getResource();
    if ($resource) {
      $data = $resource->first();
    } else {
      $data = null;
    }
    if (!$data) {
      throw new \Defiant\Http\NotFound();
    }
    return $this->render($this->serializeObject($data));
  }

  public function isAccessible() {
    return false;
  }

  protected function serializeObject(\Defiant\Model $object) {
    $array = [];
    $fields = $object->getFields();
    foreach ($fields as $field) {
      $fieldName = $field->getName();
      if (is_a($field, '\Defiant\Model\ForeignKeyField')) {
        $array[$fieldName] = $this->serializeObject(
          $field->serialize($object->$fieldName)
        );
      } else if (is_a($field, '\Defiant\Model\FieldSet')) {
        $this->linkResource($fieldName);
      } else {
        $array[$fieldName] = $field->serialize($object->$fieldName);
      }
    }
    return $array;
  }
}
