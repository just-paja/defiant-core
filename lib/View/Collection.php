<?php

namespace Defiant\View;

abstract class Collection extends \Defiant\View\Resource {
  protected $pageNumber = 0;
  protected $pageSize = 20;
  protected $totalElements = 0;

  public function render($context) {
    return parent::render([
      'embedded' => [
        'data' => $context,
      ],
      'page' => $this->getPageInfo(),
    ]);
  }

  public function getPageInfo() {
    return [
      'number' => $this->pageNumber,
      'size' => $this->pageSize,
      'totalElements' => $this->totalElements,
    ];
  }

  public function view() {
    $resource = $this->getResource();
    if ($resource) {
      $data = $resource->all();
    } else {
      $data = [];
    }
    return $this->render($data);
  }

}
