<?php
$config                    = array();
$config['exiftran']        = dirname(__FILE__) . '/../vendor/exiftran/exiftran';
$config['consumer_key']    = getenv('consumerKey');
$config['consumer_secret'] = getenv('consumerSecret');
$config['token']           = getenv('token');
$config['token_secret']    = getenv('tokenSecret');
$config['host']            = 'photos.adrien-gallou.fr';

