<?php

namespace Defiant\Http;

class HttpNotFoundTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $db = new NotFound();
    $this->assertInstanceOf('Defiant\Http\NotFound', $db);
    $this->assertInstanceOf('Defiant\Error', $db);
  }
}
