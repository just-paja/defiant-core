<?php

namespace Defiant\Database;

class DatabaseConnectionTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Connection('sqlite::memory:');
    $this->assertInstanceOf('Defiant\Database\Connection', $instance);
  }
}
