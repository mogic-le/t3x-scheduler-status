<?php

namespace Mogic\SchedulerMonitoring\Service;


use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Database\ConnectionPool;

class SqlService{

    public function select(string $table, array $properties, array $where){
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
            ->select(
                $properties,
                $table,
                $where
            )->fetchAll();

    }
}
