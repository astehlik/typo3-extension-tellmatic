<?php

namespace Sto\Tellmatic\Scheduler;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Scheduler\Task as ExtbaseTask;
use TYPO3\CMS\Scheduler\ProgressProviderInterface;

/**
 * This task behaves like the default Exbase command task but also provides
 * the progress of the current task.
 */
class ExtbaseProgressingTask extends ExtbaseTask implements ProgressProviderInterface
{
    /**
     * If an Exception occured during the last execute() call this variable contains
     * a reference to the catched Exception.
     *
     * @var \Exception
     */
    protected $lastException;

    /**
     * Stores the current process in percent.
     *
     * @var float
     */
    protected $progress = 100;

    /**
     * We unset the last Exception because it can contain too much data that causes the serialization to fail.
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        $index = array_search('lastException', $properties);
        unset($properties[$index]);
        return $properties;
    }

    /**
     * Function execute from the Scheduler
     *
     * @return boolean TRUE on successful execution, FALSE on error
     */
    public function execute()
    {
        $this->lastException = null;

        $result = parent::execute();

        // If progress is provided by the executed task save it.
        if (isset($GLOBALS['tx_tellmatic_task_progress'])) {
            $this->progress = (float)$GLOBALS['tx_tellmatic_task_progress'];
            unset($GLOBALS['tx_tellmatic_task_progress']);
        }

        $schedulerAvailable = isset($this->scheduler);

        // We need to save the task to persist the progress.
        $this->scheduler->saveTask($this);

        // Since saveTask unsets the scheduler we need to re-initialize it
        // so that logging still works.
        if ($schedulerAvailable) {
            $this->setScheduler();
        }

        return $result;
    }

    /**
     * @return \Exception
     */
    public function getLastException()
    {
        return $this->lastException;
    }

    /**
     * Gets the progress of a task.
     *
     * @return float Progress of the task as a float value in percent.
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Stores the given Exception in the $lastException class variable and calls the parent method.
     *
     * @param \Exception $e
     */
    protected function logException(\Exception $e)
    {
        parent::logException($e);
        $this->lastException = $e;
    }
}
