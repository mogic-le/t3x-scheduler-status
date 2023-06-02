<?php
use Mogic\SchedulerStatus\Task\FailTask;

defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'SchedulerStatus',
    'Monitor',
    [
        \Mogic\SchedulerStatus\Controller\MonitorController::class => 'list'
    ],
    // non-cacheable actions
    [
        \Mogic\SchedulerStatus\Controller\MonitorController::class => 'list'
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][FailTask::class] = [
    'extension'   => 'scheduler_status',
    'title'       => 'Scheduler test fail task',
    'description' => 'Always fails when executed. Used for testing.',
];
