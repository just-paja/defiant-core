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
  protected $secretKey = null;
  protected $statusMessages = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanentyl',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payament Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
  ];
  protected $viewBadRequest = ['\Defiant\View\ServerError', 'badRequest'];
  protected $viewForbidden = ['\Defiant\View\ServerError', 'forbidden'];
  protected $viewFatalError = ['\Defiant\View\ServerError', 'fatalError'];
  protected $viewNotFound = ['\Defiant\View\ServerError', 'notFound'];
  protected $viewUnauthorized = ['\Defiant\View\ServerError', 'unauthorized'];

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
    $this->models = new Model\Collection($this->databases, $this);

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

    if (isset($config['viewUnauthorized'])) {
      $this->viewUnauthorized = $config['viewUnauthorized'];
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

    if (isset($config['secretKey'])) {
      $this->secretKey = $config['secretKey'];
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

  public function getSessionCsrfToken() {
    return \Defiant\Model\CsrfToken::getForSessionId(session_id());
  }

  public function resolveRequest(Http\Request $request) {
    try {
      $this->serveResponseForRequest($request);
    } catch (\Defiant\Http\NotFound $exception) {
      $this->resolveCallback($this->viewNotFound, $request);
    } catch (\Defiant\Http\Unauthorized $exception) {
      $this->resolveCallback($this->viewUnauthorized, $request);
    } catch (\Defiant\Http\BadRequest $exception) {
      $this->resolveCallback($this->viewBadRequest, $request, [
        "exception" => $exception,
      ]);
    } catch (\Defiant\Http\Forbidden $exception) {
      $this->resolveCallback($this->viewForbidden, $request, [
        "exception" => $exception,
      ]);
    } catch (\Exception $exception) {
      $this->resolveCallback($this->viewFatalError, $request, [
        "exception" => $exception,
      ]);
    }
  }

  public function serveResponseForRequest(Http\Request $request) {
    $this->validateForCsrf($request);
    $route = $this->router->getRoute($request);

    if ($route) {
      $request->setParams($route->getParams());
      $view = $this->resolveCallbackView($route->viewCallback, $request, $route);
      if ($view->isAccessible()) {
        $viewMethod = $this->resolveCallbackViewMethod($route->viewCallback);
        $pageContent = $this->resolveView($view, $viewMethod);
        return $this->serveView($view, $pageContent);
      } else {
        throw new Http\Unauthorized();
      }
    }

    throw new Http\NotFound();
  }

  public function resolveCallbackView($viewCallback, $request, $route = null) {
    $className = $viewCallback;
    if (is_array($viewCallback)) {
      $className = $viewCallback[0];
    }
    return new $className($this, $request, $route);
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

  public function getSecretKey() {
    return $this->secretKey;
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

  public function getUserConnector() {
    return $this->models->getByClassName($this->getUserClass());
  }

  public function validateForCsrf($request) {
    $sessionToken = $this->getSessionCsrfToken();
    if ($request->isCsrfProtected()) {
      $requestToken = $request->getCsrfToken();
      if (!$sessionToken || !$requestToken) {
        throw new Http\Forbidden('Missing CSRF token');
      }
      if ($sessionToken->token !== $requestToken) {
        throw new Http\Forbidden('Invalid CSRF token');
      }
    }
  }
}

require_once 'Model/CsrfToken.php';
