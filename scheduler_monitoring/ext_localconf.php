<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SchedulerMonitoring',
        'Monitor',
        [
            \Mogic\SchedulerMonitoring\Controller\MonitorController::class => 'list'
        ],
        // non-cacheable actions
        [
            \Mogic\SchedulerMonitoring\Controller\MonitorController::class => 'list'
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    monitor {
                        iconIdentifier = scheduler_monitoring-plugin-monitor
                        title = LLL:EXT:scheduler_monitoring/Resources/Private/Language/locallang_db.xlf:tx_scheduler_monitoring_monitor.name
                        description = LLL:EXT:scheduler_monitoring/Resources/Private/Language/locallang_db.xlf:tx_scheduler_monitoring_monitor.description
                        tt_content_defValues {
                            CType = list
                            list_type = schedulermonitoring_monitor
                        }
                    }
                }
                show = *
            }
       }'
    );
})();
