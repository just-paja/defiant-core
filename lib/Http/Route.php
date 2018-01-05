<?php

namespace Defiant\Http;

class Route {
  const PARAM_METHOD_ADD = 'add';
  const PARAM_METHOD_SET = 'set';
  const PARAM_METHOD_REMOVE = 'remove';
  const PARAM_METHOD_TOGGLE = 'toggle';
  const PARAM_METHOD_TOGGLE_ARRAY = 'toggleArray';

  public $method;
  public $path;
  public $viewCallback;
  public $name;
  protected $params = [];
  protected $pathPattern;
  protected $paramNames = [];

  public function __construct($method, $path, $viewCallback, $name = null) {
    $this->method = $method;
    $this->path = $path === '/' ? '/' : rtrim($path, '/');
    $this->viewCallback = $viewCallback;
    $this->name = $name;
  }

  public function matches($path, $method = null) {
    if ($method && $this->method !== $method) {
      return false;
    }
    $matches = [];
    if (!preg_match($this->getPathPattern(), $path, $matches)) {
      return false;
    }
    foreach ($this->paramNames as $index => $param) {
      $this->params[$param] = isset($matches[$index + 1]) ? $matches[$index + 1] : null;
    }
    return true;
  }

  public function getParams() {
    return $this->params;
  }

  public function getPathPattern() {
    if (!$this->pathPattern) {
      $pattern = preg_replace('/\//', '\\/', $this->path);
      $pattern = preg_replace_callback(
        '/\:[a-zA-Z0-9]+/',
        function($matches) {
          $this->paramNames[] = ltrim($matches[0], ':');
          return '([^\/]+)';
        },
        $pattern
      );
      $this->pathPattern = '/^'.$pattern.'$/';
    }
    return $this->pathPattern;
  }

  public function getTranslatedPath(array $params = [], $modifiers) {
    $path = $this->path;
    $this->getPathPattern();
    foreach ($this->paramNames as $paramName) {
      if (!empty($params[$paramName])) {
        $path = preg_replace('/\:'.$paramName.'/', $params[$paramName], $path);
      }
      unset($params[$paramName]);
    }
    return $this->addQueryStringParams($path, $params, $modifiers);
  }

  public function parseUrlParams($url) {
    $questionMarkIndex = strpos($url, '?');
    $params = [];
    if ($questionMarkIndex === false) {
      return $params;
    }
    $parseable = substr($questionMarkIndex);
    $keyValues = explode('&', $parseable);
    foreach ($keyValues as $keyValue) {
      $exploded = explode('=', $keyValue);
      $key = urldecode($exploded[0]);
      $value = urldecode($exploded[1]);
      $bracketsIndex = strpos($key, '[]');
      if ($bracketsIndex === false) {
        $params[$key] = $value;
      } else {
        $key = substr(0, $bracketsIndex);
        if (is_array($params[$key])) {
          $params[$key][] = $value;
        } else {
          $params[$key] = [$value];
        }
      }
    }
    return $params;
  }

  public function mapModifiersToParams($url, $params, $modifiers) {
    $urlParams = $this->parseUrlParams($url);
    $params = array_merge($urlParams, $params);

    if (isset($modifiers[static::PARAM_METHOD_SET])) {
      foreach ($modifiers[static::PARAM_METHOD_SET] as $key => $value) {
        $params[$key] = $value;
      }
    }

    if (isset($modifiers[static::PARAM_METHOD_REMOVE])) {
      foreach ($modifiers[static::PARAM_METHOD_REMOVE] as $key) {
        unset($params[$key]);
      }
    }

    if (isset($modifiers[static::PARAM_METHOD_TOGGLE])) {
      foreach ($modifiers[static::PARAM_METHOD_TOGGLE] as $key => $value) {
        if (isset($params[$key]) && $params[$key] == $value) {
          unset($params[$key]);
        } else {
          $params[$key] = $value;
        }
      }
    }

    if (isset($modifiers[static::PARAM_METHOD_ADD])) {
      foreach ($modifiers[static::PARAM_METHOD_ADD] as $key => $value) {
        if (!isset($params[$key])) {
          $params[$key] = [$value];
        } else {
          if (is_array($value)) {
            $params[$key] = array_merge($params[$key], $value);
          } else {
            if (!in_array($params[$key], $value)) {
              $params[$key][] = $value;
            }
          }
        }
      }
    }

    if (isset($modifiers[static::PARAM_METHOD_TOGGLE_ARRAY])) {
      foreach ($modifiers[static::PARAM_METHOD_TOGGLE_ARRAY] as $key => $value) {
        if (!isset($params[$key])) {
          $params[$key] = [$value];
        } else {
          $valueIndex = array_search($value, $params[$key]);
          if ($valueIndex === false) {
            $params[$key][] = $value;
          } else {
            unset($params[$key][$valueIndex]);
          }
        }
        if (sizeof($params[$key]) === 0) {
          unset($params[$key]);
        }
      }
    }

    return $params;
  }

  public function addQueryStringParams($url, $params, $modifiers) {
    if ($modifiers && sizeof($modifiers) > 0) {
      $params = $this->mapModifiersToParams($url, $params, $modifiers);
    }
    if (sizeof($params) === 0) {
      return $url;
    }
    $nextPath = $url;
    $append = [];
    if (strpos($url, '?') === false) {
      $nextPath .= '?';
    }
    foreach ($params as $paramName => $paramValue) {
      if (is_array($paramValue)) {
        foreach ($paramValue as $value) {
          $append[] = urlencode($paramName).'[]='.urlencode($value);
        }
      } else {
        $append[] = urlencode($paramName).'='.urlencode($paramValue);
      }
    }
    return $nextPath.implode('&', $append);
  }
}
