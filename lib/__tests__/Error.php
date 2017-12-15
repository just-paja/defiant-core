<?php

namespace Defiant;

class ErrorTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Error('Test Error Message');
    $this->assertInstanceOf('Defiant\Error', $instance);
  }
}
