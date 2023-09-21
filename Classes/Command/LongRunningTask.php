<?php
namespace Mogic\SchedulerStatus\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A scheduler task that will run a long time. Used for testing.
 */
class LongRunningTask extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setDescription('Do nothing for a long time. Used for testing.')
            ->addArgument(
                'seconds',
                InputArgument::OPTIONAL,
                'How many seconds to wait.',
                30
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seconds = $input->getArgument('seconds');
        sleep($seconds);

        return 0;
    }
}
