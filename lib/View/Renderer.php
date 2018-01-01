<?php

namespace Defiant\View;

abstract class Renderer {
  protected $runner;

  public function __construct(\Defiant\Runner $runner) {
    $this->dirTemplates = realpath('templates');
    $this->runner = $runner;
  }

  public function getTemplatePath($template) {
    if (file_exists($template)) {
      return $template;
    }
    return $this->dirTemplates.'/'.$template;
  }

  abstract public function renderFile($template, array $context = array());
}
