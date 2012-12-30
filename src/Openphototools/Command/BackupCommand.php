<?php
namespace Openphototools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class BackupCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('tag:backup')
            ->setDescription('Retrieves photos and optionally create an iso')
            ->addArgument(
                'tag',
                InputArgument::REQUIRED
              )
            ->addArgument(
                'dir',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $deps   = $this->getApplication()->getDependancies();
      $client = $deps['openphoto'];
      $logger = $deps['logger'];
      $cpt    = 0;

      $tags = $input->getArgument('tag');
      $dir  = $input->getArgument('dir');

      $exportContent = "Date:" . date('Y-m-d H:i:s') . PHP_EOL . 'Tags:' . $tags;

      $dir 	   = $dir . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s');

      mkdir($dir, 0777, true);

      $photosDir = $dir . DIRECTORY_SEPARATOR . 'photos/';
      mkdir($photosDir);

      $listDir = $dir . DIRECTORY_SEPARATOR . 'list/';
      mkdir($listDir);

      $isoDir = $dir . DIRECTORY_SEPARATOR . 'iso/';
      mkdir($isoDir);


      file_put_contents($photosDir . 'infos.txt', $exportContent);


        $hashs    = array();
        $continue = true;
        $page     = 0;
        while($continue)
        {
          $page++;
          //bug un page, the same photos are return even if we change the page number ?
          //to prevent that, we increase the pageSize.
          $params = array('page' => $page, 'pageSize' => 2500);
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
            $arr = new \ArrayObject($photo);
            if (array_key_exists('totalPages', $arr))
            {
              $continue = (bool)($arr['totalPages'] >= $page);
            }
            if (array_key_exists('id', $arr) && array_key_exists('hash', $arr))
            {
      	      $pathOriginal = $arr['pathOriginal'];
      	      $dateTaken    = $arr['dateTaken'];
      	      $pTags        = implode(',', $arr['tags']);
      	      $title        = str_replace('/', '_', $arr['title']);

      	      $folder   = $photosDir . '/' . date('Y-m-d', $dateTaken) . '/';
      	      if (!is_dir($folder))
      	      {
      		      mkdir($folder);
      		      $logger->log(sprintf('created %s', $folder));
      	      }
              $filename     = sprintf('%s_%s.jpg', date('Y-m-d_H-i-s', $dateTaken), $arr['id']);
              $filenameJson     = sprintf('%s_%s.json', date('Y-m-d_H-i-s', $dateTaken), $arr['id']);

              $filepath = $folder . $filename;
              $filepathJson = $folder . $filenameJson;
              $logger->log(sprintf('created %s', $filepath));
      	      file_put_contents($filepath, file_get_contents($pathOriginal));
              $logger->log(sprintf('created %s', $filepathJson));
      	      file_put_contents($filepathJson, json_encode($photo));

            }
          }
          $continue = false;
        }


        $logger->setTotal(null);



        $cmd = sprintf('cd %s; dirsplit -s 700M -e2 %s', escapeshellarg($listDir), escapeshellarg(realpath($photosDir)));
        echo $cmd . PHP_EOL;
        passthru($cmd);
        echo PHP_EOL;

        foreach (glob($listDir . '/vol_*') as $dir)
        {
      	  $cmd = sprintf("mkisofs -o %s.iso -D -r --joliet-long -graft-points -path-list %s", $isoDir . '/' . basename($dir), $dir);
      	  echo $cmd . PHP_EOL;
      	  passthru($cmd);
        }

        passthru(sprintf('rm -rf %s', escapeshellarg($photosDir)));
        passthru(sprintf('rm -rf %s', escapeshellarg($listDir)));
    }

}
