<?php

namespace Defiant;

class RunnerTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Runner([]);
    $this->assertInstanceOf('Defiant\Runner', $instance);
  }
}
