<?php

class syncLogger
{

  protected $total    = null;
  protected $position = null;

  public function setTotal($total)
  {
    $this->total = $total;
    
    return $this;
  }

  public function setPosition($position)
  {
    $this->position = $position;
  
    return $position;
  }

  public function log($message)
  {
    $message = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message) . PHP_EOL;
    if (null !== $this->total && null !== $this->position)
    {
      $posStr  = sprintf('[%s/%s] ', str_pad($this->position, strlen($this->total), '0', STR_PAD_LEFT), $this->total);
      $message = $posStr . $message;
    }
    echo $message;
  }

}


