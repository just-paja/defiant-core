<?php

namespace Defiant\Database;

class DatabaseSqliteTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $connection = $this->getMockBuilder('\Defiant\Database\Connection')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $instance = new Sqlite($connection, 'testDatabase');
    $this->assertInstanceOf('Defiant\Database\Sqlite', $instance);
  }
}
