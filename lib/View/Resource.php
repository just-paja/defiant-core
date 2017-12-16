<?php

namespace Defiant\View;

abstract class Resource extends \Defiant\View\Api {
  protected $links = [];

  public function __construct(\Defiant\Runner $runner = null, \Defiant\Http\Request $request = null) {
    parent::__construct($runner, $request);
    $this->addLink('self', $this->path);
  }

  protected function addLink($name, $relativePath) {
    $this->links[$name] = $this->protocol.'://'.$this->host.$relativePath;
  }

  abstract public function getResource();

  public function render($context) {
    $context['links'] = $this->links;
    return parent::render($context);
  }

  public function view() {
    $resource = $this->getResource();
    if ($resource) {
      $data = $resource->first();
    } else {
      $data = null;
    }
    if (!$data) {
      throw new \Defiant\Http\NotFound();
    }
    return $this->render($data);
  }
}
