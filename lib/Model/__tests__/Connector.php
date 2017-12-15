<?php

namespace Defiant\Model;

class ModelConnectorTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $database = $this->getMockBuilder('\Defiant\Database\Sqlite')
      ->disableOriginalConstructor()
      ->getMock();

    $instance = new Connector('\Defiant\Model', $database);
    $this->assertInstanceOf('Defiant\Model\Connector', $instance);
  }
}
