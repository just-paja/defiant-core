<?php

namespace Defiant\Http;

class HttpRouteTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $db = new Route('get', '/home', array('HttpRouteTest', 'testConstruct'));
    $this->assertInstanceOf('Defiant\Http\Route', $db);
  }
}
