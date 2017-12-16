<?php

namespace Defiant\Model;

class ManyToManyField extends FieldSet {
  protected $trough;

  public function expandFields() {
    return [
      $this->name => [
        "type" => "\Defiant\Model\NullField",
        "bindTrough" => $this,
      ],
    ];
  }

  public function getValue($instance, $value) {
    $troughConnector = $this->trough::getConnector();
    $fkField = $this->trough::getField($this->fk);
    $viaField = $this->trough::getField($this->via);
    return $troughConnector->objects
      ->filter([ $fkField->getKeyFieldName() => $instance->id ])
      ->jumpToModelViaForeignKey($this->model, $viaField->getKeyFieldName());
  }
}
