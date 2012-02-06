<?php

class syncLogger
{
  public function log($message)
  {
    echo sprintf('[%s] %s', date('Y-m-d h:i:s'), $message) . PHP_EOL;
  }
}


