<?php

namespace Defiant\Database;

class DatabaseSqliteTableTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $connection = $this->getMockBuilder('\Defiant\Database\Connection')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $sqlite = $this->getMockBuilder('\Defiant\Database\Sqlite')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $instance = new SqliteTable($connection, $sqlite, 'testTable');
    $this->assertInstanceOf('Defiant\Database\SqliteTable', $instance);
  }
}
