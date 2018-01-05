<?php

namespace Defiant\Model;

interface CustomSaveField {
  public function saveValue(\Defiant\Model $instance);
}
