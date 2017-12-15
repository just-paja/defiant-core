<?php

namespace Defiant;

class MockModel extends Model {
}

class ModelTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new MockModel();
    $this->assertInstanceOf('Defiant\MockModel', $instance);
  }
}
