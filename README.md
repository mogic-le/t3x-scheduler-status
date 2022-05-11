# TYPO3 Extension typo3_scheduler_monitoring

Typo3 Scheduler Monitoring is easy to use extension for TYPO3 with one feature - list all created Scheduler jobs in JSON format


## 2. Installation

Quick guide:
- Install this extension - e.g. `composer require mogic/typo3-scheduler-monitoring`
- Add a static typoscript template to your root template
- Create GET request with params "type" and "token"
- Request: /?type=9251355215&token={$plugin.tx_typo3schedulermonitoring_monitor.settings.token}
- That's all, you can view the result in the frontend
