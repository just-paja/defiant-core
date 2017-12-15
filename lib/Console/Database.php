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

  public function sync() {
    foreach ($runner->databases->all() as $db) {
      $db->synchronize();
    }
  }

  public function seed() {
    $content = file_get_contents($srcFile);
    $seedBatch = json_decode($content, true);

    foreach ($seedBatch as $model=>$items) {
      foreach ($items as $item) {
        $modelName = lcfirst($model);
        $runner->models->$modelName->fetchAndUpdate($item)->save();
      }
    }
  }
}
