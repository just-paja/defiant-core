<?php

namespace Defiant\View;

class PugRenderer extends Renderer {
  protected $pug;

  public function __construct(\Defiant\Runner $runner) {
    if (!class_exists('\Tale\Pug\Renderer')) {
      throw new Error('Pug is not installed. Please require pug-php/pug with composer');
    }
    parent::__construct($runner);
    $renderer = &$this;
    $runner = $this->runner;
    $this->pug = new \Tale\Pug\Renderer();
    $this->pug->addFilter('csrfTokenField', [$this, 'renderCsrfField']);
  }

  public function getTemplatePath($template) {
    if (file_exists($template)) {
      return $template;
    }
    return 'templates/'.$template;
  }


  public function renderFile($template, array $context = array()) {
    return $this->pug->render($this->getTemplatePath($template), $context);
  }
}
