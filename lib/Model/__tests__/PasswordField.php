<?php

namespace Defiant\Model;

class ModelPasswordFieldTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new PasswordField('testField');
    $this->assertInstanceOf('Defiant\Model\PasswordField', $instance);
  }
}
