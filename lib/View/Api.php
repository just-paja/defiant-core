<?php

namespace Defiant\View;

class Api extends \Defiant\View {
  protected $links = [];

  public function __construct(\Defiant\Runner $runner = null, \Defiant\Http\Request $request = null) {
    parent::__construct($runner, $request);
    $this->addHeader('Content-Type', 'application/json; charset=utf-8');
  }

  protected function addLink($name, $relativePath) {
    $this->links[$name] = $this->protocol.'://'.$this->host.$relativePath;
  }

  public function render(array $context = []) {
    $this->linkResources();
    $context['links'] = $this->links;
    return json_encode($context);
  }

  public function view() {
    return $this->render([]);
  }

  function linkResources() {
  }

  public function linkResource($resource) {
    $route = $this->runner->getRouter()->getRouteFromPath($this->path.'/'.$resource);
    if ($route) {
      $view = $this->runner->resolveCallbackView($route->viewCallback, $this->request);
      if ($view->isAccessible()) {
        $this->addLink($resource, $route->path);
      }
    }
  }
}
