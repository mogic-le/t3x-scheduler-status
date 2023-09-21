<?php
namespace Mogic\SchedulerStatus\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * JSON API to monitor TYPO3 scheduler tasks status
 */
class MonitorController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected $defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;

    /**
     * Determine the scheduler status
     */
    public function listAction(): void
    {
        if (!$this->verifySecurityToken()) {
            $this->view->setVariablesToRender(['error', 'status']);
            $this->view->assign('status', 'error');
            $this->view->assign('error', 'Wrong API token');
            $this->response->setStatus(403);
            return;
        }

        $titles = $this->getTaskTitles();

        $schedulerTasks = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_scheduler_task')
            ->select(
                ['*'],
                'tx_scheduler_task',
                ['deleted' => 0]
            )->fetchAll();

        $late     = 0;
        $disabled = 0;
        $errored  = 0;

        $tasksInfo = [];
        foreach ($schedulerTasks as $taskRow) {
            $taskObject = unserialize($taskRow['serialized_task_object']);
            $task = [
                'id'          => $taskRow['uid'],
                'name'        => $titles[get_class($taskObject)] ?? null,
                'description' => $taskRow['description'],
                'disabled'    => (bool) $taskRow['disable'],
                'group'       => null,
                'groupid'     => $taskRow['task_group'],

                'late'        => $taskRow['nextexecution'] < $GLOBALS['EXEC_TIME'],
                'running'     => !empty($taskRow['serialized_executions']),

                'last'        => $taskRow['lastexecution_time'] === null
                    ? null : date('c', $taskRow['lastexecution_time']),
                'lasterror'   => null,
                'lastsuccess' => true,

                'next'        => date('c', $taskRow['nextexecution']),
            ];
            if ($taskRow['lastexecution_failure'] !== null
                && $taskRow['lastexecution_failure'] !== ''
            ) {
                $failInfo = unserialize($taskRow['lastexecution_failure']);
                $task['lasterror'] = $failInfo['message'];
                $task['success']   = false;
                $errored++;
            }

            if ($task['disabled']) {
                $disabled++;
                //disabled tasks are not late by definition
                $task['late'] = false;
            }
            if ($task['late']) {
                $late++;
            }

            $tasksInfo[] = $task;
        }
        $this->loadGroupNames($tasksInfo);

        if ($errored > 0) {
            $status = 'error';
        } else if ($late > 0) {
            $status = 'late';
        } else {
            $status = 'ok';
        }

        $response = [
            'status'   => $status,
            'errored'  => $errored,
            'late'     => $late,
            'disabled' => $disabled,
            'tasks'    => $tasksInfo
        ];

        $this->view->assignMultiple($response);
        $this->view->setVariablesToRender(array_keys($response));
    }

    /**
     * Load all titles for the scheduler tasks
     *
     * @return string[] List of task titles. Key is the scheduler task class name
     */
    protected function getTaskTitles(): array
    {
        $tasks  = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'] ?? [];
        $titles = [];
        foreach ($tasks as $class => $registrationInformation) {
            $title = null;
            if (isset($registrationInformation['title'])) {
                if (substr($registrationInformation['title'], 0, 4) === 'LLL:') {
                    $title = LocalizationUtility::translate($registrationInformation['title'], '');
                } else {
                    $title = $registrationInformation['title'];
                }
            }
            $titles[$class] = $title;
        }
        return $titles;
    }

    /**
     * Load "group" names into the tasks
     *
     * @param array $tasksInfo Array of collected tasks with "groupid" column
     */
    protected function loadGroupNames(array &$tasksInfo): void
    {
        $builder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_scheduler_task_group')
            ->createQueryBuilder();
        $groupRows = $builder->select('uid', 'groupName')
            ->from('tx_scheduler_task_group')
            ->where(
                $builder->expr()->in('uid', array_column($tasksInfo, 'groupid'))
            )
            ->execute()
            ->fetchAllKeyValue();

        foreach ($tasksInfo as $key => $task) {
            $tasksInfo[$key]['group'] = $groupRows[$task['groupid']] ?? null;
        }
    }

    /**
     * Check if the configured API token is provided in the request
     */
    public function verifySecurityToken(): bool
    {
        //we use $_GET because the token name is not prefixed with our extension key
        if (isset($_GET['token'])) {
            return $_GET['token'] == $this->settings['token'];
        }

        return false;
    }

}
