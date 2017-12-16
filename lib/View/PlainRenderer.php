<?php

namespace Defiant\View;

class PlainRenderer extends Renderer {
  public function renderFile($template, array $context = array()) {
    ob_start();
    extract($context);
    require($this->getTemplatePath($template));
    $content = ob_get_clean();
    return $content;
  }
}
