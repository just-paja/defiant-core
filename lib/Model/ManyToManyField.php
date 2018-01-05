<?php

namespace Defiant\Model;

class ManyToManyField extends FieldSet implements CustomSaveField {
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

  public function valueToIds($value) {
    $ids = [];
    foreach ($value as $item) {
      $ids[] = $item->id;
    }
    return $ids;
  }

  public function saveValue(\Defiant\Model $instance) {
    $name = $this->name;
    $value = $instance->$name;
    if (!is_array($value)) {
      return;
    }
    $dbValue = $this->valueToIds($this->getValue($instance, null)->all());
    $troughConnector = $this->trough::getConnector();
    $fkField = $this->trough::getField($this->fk)->getKeyFieldName();
    $viaField = $this->trough::getField($this->via)->getKeyFieldName();

    foreach ($value as $itemId) {
      $existingIndex = array_search($itemId, $dbValue);
      if ($existingIndex !== false) {
        unset($dbValue[$existingIndex]);
      } else {
        $troughConnector->create([
          $fkField => $instance->id,
          $viaField => $itemId,
        ])->save();
      }
    }
    foreach ($dbValue as $itemId) {
      $item = $troughConnector->objects->filter([
        $fkField => $instance->id,
        $viaField => $itemId,
      ])->first();
      $item->delete();
    }
  }

  public function resolveFilterColumnAndTable() {
    return [
      $this->trough::getTableName(),
      $this->trough::getField($this->via)->getKeyFieldName(),
    ];
  }

  public function getFkFieldName() {
    return $this->trough::getField($this->fk)->getKeyFieldName();
  }

  public function getViaFieldName() {
    return $this->trough::getField($this->via)->getKeyFieldName();
  }

  public function getTroughModel() {
    return $this->trough;
  }
}
