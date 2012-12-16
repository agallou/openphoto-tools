<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class TagRemoveCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:remove')
            ->setDescription('Removes a tag')
            ->addArgument(
                'removed-tag',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function getUpdateParameters(InputInterface $input) {
      return array(
        'tagsRemove' => $input->getArgument('removed-tag'),
      );
    }

    protected function getEndLog($input, $position, $idPhoto) {
      $removedTag  = $input->getArgument('removed-tag');
      return sprintf('%s Tag(s) "%s" removed on photo "%s"', $position, $removedTag, $idPhoto);
    }

}

