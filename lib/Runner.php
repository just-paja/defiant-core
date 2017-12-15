<?php

namespace Defiant;

class Runner {
  protected $databases;
  protected $models;
  protected $router;
  protected $statusMessages = [
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Internal Server Error',
  ];

  public static function getConfig() {
    if (file_exists('defiantConfig.php')) {
      require_once 'defiantConfig.php';
    }

    if (function_exists('configDefiant')) {
      return configDefiant();
    }

    throw new MissingConfigError();
  }

  public function __construct($config) {
    $this->router = new Http\Router();
    $this->databases = new Database\Collection();
    $this->models = new Model\Collection($this->databases);

    if (isset($config['routes'])) {
      $this->router->replace($config['routes']);
    }

    if (isset($config['onDatabaseChange'])) {
      $this->databases->setOnChange($config['onDatabaseChange']);
    }

    if (isset($config['database'])) {
      $this->databases->replace($config['database']);
    }

    if (isset($config['models'])) {
      $this->models->replace($config['models']);
    }
  }

  public function __get($attr) {
    if ($attr === 'models') {
      return $this->models;
    }
    return $this->$attr;
  }

  public function resolveRequest(Http\Request $request) {
    $viewCallback = $this->router->getView($request);

    if ($viewCallback) {
      return $this->resolveCallback($viewCallback[0], $viewCallback[1], $request);
    } else {
      throw new Http\NotFound();
    }
  }

  public function resolveCallback($viewClass, $viewMethod, Http\Request $request, array $context = []) {
    $view = new $viewClass($this);
    $callback = [$view, $viewMethod];
    return call_user_func($callback, $request, $context);
  }

  public function serve($status, $content) {
    ob_end_clean();
    header('HTTP/1.1 '.$status.' '.$this->statusMessages[$status]);
    echo $content;
  }

  public function serveCallback($viewClass, $viewMethod, Http\Request $request, $status, array $context = []) {
    return $this->serve($status, $this->resolveCallback($viewClass, $viewMethod, $request, $context));
  }
}
