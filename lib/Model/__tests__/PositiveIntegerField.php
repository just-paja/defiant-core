<?php

namespace Defiant\Model;

class ModelPositiveIntegerFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new PositiveIntegerField('testField');
    $this->assertInstanceOf('Defiant\Model\PositiveIntegerField', $instance);
  }
}
