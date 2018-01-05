<?php

namespace Defiant\Http;

class Request {
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

  public function getQuery() {
    return $this->query;
  }

  public function getParams() {
    return $this->params;
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
        if (is_array($this->query[$key])) {
          $this->query[$key][] = $value;
        } else {
          $this->query[$key] = [$value];
        }
      }
    }
  }

  public function query($key, $defaultValue) {
    if (isset($this->query[$key]) && (string) $this->query[$key] !== '') {
      return $this->query[$key];
    }
    return $defaultValue;
  }

  public function setParams(array $params) {
    $this->params = $params;
  }

  public function getUserId() {
    return $this->session->get(\Defiant\Model\Authenticateable::FIELD_SESSION_USER_ID);
  }
}
