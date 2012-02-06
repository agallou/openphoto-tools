<?php

class OpenPhotoOAuthFactory
{
  public function create(array $config)
  {
    return new OpenPhotoOAuth($config['host'], $config['consumer_key'], $config['consumer_secret'], $config['token'], $config['token_secret']);
  }
}

