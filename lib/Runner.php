<?php

namespace Defiant;


class Runner {
  protected $databases;
  protected $models;
  protected $loginClass;
  protected $router;
  protected $renderers = [
    'html' => '\Defiant\View\PlainRenderer',
    'pug' => '\Defiant\View\PugRenderer',
    'jade' => '\Defiant\View\PugRenderer',
  ];
  protected $statusMessages = [
    200 => 'OK',
    302 => 'Found',
    404 => 'Not Found',
    500 => 'Internal Server Error',
  ];
  protected $viewBadRequest = ['\Defiant\View\ServerError', 'badRequest'];
  protected $viewFatalError = ['\Defiant\View\ServerError', 'fatalError'];
  protected $viewNotFound = ['\Defiant\View\ServerError', 'notFound'];

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

    if (isset($config['viewNotFound'])) {
      $this->viewNotFound = $config['viewNotFound'];
    }

    if (isset($config['badRequest'])) {
      $this->badRequest = $config['badRequest'];
    }

    if (isset($config['viewFatalError'])) {
      $this->viewFatalError = $config['viewFatalError'];
    }

    if (isset($config['renderers'])) {
      $this->renderers = array_merge($this->renderers, $config['renderers']);
    }

    if (isset($config['userClass'])) {
      $this->userClass = $config['userClass'];
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
    try {
      $this->serveResponseForRequest($request);
    } catch (\Defiant\Http\NotFound $exception) {
      $this->resolveCallback($this->viewNotFound, $request);
    } catch (\Defiant\Http\BadRequest $exception) {
      $this->resolveCallback($this->viewBadRequest, $request, [
        "exception" => $exception,
      ]);
    } catch (\Exception $exception) {
      $this->resolveCallback($this->viewFatalError, $request, [
        "exception" => $exception,
      ]);
    }
  }

  public function serveResponseForRequest(Http\Request $request) {
    $route = $this->router->getRoute($request);

    if ($route) {
      $request->setParams($route->getParams());
      $view = $this->resolveCallbackView($route->viewCallback, $request);
      if ($view->isAccessible()) {
        $viewMethod = $this->resolveCallbackViewMethod($route->viewCallback);
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

  public function getRenderer($templateSuffix) {
    if ($this->renderers[$templateSuffix]) {
      return new $this->renderers[$templateSuffix]($this);
    }

    return null;
  }

  public function getUserClass() {
    return $this->userClass;
  }
}
