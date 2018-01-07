<?php

namespace Defiant\Model;

class OneToManyField extends FieldSet {
  protected $model;
  protected $fk;
  protected $isNull = true;

  public function expandFields() {
    return [
      $this->name => [
        "type" => "\Defiant\Model\NullField",
        "bindTrough" => $this,
      ],
    ];
  }

  public function getValue($instance, $value) {
    $fkTableKey = $this->model::getField($this->fk)->getKeyFieldName();
    return $this->model::getConnector()->objects
      ->filter([ $fkTableKey => $instance->id ]);
  }

  public function serialize($value, $opportunity = null) {
    $collection = [];
    $data = $value->all();
    foreach ($data as $item) {
      $collection[] = $item->toDataObject(true);
    }
    return $collection;
  }
}
