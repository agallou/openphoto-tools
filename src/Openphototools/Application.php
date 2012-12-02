<?php

namespace Openphototools;

use Symfony\Component\Console\Application as BaseApplication;


class Application extends BaseApplication
{

  protected $dependancies;

  public function setDependancies($deps) {
    $this->dependancies = $deps;

    return $this;
  }

  public function getDependancies() {
    return $this->dependancies;
  }

  public function getDependance($name) {
    return $this->dependancies[$name];
  }

}
