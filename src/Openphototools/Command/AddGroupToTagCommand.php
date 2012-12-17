<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class AddGroupToTagCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:add-group')
            ->setDescription('Adds a group to a tag')
            ->addArgument(
                'added-group',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function getUpdateParameters(InputInterface $input, $idPhoto) {
      $deps   = $this->getApplication()->getDependancies();
      $client = $deps['openphoto'];
      $logger = $deps['logger'];

      $response  = $client->get(sprintf("/photo/%s/view.json", $idPhoto));
      $dResponse = json_decode($response);
      $groups    = $dResponse->result->groups;

      $groups    = array_merge($groups, array($input->getArgument('added-group')));
      $groups    = array_unique($groups);

      return array('groups' => implode(',', $groups));
    }

    protected function getEndLog($input, $position, $idPhoto) {
      $tagsAdd = $input->getArgument('added-group');
      return sprintf('%s Group(s) "%s" added to photo "%s"', $position, $tagsAdd, $idPhoto);
    }

}

