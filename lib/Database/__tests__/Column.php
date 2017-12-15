<?php

namespace Defiant\Database;

class DatabaseColumnTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $connection = $this->getMockBuilder('\Defiant\Database\Connection')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $table = $this->getMockBuilder('\Defiant\Database\Table')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $field = $this->getMockBuilder('\Defiant\Model\Field')
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->getMock();

    $instance = new Column($connection, $table, $field);
    $this->assertInstanceOf('Defiant\Database\Column', $instance);
  }
}
