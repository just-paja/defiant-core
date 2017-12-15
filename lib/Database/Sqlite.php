<?php

namespace Defiant\Database;

class Sqlite extends \Defiant\Database {
  public function createTable($model) {
    return new SqliteDatabaseTable(
      $this->connection,
      $this,
      $model,
      $this->onChange
    );
  }
}
