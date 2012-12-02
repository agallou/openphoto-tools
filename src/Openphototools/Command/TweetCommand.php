<?php
namespace Openphototools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class TweetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('tweet:add')
            ->setDescription('Add a Tweet photo to openphoto with the right date and location')
            ->addArgument(
                'tweetid',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tweetId = $input->getArgument('tweetid');
        $output->writeln($tweetId);

        $twitter = $this->getApplication()->getDependance('twitter');

        $ret = $twitter->request('GET', $twitter->url('1.1/statuses/show'), array(
          'id' => $tweetId
        ));
        $data = json_decode($twitter->response['response'], true);
        $date = $data['created_at'];

        $oDate = \DateTime::createFromFormat("D M d H:i:s O Y", $date);
        $output->writeln(sprintf('Created at : %s', $date));

        $text = $data['text'];
        $output->writeln(sprintf('Text : %s', $text));

        $user = $data['user']['name'];
        $output->writeln(sprintf('User : %s', $user));

        if (!isset($data['entities']['media'])) {
          $output->writeln('<error>No media found</error>');
          exit(1);
        }

        foreach ($data['entities']['media'] as $media) {
          $id = $this->uploadMedia($data, $media, $oDate);
          $output->writeln(sprintf('Photo %s uploaded', $id));
        }
    }

    protected function uploadMedia($twitt, $media, $date) {
      $openphoto = $this->getApplication()->getDependance('openphoto');
      $url = $media['media_url'];
      $r = $openphoto->post('/photo/upload.json', array(
        'photo' => base64_encode(file_get_contents($url)),
        'description' => implode(PHP_EOL, array(
          sprintf('Depuis twitter, par %s', $twitt['user']['name']),
          $twitt['text'],
          sprintf("https://twitter.com/%s/status/%s", $twitt['user']['screen_name'], $twitt['id_str']),
        )),
        'tags'      => 'twitter',
        'dateTaken' => $date->getTimestamp(),
      ));
      $result = json_decode($r);
      return $result->result->id;
    }
}
