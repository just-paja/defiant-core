<?php

namespace Defiant\Model;

class ModelNullFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new NullField('testField');
    $this->assertInstanceOf('Defiant\Model\NullField', $instance);
  }
}
