<?php

namespace Defiant\Model;

class ForeignKeyField extends FieldSet implements CustomSaveField {
  public function expandFields() {
    $keyFieldName = $this->getKeyFieldName();
    return [
      $this->name => [
        'type' => '\Defiant\Model\NullField',
        "bindTrough" => $this,
      ],
      $keyFieldName => [
        'type' => '\Defiant\Model\IntegerField',
        'default' => $this->default,
        'isNull' => $this->isNull,
      ],
    ];
  }

  public function getKeyFieldName() {
    return $this->name.'Id';
  }

  public function getValue($instance, $value) {
    if ($value instanceof $this->model) {
      return $value;
    }

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

  public function resolveFilterColumnAndTable() {
    return [
      null,
      $this->getKeyFieldName(),
    ];
  }

  public function saveValue(\Defiant\Model $instance) {
    $name = $this->name;
    $value = null;
    $keyFieldName = $this->getKeyFieldName();

    if ($instance->hasValue($name)) {
      $value = $instance->$name;
    } elseif ($instance->hasValue($keyFieldName)) {
      $value = $instance->$keyFieldName;
    } else {
      return;
    }

    if (!($value instanceof $this->model)) {
      return;
    }

    $instance->$keyFieldName = $value->id;
    unset($instance->$name);
    $instance->save();
  }

  public function validateValue($value) {
    if ($value) {
      $model = $this->model;
      $connector = $model::getConnector();
      $object = $connector->objects->find($value);

      if (!$object) {
        throw new \Defiant\View\ValidationError(sprintf(
          '%s of id "%s" does not exist.',
          $model,
          $value
        ), $this->name);
      }
    }
    return true;
  }
}
