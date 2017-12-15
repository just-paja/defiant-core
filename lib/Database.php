<?php

namespace Defiant;

abstract class Database {
  protected $ident;
  protected $cfg;
  protected $connection;
  protected $onChange;

  public static function fromConfig($config, callable $onChange = null) {
    $connection = new Database\Connection(
      isset($config['dsn']) ? $config['dsn'] : null,
      isset($config['user']) ? $config['user'] : null,
      isset($config['password']) ? $config['password'] : null
    );
    return Database::fromConnection($connection, 'dbname', $onChange);
  }

  public static function fromConnection(Database\Connection $connection, $name, callable $onChange = null) {
    $dbms = $connection->getDbms();
    if ($dbms === 'sqlite') {
      return new Database\Sqlite($connection, $name, $onChange);
    }
  }

  public function __construct(Database\Connection $connection, $name, callable $onChange = null) {
    $this->connection = $connection;
    $this->name = $name;
    $this->onChange = $onChange;
  }

  abstract public function createTable($model);

  public function connect() {
    $this->connection->connect();
  }

  public function ensureExistence() {
    if ($this->connection->getDbms() != 'sqlite') {
      $this->connection->query('CREATE DATABASE IF NOT EXISTS '.$this->name.';');
    }
  }

  public function synchronize() {
    $this->ensureExistence();
    $tables = $this->getSchemaTables();
    foreach ($tables as $table) {
      $table->synchronize();
    }
  }

  public function getSchemaTables() {
    $models = Model::getAllModels();
    $tables = [];
    foreach ($models as $model) {
      $tables[] = $this->createTable($model);
    }
    return $tables;
  }

  public function query($query, array $params = array()) {
    return $this->connection->query($query, $params);
  }

  public function exec($query, array $params = array()) {
    return $this->connection->exec($query, $params);
  }

  public function insert($table, $data) {
    $keys = [];
    $placeholders = [];
    $queryParams = [];

    foreach ($data as $key=>$value) {
      $placeholder = ':'.$key;
      $keys[] = $key;
      $placeholders[] = $placeholder;
      $queryParams[$placeholder] = $value;
    }

    $query = [
      'INSERT INTO',
      $table,
      '('.join(', ', $keys).')',
      'VALUES',
      '('.join(', ', $placeholders).')',
    ];

    $this->query(join(' ', $query), $queryParams);
    return $this->connection->getLastInsertId();
  }

  public function update($table, $id, $data) {
    $placeholders = [];
    $queryParams = [
      "id" => $id,
    ];

    foreach ($data as $key=>$value) {
      $placeholder = ':'.$key;
      $placeholders[] = "$key = $placeholder";
      $queryParams[$placeholder] = $value;
    }

    $query = join(' ', [
      'UPDATE',
      $table,
      'SET',
      join(', ', $placeholders),
      'WHERE `id` = :id'
    ]);

    $sth = $this->query($query, $queryParams);
    $rowCount = $sth->rowCount();

    if ($rowCount === 0) {
      throw new Database\Error(null, 'Updated 0 rows', $query, $queryParams);
    }
    return $sth;
  }
}
