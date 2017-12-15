<?php

namespace Defiant\Database;

class Sqlite extends \Defiant\Database {
  public function createTable($model) {
    return new SqliteTable(
      $this->connection,
      $this,
      $model,
      $this->onChange
    );
  }
}
