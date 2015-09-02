<?php
namespace Sto\Tellmatic\Command;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Tellmatic\Request\AddressCountRequest;
use Sto\Tellmatic\Tellmatic\Request\AddressSearchRequest;
use Sto\Tellmatic\Tellmatic\Request\SetCodeRequest;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Utility\OffsetIterator;
use Tx\Authcode\Domain\Model\AuthCode;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Command controller for filling the solor index queue.
 */
class AuthCodeCommandController extends CommandController {

	/**
	 * @inject
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRepository
	 */
	protected $authCodeRepository;

	/**
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $logger;

	/**
	 * @inject
	 * @var \TYPO3\CMS\Core\Registry
	 */
	protected $registry;

	/**
	 * @inject
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 */
	protected $tellmaticClient;

	/**
	 * @param \TYPO3\CMS\Core\Log\LogManager $logManager
	 */
	public function injectLogManager(\TYPO3\CMS\Core\Log\LogManager $logManager) {
		$this->logger = $logManager->getLogger(__CLASS__);
	}

	/**
	 * Refresh all auth codes.
	 *
	 * @param int $itemCountPerRun The number of items that are added to the queue in one run.
	 * @param string $refreshInterval
	 * @param bool $forceNewRun
	 */
	public function refreshCodesCommand($itemCountPerRun = 2, $refreshInterval = '2 minutes', $forceNewRun = FALSE) {

		/** @var OffsetIterator $offsetIterator */
		$offsetIterator = $this->registry->get(__CLASS__, 'refreshCodesAddressOffset', $this->objectManager->get(OffsetIterator::class));
		$lastRunStartTime = $this->registry->get(__CLASS__, 'refreshCodesLastRunStartTime', NULL);

		if (!$offsetIterator->valid()) {
			if ($forceNewRun || $this->startNewRun($lastRunStartTime, $refreshInterval)) {
				$this->logger->info('Starting new address code sync run.');
				$this->registry->set(__CLASS__, 'refreshCodesLastRunStartTime', new \DateTime());
				$offsetIterator->rewind();
			} else {
				$this->logger->info('No more address records available for synchronization.');
				$GLOBALS['tx_tellmatic_task_progress'] = 100;
				return;
			}
		}

		$addressCount = $this->getAddressCount();
		$addresses = $this->getAddresses($offsetIterator->current(), $itemCountPerRun);
		$processedItemCount = 0;

		foreach ($addresses as $address) {
			$this->updateCodeForAddress((int)$address['id'], $address['email'], $address['code_external']);
			$processedItemCount++;
		}

		$offsetIterator->next($processedItemCount, $itemCountPerRun, $addressCount);
		$this->registry->set(__CLASS__, 'refreshCodesAddressOffset', $offsetIterator);

		$progress = $offsetIterator->getProgressInPercent();
		$this->logger->info(sprintf('Updated %d address codes, total record count is %d, progress is %f.', $processedItemCount, $addressCount, $progress));
		$GLOBALS['tx_tellmatic_task_progress'] = $progress;
	}

	/**
	 * Returns the number of existing addresses.
	 *
	 * @return int
	 */
	protected function getAddressCount() {

		$addressCountRequest = GeneralUtility::makeInstance(AddressCountRequest::class);

		$result = $this->tellmaticClient->sendAddressCountRequest($addressCountRequest);

		if (!$result->getSuccess()) {
			$errorMessage = $this->getErrorMessage('Address count failed', $result);
			$this->logger->error($errorMessage);
			throw new \RuntimeException($errorMessage);
		}

		$addressCount = $result->getAddressCount();

		$this->logger->info(sprintf('Found %d address records in the database.', $addressCount));

		return $addressCount;
	}

	/**
	 * Returns all addresses from the given offset with the given limit.
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	protected function getAddresses($offset, $limit) {

		$addressSearchRequest = GeneralUtility::makeInstance(AddressSearchRequest::class);
		$addressSearchRequest->setOffset($offset);
		$addressSearchRequest->setLimit($limit);

		$result = $this->tellmaticClient->sendAddressSearchRequest($addressSearchRequest);

		if ($result->getSuccess()) {
			return $result->getAddresses();
		} else {
			$errorMessage = $this->getErrorMessage('Fetching address records failed', $result);
			$this->logger->error($errorMessage);
			throw new \RuntimeException($errorMessage);
		}
	}

	/**
	 * Builds an error message of the given Tellmatic response.
	 *
	 * @param string $prefix
	 * @param TellmaticResponse $result
	 * @return string
	 */
	protected function getErrorMessage($prefix, $result) {
		return $prefix . ': ' . $result->getFailureReason() . ' (' . $result->getFailureCode() . ')';
	}

	/**
	 * Determines if a new run should be started.
	 *
	 * @param \DateTime $lastRunStartTime
	 * @param string $refreshInterval
	 * @return bool
	 */
	protected function startNewRun($lastRunStartTime, $refreshInterval) {

		if (!isset($lastRunStartTime)) {
			return TRUE;
		}

		$nextRunTime = strtotime($refreshInterval, $lastRunStartTime->getTimestamp());
		if (!$nextRunTime) {
			throw new \InvalidArgumentException('The refresh interval for the auth code regeneration is invalid.');
		}

		$this->logger->debug(sprintf('Next run time is: %s, current time is: %s', date('c', $nextRunTime), date('c')));

		if ($nextRunTime > time()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Updates the auth code for the given address.
	 *
	 * @param int $addressId
	 * @param string $email
	 * @param string $currentAuthCode
	 */
	protected function updateCodeForAddress($addressId, $email, $currentAuthCode) {

		$authCodeContext = 'tellmatic_persistent';

		if (!empty($currentAuthCode)) {
			$currentAuthCode = $this->authCodeRepository->findOneByAuthCode($currentAuthCode);
			if (
				isset($currentAuthCode)
				&& $currentAuthCode->getIdentifier() === $email
				&& $currentAuthCode->getIdentifierContext() === $authCodeContext
			) {
				$this->logger->debug(sprintf('Auth code for email %s already exists and matches. Skipping regeneration.', $email));
				return;
			}
		}

		$authCode = $this->objectManager->get(AuthCode::class);
		$this->authCodeRepository->setAuthCodeExpiryTime('+ 1 year');
		$this->authCodeRepository->generateIndependentAuthCode($authCode, $email, $authCodeContext);

		$setCodeRequest = GeneralUtility::makeInstance(SetCodeRequest::class, $addressId, $authCode->getAuthCode());
		$result = $this->tellmaticClient->sendSetCodeRequest($setCodeRequest);
		if (!$result->getSuccess()) {
			$errorMessage = $this->getErrorMessage('Setting auth code for addres ' . $email . ' failed', $result);
			$this->logger->error($errorMessage);
			throw new \RuntimeException($errorMessage);
		}
	}
}