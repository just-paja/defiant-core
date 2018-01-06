<?php

namespace Defiant\Model;

class CsrfToken extends \Defiant\Model {
  protected static $fields = [
    'sessionId' => '\Defiant\Model\VarcharField',
    'token' => '\Defiant\Model\VarcharField',
  ];

  public static function getForSessionId($sessionId) {
    $token = static::getConnector()->objects->filter([
      'sessionId' => $sessionId,
    ])->first();

    return $token ? $token : static::createForSessionId($sessionId);
  }

  public static function createForSessionId($sessionId) {
    return static::getConnector()->create([
      'sessionId' => $sessionId,
      'token' => static::generateUniqueTokenForSessionId($sessionId),
    ])->save();
  }

  public static function generateUniqueTokenForSessionId($sessionId) {
    return sha1(random_bytes(128).$sessionId);
  }
}
