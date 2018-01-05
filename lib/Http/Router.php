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

  public function getRouteFromPath($path, $method = null) {
    foreach ($this->routes as $route) {
      if ($route->matches($path, $method)) {
        return $route;
      }
    }
    return null;
  }

  public function getRoute(Request $request) {
    return $this->getRouteFromPath($request->path, $request->method);
  }

  public function getView(Request $request) {
    $route = $this->getRoute($request);

    if ($route) {
      return $route->viewCallback;
    }

    return null;
  }

  public function getUrl($name, array $params = [], $modifiers = null) {
    $resolvedRoute = null;
    foreach ($this->routes as $route) {
      if ($route->name === $name) {
        $resolvedRoute = $route;
        break;
      }
    }
    if ($resolvedRoute) {
      return $route->getTranslatedPath($params, $modifiers);
    }
    throw new RoutingError(sprintf('Path specified as %s was not found', $name));
  }

  public function replace($routes) {
    $this->routes = [];
    foreach ($routes as $route) {
      $this->routes[] = new Route(
        $route[0],
        $route[1],
        $route[2],
        isset($route[3]) ? $route[3] : null
      );
    }
  }
}
