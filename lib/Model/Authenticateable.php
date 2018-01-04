<?php

namespace Defiant\Model;

abstract class Authenticateable extends \Defiant\Model {
  const FIELD_USERNAME = 'username';
  const FIELD_PASSWORD = 'password';
  const FIELD_SESSION_USER_ID = 'userId';

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

    return $user;
  }

  public static function login(\Defiant\Http\Request $request, self $user) {
    $request->session->set(static::FIELD_SESSION_USER_ID, $user->id);
  }
}
