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
use Sto\Tellmatic\Utility\OffsetIterator;
use Tx\Authcode\Domain\Model\AuthCode;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Command controller for filling the solor index queue.
 */
class AuthCodeCommandController extends CommandController
{
    const AUTH_CODE_CONTEXT = 'tellmatic_persistent';

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
    public function injectLogManager(\TYPO3\CMS\Core\Log\LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * Refresh all auth codes.
     *
     * @param int $itemCountPerRun The number of auth codes that are refreshed during one run.
     * @param string $refreshInterval The time betweeen a new synchronization run.
     * @param bool $forceNewRun Ignore refresh interval and force the start of a new run.
     * @throws \Exception
     */
    public function refreshCodesCommand($itemCountPerRun = 20, $refreshInterval = '1 day', $forceNewRun = false)
    {
        /** @var OffsetIterator $offsetIterator */
        $offsetIterator = $this->registry->get(
            __CLASS__,
            'refreshCodesAddressOffset',
            $this->objectManager->get(OffsetIterator::class)
        );
        $lastRunStartTime = $this->registry->get(__CLASS__, 'refreshCodesLastRunStartTime', null);

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

        try {
            $addressCount = $this->getAddressCount();
            $addresses = $this->getAddresses($offsetIterator->current(), $itemCountPerRun);
            $processedItemCount = 0;

            foreach ($addresses as $address) {
                $this->updateCodeForAddress((int)$address['id'], $address['email'], $address['code_external']);
                $processedItemCount++;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            throw $e;
        }

        $offsetIterator->next($processedItemCount, $itemCountPerRun, $addressCount);
        $this->registry->set(__CLASS__, 'refreshCodesAddressOffset', $offsetIterator);

        $progress = $offsetIterator->getProgressInPercent();
        $this->logger->info(
            sprintf(
                'Updated %d address codes, total record count is %d, progress is %f.',
                $processedItemCount,
                $addressCount,
                $progress
            )
        );
        $GLOBALS['tx_tellmatic_task_progress'] = $progress;
    }

    /**
     * Returns the number of existing addresses.
     *
     * @return int
     */
    protected function getAddressCount()
    {
        $addressCountRequest = GeneralUtility::makeInstance(AddressCountRequest::class);

        $result = $this->tellmaticClient->sendAddressCountRequest($addressCountRequest);

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
    protected function getAddresses($offset, $limit)
    {
        $addressSearchRequest = GeneralUtility::makeInstance(AddressSearchRequest::class);
        $addressSearchRequest->setOffset($offset);
        $addressSearchRequest->setLimit($limit);

        $response = $this->tellmaticClient->sendAddressSearchRequest($addressSearchRequest);
        return $response->getAddresses();
    }

    /**
     * Determines if a new run should be started.
     *
     * @param \DateTime $lastRunStartTime
     * @param string $refreshInterval
     * @return bool
     */
    protected function startNewRun($lastRunStartTime, $refreshInterval)
    {
        if (!isset($lastRunStartTime)) {
            return true;
        }

        $nextRunTime = strtotime($refreshInterval, $lastRunStartTime->getTimestamp());
        if (!$nextRunTime) {
            throw new \InvalidArgumentException('The refresh interval for the auth code regeneration is invalid.');
        }

        $this->logger->debug(sprintf('Next run time is: %s, current time is: %s', date('c', $nextRunTime), date('c')));

        if ($nextRunTime > time()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Updates the auth code for the given address.
     *
     * @param int $addressId
     * @param string $email
     * @param string $currentAuthCode
     */
    protected function updateCodeForAddress($addressId, $email, $currentAuthCode)
    {
        if (!empty($currentAuthCode)) {
            $currentAuthCode = $this->authCodeRepository->findOneByAuthCode($currentAuthCode);
            if (isset($currentAuthCode)
                && $currentAuthCode->getIdentifier() === $email
                && $currentAuthCode->getIdentifierContext() === static::AUTH_CODE_CONTEXT
            ) {
                $this->logger->debug(
                    sprintf('Auth code for email %s already exists and matches. Skipping regeneration.', $email)
                );
                return;
            }
        }

        $authCode = $this->objectManager->get(AuthCode::class);
        $this->authCodeRepository->setAuthCodeExpiryTime('+ 1 year');
        $this->authCodeRepository->generateIndependentAuthCode($authCode, $email, static::AUTH_CODE_CONTEXT);

        $setCodeRequest = GeneralUtility::makeInstance(SetCodeRequest::class, $addressId, $authCode->getAuthCode());
        $this->tellmaticClient->sendSetCodeRequest($setCodeRequest);
    }
}
