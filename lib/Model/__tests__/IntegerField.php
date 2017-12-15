<?php

namespace Defiant\Model;

class ModelIntegerFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new IntegerField('testField');
    $this->assertInstanceOf('Defiant\Model\IntegerField', $instance);
  }
}
