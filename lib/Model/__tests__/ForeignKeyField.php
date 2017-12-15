<?php

namespace Defiant\Model;

class ModelForeignKeyFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new ForeignKeyField('testField');
    $this->assertInstanceOf('Defiant\Model\ForeignKeyField', $instance);
  }
}
