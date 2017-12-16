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

  public function getRouter() {
    return $this->router;
  }

  public function resolveRequest(Http\Request $request) {
    $viewCallback = $this->router->getView($request);

    if ($viewCallback) {
      $view = $this->resolveCallbackView($viewCallback, $request);
      if ($view->isAccessible()) {
        $viewMethod = $this->resolveCallbackViewMethod($viewCallback);
        $pageContent = $this->resolveView($view, $viewMethod);
        return $this->serveView($view, $pageContent);
      }
    }

    throw new Http\NotFound();
  }

  public function resolveCallbackView($viewCallback, $request) {
    $className = $viewCallback;
    if (is_array($viewCallback)) {
      $className = $viewCallback[0];
    }
    return new $className($this, $request);
  }

  public function resolveCallbackViewMethod($viewCallback) {
    $methodName = 'view';
    if (is_array($viewCallback) && $viewCallback[1]) {
      $methodName = $viewCallback[1];
    }
    return $methodName;
  }

  public function resolveView(View $view, $viewMethod, array $context = []) {
    return $view->$viewMethod($context);
  }

  public function resolveCallback($viewCallback, Http\Request $request, array $context = []) {
    $view = $this->resolveCallbackView($viewCallback, $request);
    $viewMethod = $this->resolveCallbackViewMethod($viewCallback);
    $pageContent = $this->resolveView($view, $viewMethod, $context);
    return $this->serveView($view, $pageContent);
  }

  public function serveView(View $view, $content) {
    $status = $view->getStatus();
    $headers = $view->getHeaders();
    ob_end_clean();
    header('HTTP/1.1 '.$status.' '.$this->statusMessages[$status]);

    foreach ($headers as $header => $value) {
      header($header.': '.$value);
    }
    echo $content;
  }
}
