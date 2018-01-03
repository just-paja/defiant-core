<?php

namespace Defiant\Model;

abstract class Authenticateable extends \Defiant\Model {
  const FIELD_USERNAME = 'username';
  const FIELD_PASSWORD = 'password';

  public static function authenticate($username, $password) {
    $connector = static::getConnector();
    $user = $connector->objects->filter([
      static::FIELD_USERNAME => $username,
    ])->first();

    if (!$user) {
      throw new \Defiant\Model\Error(sprintf('User %s not found', $username));
    }

    $hashedPassword = \Defiant\Model\PasswordField::hashValue($password);

    if ($user->password !== $hashedPassword) {
      throw new \Defiant\Model\FieldError(sprintf('Invalid password'));
    }

    return $item;
  }
}
