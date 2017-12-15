<?php

namespace Defiant\Http;

class HttpRouterTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $db = new Router();
    $this->assertInstanceOf('Defiant\Http\Router', $db);
  }
}
