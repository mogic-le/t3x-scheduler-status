<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Typo3SchedulerMonitoring',
        'Monitor',
        [
            \Mogic\Typo3SchedulerMonitoring\Controller\MonitorController::class => 'list'
        ],
        // non-cacheable actions
        [
            \Mogic\Typo3SchedulerMonitoring\Controller\MonitorController::class => 'list'
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    monitor {
                        iconIdentifier = typo3_scheduler_monitoring-plugin-monitor
                        title = Scheduler Monitoring
                        description = Json response for created Scheduler Jobs
                        tt_content_defValues {
                            CType = list
                            list_type = typo3schedulermonitoring_monitor
                        }
                    }
                }
                show = *
            }
       }'
    );
})();
