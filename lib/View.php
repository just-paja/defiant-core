<?php

namespace Defiant;

class View {
  protected $models;

  public function __construct(Runner $runner = null) {
    if ($runner) {
      $this->models = $runner->models;
    }
  }

  public function render($template, Http\Request $request = null, array $context = []) {
    ob_start();
    extract($context);
    require('templates/'.$template);
    $content = ob_get_clean();
    return $content;
  }
}
