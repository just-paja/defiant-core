<?php

namespace Defiant\Model;

class DatetimeField extends Field {
  const dbType = 'DATETIME';
  protected $setNowOnInsert = false;
  protected $setNowOnUpdate = false;

  public function serialize($value, $opportunity = null) {
    if ($this->setNowOnInsert && $opportunity === MODEL_INSERT) {
      return (new \DateTime())->format('c');
    }
    if (!$value && $this->setNowOnUpdate && ($opportunity === MODEL_UPDATE || $opportunity === MODEL_INSERT)) {
      return (new \DateTime())->format('c');
    }
    return $value;
  }
}
