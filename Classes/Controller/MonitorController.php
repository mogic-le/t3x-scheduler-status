<?php
namespace Mogic\Typo3SchedulerMonitoring\Controller;



use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
/***
 *
 * This file is part of the "Scheduler Monitoring" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019
 *
 ***/

/**
 * Monitor
 */
class MonitorController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * tokenMiddleware
     *
     * @var \Mogic\Typo3SchedulerMonitoring\Middleware\TokenMiddleware
     */
    protected $tokenMiddleware = null;


    /**
     * sqlService
     *
     * @var \Mogic\Typo3SchedulerMonitoring\Service\SqlService
     */
    protected $sqlService = null;

    /**
     * @param \Mogic\Typo3SchedulerMonitoring\Middleware\TokenMiddleware $tokenMiddleware
     */
    public function injectTokenMiddleware(\Mogic\Typo3SchedulerMonitoring\Middleware\TokenMiddleware $tokenMiddleware)
    {
        $this->tokenMiddleware = $tokenMiddleware;
    }

    /**
     * @param \Mogic\Typo3SchedulerMonitoring\Service\SqlService $sqlService
     */
    public function injectSqlService(\Mogic\Typo3SchedulerMonitoring\Service\SqlService $sqlService)
    {
        $this->sqlService = $sqlService;
    }


    /**
     * action list
     *
     * @return string
     */
    public function listAction()
    {
        if (!$this->tokenMiddleware->checkRequestSecurityToken($this->settings['token'])) {
            // Return message for invalid token or not set
            $response = ['status' => 0, 'response' => "the Security Token included in the Request in Invalid or not set"];
        } else {
            $response = ['status'=>1,'response'=>[]];
            $serverInfo = ['server_time'=>$GLOBALS['EXEC_TIME'],'server_timezone'=>date_default_timezone_get()];

            $schedulerTasks = $this->sqlService->select('tx_scheduler_task', ['*'], ['deleted' => 0]);
            foreach ($schedulerTasks as $schedulerTask) {

                $taskGroupInfo = ['uid'=>$schedulerTask['task_group'],'name'=>''];
                $taskInfo = $this->getTaskInfo($schedulerTask);

                if($schedulerTask['task_group']){
                    $taskGroupInfo['name'] = $this->getTaskGroupInfo($schedulerTask['task_group']);
                }
                $taskStatus = $this->getTaskStatus(unserialize($schedulerTask['lastexecution_failure']));

                $response['response'][]=$this->mapSchedulerTaskFieldsForResponse($schedulerTask,$taskInfo,$taskStatus,$taskGroupInfo,$serverInfo);

            }
        }
        return json_encode($response);
    }


    protected function mapSchedulerTaskFieldsForResponse($schedulerFields,$taskInfo,$taskStatus,$taskGroupInfo,$serverInfo){
        return [
            'uid' => $schedulerFields['uid'],
            'name' => $taskInfo['title'],
            'description' => $taskInfo['description'],
            'disabled' => $schedulerFields['disable'],
            'class' => $taskInfo['class'],
            'task_group' => $taskGroupInfo,
            'extension' => $taskInfo['class'],
            'frequency' => $taskInfo['frequency'],// Integer
            'type' => $taskInfo['type'],
            'parallel_execution' => boolval($taskInfo['multiple']),
            'late'=>$taskInfo['late'],
            'is_running'=>$taskInfo['is_running'],
            'last_execution'=>$schedulerFields['lastexecution'],
            'lastexecution_context'=>$schedulerFields['lastexecution_context'], // CLI or BE
            'next_execution'=>$schedulerFields['nextexecution'],
            'status'=>$taskStatus['status'], // 0 - 1
            'last_execution_data'=>$taskStatus['last_execution_data'],
            'server_time'=>$serverInfo['server_time'],
            'server_timezone'=>$serverInfo['server_timezone'],
            ];
    }

    /**
     * This method fetches a list of all classes that have been registered with the Scheduler
     * For each item the following information is provided, as an associative array:
     *
     * ['extension']	=>	Key of the extension which provides the class
     * ['filename']		=>	Path to the file containing the class
     * ['title']		=>	String (possibly localized) containing a human-readable name for the class
     * ['provider']		=>	Name of class that implements the interface for additional fields, if necessary
     *
     * The name of the class itself is used as the key of the list array
     *
     * @return array List of registered classes
     */
    protected function getRegisteredClasses(): array
    {
        $list = [];
        $title = $description = '';
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'] ?? [] as $class => $registrationInformation) {
            if(isset($registrationInformation['title'])){
                if(substr( $registrationInformation['title'], 0, 4 ) === "LLL:"){
                    $title = LocalizationUtility::translate($registrationInformation['title'],'');
                }else{
                    $title = $registrationInformation['title'];
                }
            }

            if(isset($registrationInformation['description'])){
                if(substr( $registrationInformation['description'], 0, 4 ) === "LLL:"){
                    $description = LocalizationUtility::translate($registrationInformation['description'],'');
                }else{
                    $description = $registrationInformation['description'];
                }
            }
            $list[$class] = [
                'extension' => $registrationInformation['extension'],
                'title' => $title,
                'description' => $description,
                'provider' => $registrationInformation['additionalFields'] ?? '',
            ];
        }
        return $list;
    }

    protected function getTaskInfo($schedulerTask){
        $task = unserialize($schedulerTask['serialized_task_object']);

        $registeredClasses = $this->getRegisteredClasses();
        $schedulerClass = $registeredClasses[get_class($task)];

        $taskInfo = [];
        if (isset($schedulerClass)) {
            $taskInfo['class'] = get_class($task);
            $taskInfo['title'] = $schedulerClass['title'];
            $taskInfo['description'] = $schedulerClass['description'];
            $taskInfo['extension'] = $schedulerClass['extension'];
            $taskInfo['late'] = FALSE;
            $taskInfo['is_running'] = FALSE;
            if (!empty($schedulerTask['serialized_executions'])) {
                $taskInfo['is_running'] = TRUE;
            }
            if (!empty($schedulerTask['nextexecution'])) {
                if ($schedulerTask['nextexecution'] < $GLOBALS['EXEC_TIME']){
                    $taskInfo['late'] = TRUE;
                }
            }

            // Get execution information
            $taskInfo['start'] = (int)$task->getExecution()->getStart();
            $taskInfo['end'] = (int)$task->getExecution()->getEnd();
            $taskInfo['interval'] = $task->getExecution()->getInterval();
            $taskInfo['croncmd'] = $task->getExecution()->getCronCmd();
            $taskInfo['multiple'] = $task->getExecution()->getMultiple();
            if (!empty($taskInfo['interval']) || !empty($taskInfo['croncmd'])) {
                $taskInfo['type'] = 'recurring';
                $taskInfo['frequency'] = $taskInfo['interval'] ?: $taskInfo['croncmd'];
            } else {
                $taskInfo['type'] = 'single';
                $taskInfo['frequency'] = '';
                $taskInfo['end'] = 0;
            }
        }
        return $taskInfo;
    }


    protected function getTaskStatus($lastExecutionData){
        $lastExecutionInfo = ['status'=>0,'last_execution_data'=>[]];
        if($lastExecutionData == NULL){
            $lastExecutionInfo['status'] = 1;
        }else{
            $lastExecutionInfo['status'] = 0;
            $lastExecutionInfo['last_execution_data'] = [
                'code' => $lastExecutionData['code'],
                'message' => $lastExecutionData['message'],
                'file' => $lastExecutionData['file'],
                'line' => $lastExecutionData['line'],
                'trace' => $lastExecutionData['trace'],
            ];
        }
        return $lastExecutionInfo;
    }

    protected function getTaskGroupInfo($taskGroupUid){
        $schedulerTaskGroup = $this->sqlService->select('tx_scheduler_task_group', ['*'], ['uid' => $taskGroupUid]);
        if($schedulerTaskGroup){
            return $schedulerTaskGroup[0]['groupName'];
        }
        return null;
    }

}
