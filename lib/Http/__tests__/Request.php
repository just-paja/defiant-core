<?php

namespace Defiant\Http;

class HttpRequestTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $db = new Request([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI' => '/home',
      'HTTP_HOST' => 'localhost',
    ]);
    $this->assertInstanceOf('Defiant\Http\Request', $db);
  }
}
