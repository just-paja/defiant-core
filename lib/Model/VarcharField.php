<?php

namespace Defiant\Model;

class VarcharField extends Field {
  protected $length = 255;
  const dbType = 'VARCHAR';
}
