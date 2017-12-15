<?php

namespace Defiant\Model;

class ModelErrorTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Error('message');
    $this->assertInstanceOf('Defiant\Model\Error', $instance);
    $this->assertInstanceOf('Defiant\Error', $instance);
  }
}
