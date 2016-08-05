<?php

namespace Koalamon\KoalaStats\Command;

use GuzzleHttp\Client;
use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Reporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectCommand extends \Cilex\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('collect')
            ->setDescription('continuous run command and send avarage to koalamon ')
            ->addArgument('shellCommand', InputArgument::REQUIRED, 'The command line command')
            ->addArgument('eventIdentifier', InputArgument::REQUIRED, 'The event identifer')
            ->addArgument('projectApiKey', InputArgument::REQUIRED, 'The project api key')
            ->addArgument('systemIdentifier', InputArgument::REQUIRED, 'The system identifier')
            ->addArgument('toolIdentifier', InputArgument::REQUIRED, 'The tool identifier')
            ->addArgument('interval', InputArgument::REQUIRED, 'The interval')
            ->addArgument('collectionSize', InputArgument::REQUIRED, 'The collectionSize')
            ->addOption('koalamonServer', 's', InputOption::VALUE_OPTIONAL, 'The koalamon server', 'https://monitor.koalamon.com')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'The event message', '')
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


        $message = $input->getOption('message');
        $interval = $input->getArgument('interval');
        $collectionSize = $input->getArgument('collectionSize');

        $index = 0;
        $values = [];

        while (true) {
            exec($command, $outputArray, $returnCode);

            if ($returnCode != 0) {
                exit($returnCode);
            }

            $values[$index] = $outputArray[0];

            if ($index == $collectionSize - 1) {
                $index = 0;
                $sum = array_sum($values);
                $average = $sum / $collectionSize;

                $reporter = new Reporter('', $projectApiKey, new Client(), $koalamonServer);
                $eventMessage = str_replace('#average#', $average, $message);
                $event = new Event($identifier,
                    $systemIdentifier,
                    Event::STATUS_SUCCESS,
                    $toolIdentifier,
                    $eventMessage,
                    $average,
                    $url);

                $reporter->sendEvent($event);
                $output->writeln("\n  <info>Status successfully send (average: " . $average . ").</info>\n");
            }

            $output->writeln("<info>Current value: " . $values[$index] . "</info>");

            sleep($interval);
            $index++;
        }
    }
}
