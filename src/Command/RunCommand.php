<?php

namespace Koalamon\KoalaStats\Command;

use Cilex\Provider\Console\Command;
use GuzzleHttp\Client;
use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Reporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command 
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run command and send result to koalamon')
            ->addArgument('shellCommand', InputArgument::REQUIRED, 'The command line command')
            ->addArgument('eventIdentifier', InputArgument::REQUIRED, 'The event identifer')
            ->addArgument('projectApiKey', InputArgument::REQUIRED, 'The project api key')
            ->addArgument('systemIdentifier', InputArgument::REQUIRED, 'The system identifier')
            ->addArgument('toolIdentifier', InputArgument::REQUIRED, 'The tool identifier')
            ->addOption('koalamonServer', 's', InputOption::VALUE_OPTIONAL, 'The koalamon server', 'https://webhook.koalamon.com')
            ->addOption('eventUrl', 'u', InputOption::VALUE_OPTIONAL, 'An url representing this event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('shellCommand');
        $identifier = $input->getArgument('eventIdentifier');
        $projectApiKey = $input->getArgument('projectApiKey');
        $systemIdentifier = $input->getArgument('systemIdentifier');
        $toolIdentifier = $input->getArgument('toolIdentifier');
        $url = $input->getOption('eventUrl');
        $koalamonServer = $input->getOption('koalamonServer');

        exec($command, $value, $returnCode);

        if ($returnCode == 0) {
            $reporter = new Reporter('', $projectApiKey, new Client(), $koalamonServer);
            $event = new Event($identifier, $systemIdentifier, Event::STATUS_SUCCESS, $toolIdentifier, '', (int)$value[0], $url);

            $reporter->sendEvent($event);

            $output->writeln("\n  <info>Stats successfully send.</info>\n");
        }
    }
}
