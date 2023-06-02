<?php
namespace Mogic\SchedulerStatus\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A scheduler task that will always fail. Used for testing.
 */
class FailTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    public function execute()
    {
        throw new \Exception('FailTask fail message', 500);
    }
}
