<?php

namespace Defiant\Resource;

class ResourceCollectionTest extends \PHPUnit\Framework\TestCase {
  protected function setUp() {
  }

  public function testConstruct() {
    $collection = new Collection();
    $this->assertInstanceOf('Defiant\Resource\Collection', $collection);
  }

  protected function tearDown() {
  }
}
