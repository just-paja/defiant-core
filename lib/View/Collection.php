<?php

namespace Defiant\View;

abstract class Collection extends \Defiant\View\Resource {
  protected $pageNumber = 0;
  protected $pageSize = 20;
  protected $totalElements = 0;

  public function __construct(\Defiant\Runner $runner = null, \Defiant\Http\Request $request = null) {
    parent::__construct($runner, $request);
    $this->pageNumber = $request->query('pageNumber', 0);
    $this->pageSize = $request->query('pageSize', 20);

    if ($this->pageNumber < 0) {
      throw new \Defiant\Http\BadRequest('Page cannot be less than zero');
    }

    if ($this->pageSize <= 0) {
      throw new \Defiant\Http\BadRequest('Page size must be greater than zero');
    }
  }

  public function render(array $context = []) {
    return parent::render([
      'embedded' => [
        'data' => $context,
      ],
      'page' => $this->getPageInfo(),
    ]);
  }

  public function getCollectionResource() {
    return $this->getResource()->limit($this->pageNumber * $this->pageSize, $this->pageSize);
  }

  public function getPageInfo() {
    $this->totalElements = $this->getTotal();
    $firstPage = 0;
    if ($this->pageSize >= $this->totalElements) {
      $lastPage = $firstPage;
    } else {
      $lastPage = floor($this->totalElements / $this->pageSize) - 1;
    }
    $this->addLink('first', $this->path.'?pageNumber='.$firstPage.'&pageSize='.$this->pageSize);
    $this->addLink('last', $this->path.'?pageNumber='.$lastPage.'&pageSize='.$this->pageSize);
    if ($this->pageNumber < $lastPage) {
      $this->addLink('next', $this->path.'?pageNumber='.($this->pageNumber + 1).'&pageSize='.$this->pageSize);
    }
    return [
      'number' => $this->pageNumber,
      'size' => $this->pageSize,
      'totalElements' => $this->totalElements,
    ];
  }

  public function getTotal() {
    return $this->getResource()->count();
  }

  public function view() {
    $resource = $this->getCollectionResource();
    if ($resource) {
      $data = $resource->all();
    } else {
      $data = [];
    }
    return $this->render($data);
  }

}
