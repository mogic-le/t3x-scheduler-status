<?php
namespace Mogic\SchedulerStatus\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A scheduler task that will always fail. Used for testing.
 */
class FailTask extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setDescription('Simply fail with an exception. Used for testing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('FailTask fail message', 500);
    }
}
