<?php

namespace Defiant\Model;

class ModelEmailFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new EmailField('testField');
    $this->assertInstanceOf('Defiant\Model\EmailField', $instance);
  }
}
