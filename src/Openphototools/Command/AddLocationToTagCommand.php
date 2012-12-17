<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class AddLocationToTagCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:add-location')
            ->setDescription('Adds a location to a tag (use _ for negative values)')
            ->addArgument(
                'latitude',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'longitude',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function getUpdateParameters(InputInterface $input, $idPhoto) {
      return array(
        'longitude' => str_replace('_', '-', $input->getArgument('longitude')),
        'latitude'  => str_replace('_', '-', $input->getArgument('latitude')),
      );
    }

    protected function getEndLog($input, $position, $idPhoto) {
      $longitude = str_replace('_', '-', $input->getArgument('longitude'));
      $latitude  = str_replace('_', '-', $input->getArgument('latitude'));
      return sprintf('%a latitude "%s" and longitude %s added to photo "%s"', $position, $latitude, $longitude, $idPhoto);
    }

}

