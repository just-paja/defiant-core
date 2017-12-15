<?php

namespace Defiant\Console;

class Database extends Module {
  public function onChange($changeType, $changeIdentifier, $changes = null) {
    echo $changeType." ".$changeIdentifier."\n";

    if ($changes) {
      foreach ($changes as $property=>$change) {
        echo '  '.
          $property.
          ' ('.
          $this->renderValue($change->previousValue).
          ' -> '.
          $this->renderValue($change->currentValue).
          ')'.
          "\n";
      }
    }
  }

  public function configure(array $config) {
    $config['onDatabaseChange'] = function($changeType, $changeIdentifier, $changes = null) {
      $this->onChange($changeType, $changeIdentifier, $changes);
    };
    parent::configure($config);
  }

  public function sync($cmd = null) {
    foreach ($this->runner->databases->all() as $db) {
      $db->synchronize();
    }
  }

  public function seed($cmd = null) {
    $cmd->argument()
      ->referToAs('datafile')
      ->description('JSON File containing model data to be loaded')
      ->require(true)
      ->file();
    $cmd->parse();
    $srcFile = $cmd[1];
    $content = file_get_contents($srcFile);
    $seedBatch = json_decode($content, true);

    foreach ($seedBatch as $model=>$items) {
      foreach ($items as $item) {
        $this->runner->models->$model->fetchAndUpdate($item)->save();
      }
    }
  }
}
