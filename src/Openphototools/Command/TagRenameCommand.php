<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class TagRenameCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:rename')
            ->setDescription('Rename a tag')
            ->addArgument(
                'new-tag',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function getUpdateParameters(InputInterface $input) {
      return array(
        'tagsAdd'    => $input->getArgument('new-tag'),
        'tagsRemove' => $input->getArgument('existing-tag'),
      );
    }

    protected function getEndLog($input, $position, $idPhoto) {
      $oldTag = $input->getArgument('existing-tag');
      $newTag = $input->getArgument('new-tag');
      return sprintf('%s Tag(s) "%s" renamed to "%s" on photo "%s"', $position, $oldTag, $newTag, $idPhoto);
    }

}

