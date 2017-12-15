<?php

namespace Defiant;

class ViewTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $db = new View();
    $this->assertInstanceOf('Defiant\View', $db);
  }
}
