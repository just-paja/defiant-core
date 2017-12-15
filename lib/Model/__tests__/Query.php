<?php

namespace Defiant\Model;

class ModelQueryTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $database = $this->getMockBuilder('\Defiant\Database\Sqlite')
      ->disableOriginalConstructor()
      ->getMock();

    $instance = new Query($database, '\Defiant\Model');
    $this->assertInstanceOf('Defiant\Model\Query', $instance);
  }
}
