<?php
defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'scheduler_status', 'Configuration/TypoScript', 'Scheduler status'
);
