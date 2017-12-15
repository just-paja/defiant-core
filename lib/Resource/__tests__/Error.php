<?php

namespace Defiant\Resource;

class ResourceErrorTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $instance = new Error('Some message', [
      'param' => 'paramValue',
    ]);
    $this->assertInstanceOf('Defiant\Resource\Error', $instance);
  }
}
