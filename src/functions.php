<?php

function getHashs(OpenPhotoOAuth $client, syncLogger $logger, $tags = null)
{
  $hashs    = array();
  $continue = true;
  $page     = 0;
  while($continue)
  {
    $page++;
    //bug un page, the same photos are return even if we change the page number ?
    //to prevent that, we increase the pageSize.
    $params = array('page' => $page, 'pageSize' => 1000);
    if (null !== $tags)
    {
      $params['tags'] = $tags;
    }
    $response  = $client->get("/photos/list.json", $params);
    if (false === $response || !strlen(trim($response)))
    {
      $logger->log('[error] Error getting /photos/list.json');
      break;
    }
    $dResponse = json_decode($response);
    $photos    = $dResponse->result;
    foreach ($photos as $photo)
    {
      $arr = new ArrayObject($photo);
      if (array_key_exists('totalPages', $arr))
      {
        $continue = (bool)($arr['totalPages'] >= $page);
      }
      if (array_key_exists('id', $arr) && array_key_exists('hash', $arr))
      {
        $hashs[$arr['id']] = $arr['hash'];
      }
    }
  }

  return $hashs;
}

function isFileUploaded($exiftran, $hashs, $file)
{
  $tmpFile = '/tmp/openphoto-api-tmp_file';
  copy($file, $tmpFile);
  exec(sprintf('%s -ai %s 2>/dev/null', $exiftran, $tmpFile));
  $sha1 = sha1_file($tmpFile);

  return in_array($sha1, $hashs);
}

function uploadFile(OpenPhotoOAuth $client, syncLogger $logger, $exiftran, $hashs, $file)
{
  $expTitle = explode('/', $file);
  $title    = array_pop($expTitle);
  $dir      = array_pop($expTitle);
  if (substr($dir, 0, 1) == '[' && substr($dir, -1, 1) == ']')
  {

    $mainTag = substr($dir, 14, -1);
  }
  elseif(substr($dir, 4, 1) == '-' && substr($dir, 10, 1) == '_')
  {
    $mainTag = substr($dir, 11);
  }
  else
  {
    $mainTag  = substr($dir, 11);
  }

  $tags     =  $mainTag . ',__synchronised__';
  $title    = $dir . '/' . $title;

  if (!isFileUploaded($exiftran, $hashs, $file))
  {
    $logger->log(sprintf('[uploading] %s', $file));
    $r = $client->post('/photo/upload.json', array(
      'photo' => base64_encode(file_get_contents($file)),
      'title' => $title,
      'tags'  => $tags,
    ));
    $result = json_decode($r);
    if (null === $result)
    {
       $logger->log('[error] Error getting /photo/upload.json');
       return;
    }
    if ($result->code != 201)
    {
      $logger->log(sprintf('[error] [%s] %s', $result->code, $result->message));
    }
  }
  else
  {
    $logger->log(sprintf('[existing] %s', $file));
  }
}

function getPaths($dir)
{
  $paths = array();
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($iterator as $path) {
    if ($path->isDir()) {
      continue;
    }
    $pathName = $path->getRealPath();
    if (strtolower(substr($pathName, -3)) != 'jpg')
    {
      continue;
    }
    $paths[] = $pathName;
  }
  sort($paths);
  return $paths;
}

function getPhoto(OpenPhotoOAuth $client, $id)
{
  $response = $client->get(sprintf('/photo/%s/view.json', $id));
  $photo    = json_decode($response);
  return new ArrayObject($photo->result);
}

function getUserInput($msg)
{
  fwrite(STDOUT, "$msg: ");
  $varin = trim(fgets(STDIN));
  return $varin;
}
