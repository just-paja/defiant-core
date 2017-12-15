<?php

namespace Defiant\Model;

class ModelDatetimeFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new DatetimeField('testField');
    $this->assertInstanceOf('Defiant\Model\DatetimeField', $instance);
  }
}
