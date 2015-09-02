<?php
namespace Sto\Tellmatic\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Offset iterator used to store / handle the offset when looping over records.
 */
class OffsetIterator {

	/**
	 * @var int
	 */
	protected $lastRunRowCount;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @var bool
	 */
	protected $startNewRun = FALSE;

	/**
	 * @var int
	 */
	protected $totalRecordCount;

	/**
	 * Initializes the object manager.
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
	}

	/**
	 * Makes sure that the object manager is not serialized.
	 *
	 * @return array
	 */
	public function __sleep() {
		$classVariables = get_class_vars(get_class($this));
		unset($classVariables['objectManager']);
		return array_keys($classVariables);
	}

	/**
	 * Re-initializes the object manager.
	 */
	public function __wakeup() {
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
	}

	/**
	 * Returns the number of total records.
	 *
	 * @return int
	 */
	public function count() {
		return $this->totalRecordCount;
	}

	/**
	 * Returns the current offset.
	 *
	 * @return int
	 */
	public function current() {
		return $this->offset;
	}

	/**
	 * Returns the current progress in percent.
	 *
	 * @return float
	 */
	public function getProgressInPercent() {
		if ($this->totalRecordCount === 0) {
			return 100;
		}
		if ($this->offset === 0) {
			return 0;
		}
		return min(100 / $this->totalRecordCount * $this->offset, 100);
	}

	/**
	 * This updates the iterator state after a run.
	 *
	 * @param int $rows The number of processed rows in the run.
	 * @param int $itemCountPerRun The limit that is used.
	 * @param int $totalRecords The total number of records available.
	 */
	public function next($rows, $itemCountPerRun, $totalRecords) {
		$this->increaseOffset($itemCountPerRun);
		$this->setTotalRecordCount($totalRecords);
		$this->setLastRunRowCount($rows);
	}

	/**
	 * Resets the state so that a new run is started.
	 */
	public function rewind() {
		$this->offset = 0;
		$this->totalRecordCount = 0;
		$this->startNewRun = TRUE;
	}

	/**
	 * Returns TRUE when there are still items left.
	 */
	public function valid() {
		$startNewRun = FALSE;
		if ($this->startNewRun) {
			$this->startNewRun = FALSE;
			$startNewRun = TRUE;
		}
		return ($startNewRun || $this->offset < $this->totalRecordCount);
	}

	/**
	 * Increases the offset with the given amount.
	 *
	 * @param int $itemsPerRun
	 */
	protected function increaseOffset($itemsPerRun) {
		$this->offset += $itemsPerRun;
	}

	/**
	 * @param int $lastRunRowCount
	 */
	protected function setLastRunRowCount($lastRunRowCount) {

		$lastRunRowCount = (int)$lastRunRowCount;

		// If the last run did not have any rows the stored total record count was larger than the real
		// total record count. Therefor we increase the offset so that we know we are finished.
		if ($lastRunRowCount === 0) {
			$this->offset += 1000;
		}

		$this->lastRunRowCount = $lastRunRowCount;
	}

	/**
	 * Returns the total record count.
	 *
	 * @param int $totalRecordCount
	 */
	protected function setTotalRecordCount($totalRecordCount) {
		$this->totalRecordCount = (int)$totalRecordCount;
	}
}