<?php

namespace Defiant\View;

abstract class Renderer {
  private $pug;

  public function __construct() {
    $this->dirTemplates = realpath('templates');
  }

  public function getTemplatePath($template) {
    if (file_exists($template)) {
      return $template;
    }
    return $this->dirTemplates.'/'.$template;
  }

  abstract public function renderFile($template, array $context = array());
}
