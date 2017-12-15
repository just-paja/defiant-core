<?php

namespace Defiant\Database;

class Connection {
  protected $dbms;
  protected $host;
  protected $db;
  protected $user;
  protected $password;

  public function __construct(
    $dsn,
    $user = null,
    $password = null
  ) {
    $this->dbms = substr($dsn, 0, strpos($dsn, ':'));
    $this->dsn = $dsn;
    $this->user = $user;
    $this->password = $password;
  }

  public function connect() {
    $this->pdo = new \PDO($this->dsn, $this->user, $this->password);
    $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }

  public function query($query, array $params = array()) {
    try {
      $sth = $this->pdo->prepare($query);
    } catch(\PDOException $exception) {
      throw new Error($exception->getCode(), $exception->getMessage(), $query, $params);
    }

    if (!$sth) {
      $info = $this->pdo->errorInfo();
      throw new Error($info[0], $info[2], $query, $params);
    }

    try {
      $result = $sth->execute($params);
    } catch(\PDOException $exception) {
      throw new Error($exception->getCode(), $exception->getMessage(), $query, $params);
    }

    if (!$result) {
      $info = $this->pdo->errorInfo();
      throw new Error($info[0], $info[2], $query, $params);
    }

    return $sth;
  }

  public function exec($query) {
    try {
      $result = $this->pdo->exec($query);
    } catch(\PDOException $exception) {
      throw new Error($exception->getCode(), $exception->getMessage(), $query);
    }

    return $result;
  }

  public function transaction($queries) {
    $results = [];
    $this->pdo->beginTransaction();
    foreach ($queries as $q) {
      try {
        $results[] = $this->exec($q);
      } catch(Error $exception) {
        $this->pdo->rollBack();
        throw $exception;
      }
    }
    $this->pdo->commit();
    return $results;
  }

  public function getDb() {
    return $this->db;
  }

  public function getDbms() {
    return $this->dbms;
  }

  public function getLastInsertId() {
    return $this->pdo->lastInsertId();
  }
}
