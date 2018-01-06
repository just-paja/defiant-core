<?php

namespace Defiant\Model;

class Query {
  const OPERATION_IN = 'in';
  const OPERATION_CONTAINS = 'contains';

  protected $distinct = false;
  protected $mode = 'select';
  protected $database;
  protected $filter;
  protected $joins;
  protected $jumps;
  protected $limit;
  protected $model;
  protected $offset;
  protected $orderBy;
  protected $aliasCount = 0;

  public function __construct(\Defiant\Database $database, $model) {
    $this->filter = [];
    $this->joins = [];
    $this->jumps = [];
    $this->database = $database;
    $this->setModel($model);
  }

  public function setModel($model) {
    $this->model = $model;
  }

  public function all() {
    return $this->map($this->select()->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function count() {
    $this->mode = 'count';
    $data = $this->select()->fetch(\PDO::FETCH_ASSOC);
    return $data['count'];
  }

  public function distinct($really = true) {
    $this->distinct = $really;
    return $this;
  }

  public function filter(array $conds) {
    $this->filter = array_merge($this->filter, $conds);
    return $this;
  }

  public function find($id) {
    return $this
      ->filter([ "id" => $id ])
      ->first();
  }

  public function first() {
    $item = $this->limit(0, 1)->select()->fetch(\PDO::FETCH_ASSOC);
    if ($item) {
      return $this->extend($item);
    }
    return null;
  }

  public function limit($offset, $limit) {
    $this->offset = $offset;
    $this->limit = $limit;
    return $this;
  }

  public function limitByPage($page, $size = 20) {
    return $this->limit($size * $page, $size * $page + $size);
  }

  protected function extend(array $data) {
    $lastJump = $this->getLastJump();
    if ($lastJump) {
      $item = new $lastJump['model']($data, $this->database);
    } else {
      $item = new $this->model($data, $this->database);
    }
    $item->setComesFromDb();
    return $item;
  }

  public function orderBy($orderBy) {
    $this->orderBy = $orderBy;
    return $this;
  }

  protected function map(array $data) {
    $items = [];
    foreach ($data as $item) {
      $items[] = $this->extend($item);
    }
    return $items;
  }

  protected function getLastJump() {
    $lastJumpIndex = sizeof($this->jumps) - 1;
    return $lastJumpIndex === -1 ? null : $this->jumps[$lastJumpIndex];
  }

  protected function mapColumnWithTableName($table, $columns) {
    $mapped = [];
    foreach ($columns as $column) {
      $mapped[] = '`'.$table.'`.'.'`'.$column.'`';
    }
    return $mapped;
  }

  protected function getJumpJoinCond($jump) {
    $srcTable = $this->model::getTableName();
    $cond = [];
    if ($jump['reverse']) {
      $cond[] = '`'.$srcTable.'`.`id`';
      $cond[] = '=';
      $cond[] = '`'.$jump['alias'].'`.`'.$jump['fk'].'`';
    } else {
      $cond[] = '`'.$srcTable.'`.`'.$jump['fk'].'`';
      $cond[] = '=';
      $cond[] = '`'.$jump['alias'].'`.`id`';
    }

    if ($jump['extraConds']) {
      $extraCondsQuery = [];
      foreach ($jump['extraConds'] as $field => $value) {
        $extraCondsQuery[] = '`'.$jump['alias'].'`.`'.$field.'` = '.$value;
      }
      $cond[] = 'AND';
      $cond[] = join(' AND ', $extraCondsQuery);
    }

    return implode(' ', $cond);
  }

  protected function select() {
    $baseTableName = $this->model::getTableName();
    $jump = sizeof($this->jumps) > 0;
    if ($jump) {
      $lastJump = $this->getLastJump();
      $columns = $this->mapColumnWithTableName(
        $lastJump['alias'],
        $lastJump['model']::getFieldNames()
      );
    } else {
      $columns = $this->mapColumnWithTableName(
        $baseTableName,
        $this->model::getFieldNames()
      );
    }

    $query = ['SELECT'];

    if ($this->distinct) {
      $query[] = 'DISTINCT';
    }

    if ($this->mode === 'count') {
      $query[] = 'COUNT(*) as count';
    } else {
      $query[] = implode(', ', $columns);
    }

    $queryParams = [];
    $filterStatement = [];

    if ($this->filter) {
      foreach ($this->filter as $field => $value) {
        $fs = $this->getFilterStatement($field, $value, $queryParams);
        if ($fs) {
          $filterStatement[] = $fs;
        }
      }
    }

    $query[] = 'FROM';
    $query[] = $baseTableName;

    foreach ($this->jumps as $jump) {
      $query[] = 'JOIN';
      $query[] = $jump['model']::getTableName();
      $query[] = 'AS '.$jump['alias'];
      $query[] = 'ON('.$this->getJumpJoinCond($jump).')';
    }

    foreach ($this->joins as $join) {
      $query[] = 'JOIN';
      $query[] = $join['model']::getTableName();
      $query[] = 'AS '.$join['alias'];
      $query[] = 'ON('.$this->getJumpJoinCond($join).')';
    }

    if (sizeof($filterStatement) > 0) {
      $query[] = 'WHERE';
      $query[] = implode(' AND ', $filterStatement);
    }

    if ($this->orderBy) {
      $query[] = 'ORDER BY';
      $query[] = $this->constructOrderQuery();
    }

    if ($this->offset || $this->limit) {
      $query[] = 'LIMIT';
      $query[] = "$this->offset, $this->limit";
    }
    // error_log(implode(' ', $query));
    // var_dump(implode(' ', $query));
    // var_dump($queryParams);
    // exit;
    return $this->database->query(implode(' ', $query), $queryParams);
  }

  public function getFilterStatement($fieldDesc, $value, &$queryParams) {
    $separatorIndex = strpos($fieldDesc, '__');
    $baseTableName = $this->model::getTableName();

    if ($separatorIndex !== false) {
      $fieldName = substr($fieldDesc, 0, $separatorIndex);
      $operation = substr($fieldDesc, $separatorIndex + 2);

      if ($operation == static::OPERATION_IN) {
        if (sizeof($value) > 0) {
          list($table, $fieldColumn) = $this->resolveColumnAndTable($fieldName);
          $queryValue = join(',', $value);
          if (!$table) {
            $table = $baseTableName;
          }
          return '`'.$table.'`.`'.$fieldColumn.'` IN ('.$queryValue.')';
        }
      }
      if ($operation == static::OPERATION_CONTAINS) {
        foreach ($value as $itemId) {
          $field = $this->model::getField($fieldName);
          $this->joinViaForeignKey(
            $field->getTroughModel(),
            $field->getFkFieldName(),
            true,
            [$field->getViaFieldName() => $itemId]
          );
        }
      }
    } else {
      list($table, $fieldColumn) = $this->resolveColumnAndTable($fieldDesc);
      if (!$table) {
        $table = $baseTableName;
      }
      $queryParams[$fieldDesc] = $value;
      return "`$table`.`$fieldColumn` = :$fieldDesc";
    }
    return null;
  }

  public function resolveColumnAndTable($fieldName) {
    $field = $this->model::getField($fieldName);

    if ($field instanceof \Defiant\Model\ManyToManyField) {
      $this->joinViaForeignKey(
        $field->getTroughModel(),
        $field->getFkFieldName(),
        true
      );
      return $field->resolveFilterColumnAndTable();
    }

    if ($field instanceof \Defiant\Model\ForeignKeyField) {
      return $field->resolveFilterColumnAndTable();
    }

    return [
      $this->model::getTableName(),
      $fieldName,
    ];
  }

  public function constructOrderQuery() {
    $cols = explode(',', $this->orderBy);
    $query = [];
    foreach ($cols as $col) {
      if (strpos($col, '-') === 0) {
        $col = $col.' DESC';
      } else {
        $col = $col.' ASC';
      }
      $query[] = $col;
    }
    return implode(',', $query);
  }

  public function jumpToModelViaForeignKey($model, $fk, $reverse = false) {
    $this->jumps[] = [
      "model" => $model,
      "fk" => $fk,
      "reverse" => $reverse,
      "alias" => 'jump_'.$this->aliasCount++,
      "extraConds" => null,
    ];
    return $this;
  }

  public function joinViaForeignKey($model, $fk, $reverse = false, $extraConds = null) {
    $spec = [
      "model" => $model,
      "fk" => $fk,
      "reverse" => $reverse,
      "alias" => 'join_'.$this->aliasCount++,
      "extraConds" => null,
    ];

    if ($extraConds) {
      $spec['extraConds'] = $extraConds;
    }

    $this->joins[] = $spec;
    return $this;
  }

  public function clone() {
    $item = new self($this->database, $this->model);
    $item->distinct = $item->distinct;
    $item->mode = $item->mode;
    $item->database = $item->database;
    $item->filter = $item->filter;
    $item->joins = $item->joins;
    $item->jumps = $item->jumps;
    $item->limit = $item->limit;
    $item->model = $item->model;
    $item->offset = $item->offset;
    $item->orderBy = $item->orderBy;
    $item->aliasCount = $item->aliasCount;
    return $item;
  }
}
