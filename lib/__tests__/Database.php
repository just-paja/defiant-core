<?php

namespace Defiant;

class MockDatabase extends Database {
  public function createTable($model) {
    return true;
  }
}

class DatabaseTest extends \PHPUnit\Framework\TestCase {
  public function testConstruct() {
    $connection = new Database\Connection('sqlite::memory:');
    $db = new MockDatabase($connection, 'testDatabase');
    $this->assertInstanceOf('Defiant\MockDatabase', $db);
  }
}
