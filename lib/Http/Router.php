<?php

namespace Defiant\Http;

class Router {
  private $routes;

  public function __construct($routes = null) {
    $this->routes = [];
    if ($routes) {
      $this->replace($routes);
    }
  }

  public function getRoute(Request $request) {
    foreach ($this->routes as $route) {
      if ($route->method != $request->method) {
        continue;
      }
      if ($route->path != $request->path) {
        continue;
      }
      return $route;
    }
    return null;
  }

  public function getView(Request $request) {
    $route = $this->getRoute($request);

    if ($route) {
      return $route->viewCallback;
    }

    return null;
  }

  public function replace($routes) {
    $this->routes = [];
    foreach ($routes as $route) {
      $this->routes[] = new Route($route[0], $route[1], $route[2]);
    }
  }
}
