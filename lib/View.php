<?php

namespace Defiant;

class View {
  protected $host;
  protected $models;
  protected $path;
  protected $protocol;
  protected $request;
  protected $runner;
  protected $status = 200;
  protected $headers = [];

  public function __construct(Runner $runner = null, Http\Request $request = null) {
    if ($runner) {
      $this->runner = $runner;
      $this->models = $runner->models;
    }
    if ($request) {
      $this->request = $request;
      $this->host = $request->host;
      $this->protocol = $request->protocol;
      $this->path = $request->path;
    }
  }

  public function addHeader($header, $value) {
    $this->headers[$header] = $value;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function getStatus() {
    return $this->status;
  }

  public function isAccessible() {
    return true;
  }

  public function renderTemplate($template, array $context = []) {
    ob_start();
    $request = $this->request;
    extract($context);
    require('templates/'.$template);
    $content = ob_get_clean();
    return $content;
  }
}
