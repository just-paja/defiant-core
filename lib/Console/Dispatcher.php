<?php

namespace Defiant\Console;

class Dispatcher {
  public static function getCommands() {
    \Defiant\Runner::getConfig();
    $modules = Module::getAncestors();
    $commands = [];

    foreach ($modules as $module) {
      $class = new \ReflectionClass($module);
      $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach ($methods as $method) {
        if ($method->name !== 'configure' && $method->class === $module) {
          $commands[] = $module::callsign.':'.$method->name;
        }
      }
    }
    return $commands;
  }

  public static function getModule($callsign) {
    $modules = Module::getAncestors();
    $commands = [];

    foreach ($modules as $moduleClass) {
      if ($moduleClass::callsign === $callsign) {
        return new $moduleClass();
      }
    }

    return null;
  }

  public static function renderBackTrace($trace) {
    ob_start();
    foreach ($trace as $frame) {
      echo '* in '.($frame['class'] ? $frame['class'].$frame['type'] : '').$frame['function']."()\n";
      echo '  at '.$frame['file'].':'.$frame['line']."\n";

      // if (sizeof($frame['args'])) {
      //   echo '  with args: '."\n";
      //   $argInfo = explode("\n", var_export($frame['args'], true));
      //   foreach ($argInfo as $value) {
      //     echo '    '.$value."\n";
      //   }
      // }
    }
    return ob_get_clean();
  }

  public static function renderDatabaseError(\Defiant\Database\Error $exception) {
    ob_start();
    $code = $exception->getCode();
    $message = $exception->getMessage();
    $params = $exception->getParams();
    echo "------------------------------\n";
    echo "DATABASE ERROR $code: $message\n";
    echo "QUERY:\n\n";
    echo $exception->getQuery() . "\n\n";

    if ($params && sizeof($params) > 0) {
      echo "PARAMS:\n\n";
      var_dump($params);
    }

    echo "STACK TRACE:\n";
    echo static::renderBackTrace($exception->getTrace());
    return ob_get_clean();
  }

  public static function renderException(\Exception $exception) {
    ob_start();
    $message = $exception->getMessage();
    $type = str_replace('Defiant\\', '', get_class($exception));

    if ($message) {
      echo "$type: $message\n\n";
    } else {
      echo "$type!\n\n";
    }

    echo "STACK TRACE:\n";
    echo static::renderBackTrace($exception->getTrace());
    return ob_get_clean();
  }

  public static function run($moduleName, $command, $cmd) {
    try {
      $module = \Defiant\Console\Dispatcher::getModule($moduleName);
      $module->configure(\Defiant\Runner::getConfig());
      $module->$command($cmd);
    } catch(\Defiant\Database\Error $exception) {
      echo static::renderDatabaseError($exception);
      exit(10);
    } catch(\Exception $exception) {
      echo static::renderException($exception);
      exit(1);
    }
  }
}

require_once 'Database.php';
require_once 'Model.php';
