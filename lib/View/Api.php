<?php

namespace Defiant\View;

class Api extends \Defiant\View {
  public function __construct(\Defiant\Runner $runner = null, \Defiant\Http\Request $request = null) {
    parent::__construct($runner, $request);
    $this->addHeader('Content-Type', 'application/json; charset=utf-8');
  }

  public function render(array $context) {
    return json_encode($context);
  }

  public function view() {
    return $this->render([]);
  }

  public function linkResource($resource) {
    $route = $this->runner->getRouter()->getRouteFromPath($this->path.'/'.$resource);
    if ($route) {
      $view = $this->runner->resolveCallbackView($route->viewCallback, $this->request);
      if ($view->isAccessible()) {
        $this->addLink($resource, $route->path);
      }
    } else {
      throw new Error(sprintf('Resource %s does not exist on %s', $resource, $this->path));
    }
  }

  public function isAccessible() {
    return false;
  }
}
