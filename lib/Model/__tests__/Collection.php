<?php

namespace Defiant\Model;

class ModelCollectionTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $databases = $this->getMockBuilder('\Defiant\Database\Collection')
      ->disableOriginalConstructor()
      ->getMock();

    $instance = new Collection($databases);
    $this->assertInstanceOf('Defiant\Model\Collection', $instance);
  }
}
