<?php
namespace Openphototools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SyncFolderCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('folder:sync')
            ->setDescription('Synchronizes a folder with openphoto')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $deps   = $this->getApplication()->getDependancies();
      $client = $deps['openphoto'];
      $logger = $deps['logger'];
      $dir    = $input->getArgument('folder');

      ini_set('memory_limit', '1G');

      $hashs = getHashs($client, $logger);
      $paths = getPaths($dir);
      $cpt   = 0;

      $logger->log(sprintf('%s photos on openphoto', count($hashs)));

      $logger->setTotal(count($paths));
      foreach ($paths as $pathName)
      {
        $cpt++;
        $logger->setPosition($cpt);
        uploadFile($client, $logger, $deps['exiftran.path'], $hashs, $pathName);
      }
      $logger->setTotal(null);

      $newHashs = getHashs($client, $logger);
      $logger->log(sprintf('%s photos added', count($newHashs) - count($hashs)));
      $logger->log(sprintf('%s photos now on openphoto', count($hashs)));
    }

}
