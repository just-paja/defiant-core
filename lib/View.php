<?php

namespace Defiant;

class View {
  protected $headers = [];
  protected $host;
  protected $models;
  protected $params = [];
  protected $path;
  protected $protocol;
  protected $request;
  protected $runner;
  protected $status = 200;
  protected $user = null;

  public function __construct(Runner $runner = null, Http\Request $request = null) {
    if ($runner) {
      $this->runner = $runner;
      $this->models = $runner->models;
    }
    if ($request) {
      $this->host = $request->host;
      $this->params = $request->getParams();
      $this->path = $request->path;
      $this->protocol = $request->protocol;
      $this->request = $request;
    }
  }

  public function addHeader($header, $value) {
    $this->headers[$header] = $value;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function getParam($name) {
    return isset($this->params[$name]) ? $this->params[$name] : null;
  }

  public function getStatus() {
    return $this->status;
  }

  public function getUser() {
    if (!$this->user) {
      $userId = $this->request->getUserId();
      if ($userId) {
        $this->user = $this->runner
          ->getUserConnector()
          ->objects
          ->find($userId);
      }
    }
    return $this->user;
  }

  public function isAccessible() {
    return true;
  }

  public function login($username, $password) {
    $userConnector = $this->runner->getUserConnector();
    $user = $userConnector->model::authenticate($username, $password);
    $userConnector->model::login($this->request, $user);
    return $user;
  }

  public function logoutUser() {
    $this->runner->getUserConnector()->model::logout($this->request);
  }

  public function renderTemplate($template, array $context = []) {
    $nameSplit = explode('.', $template);
    $suffix = $nameSplit[sizeof($nameSplit) - 1];

    if ($renderer = $this->runner->getRenderer($suffix)) {
      if (!($renderer instanceof \Defiant\View\Renderer)) {
        throw new Error(sprintf('Renderer %s does not inherit from Defiant\\View\\Renderer', get_class($renderer)));
      }
      $context['request'] = $this->request;
      $context['router'] = $this->runner->getRouter();
      $context['user'] = $this->getUser();
      return $renderer->renderFile($template, $context);
    }

    throw new Error(sprintf('Renderer for suffix %s is not configured!', $suffix));
  }

  public function url($path, array $params = []) {
    return $this->runner->getRouter()->getUrl($path, $params);
  }

  public function redirect($location) {
    $this->status = 302;
    $this->headers['Location'] = $location;
  }
}
