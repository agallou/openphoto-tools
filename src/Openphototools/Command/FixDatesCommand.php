<?php
namespace Openphototools\Command;

use Openphototools\Command\BaseTagAddCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class FixDatesCommand extends Command
{
    protected $lastPhotoInfos = array();

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('tag:fix-dates')
            ->setDescription('fix image date')
        ;
    }

    protected function getUpdateParameters(InputInterface $input, $idPhoto) {
      $dResponse = $this->getPhotoInfo($idPhoto);
      $result    = $dResponse->result;
      $groups    = $result->groups;
      $dateTaken = $result->dateTaken;

      $exif = exif_read_data($result->pathOriginal);
      $dateTimeOriginal = $exif['DateTimeOriginal'];
      $dt = \DateTime::createFromFormat('Y:m:d H:i:s', $dateTimeOriginal);
      $correctTime = $dt->getTimestamp();

      $this->lastPhotoInfos = array(
        'exif_datetimeoriginal' => $dateTimeOriginal,
        'openphoto_datetime'    => date('Y-m-d H:i:s', $dateTaken),
      );

      return array('dateTaken' => $correctTime);
    }

    protected function getEndLog($input, $position, $idPhoto) {
      return sprintf('%s Fixed date on photo "%s" (%s => %s)', $position, $idPhoto, $this->lastPhotoInfos['openphoto_datetime'],
        $this->lastPhotoInfos['exif_datetimeoriginal']
      );
    }

}

