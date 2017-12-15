<?php

namespace Defiant\Model;

class ModelVarcharFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new VarcharField('testField');
    $this->assertInstanceOf('Defiant\Model\VarcharField', $instance);
  }
}
