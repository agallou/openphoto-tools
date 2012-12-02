<?php
namespace Openphototools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


abstract class BaseTagAddCommand extends Command
{

    protected function configure()
    {
        $this
            ->addArgument(
                'existing-tag',
                InputArgument::REQUIRED
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $deps   = $this->getApplication()->getDependancies();
      $client = $deps['openphoto'];
      $logger = $deps['logger'];

      $tag          = $input->getArgument('existing-tag');
      $hashs        = getHashs($client, $logger, $tag);
      $total        = count($hashs);
      $cpt          = 0;
      $updated      = 0;

      $logger->log(sprintf('%s photos tagged with "%s"', count($hashs), $tag));

      foreach (array_keys($hashs) as $idPhoto)
      {
        $cpt++;
        $position  = sprintf('[%s/%s]', str_pad($cpt,strlen($total), '0', STR_PAD_LEFT), $total);
        $this->getUpdateParameters($input);
        $response  = $client->post(sprintf("/photo/%s/update.json", $idPhoto), $this->getUpdateParameters($input));
        $dResponse = json_decode($response);
        $logger->log(sprintf('%s Updating photo "%s"...', $position, $idPhoto));
        if (false !== $dResponse)
        {
          if (false === $dResponse->result)
          {
            $logger->log(sprintf('%s [error] Error updating photo', $position));
            continue;
          }
        }
        $updated++;
        $logger->log($this->getEndLog($input, $position, $idPhoto));
      }
      $logger->log(sprintf('%s photos updated', $cpt++));
    }

    abstract protected function getUpdateParameters(InputInterface $input);
    abstract protected function getEndLog($input, $position, $idPhoto);
}
