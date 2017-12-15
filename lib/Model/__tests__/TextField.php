<?php

namespace Defiant\Model;

class ModelTextFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new TextField('testField');
    $this->assertInstanceOf('Defiant\Model\TextField', $instance);
  }
}
