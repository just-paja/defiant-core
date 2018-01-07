<?php

namespace Defiant\View;

class GenericForm extends \Defiant\View\Form {
  protected $model;
  protected $fieldNames;

  public static function fromModel($model, Array $fieldNames = []) {
    return new self($model, $fieldNames);
  }

  public function __construct($model, Array $fieldNames = []) {
    $this->model = $model;
    $this->fieldNames = $fieldNames;
  }

  public function getInstanceFields() {
    $fields = $this->model::getDefinedFields();
    $filtered = [];

    foreach ($fields as $field) {
      if (in_array($field->getName(), $this->fieldNames)) {
        $filtered[] = $field;
      }
    }

    return $filtered;
  }
}
