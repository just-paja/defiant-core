<?php

namespace Defiant\Database;

class DatabaseCollectionTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Collection([]);
    $this->assertInstanceOf('Defiant\Database\Collection', $instance);
  }
}
