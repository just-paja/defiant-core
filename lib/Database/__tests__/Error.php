<?php

namespace Defiant\Database;

class DatabaseErrorTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Error('X5654', 'Test Error Message', 'SELECT * FROM ALL');
    $this->assertInstanceOf('Defiant\Database\Error', $instance);
    $this->assertInstanceOf('Defiant\Error', $instance);
  }
}
