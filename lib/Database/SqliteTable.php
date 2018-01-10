<?php

namespace Defiant\Database;

class SqliteTable extends \Defiant\Database\Table {
  protected function loadColumns() {
    $this->columns = [];
    $result = $this->connection->query("PRAGMA TABLE_INFO(".$this->name.")")->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($result as $column) {
      $typeInfo = explode('(', $column['type']);
      $type = trim($typeInfo[0]);
      $length = isset($typeInfo[1]) ? intval($typeInfo[1]) : null;
      if (strpos($type, ' ') !== false) {
        if (strpos($type, 'UNSIGNED')) {
          $typeExploded = explode(' ', $type);
          $type = $typeExploded[0];
          $column['isUnsigned'] = true;
        }
      }
      $def = [
        "dbType" => $type,
        "default" => $column['dflt_value'],
        "isNull" => !$column['notnull'],
        "isPrimary" => !!$column['pk'],
        "isAutoincrement" => !!$column['pk'],
        "length" => $length,
      ];
      if (isset($column['isUnsigned'])) {
        $def['isUnsigned'] = $column['isUnsigned'];
      }
      if (in_array($type, [
        'TINYINT',
        'INT',
        'INTEGER',
      ])) {
        if (!isset($def['isUnsigned'])) {
          $def['isUnsigned'] = false;
        }
        if (isset($def['default'])) {
          $def['default'] = intval($def['default']);
        }
      }

      $field = \Defiant\Model\Field::createFromDef($column['name'], $def);
      $this->columns[] = new Column($this->connection, $this, $field);
    }
  }

  protected function getColumnSpec($col) {
    return implode(' ', array_filter(array(
      '`'.$col->getName().'`',
      $col->getType() . ($col->getLength() !== null ? '('.$col->getLength().')' : ''),
      $col->isUnsigned() ? 'UNSIGNED' : '',
      $col->isNull() ? 'NULL' : 'NOT NULL',
      $col->isPrimary() ? 'PRIMARY KEY':'',
      $col->isUnique() && !$col->isPrimary() ? 'UNIQUE':'',
      $col->isAutoincrement() ? 'AUTOINCREMENT' : '',
      $col->hasDefault() ? 'DEFAULT '.$col->getDefault() : '',
    )));
  }

  protected function getColumnSpecs() {
    $colSpecs = [];
    foreach ($this->getColumns() as $col) {
      $colSpecs[] = $this->getColumnSpec($col);
    }
    // var_dump($colSpecs);
    return $colSpecs;
  }

  public static function getCreateQuery($tableName, $colSpecs) {
    $query = "CREATE TABLE `".$tableName."` (\n";
    $query .= '  '.implode(",\n  ", $colSpecs)."\n";
    $query .= ")";
    return $query;
  }

  public static function getRenameQuery($oldName, $newName) {
    return 'ALTER TABLE '.$oldName.' RENAME TO '.$newName;
  }

  public static function getToggleForeignKeysQuery($enabled) {
    return 'PRAGMA FOREIGN_KEYS='.($enabled ? 'on':'off');
  }

  public static function getCopyDataQuery($srcTable, $destTable, $copyCols, $defaultsCols = []) {
    $defaultNames = [];
    $defaultValues = [];
    foreach ($defaultsCols as $defaultsCol) {
      $defaultNames[] = $defaultsCol->getName();
      $defaultValues[] = "'".$defaultsCol->getDefault()."' as `".$defaultsCol->getName()."`";
    }
    $colNames = implode(',', array_merge($copyCols, $defaultNames));
    $colValues = implode(',', array_merge($copyCols, $defaultValues));
    return implode("\n", [
      'INSERT INTO '.$destTable.' ('.$colNames.')',
      '  SELECT '.$colValues,
      '  FROM '.$srcTable,
    ]);
  }

  public static function getDropQuery($tableName, $IfExists = false) {
    return 'DROP TABLE '.($IfExists ? ' IF EXISTS' : '').' '.$tableName;
  }

  protected function getAddQuery() {
    return [
      static::getCreateQuery($this->name, $this->getColumnSpecs())
    ];
  }

  public static function getExistingColumns($columns) {
    $existing = [];
    foreach ($columns as $column) {
      $existing[] = $column;
    }
    return $existing;
  }

  protected function getChangeQuery() {
    $tempName = $this->name.'_MOD';
    $specs = $this->getColumnSpecs();
    $copyCols = [];
    $defaultsCols = [];

    foreach ($this->columnsKeep as $col) {
      $copyCols[] = $col->getName();
    }

    return [
      static::getToggleForeignKeysQuery(false),
      static::getDropQuery($tempName, true),
      static::getRenameQuery($this->name, $tempName),
      static::getCreateQuery($this->name, $specs),
      static::getCopyDataQuery(
        $tempName,
        $this->name,
        static::getExistingColumns($copyCols),
        $this->columnsAdd
      ),
      static::getDropQuery($tempName),
      static::getToggleForeignKeysQuery(true),
    ];
  }

  public function getSaveQuery() {
    if ($this->exists()) {
      return $this->getChangeQuery();
    }
    return $this->getAddQuery();
  }
}
