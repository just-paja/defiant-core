<?php

namespace Defiant\Resource;

class ResourceChangeTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Change(5, 42);
    $this->assertInstanceOf('Defiant\Resource\Change', $instance);
  }
}
