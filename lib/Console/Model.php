<?php

namespace Defiant\Console;

class Model extends Module {
  const callsign = 'model';

  public function list($cmd = null) {
    $modelList = \Defiant\Model::getAllModels();
    foreach ($modelList as $model) {
      echo $model."\n";
    }
  }
}
