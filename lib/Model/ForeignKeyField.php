<?php

namespace Defiant\Model;

class ForeignKeyField extends FieldSet {
  public function expandFields() {
    $keyFieldName = $this->getKeyFieldName();
    return [
      $this->name => [
        "type" => "NullField",
        "bindTrough" => $this,
      ],
      $keyFieldName => "IntegerField",
    ];
  }

  public function getKeyFieldName() {
    return $this->name.'Id';
  }

  public function getValue($instance, $value) {
    $keyFieldName = $this->getKeyFieldName();
    $keyValue = $instance->$keyFieldName;
    $model = $this->model;

    if ($keyValue) {
      return $model::getConnector()->objects->find($keyValue);
    }
    return null;
  }

  public function hintDb() {
    if (strpos(strtolower($this->name), 'id') === strlen($this->name) - 2) {
      return 10;
    }
    return 0;
  }
}
