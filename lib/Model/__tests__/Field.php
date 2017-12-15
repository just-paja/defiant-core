<?php

namespace Defiant\Model;

class ModelFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Field('testField');
    $this->assertInstanceOf('Defiant\Model\Field', $instance);
  }
}
