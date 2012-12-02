<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class AddTagToTagCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:add-tag')
            ->setDescription('Adds a tag to a tag')
            ->addArgument(
                'added-tag',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function getUpdateParameters(InputInterface $input) {
      return array('tagsAdd' => $input->getArgument('added-tag'));
    }

    protected function getEndLog($input, $position, $idPhoto) {
      $tagsAdd = $input->getArgument('added-tag');
      return sprintf('%s Tag(s) "%s" added to photo "%s"', $position, $tagsAdd, $idPhoto);
    }

}

