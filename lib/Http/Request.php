<?php

namespace Defiant\Http;

class Request {
  const CSRF_FIELD_NAME = 'csrfSignature';
  const CSRF_PROTECTED_METHODS = ['post', 'put', 'patch'];

  public $method;
  public $path;
  public $host;
  protected $params = [];
  protected $query = [];

  public static function fromGlobals() {
    return new self($_SERVER, $_GET, $_POST, $_SESSION);
  }

  public function __construct(&$server, &$get, &$post, &$session) {
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->host = $server['HTTP_HOST'];
    $this->protocol = $this->isSsl($server) ? 'https' : 'http';
    $pathSplit = explode('?', $server['REQUEST_URI']);
    $this->body = $post;
    $this->session = new RequestSession($session);
    $this->path = $pathSplit[0];
    if (isset($pathSplit[1])) {
      $this->parseQueryString($pathSplit[1]);
    }
  }

  public function isCsrfProtected() {
    return in_array($this->method, static::CSRF_PROTECTED_METHODS);
  }

  public function getBodyParam($key, $defaultValue) {
    return static::getParamFrom($this->body, $key, $defaultValue);
  }

  public function getCsrfToken() {
    return $this->getBodyParam(static::CSRF_FIELD_NAME, null);
  }

  public function getMethod() {
    return $this->method;
  }

  public function getQuery() {
    return $this->query;
  }

  public function getParams() {
    return $this->params;
  }

  public function getParamFrom(&$source, $key, $defaultValue) {
    if (!empty($source[$key])) {
      return $source[$key];
    }
    return $defaultValue;
  }

  protected function isSsl($server) {
    if (isset($server['HTTPS'])) {
      if ('on' == strtolower($server['HTTPS']) || '1' == $server['HTTPS']) {
        return true;
      }
    } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
      return true;
    }
    return false;
  }

  public function parseQueryString($queryString) {
    $arguments = explode('&', $queryString);
    foreach ($arguments as $argument) {
      $argumentSplit = explode('=', $argument);
      $key = urldecode($argumentSplit[0]);
      $value = isset($argumentSplit[1]) ? urldecode($argumentSplit[1]) : null;
      $bracketsIndex = strpos($key, '[]');

      if ($bracketsIndex === false) {
        $this->query[$key] = $value;
      } else {
        $key = substr($key, 0, $bracketsIndex);
        if (isset($this->query[$key]) && is_array($this->query[$key])) {
          $this->query[$key][] = $value;
        } else {
          $this->query[$key] = [$value];
        }
      }
    }
  }

  public function query($key, $defaultValue) {
    return static::getParamFrom($this->query, $key, $defaultValue);
  }

  public function setParams(array $params) {
    $this->params = $params;
  }

  public function getUserId() {
    return $this->session->get(\Defiant\Model\Authenticateable::FIELD_SESSION_USER_ID);
  }
}
