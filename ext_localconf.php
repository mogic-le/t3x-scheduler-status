<?php
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
